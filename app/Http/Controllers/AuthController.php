<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
{
    try {
        // Validation des champs
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'numeroTelephone' => 'nullable|string|max:20',
            'date_naissance' => 'nullable|date',
            'adresse' => 'nullable|string|max:255',
            'specialité' => 'nullable|string|max:255',
            'emploi' => 'nullable|string|max:255',
            'organisme'=>'nullable|string',
            'numerodossierprisenchage'=>'nullable|string',

            'antecedents'               => 'nullable|array',
            'antecedents.*.titre'       => 'required|string|max:255',
            'antecedents.*.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Échec de la validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Création de l'utilisateur
        $user = User::create([
            'name' => $request->name,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'numeroTelephone' => $request->numeroTelephone,
            'date_naissance' => $request->date_naissance,
            'adresse' => $request->adresse,
            'specialité' => $request->specialité,
            'emploi' => $request->emploi,
            'organisme'=>$request->organisme,
            'numerodossierprisenchage'=>$request->numerodossierprisenchage
        ]);

        // Envoi de l'email de vérification
        $user->sendEmailVerificationNotification();

        if ($request->has('antecedents')) {
            foreach ($request->antecedents as $ant) {
                $user->antecedents()->create([
                    'titre'       => $ant['titre'],
                    'description' => $ant['description'] ?? null,
                ]);
            }
        }

        $user = $user->load('antecedents');

        // Création du token d'authentification
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inscription réussie. Veuillez vérifier votre adresse e-mail.',
            'user' => $user,
            'token' => $token,
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Une erreur est survenue lors de l\'inscription',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    public function redirectToProvider($provider)
    {
        if (!in_array($provider, ['facebook', 'google', 'linkedin'])) {
            return response()->json(['error' => 'Provider not supported'], 400);
        }

        try {
            if ($provider === 'linkedin') {
                return Socialite::driver('linkedin')
            ->scopes(['openid', 'profile', 'email']) // Scopes mis à jour
            ->stateless()
            ->redirect();
            }

            return Socialite::driver($provider)->stateless()->redirect();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error with OAuth provider: ' . $e->getMessage()], 500);
        }
    }

    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Lien invalide'], 400);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return response()->json(['message' => 'Email vérifié avec succès']);
    }


    public function resend(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur introuvable'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email déjà vérifié'], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Lien de vérification renvoyé']);
    }



    public function handleProviderCallback($provider)
    {
        dd('LinkedIn Callback Parameters: ', request()->all());
        try {
            // Récupérer les informations de l'utilisateur via l'API de Socialite
            $user = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error occurred during authentication: ' . $e->getMessage()], 500);
        }

        $authUser = $this->findOrCreateUser($user, $provider);

        $token = $authUser->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Authentication successful',
            'user' => $authUser,
            'token' => $token,
        ]);
    }


    private function findOrCreateUser($socialUser, $provider)
    {
        $authUser = User::where('provider_id', $socialUser->getId())->first();

        if (!$authUser) {
            $authUser = User::create([
                'first_name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'password' => Hash::make(Str::random(16)),

            ]);
        }

        return $authUser;
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->last_login = now();
        $user->save();

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function profile(Request $request)
    {

    return response()->json([
        'message' => 'Utilisateur connecté récupéré avec succès',
        'user' => $request->user()
    ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent to your email']);
        }

        return response()->json(['error' => 'Failed to send reset link'], 500);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->save();
                }
            );


            if ($status === 'passwords.reset') {
                return response()->json(['message' => 'Your password has been reset!']);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred during password reset', 'message' => $e->getMessage()], 500);
        }

        return response()->json(['error' => 'Failed to reset password', 'status' => $status], 500);
    }

    public function logout(Request $request)
    {
    // if (!$request->user()->hasVerifiedEmail()) {
    //     return response()->json(['message' => 'Email non vérifié.'], 403);
    // }

    $request->user()->currentAccessToken()->delete();

    return response()->json(['message' => 'Successfully logged out'], 200);
    }

}