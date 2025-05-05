<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    /** Liste paginée des patients (role_id = 4) */
    public function index()
    {
        $patients = User::where('role_id', 4)
                        ->when(request('search'), function ($q) {
                            $q->where('name', 'like', '%'.request('search').'%')
                              ->orWhere('email', 'like', '%'.request('search').'%');
                        })
                        ->paginate(15);

        return response()->json($patients, 200);
    }

    /** Afficher un patient précis */
    public function show($id)
    {
        $patient = User::where('role_id', 4)->findOrFail($id);
        return response()->json($patient, 200);
    }

    /** Mettre à jour un patient */
    public function update(Request $request, $id)
    {
        $patient = User::where('role_id', 4)->findOrFail($id);

        $validated = $request->validate([
            'name'            => ['sometimes','string','max:255'],
            'prenom'          => ['sometimes','string','max:255'],
            'email'           => ['sometimes','email', Rule::unique('users')->ignore($patient->id)],
            'numeroTelephone' => ['sometimes','string','max:30'],
            'adresse'         => ['sometimes','string','max:255'],
            'date_naissance'  => ['sometimes','date'],
            // les patients n’ont généralement pas “specialité” ni “emploi” ;
            // ajoute ou retire des champs selon tes besoins métier
        ]);

        $patient->update($validated);

        return response()->json([
            'message' => 'Patient mis à jour avec succès',
            'patient' => $patient->fresh(),
        ], 200);
    }

    /** Supprimer un patient */
    public function destroy($id)
    {
        $patient = User::where('role_id', 4)->findOrFail($id);
        $patient->delete();

        return response()->json(['message' => 'Patient supprimé avec succès'], 200);
    }
}
