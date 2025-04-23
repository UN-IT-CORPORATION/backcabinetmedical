<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return response()->json(Service::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'icone' => 'nullable|string|max:255',
            'description_courte' => 'nullable|string|max:255',
            'details' => 'nullable|string',
            'horaires' => 'nullable|string|max:255',
        ]);

        $service = Service::create($request->all());

        return response()->json(['message' => 'Service créé avec succès', 'service' => $service], 201);
    }

    public function show($id)
    {
        $service = Service::findOrFail($id);
        return response()->json($service);
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'icone' => 'nullable|string|max:255',
            'description_courte' => 'nullable|string|max:255',
            'details' => 'nullable|string',
            'horaires' => 'nullable|string|max:255',
        ]);

        $service->update($request->all());

        return response()->json(['message' => 'Service mis à jour', 'service' => $service]);
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json(['message' => 'Service supprimé']);
    }
}
