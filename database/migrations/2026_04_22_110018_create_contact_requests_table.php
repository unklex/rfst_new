<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_requests', function (Blueprint $table): void {
            $table->id();

            // Contact payload
            $table->string('name', 120);
            $table->string('phone', 32);
            $table->string('email', 191)->nullable();
            $table->text('message')->nullable();

            // Consent (152-ФЗ audit trail)
            $table->boolean('consent_accepted')->default(false);
            $table->string('consent_text_hash', 64)->nullable(); // sha256

            // Tracking
            $table->json('utm')->nullable();
            $table->string('referer_url', 512)->nullable();
            $table->string('landing_url', 512)->nullable();
            $table->string('ip_hash', 64)->nullable(); // sha256
            $table->string('user_agent', 512)->nullable();

            // Workflow
            $table->string('status', 20)->default('new'); // new | handled
            $table->timestamp('handled_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_requests');
    }
};
