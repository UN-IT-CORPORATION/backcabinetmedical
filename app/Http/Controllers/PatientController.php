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
        $patients = User::with('antecedents') // chargement de la relation
                        ->where('role_id', 4)
                        ->when(request('search'), function ($q) {
                            $q->where(function($subQuery) {
                                $subQuery->where('name', 'like', '%' . request('search') . '%')
                                         ->orWhere('email', 'like', '%' . request('search') . '%');
                            });
                        })
                        ->paginate(15);

        return response()->json($patients, 200);
    }

    /** Afficher un patient précis */
    public function show($id)
    {
        $patient = User::with('antecedents')->where('role_id', 4)->findOrFail($id);
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

        // antécédents
        'antecedents'               => 'nullable|array',
        'antecedents.*.id'          => 'nullable|integer|exists:antecedents,id',
        'antecedents.*.titre'       => 'required_without:antecedents.*.id|string|max:255',
        'antecedents.*.description' => 'nullable|string',
    ]);

    $patient->update($validated);

    if ($request->has('antecedents')) {
        $nouveauxIds = [];

        foreach ($request->antecedents as $ant) {
            // Mise à jour si ID fourni et correspond à ce patient
            if (isset($ant['id'])) {
                $a = $patient->antecedents()->find($ant['id']);
                if ($a) {
                    $a->update([
                        'titre'       => $ant['titre'],
                        'description' => $ant['description'] ?? null,
                    ]);
                    $nouveauxIds[] = $a->id;
                }
            } else {
                // Création d’un nouveau
                $a = $patient->antecedents()->create([
                    'titre'       => $ant['titre'],
                    'description' => $ant['description'] ?? null,
                ]);
                $nouveauxIds[] = $a->id;
            }
        }

        // Supprimer les anciens antécédents qui ne sont plus listés
        $patient->antecedents()
                ->whereNotIn('id', $nouveauxIds)
                ->delete();
    }

    return response()->json([
        'message' => 'Patient mis à jour avec succès',
        'patient' => $patient->fresh('antecedents'),
    ], 200);
}

    /** Supprimer un patient */
    public function destroy($id)
    {
        $patient = User::where('role_id', 4)->findOrFail($id);
        $patient->delete();

        return response()->json(['message' => 'Patient supprimé avec succès'], 200);
    }

    public function search(Request $request)
{
    $query = $request->get('q');


    $patients = User::with('antecedents')->where('role_id', 4)
        ->where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%")
              ->orWhere('numeroTelephone', 'LIKE', "%{$query}%");
        })
        ->limit(10)
        ->get();

    return response()->json($patients);
}

}
