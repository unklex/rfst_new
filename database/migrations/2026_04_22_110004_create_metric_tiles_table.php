<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metric_tiles', function (Blueprint $table): void {
            $table->id();
            $table->string('key_label', 10);
            $table->string('key_strong', 80);
            $table->text('value_html');
            $table->text('caption_html');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['sort', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metric_tiles');
    }
};
