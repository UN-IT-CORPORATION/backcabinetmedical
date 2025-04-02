<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PatientController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Validation des données
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'adresse' => 'required|string|max:255',
                'telephone' => 'required|string|max:20',
                'email' => 'required|email|unique:patients,email',
                'date_naissance' => 'required|date',
            ]);
            
            // Récupérer l'ID du rôle 'Utilisateur'
            $role = Role::where('name', 'Utilisateur')->first();

            if (!$role) {
                return response()->json([
                    'message' => 'Le rôle "Utilisateur" est introuvable',
                ], 500);
            }

            // Ajouter le role_id au tableau des données
            $patientData = $request->all();
            $patientData['role_id'] = 2;  // Associer le rôle 'Utilisateur' à ce patient

            \Illuminate\Support\Facades\Log::info('Données validées:', $validated);
            
            // Création du patient avec le rôle
            $patient = Patient::create($patientData);
            \Illuminate\Support\Facades\Log::info('Patient créé:', $patient->toArray());
            
            return response()->json([
                'message' => 'Patient ajouté avec succès',
                'patient' => $patient
            ], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la création du patient: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la création du patient',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validation des données
            $validated = $request->validate([
                'nom' => 'sometimes|string|max:255',
                'adresse' => 'sometimes|string|max:255',
                'telephone' => 'sometimes|string|max:20',
                'email' => 'sometimes|email|unique:patients,email,' . $id,
                'date_naissance' => 'sometimes|date',
            ]);

            \Illuminate\Support\Facades\Log::info('Données validées pour modification:', $validated);

            // Trouver le patient et mettre à jour les données
            $patient = Patient::findOrFail($id);
            $patient->update($validated);

            \Illuminate\Support\Facades\Log::info('Patient mis à jour:', $patient->toArray());

            return response()->json([
                'message' => 'Patient mis à jour avec succès',
                'patient' => $patient
            ], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la mise à jour du patient: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du patient',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $patient = Patient::findOrFail($id);
            $patient->delete();

            \Illuminate\Support\Facades\Log::info('Patient supprimé:', ['id' => $id]);

            return response()->json([
                'message' => 'Patient supprimé avec succès'
            ], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la suppression du patient: ' . $e->getMessage());

            return response()->json([
                'message' => 'Erreur lors de la suppression du patient',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
