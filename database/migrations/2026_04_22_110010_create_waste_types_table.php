<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_types', function (Blueprint $table): void {
            $table->id();
            $table->string('fkko_code', 60);
            $table->string('glyph', 4);
            $table->text('title_html');
            $table->text('description');
            $table->string('hazard_class_label', 40);
            $table->boolean('is_hazard')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['sort', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_types');
    }
};
