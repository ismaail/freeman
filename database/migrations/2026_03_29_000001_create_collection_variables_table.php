<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('value')->default('');
            $table->boolean('enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_variables');
    }
};
