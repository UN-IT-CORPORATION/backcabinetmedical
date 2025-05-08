<?php

// app/Http/Controllers/TraitementController.php
namespace App\Http\Controllers;

use App\Models\Traitement;
use Illuminate\Http\Request;

class TraitementController extends Controller
{
    /* GET /traitements */
    public function index()
    {
        return response()->json(Traitement::with('services')->get());
    }

    /* POST /traitements */
    public function store(Request $request)
    {

        $data = $request->validate([
            'nom'      => 'required|string|max:255',
            'prix'     => 'required|numeric|min:0',
            'services' => 'nullable|array',
            'services.*' => 'integer|exists:services,id',
        ]);

        $traitement = Traitement::create($data);
        if (!empty($data['services'])) {
            $traitement->services()->attach($data['services']);
        }

        return response()->json([
            'message'    => 'Traitement créé',
            'traitement' => $traitement->load('services')
        ], 201);
    }

    /* GET /traitements/{id} */
    public function show(int $id)
    {
        return response()->json(
            Traitement::with('services')->findOrFail($id)
        );
    }

    /* PUT|PATCH /traitements/{id} */
    public function update(Request $request, int $id)
    {
        $traitement = Traitement::findOrFail($id);

        $data = $request->validate([
            'nom'      => 'sometimes|required|string|max:255',
            'prix'     => 'sometimes|required|numeric|min:0',
            'services' => 'nullable|array',
            'services.*' => 'integer|exists:services,id',
        ]);

        $traitement->update($data);

        // Si le front envoie un tableau complet → sync
        if ($request->has('services')) {
            $traitement->services()->sync($data['services'] ?? []);
        }

        return response()->json([
            'message'    => 'Traitement mis à jour',
            'traitement' => $traitement->load('services')
        ]);
    }

    /* DELETE /traitements/{id} */
    public function destroy(int $id)
    {
        Traitement::findOrFail($id)->delete();
        // La suppression dans le pivot est auto grâce à cascadeOnDelete()
        return response()->json(['message' => 'Traitement supprimé']);
    }
}