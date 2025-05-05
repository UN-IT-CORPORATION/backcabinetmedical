<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    /** Liste paginée des docteurs (role_id = 2) */
    public function index()
    {
        $doctors = User::where('role_id', 2)
                       ->when(request('search'), function ($q) {
                           $q->where('name', 'like', '%'.request('search').'%')
                             ->orWhere('email', 'like', '%'.request('search').'%');
                       })
                       ->paginate(15);

        return response()->json($doctors, 200);
    }

    /** Afficher un docteur précis */
    public function show($id)
    {
        $doctor = User::where('role_id', 2)->findOrFail($id);
        return response()->json($doctor, 200);
    }

    /** Mettre à jour un docteur */
    public function update(Request $request, $id)
    {
        $doctor = User::where('role_id', 2)->findOrFail($id);

        $validated = $request->validate([
            'name'            => ['sometimes','string','max:255'],
            'prenom'          => ['sometimes','string','max:255'],
            'email'           => ['sometimes','email', Rule::unique('users')->ignore($doctor->id)],
            'numeroTelephone' => ['sometimes','string','max:30'],
            'adresse'         => ['sometimes','string','max:255'],
            'specialité'      => ['sometimes','string','max:255'],
            'date_naissance'  => ['sometimes','date'],
        ]);

        $doctor->update($validated);

        return response()->json([
            'message' => 'Docteur mis à jour avec succès',
            'doctor'  => $doctor->fresh(),
        ], 200);
    }


    /** Supprimer un docteur */
    public function destroy($id)
    {
        $doctor = User::where('role_id', 2)->findOrFail($id);
        $doctor->delete();

        return response()->json(['message' => 'Docteur supprimé avec succès'], 200);
    }
}
