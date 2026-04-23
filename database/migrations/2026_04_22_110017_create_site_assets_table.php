<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_assets', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 80)->unique(); // hero_bg, about_archive, quote_reviewer, favicon, og_image
            $table->string('title', 120)->nullable();
            $table->string('alt', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_assets');
    }
};
