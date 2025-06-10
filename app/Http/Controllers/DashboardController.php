<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Paiement;
use App\Models\Rendezvous;
use Illuminate\Http\Request;

class DashboardController extends Controller
{


    public function getTotalPatient(){

  try{
    $nombrepatient=User::where('role_id',4)->count();

    return response()->json([
        'nombrePatient'=>$nombrepatient
    ]);

  }catch(\Throwable $e){
    return response()->json([
        'message'=>$e->getMessage(),
    ]);
  }

    }

    public function getTotalRendezvousByDate(Request $request){

        $data=$request->validate([
            'date'=>'date|required'
        ]);

        try{

            $nombreTotalRendezvous=Rendezvous::where('date',$data['date'])->count();

            return response()->json([
                'nombreRendezvous'=>$nombreTotalRendezvous
            ],200);

        }catch(\Throwable $e){
            return response()->json([
                'message'=>'Erreur lors du calcul nombre rendez vous',
                'error'=>$e->getMessage(),

            ]);
        }
    }

    public function revenueMonthly(Request $request)
{
    $year = $request->query('year');

    // Validation : doit être une année à 4 chiffres
    if (!$year || !preg_match('/^\d{4}$/', $year)) {
        return response()->json(['error' => 'Format invalide. Année attendue (ex: 2025)'], 400);
    }

    $results = Paiement::selectRaw('MONTH(date_paiement) as mois, SUM(montant) as total')
        ->whereYear('date_paiement', $year)
        ->groupBy('mois')
        ->orderBy('mois')
        ->get();

    // Générer les 12 mois de l’année avec 0 par défaut
    $moisComplet = collect(range(1, 12))->map(function ($m) use ($results) {
        $item = $results->firstWhere('mois', $m);
        return $item ? $item->total : 0;
    });

    return response()->json([
        'labels' => [
            'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
            'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
        ],
        'data' => $moisComplet
    ],200);
}


public function revenueDaily(Request $request)
{
    $year = $request->query('year');
    $month = $request->query('month');

    // Validation simple
    if (!preg_match('/^\d{4}$/', $year) || !preg_match('/^\d{1,2}$/', $month)) {
        return response()->json(['error' => 'Format invalide. Année et mois requis.'], 400);
    }

    // Convertir en int pour éviter les zéros inutiles
    $year = intval($year);
    $month = intval($month);

    // Nombre de jours du mois donné
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    $results = Paiement::selectRaw('DAY(date_paiement) as jour, SUM(montant) as total')
        ->whereYear('date_paiement', $year)
        ->whereMonth('date_paiement', $month)
        ->groupBy('jour')
        ->orderBy('jour')
        ->get();

    // Créer la série de jours du mois (1..n)
    $revenuParJour = collect(range(1, $daysInMonth))->map(function ($jour) use ($results) {
        $item = $results->firstWhere('jour', $jour);
        return $item ? $item->total : 0;
    });

    return response()->json([
        'labels' => range(1, $daysInMonth),
        'data' => $revenuParJour
    ],200);
}

public function derniersPatients()
{
    $patients = User::where('role_id', 4)
        ->orderByDesc('created_at')
        ->take(5)
        ->get();

    return response()->json(['patients' => $patients],200);
}








}
