<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Add this line to import the DB class

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('medecins', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('specialite');
            $table->string('telephone');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
            $table->foreignId('role_id')->constrained('roles')->default(3); // Par défaut, un médecin a le rôle 'Médecin'
        });

        // Set default value for role_id
        DB::statement('ALTER TABLE medecins ALTER COLUMN role_id SET DEFAULT 3');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medecins');
    }
};