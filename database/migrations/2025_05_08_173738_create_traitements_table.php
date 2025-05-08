<?php

// database/migrations/2025_05_08_000000_create_traitements_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('traitements', function (Blueprint $table) {
            $table->id();
            $table->string('nom');        // nom_traitement
            $table->decimal('prix', 8, 2);
            $table->timestamps();
        });

        // Pivot : service_traitement
        Schema::create('service_traitement', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('traitement_id')->constrained()->cascadeOnDelete();
            $table->primary(['service_id', 'traitement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_traitement');
        Schema::dropIfExists('traitements');
    }
};