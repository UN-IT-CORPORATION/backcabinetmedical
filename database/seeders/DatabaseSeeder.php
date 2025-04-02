<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Exécuter le RoleSeeder d'abord
        $this->call(RoleSeeder::class);

        // Récupérer l'ID du rôle "Admin"
        $adminRole = DB::table('roles')->where('name', 'Admin')->first();

        // Créer un utilisateur admin avec le rôle récupéré
        User::create([
            'name' => 'Admin2',
            'email' => 'admin2@gmail.com',
            'password' => Hash::make('1234'),
            'role_id' => $adminRole ? $adminRole->id : null, // Vérification si le rôle existe
        ]);
    }
}
