<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PatientController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'adresse' => 'required|string|max:255',
                'telephone' => 'required|string|max:20',
                'email' => 'required|email|unique:patients,email',
                'date_naissance' => 'required|date',
            ]);
            
            \Illuminate\Support\Facades\Log::info('Données validées:', $validated);
            
            $patient = Patient::create($request->all());
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
            $validated = $request->validate([
                'nom' => 'sometimes|string|max:255',
                'adresse' => 'sometimes|string|max:255',
                'telephone' => 'sometimes|string|max:20',
                'email' => 'sometimes|email|unique:patients,email,' . $id,
                'date_naissance' => 'sometimes|date',
            ]);

            \Illuminate\Support\Facades\Log::info('Données validées pour modification:', $validated);

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