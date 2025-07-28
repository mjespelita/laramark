<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

// php artisan release:push v1.2.3 "Hotfix Patch" "Fixed login bug and optimized queries." --m="hotfix: login issue"

class GitRelease extends Command
{
    protected $signature = 'release:push
                            {tag : Version tag (e.g. v1.0.0)}
                            {title : Release title}
                            {content : Release notes}
                            {--m=Auto commit message}';

    protected $description = 'Push git changes and create a release tag with notes';

    public function handle()
    {
        $tag = $this->argument('tag');
        $title = $this->argument('title');
        $content = $this->argument('content');
        $commitMsg = $this->option('m') ?? "Release $tag";

        $this->info("Committing changes...");
        shell_exec("git add .");
        shell_exec("git commit -m \"$commitMsg\"");
        shell_exec("git push origin master");

        $this->info("Creating annotated tag...");
        shell_exec("git tag -a $tag -m \"$title\n\n$content\"");
        shell_exec("git push origin $tag");

        $this->info("✅ Release $tag created and pushed.");
    }
}
