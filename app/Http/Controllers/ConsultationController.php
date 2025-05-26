<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Stock;
use App\Models\Paiement;
use App\Models\Antecedent;
use App\Models\Traitement;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class ConsultationController extends Controller
{
    /* POST /api/consultations */
    public function store(Request $request)
    {
        /* ─────────── Validation ─────────── */
        $data = $request->validate([
            /* --- Patient --- */
            'patient'                 => ['required','array'],
            'patient.name'            => 'required|string|max:100',
            'patient.prenom'          => 'required|string|max:100',
            'patient.email'           => 'required|email',
            'patient.password'        => 'required|string|min:8|confirmed',
            'patient.role_id'         => 'required|integer|in:4',
            'patient.numeroTelephone' => 'nullable|string|max:20',
            'patient.date_naissance'  => 'nullable|date',
            'patient.adresse'         => 'nullable|string|max:255',
            'patient.specialité'      => 'nullable|string|max:255',
            'patient.organisme'       =>'nullable|string',
            'patient.numerodossierprisenchage'=>'nullable|string',

            /* --- Consultation --- */
            'date_consultation' => 'required|date',
            'nb_seances'        => 'required|integer|min:1',
            'seancerestant' =>'nullable|string',
            'observation'       => 'nullable|string',
            'temperature'       => 'nullable|numeric|between:25,45',
            'tension'           => 'nullable|string|max:15',

            /* --- Antécédents --- */
            'antecedents'               => 'nullable|array',
            'antecedents.*.id'          => 'nullable|integer',                // plus de "exists"
            'antecedents.*.titre'       => 'required_without:antecedents.*.id|string|max:255',
            'antecedents.*.description' => 'nullable|string',

            /* --- Actes & Produits --- */
            'traitements'   => 'required|array|min:1',
            'traitements.*' => 'integer|exists:traitements,id',
            'produits'      => 'nullable|array',
            'produits.*'    => 'integer|exists:stocks,id',

            /* --- Paiements --- */
            'paiements'         => 'nullable|array',
            'paiements.*.montant'=> 'required|numeric',
            'paiements.*.date'   => 'required|date',
            'paiements.*.type' =>'required'
        ]);

        DB::beginTransaction();
        try {
            /* ---------- Patient ---------- */
            $patient = User::where('role_id',4)
                ->where(function ($q) use ($data){
                    $q->where('email',$data['patient']['email'])
                      ->orWhere('numeroTelephone',$data['patient']['numeroTelephone']);
                })->first();

                if (!$patient) {
                    // --- Création ---
                    $patient = User::create([
                        'name'                         => $data['patient']['name'],
                        'prenom'                       => $data['patient']['prenom'],
                        'email'                        => $data['patient']['email'],
                        'password'                     => Hash::make($data['patient']['password']),
                        'role_id'                      => 4,
                        'numeroTelephone'              => $data['patient']['numeroTelephone'] ?? null,
                        'date_naissance'               => $data['patient']['date_naissance'] ?? null,
                        'adresse'                      => $data['patient']['adresse'] ?? null,
                        'specialité'                   => $data['patient']['specialité'] ?? null,
                        'organisme'                    => $data['patient']['organisme'] ?? null,
                        'numerodossierprisenchage'     => $data['patient']['numerodossierprisenchage'] ?? null,
                    ]);
                } else {
                    // --- Mise à jour éventuelle ---
                    $patient->fill([
                        'name'                         => $data['patient']['name'],
                        'prenom'                       => $data['patient']['prenom'],
                        'email'                        => $data['patient']['email'],
                        'numeroTelephone'              => $data['patient']['numeroTelephone'] ?? $patient->numeroTelephone,
                        'date_naissance'               => $data['patient']['date_naissance'] ?? $patient->date_naissance,
                        'adresse'                      => $data['patient']['adresse'] ?? $patient->adresse,
                        'specialité'                   => $data['patient']['specialité'] ?? $patient->specialité,
                        'organisme'                    => $data['patient']['organisme'] ?? $patient->organisme,
                        'numerodossierprisenchage'     => $data['patient']['numerodossierprisenchage'] ?? $patient->numerodossierprisenchage,
                    ])->save();
                }


            /* ---------- Consultation ---------- */
            $seancerestant = $data['nb_seances'];
            $consultation = Consultation::create([
                'user_id'          => $patient->id,
                'date_consultation'=> $data['date_consultation'],
                'nb_seances'       => $data['nb_seances'],
                'seancerestant'=>$seancerestant,
                'total'            => 0,
                'observation'      => $data['observation'] ?? null,
                'temperature'      => $data['temperature'] ?? null,
                'tension'          => $data['tension']     ?? null,
            ]);

            $total = 0;

            /* ---------- Traitements ---------- */
            foreach ($data['traitements'] as $id) {
                $t = Traitement::findOrFail($id);
                $consultation->traitements()->attach($t->id,['prix'=>$t->prix]);
                $total += $t->prix;
            }

            /* ---------- Produits / Stocks ---------- */
            if (!empty($data['produits'])) {
                foreach ($data['produits'] as $id) {
                    $s = Stock::findOrFail($id);
                    $consultation->produits()->attach($s->id,['prix'=>$s->prix ?? 0]);
                    $total += $s->prix ?? 0;
                }
            }

            /* ---------- Antécédents ---------- */
            if (!empty($data['antecedents'])) {
                foreach ($data['antecedents'] as $ant) {
                    // 1) on tente de récupérer l’antécédent si ID fourni
                    $a = null;
                    if (isset($ant['id'])) {
                        $a = Antecedent::where('user_id',$patient->id)->find($ant['id']);
                    }
                    // 2) si aucun ID ou ID introuvable → on crée
                    if (!$a) {
                        $a = $patient->antecedents()->create([
                            'titre'       => $ant['titre'],
                            'description' => $ant['description'] ?? null,
                        ]);
                    }
                    // 3) on relie à la consultation
                    $consultation->antecedents()->attach($a->id);
                }
            }

            /* ---------- Paiements ---------- */
            if (!empty($data['paiements'])) {
                foreach ($data['paiements'] as $p) {
                    $consultation->paiements()->create([
                        'montant'       => $p['montant'],
                        'date_paiement' => $p['date'],
                        'type'           =>$p['type'],
                    ]);
                }
            }

            $consultation->update(['total'=>$total]);
            DB::commit();

            return response()->json([
                'message'      => 'Consultation enregistrée avec succès',
                'consultation' => $consultation->load(
                    'patient','traitements','produits','paiements','antecedents'
                )
            ],201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Erreur lors de la création de la consultation',
                'message' => $e->getMessage(),
            ],500);
        }
    }

    /* GET /api/consultations */
    public function index()
    {
        $list = Consultation::with([
            'patient','traitements','produits','paiements','antecedents'
        ])->orderByDesc('date_consultation')->get();

        foreach ($list as $consultation) {
            // Somme des paiements
            $totalPaye = $consultation->paiements->sum('montant');

            // Reste à payer
            $reste = $consultation->total - $totalPaye;
            $consultation->payementrestant = $reste;

            // Statut de paiement
            $consultation->statuspaiement = $reste <= 0 ? 'Payé' : 'En cours';

            // Statut des séances
            $effectuees = $consultation->nb_seances - $consultation->seancerestant;
            $consultation->statusseance = $consultation->seancerestant == 0
                ? 'Complet'
                : $effectuees . '/' . $consultation->nb_seances;
        }

        return response()->json(['consultations' => $list]);
    }

    public function finishSceance($id){

        $consultation = Consultation::with([
            'patient', 'traitements', 'produits', 'paiements', 'antecedents'
        ])->find($id);

        $consultation->seancerestant='0';
        $consultation->save();

        return response()->json(['message'=>'Traitement Terminé']);

    }

    public function addSceance($id){
        $consultation = Consultation::with([
            'patient', 'traitements', 'produits', 'paiements', 'antecedents'
        ])->find($id);

        if($consultation->seancerestant >0){
            $consultation->seancerestant-=1;
            $consultation->save();

            return response()->json(
                [
                    'message'=>'Sceance mise à jour',
                     'consultattion_restant'=>$consultation->seancerestant
        ]);

        }else{
            return response()->json(['message'=>'Sceance déja complet']);
        }
        ;


    }

    /* GET /api/patients/{id}/consultations */
    public function getByPatient($id)
    {
        $patient = User::where('role_id',4)->with([
            'antecedents',
            'consultations.traitements',
            'consultations.produits',
            'consultations.paiements',
            'consultations.antecedents',
        ])->findOrFail($id);

        return response()->json([
            'patient'       => $patient,
            'consultations' => $patient->consultations
        ]);
    }
}