<?php

use App\Models\Chatattachments;
use App\Models\Chats;
use App\Models\Messages;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// backup

Artisan::command('backup', function () {
    $this->info('📦 Starting backup...');

    // Backup directory
    $backupPath = public_path('backup');

    // Create backup folder if it doesn't exist
    if (!File::exists($backupPath)) {
        File::makeDirectory($backupPath, 0755, true);
    } else {
        // Delete all files in the backup folder
        $files = File::files($backupPath);
        foreach ($files as $file) {
            File::delete($file);
        }
        $this->info('🧹 Old backup files deleted.');
    }

    // Create SQL file
    $sqlFile = $backupPath . '/database.sql';
    $tables = DB::select('SHOW TABLES');
    $dbName = env('DB_DATABASE');
    $key = "Tables_in_$dbName";

    $sqlDump = '';

    foreach ($tables as $table) {
        $tableName = $table->$key;
        $create = DB::select("SHOW CREATE TABLE `$tableName`")[0]->{'Create Table'};
        $sqlDump .= "\n\n-- Table structure for `$tableName`\n\n$create;\n";

        $rows = DB::table($tableName)->get();
        foreach ($rows as $row) {
            $columns = array_map(fn($v) => "`$v`", array_keys((array)$row));
            $values = array_map(fn($v) => is_null($v) ? 'NULL' : DB::getPdo()->quote($v), array_values((array)$row));
            $sqlDump .= "INSERT INTO `$tableName` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
        }
    }

    File::put($sqlFile, $sqlDump);

    // Create zip
    $timestamp = now()->format('Y-m-d_H-i-s');
    $zipPath = $backupPath . "/backup-{$timestamp}.zip";

    $zip = new \ZipArchive;
    if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
        $this->info("📁 Zipping files...");

        $allFilesAndDirs = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(base_path(), RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($allFilesAndDirs as $file) {
            if (strpos($file->getRealPath(), base_path('vendor')) !== false) {
                continue;
            }

            $relativePath = ltrim(str_replace(base_path(), '', $file->getRealPath()), '/\\');
            $zip->addFile($file->getRealPath(), $relativePath);
        }

        // Include the generated SQL
        $zip->addFile($sqlFile, 'database.sql');

        $zip->close();
        $this->info("✅ Backup complete: {$zipPath}");
    } else {
        $this->error('❌ Failed to create zip file.');
    }

    File::delete($sqlFile);
    return 0;
});

// scheduled = run every minute on CRON and set time

Artisan::command('sched', function () {

    // Get the current time
    $currentTime = Carbon::now();

    // If today is Sunday, do not proceed
    if ($currentTime->isSunday()) {
        $this->info('Today is Sunday. Scheduled tasks will not run.');
        return;
    }

    // Check if the time is exactly 8:00 AM
    if ($currentTime->format('H:i') == '08:00') {


        $this->info('Running scheduled tasks at 8:00 AM...');
    } else {
        $this->info('It is not the scheduled time to run the task.');
    }

})->purpose('Scheduled Tasks')->dailyAt('08:00'); // optional ->dailyAt('08:00') if not running on CRON

Artisan::command('file-cleanup', function () {

    $now = Carbon::now();
    $deletedChatIds = [];

    $fileAttachments = Chatattachments::all();

    foreach ($fileAttachments as $fileAttachment) {
        $created = Carbon::parse($fileAttachment->created_at);
        $daysOld = $created->diffInDays($now);

        $file = 'public/storage/' . $fileAttachment->path;

        if (
            $daysOld >= 31 &&
            $fileAttachment->path != 'files/chat_uploads/file-placeholder.jpg' &&
            file_exists($file)
        ) {
            unlink($file);
            $this->info("Deleted: {$file}");

            // Track which chat this belongs to
            $deletedChatIds[] = $fileAttachment->chats_id;

            // Update to placeholder
            $fileAttachment->update([
                'path' => 'files/chat_uploads/file-placeholder.jpg'
            ]);
        }
    }

    // Send a message only to chats that had deleted files
    $uniqueChatIds = array_unique($deletedChatIds);

    foreach ($uniqueChatIds as $chatId) {
        $chat = Chats::find($chatId);

        if ($chat) {
            Messages::create([
                'message' => "We're sorry to inform you that files shared in chat are automatically deleted after 30 days as part of our retention policy—please download important files before they expire. \n\n- System Administrator",
                'chats_id' => $chat->id,
                'chats_users_id' => $chat->users_id,
                'users_id' => 0,
                'has_attachments' => 0,
            ]);
        }
    }

    $this->info('File cleanup completed.');
});
