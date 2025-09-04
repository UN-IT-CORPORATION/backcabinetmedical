<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->id();

            // Pour différencier l'origine de la photo (user ou patient)
            $table->morphs('photoable'); 
            // => crée photoable_id et photoable_type (polymorphic relation)

            $table->string('category')->default('profile'); 
            // "profile" = photo de profil utilisateur
            // "medical" = photo médicale patient

            $table->string('photo_type')->nullable(); 
            // utile pour les photos médicales (ex: radio, scanner...)
            
            $table->string('file_path'); // chemin du fichier

            $table->date('upload_date')->nullable(); // utile pour medical_photos
            $table->text('description')->nullable(); // description libre

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
