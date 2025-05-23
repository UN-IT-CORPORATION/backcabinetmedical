<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Paiement;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    public function paiement(Request $request){

    $id=$request->consultation_id;
    // $payment=Paiement::where('consultation_id',$id)->get();

    $consultation=Consultation::find($id);
    $montantTotalTraitement=$consultation->total;

    if($request->montant<$montantTotalTraitement){

     $data=$request->validate([
        'consultation_id'=>'required',
        'montant'=>'integer|required',
        'date_paiement'=>'required',
        'type'=>'required'
     ]);

     $paiement=Paiement::create($data);

     $totalPaye = Paiement::where('consultation_id', $id)
    ->sum('montant');

    $reste=$montantTotalTraitement-$totalPaye;

     return response()->json([
        'Message' =>'Paiement Effectué',
         'consultation'=>$paiement,
         'Reste à payer'=>$reste
     ],200);
    } else{

        return response()->json(['message'=>'Somme trop Elevé'],200);
    }

    }

    public function listpayementConsultation($id){

        $payment=Paiement::where('consultation_id',$id)->get();

        return response()->json(['payement'=>$payment],200);
    }

    public function payementrestant($id){

        $consultation=Consultation::find($id);
        $totalPaye = Paiement::where('consultation_id', $id)
        ->sum('montant');
        $montantTotalTraitement=$consultation->total;
        $reste=$montantTotalTraitement-$totalPaye;
        return response()->json(['payementrestant'=>$reste]);


    }
}