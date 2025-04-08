<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run()
    {
        DB::table('roles')->insert([
            ['role' => 'Admin'],
            ['role' => 'Docteur'],
            ['role' => 'Secretaire'],
            ['role' => 'Patient'],
            ['role' => 'Pharmacien'],
            ['role' => 'Laborantin'],
            ['role' => 'Infirmier'],
            ['role' => 'Visiteur'],
            ['role' => 'Medecin'],
            ['role' => 'Etudiant'],
        ]);
    }
}