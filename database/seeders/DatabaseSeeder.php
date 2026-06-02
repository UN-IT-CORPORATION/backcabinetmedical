<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. D'abord les rôles (sécurisés avec firstOrCreate)
        $this->call([
            RoleSeeder::class,
        ]);

        $adminRole = Role::where('role', 'Admin')->first();
        $defaultRole = Role::where('role', 'Visiteur')->first();

        // 2. Création ou mise à jour de l'Admin
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'], // Condition de recherche (unique)
            [
                'name' => 'Admin',
                'prenom' => 'Principal',
                'password' => Hash::make('azerty'),
                'numeroTelephone' => '0600000000',
                'adresse' => '1 rue de l\'admin',
                'date_naissance' => '1990-01-01',
                'specialité' => 'Informatique',
                'emploi' => 'Administrateur',
                'role_id' => $adminRole?->id,
            ]
        );

        // 3. Création ou mise à jour de l'utilisateur de test
        User::updateOrCreate(
            ['email' => 'test@example.com'], // Condition de recherche (unique)
            [
                'name' => 'Test',
                'prenom' => 'User',
                'password' => Hash::make('password'),
                'numeroTelephone' => '0700000000',
                'adresse' => '2 rue du test',
                'date_naissance' => '1995-05-15',
                'specialité' => null,
                'emploi' => null,
                'role_id' => $defaultRole?->id,
            ]
        );
    }
}
