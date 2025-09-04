<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    // 📌 Upload multiple photos (profil ou médicale)
    public function upload(Request $request)
    {
        $request->validate([
            'photoable_id'   => 'required|integer',
            'photoable_type' => 'required|string|in:User,Patient',
            'category'       => 'required|string|in:profile,medical',
            'photos.*'       => 'required|image|mimes:jpg,jpeg,png', // multiple photos
            'photo_type'     => 'nullable|string|max:255',
            'upload_date'    => 'nullable|date',
            'description'    => 'nullable|string',
        ]);

        $uploadedPhotos = [];

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store('photos', 'public');

                $photo = Photo::create([
                    'photoable_id'   => $request->photoable_id,
                    'photoable_type' => "App\\Models\\" . $request->photoable_type,
                    'category'       => $request->category,
                    'photo_type'     => $request->photo_type,
                    'file_path'      => '/storage/' . $path,
                    'upload_date'    => $request->upload_date ?? now(),
                    'description'    => $request->description,
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

    // 📌 Lister les photos d’un User ou Patient
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

    // 📌 Supprimer une photo
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
