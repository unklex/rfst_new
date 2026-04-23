<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitrix_features', function (Blueprint $table): void {
            $table->id();
            $table->string('number', 10);
            $table->text('title_html');
            $table->string('subtitle', 255);
            $table->string('url', 255)->default('#');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['sort', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitrix_features');
    }
};
