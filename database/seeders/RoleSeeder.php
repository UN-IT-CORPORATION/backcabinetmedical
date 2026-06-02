<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Admin', 'Docteur', 'Secretaire', 'Patient', 'Pharmacien', 
            'Laborantin', 'Infirmier', 'Visiteur', 'Medecin', 'Etudiant'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['role' => $role]);
        }
    }
}
