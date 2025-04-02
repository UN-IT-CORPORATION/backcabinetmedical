<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Création de la table patients
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('adresse');
            $table->string('telephone');
            $table->string('email')->unique();
            $table->date('date_naissance');
            $table->timestamps();
            $table->foreignId('role_id')->default(2)->constrained('roles'); // Par défaut, un patient est un utilisateur
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Suppression de la table patients
        Schema::dropIfExists('patients');
    }
};
