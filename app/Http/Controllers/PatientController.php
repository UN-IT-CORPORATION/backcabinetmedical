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
    
}