<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table): void {
            $table->id();
            $table->string('line_label', 40);
            $table->string('index_label', 20);
            $table->string('symbol', 10);
            $table->text('title_html');
            $table->text('description');
            $table->json('spec_rows');
            $table->string('footer_code', 20);
            $table->string('cta_url', 255)->default('#');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['sort', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
