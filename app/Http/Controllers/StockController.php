<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $stocks = Stock::all();
        return response()->json($stocks);
    }

    public function show($id)
    {
        $stock = Stock::findOrFail($id);
        return response()->json($stock);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'quantite_total' => 'required|integer|min:0',
            'quantite_carton' => 'required|integer|min:0',
            'service_id' => 'required|exists:services,id',
        ]);

        $stock = Stock::create($data);
        return response()->json($stock, 201);
    }
    public function update(Request $request, $id)
    {
        $stock = Stock::findOrFail($id);

        $data = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'quantite_total' => 'sometimes|required|integer|min:0',
            'quantite_carton' => 'sometimes|required|integer|min:0',
            'service_id' => 'sometimes|required|exists:services,id',
        ]);

        $stock->update($data);
        return response()->json($stock);
    }
    public function destroy($id)
    {
        $stock = Stock::findOrFail($id);
        $stock->delete();
        return response()->json(null, 204);
    }
}
