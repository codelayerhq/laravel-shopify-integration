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
        Schema::create('shopify_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->string('shop');
            $table->boolean('is_online');
            $table->string('state');
            $table->string('scope')->nullable();
            $table->string('access_token')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->string('user_first_name')->nullable();
            $table->string('user_last_name')->nullable();
            $table->string('user_email')->nullable();
            $table->boolean('user_email_verified')->nullable();
            $table->boolean('account_owner')->nullable();
            $table->string('locale')->nullable();
            $table->boolean('collaborator')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopify_sessions');
    }
};
