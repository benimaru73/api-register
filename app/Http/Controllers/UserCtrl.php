<?php

namespace App\Http\Controllers;

use App\Mail\ValidationEmail;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;


class UserCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        //
    }

    /**
    * 
    * Validation email
    */
    public function validateEmail($token)
    {
        $result=Utilisateur::getToken($token);
        if(!$result["success"])
        {
            return response()->json($result);
        }
        $verifieduser=$result["user"];
        $verifieduser->isverified=true;
        $verifieduser->save();
        return response()->json([
                'success'=>true,
                'message'=>"Utilisateur $verifieduser->email a bien ete confirme ",
                'status'=>200
            ]
            ,200);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|email|unique:utilisateur,email',
                'nom' => 'required|string',
                'mdp1' => 'required|string|min:6',
                'mdp2'=>'required|string|min:6',
               
            ]);
    
            $utilisateur = Utilisateur::create([
                'email' => $request->email,
                'nom' => $request->nom,
                'mdp' => bcrypt($request->mdp1),
                'dateinscription'=>Carbon::now()->toDateTimeString(),
                 'isverified'=>false
            ]);

            $tokenutilisateur=$utilisateur->createToken();
            
            $url = route('confirmEmail', ['token' => $tokenutilisateur->token]);
            echo $url;
            
            Mail::to($utilisateur->email)->send(new ValidationEmail($utilisateur,$url));


            return response()->json([
                'message' => 'Un e-mail de validation a été envoyé. Veuillez vérifier votre boîte de réception.',
                'status' => 200,
            ], 200);
         
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Erreur de validation des données.',
                'messages' => $e->errors(),
            ], 422);
        }
        catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur interne s\'est produite.',
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    
}
