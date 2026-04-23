<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nav_items', function (Blueprint $table): void {
            $table->id();
            $table->string('label', 120);
            $table->string('anchor', 120);
            $table->boolean('is_external')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['sort', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_items');
    }
};
