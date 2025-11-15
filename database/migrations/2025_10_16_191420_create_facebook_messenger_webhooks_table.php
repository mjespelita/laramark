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
        Schema::create('facebook_messenger_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('user_app_id')->nullable();
            $table->string('user_id')->nullable();
            $table->string('psid')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facebook_messenger_webhooks');
    }
};
