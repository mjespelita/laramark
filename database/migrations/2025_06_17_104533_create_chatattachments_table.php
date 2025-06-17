<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chatattachments', function (Blueprint $table) {
            $table->id();
$table->integer('chats_id');
$table->integer('messages_id');
$table->string('original_name');
$table->string('stored_as');
$table->string('path');
$table->boolean('isTrash')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatattachments');
    }
};
