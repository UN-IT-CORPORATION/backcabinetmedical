<?php

namespace App\Http\Controllers;

use App\Models\Rendezvous;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RendezvousController extends Controller
{


    public function store(Request $request){

       $data=$request->validate(([

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
        'service_id'=>'required|integer',
        'date'=>'required|date',
        'heure'=>'required|string',
        'motif'=>'required|string'

       ]

       ));

       DB::beginTransaction();

       try{
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


                $rendezvous=Rendezvous::create([
                    'user_id'=>$patient->id,
                    'service_id'=>$data['service_id'],
                    'date'=>$data['date'],
                    'heure'=>$data['heure'],
                    'desciption'=>$data['motif']
                ]);

                DB::commit();

                return response()->json([
                'message'=>'Enregistrement rendez-vous avec succes',
                'rendezvous'=>$rendezvous,
                ],200);




       } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'error'   => 'Erreur lors de la création de la consultation',
            'message' => $e->getMessage(),
        ],500);
    }

    }

  public function getAllRendezvous(){
    try{
        $rendezvous=Rendezvous::with(['patient','service'])->get();

        return response()->json([
            'message'=>'Enregistement avec succes',
             'rendezvous'=>$rendezvous,
        ],200);
    } catch(\Throwable $e){
        return response()->json([
            'error'   => 'Erreur lors de la visualisation',
            'message' => $e->getMessage(),
        ],500);


    }


  }


  public function getByMonth(Request $request)
{
    $year = $request->input('year');
    $month = $request->input('month');

    if (!$year || !$month) {
        return response()->json([
            'error' => 'Année et mois requis.'
        ], 400);
    }


    $start = "{$year}-{$month}-01";
    $end = date("Y-m-t", strtotime($start));

    try{
        $rendezvous =Rendezvous::with(['patient', 'service'])
        ->whereBetween('date', [$start, $end])
        ->orderBy('date')
        ->get();

    return response()->json([
        'rendezvous' => $rendezvous
    ],200);
    } catch(\Throwable $e){
        return response()->json([
            'error'   => 'Erreur lors de la visualisation',
            'message' => $e->getMessage(),
        ],500);
    }

}



}