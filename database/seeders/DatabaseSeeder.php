<?php

namespace Database\Seeders;

use App\Models\Role;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // D'abord les rôles
        $this->call([
            RoleSeeder::class,
        ]);

        // Récupération du rôle "Admin"
        $adminRole = Role::where('role', 'Admin')->first();

        // Récupération du rôle "Visiteur" ou autre pour le test
        $defaultRole = Role::where('role', 'Visiteur')->first();

        // Création de l'utilisateur Admin
        User::factory()->create([
            'name' => 'Admin',
            'prenom' => 'Principal',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('azerty'),
            'numeroTelephone' => '0600000000',
            'adresse' => '1 rue de l\'admin',
            'date_naissance' => '1990-01-01',
            'specialité' => 'Informatique',
            'emploi' => 'Administrateur',
            'role_id' => $adminRole?->id, // rôle lié via clé étrangère
        ]);

        // Création d'un utilisateur de test
        User::factory()->create([
            'name' => 'Test',
            'prenom' => 'User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'numeroTelephone' => '0700000000',
            'adresse' => '2 rue du test',
            'date_naissance' => '1995-05-15',
            'specialité' => null,
            'emploi' => null,
            'role_id' => $defaultRole?->id, // un autre rôle
        ]);
    }
}
