<?php


namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Stock;
use App\Models\Traitement;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class ConsultationController extends Controller
{
    public function store(Request $request)
    {

        $data = $request->validate([
            'patient' => ['required', 'array'],
            'patient.name' => 'required|string|max:100',
            'patient.prenom' => 'required|string|max:100',
            'patient.email' => 'required|email',
            'patient.password' => 'required|string|min:8|confirmed',
            'patient.role_id' => 'required|integer|in:4',
            'patient.numeroTelephone' => 'nullable|string|max:20',
            'patient.date_naissance' => 'nullable|date',
            'patient.adresse' => 'nullable|string|max:255',
            'patient.specialité' => 'nullable|string|max:255',

            'date_consultation' => 'required|date',
            'nb_seances' => 'required|integer|min:1',
            'traitements' => 'required|array|min:1',
            'traitements.*' => 'integer|exists:traitements,id',
            'produits' => 'nullable|array',
            'produits.*' => 'integer|exists:stocks,id',
            'paiements' => 'nullable|array',
            'paiements.*.montant' => 'required|numeric',
            'paiements.*.date' => 'required|date',
        ]);
        DB::enableQueryLog();
        DB::beginTransaction();

        try {
            // Vérifie si le patient existe déjà (par email ou téléphone)
            $patient = User::where('role_id', 4)
                ->where(function ($q) use ($data) {
                    $q->where('email', $data['patient']['email'])
                      ->orWhere('numeroTelephone', $data['patient']['numeroTelephone']);
                })
                ->first();

            if (!$patient) {
                $patient = User::create([
                    'name' => $data['patient']['name'],
                    'prenom' => $data['patient']['prenom'],
                    'email' => $data['patient']['email'],
                    'password' => Hash::make($data['patient']['password']),
                    'role_id' => $data['patient']['role_id'],
                    'numeroTelephone' => $data['patient']['numeroTelephone'] ?? null,
                    'date_naissance' => $data['patient']['date_naissance'] ?? null,
                    'adresse' => $data['patient']['adresse'] ?? null,
                    'specialité' => $data['patient']['specialité'] ?? null,
                ]);
            }

            // Créer consultation
            $consultation = Consultation::create([
                'user_id' => $patient->id,
                'date_consultation' => $data['date_consultation'],
                'nb_seances' => $data['nb_seances'],
                'total' => 0,
            ]);

            $total = 0;

            // Attacher traitements
            foreach ($data['traitements'] as $id) {
                $traitement = Traitement::findOrFail($id);
                $consultation->traitements()->attach($traitement->id, ['prix' => $traitement->prix]);
                $total += $traitement->prix;
            }

            // Attacher stocks (produits)
            if (!empty($data['produits'])) {
                foreach ($data['produits'] as $id) {
                    $stock = Stock::findOrFail($id);
                    $consultation->produits()->attach($stock->id, ['prix' => $stock->prix ?? 0]);
                    $total += $stock->prix ?? 0;
                }
            }

            // Paiements
            if (!empty($data['paiements'])) {
                foreach ($data['paiements'] as $paiement) {
                    $consultation->paiements()->create([
                        'montant' => $paiement['montant'],
                        'date_paiement' => $paiement['date']
                    ]);
                }
            }

            $consultation->update(['total' => $total]);

            DB::commit();
            Log::info(DB::getQueryLog());


            return response()->json([
                'message' => 'Consultation enregistrée avec succès',
                'consultation' => $consultation->load('patient', 'traitements', 'produits', 'paiements')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Erreur lors de la création de la consultation',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $consultations = Consultation::with(['patient', 'traitements', 'produits', 'paiements'])
                            ->orderBy('date_consultation', 'desc')
                            ->get();

        return response()->json([
            'consultations' => $consultations
        ]);
    }

    /**
     * GET /api/patients/{id}/consultations
     * Récupérer un patient + ses consultations
     */
    public function getByPatient($id)
    {
        $patient = User::where('role_id', 4)->with(['consultations.traitements', 'consultations.produits', 'consultations.paiements'])->findOrFail($id);

        return response()->json([
            'patient' => $patient,
            'consultations' => $patient->consultations
        ]);
    }
}
