<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contact_requests', function (Blueprint $table): void {
            // FastAPI (or any external lead receiver) forwarding columns.
            // Null until the ForwardLeadToFastApiJob runs, populated on each
            // successful or failed forward attempt.
            $table->unsignedSmallInteger('fastapi_status_code')->nullable()->after('handled_at');
            $table->json('fastapi_response')->nullable()->after('fastapi_status_code');
            $table->timestamp('forwarded_at')->nullable()->after('fastapi_response');
            $table->string('external_id', 64)->nullable()->after('forwarded_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('contact_requests', function (Blueprint $table): void {
            $table->dropIndex(['external_id']);
            $table->dropColumn(['fastapi_status_code', 'fastapi_response', 'forwarded_at', 'external_id']);
        });
    }
};
