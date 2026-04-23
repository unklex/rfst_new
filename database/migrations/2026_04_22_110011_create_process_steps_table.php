<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_steps', function (Blueprint $table): void {
            $table->id();
            $table->string('number', 10);
            $table->string('title', 120);
            $table->text('description');
            $table->string('meta_label', 40);
            $table->string('meta_value', 40);
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['sort', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_steps');
    }
};
