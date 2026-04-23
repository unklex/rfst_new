<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('footer_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('footer_column_id')->constrained('footer_columns')->cascadeOnDelete();
            $table->string('label', 120);
            $table->string('url', 255)->default('#');
            $table->boolean('is_external')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['footer_column_id', 'sort', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_links');
    }
};
