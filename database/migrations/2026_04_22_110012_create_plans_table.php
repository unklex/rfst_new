<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table): void {
            $table->id();
            $table->text('title_html');
            $table->string('badge', 40);
            $table->string('price_main', 40);
            $table->string('price_suffix', 20);
            $table->string('price_caption', 120);
            $table->json('features');
            $table->string('cta_label', 80);
            $table->string('cta_url', 255)->default('#');
            $table->boolean('is_highlighted')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['sort', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
