<?php

namespace App\Http\Controllers;

use App\Models\Medecin;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class MedecinController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Vérifier si la table "medecins" existe
            if (!Schema::hasTable('medecins')) {
                return response()->json([
                    'message' => 'Erreur: la table "medecins" n\'existe pas.',
                ], 500);
            }

            // Validation des données
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'specialite' => 'required|string|max:255',
                'telephone' => 'required|string|max:20',
                'email' => 'required|email|unique:medecins,email',
                'password' => 'required|string|min:6|confirmed',
            ]);

            // Récupérer l'ID du rôle Médecin
            $role = Role::where('name', 'Médecin')->first();

            if (!$role) {
                return response()->json([
                    'message' => 'Le rôle "Médecin" est introuvable',
                ], 500);
            }

            // Création du médecin avec le mot de passe haché et rôle Médecin
            $medecin = Medecin::create([
                'nom' => $validated['nom'],
                'specialite' => $validated['specialite'],
                'telephone' => $validated['telephone'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'role_id' => 3,  // Associer le rôle 'Médecin'
            ]);

            return response()->json([
                'message' => 'Médecin ajouté avec succès',
                'medecin' => $medecin
            ], 201); // 201 pour "Created"
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la création du médecin: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la création du médecin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nom' => 'sometimes|string|max:255',
                'specialite' => 'sometimes|string|max:255',
                'telephone' => 'sometimes|string|max:20',
                'email' => 'sometimes|email|unique:medecins,email,' . $id . ',id',
                'password' => 'sometimes|nullable|string|min:6|confirmed',
            ]);

            $medecin = Medecin::findOrFail($id);

            // Si un nouveau mot de passe est envoyé, on le hash
            if ($request->filled('password')) {
                $validated['password'] = Hash::make($request->password);
            }

            // Mise à jour uniquement si des données sont présentes
            if (!empty($validated)) {
                $medecin->update($validated);
            }

            return response()->json([
                'message' => 'Médecin mis à jour avec succès',
                'medecin' => $medecin
            ], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la mise à jour du médecin: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du médecin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $medecin = Medecin::findOrFail($id);
            $medecin->delete();

            return response()->json([
                'message' => 'Médecin supprimé avec succès'
            ], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la suppression du médecin: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la suppression du médecin',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
