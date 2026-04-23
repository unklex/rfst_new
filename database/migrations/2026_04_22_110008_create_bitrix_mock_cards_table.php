<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitrix_mock_cards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('column_id')->constrained('bitrix_mock_columns')->cascadeOnDelete();
            $table->string('accent', 20)->default('signal'); // signal | ink | green
            $table->string('label', 120);
            $table->text('value_html');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['column_id', 'sort', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitrix_mock_cards');
    }
};
