<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class PakamurosController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('pakamuros')->redirect();
    }

    public function callback(): RedirectResponse
    {
        Log::info('Pakamuros callback iniciado', ['query' => request()->query()]);

        try {
            $pakamurosUser = Socialite::driver('pakamuros')->user();
            Log::info('Pakamuros usuario obtenido', [
                'id' => $pakamurosUser->getId(),
                'name' => $pakamurosUser->getName(),
                'email' => $pakamurosUser->getEmail(),
                'raw' => $pakamurosUser->getRaw(),
            ]);
        } catch (\Exception $e) {
            Log::error('Pakamuros callback error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('login')
                ->withErrors(['pakamuros' => 'Error al autenticar con Pakamuros. Intenta de nuevo.']);
        }

        $raw = $pakamurosUser->getRaw();

        $user = User::where('pakamuros_id', $pakamurosUser->getId())->first();

        if (! $user) {
            $user = User::where('email', $pakamurosUser->getEmail())->first();

            if ($user) {
                $user->update([
                    'pakamuros_id' => $pakamurosUser->getId(),
                    'name' => $pakamurosUser->getName(),
                ]);
            } else {
                $user = User::create([
                    'name' => $pakamurosUser->getName(),
                    'email' => $pakamurosUser->getEmail(),
                    'pakamuros_id' => $pakamurosUser->getId(),
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => now(),
                ]);
            }
        } else {
            $user->update([
                'name' => $pakamurosUser->getName(),
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
