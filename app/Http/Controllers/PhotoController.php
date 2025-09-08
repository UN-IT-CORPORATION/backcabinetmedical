<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\User;
use App\Models\Consultation; // ✅ Import ajouté
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    // Upload multiple photos (profil ou médicale)
    public function upload(Request $request)
    {
        $request->validate([
            'photoable_id'   => 'required|integer',
            'photoable_type' => 'required|string|in:User,Consultation',
            'category'       => 'required|string|in:profile,medical',
            'photos.*'       => 'required|image|mimes:jpg,jpeg,png',
            'photo_type'     => 'nullable|string|max:255',
            'upload_date'    => 'nullable|date',
            'description'    => 'nullable|string',
        ]);

        // Vérifier que la consultation existe si c’est une photo de consultation
        if ($request->photoable_type === 'Consultation') {
            $consultation = Consultation::find($request->photoable_id);
            if (!$consultation) {
                return response()->json(['error' => 'Consultation non trouvée'], 404);
            }
        }

        $uploadedPhotos = [];

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store('photos', 'public');

                $photo = Photo::create([
                    'photoable_id'    => $request->photoable_id,
                    'photoable_type'  => "App\\Models\\" . $request->photoable_type,
                    'consultation_id' => $request->photoable_type === 'Consultation' ? $request->photoable_id : null,
                    'category'        => $request->category,
                    'photo_type'      => $request->photo_type,
                    'file_path'       => '/storage/' . $path,
                    'upload_date'     => $request->upload_date ?? now(),
                    'description'     => $request->description,
                ]);

                $uploadedPhotos[] = $photo;
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($uploadedPhotos) . ' photo(s) ajoutée(s) avec succès',
            'data'    => $uploadedPhotos,
        ], 201);
    }


    // Lister les photos d’un User ou Consultation
    public function listByOwner($type, $id)
    {
        $model = "App\\Models\\" . ucfirst($type);

        if (!class_exists($model)) {
            return response()->json(['error' => 'Type invalide'], 400);
        }

        $photos = Photo::where('photoable_type', $model)
                       ->where('photoable_id', $id)
                       ->orderBy('created_at', 'desc')
                       ->get();

        return response()->json($photos);
    }
    
    // Lister les photos d’une consultation spécifique
    public function listByConsultation($id)
    {
        $consultation = Consultation::find($id);

        if (!$consultation) {
            return response()->json(['error' => 'Consultation non trouvée'], 200);
        }

        // Retourne toutes les photos liées à cette consultation
        return response()->json($consultation->photos()->orderBy('created_at', 'desc')->get());
    }



    // Supprimer une photo
    public function delete($id)
    {
        $photo = Photo::findOrFail($id);

        if (Storage::disk('public')->exists(str_replace('/storage/', '', $photo->file_path))) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $photo->file_path));
        }

        $photo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Photo supprimée avec succès',
        ]);
    }
}
