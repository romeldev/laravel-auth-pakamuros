# Integrar Login con Pakamuros (OAuth2) en tu proyecto Laravel

Guia para integrar el boton "Login con Pakamuros" en cualquier proyecto Laravel usando Socialite.

## Requisitos previos

- Laravel 10+
- Un proyecto con autenticacion funcionando (Breeze, Jetstream, etc.)
- Credenciales OAuth de Pakamuros (`client_id` y `client_secret`)

## 1. Instalar Laravel Socialite

```bash
composer require laravel/socialite
```

## 2. Variables de entorno

Agregar en `.env`:

```env
PAKAMUROS_CLIENT_ID=tu-client-id
PAKAMUROS_CLIENT_SECRET=tu-client-secret
PAKAMUROS_REDIRECT_URI=http://tu-app.test/auth/pakamuros/callback
PAKAMUROS_URL=http://url-de-pakamuros
```

> **Importante:** El `PAKAMUROS_REDIRECT_URI` debe coincidir exactamente con el que esta registrado en el servidor Pakamuros.

## 3. Configurar config/services.php

Agregar el bloque `pakamuros`:

```php
'pakamuros' => [
    'client_id' => env('PAKAMUROS_CLIENT_ID'),
    'client_secret' => env('PAKAMUROS_CLIENT_SECRET'),
    'redirect' => env('PAKAMUROS_REDIRECT_URI'),
    'url' => env('PAKAMUROS_URL'),
],
```

## 4. Crear el proveedor Socialite personalizado

Crear `app/Socialite/PakamurosProvider.php`:

```php
<?php

namespace App\Socialite;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User;

class PakamurosProvider extends AbstractProvider
{
    protected $scopes = [];

    protected function baseUrl(): string
    {
        return rtrim(config('services.pakamuros.url'), '/');
    }

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(
            $this->baseUrl() . '/oauth/authorize',
            $state
        );
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl() . '/oauth/token';
    }

    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get(
            $this->baseUrl() . '/api/oauth/user',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
            ]
        );

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['data'] ?? $data;
    }

    protected function mapUserToObject(array $user): User
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['id'],
            'name' => $user['nombre_completo'],
            'email' => $user['email'],
            'nickname' => $user['usuario'],
        ]);
    }
}
```

## 5. Registrar el driver en AppServiceProvider

En `app/Providers/AppServiceProvider.php`, agregar en el metodo `boot()`:

```php
use App\Socialite\PakamurosProvider;
use Laravel\Socialite\Facades\Socialite;

public function boot(): void
{
    Socialite::extend('pakamuros', function ($app) {
        $config = $app['config']['services.pakamuros'];

        return new PakamurosProvider(
            $app['request'],
            $config['client_id'],
            $config['client_secret'],
            $config['redirect']
        );
    });
}
```

## 6. Migracion

Crear una migracion para agregar el campo `pakamuros_id` a la tabla `users`:

```bash
php artisan make:migration add_pakamuros_fields_to_users_table --table=users
```

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('pakamuros_id')->nullable()->unique()->after('id');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('pakamuros_id');
    });
}
```

Ejecutar la migracion:

```bash
php artisan migrate
```

No olvidar agregar `pakamuros_id` al array `$fillable` del modelo `User`:

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'pakamuros_id',
];
```

## 7. Controlador

Crear `app/Http/Controllers/Auth/PakamurosController.php`:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        try {
            $pakamurosUser = Socialite::driver('pakamuros')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['pakamuros' => 'Error al autenticar con Pakamuros.']);
        }

        $user = User::where('pakamuros_id', $pakamurosUser->getId())->first();

        if (! $user) {
            $user = User::where('email', $pakamurosUser->getEmail())->first();

            if ($user) {
                // Vincular cuenta existente con Pakamuros
                $user->update([
                    'pakamuros_id' => $pakamurosUser->getId(),
                    'name' => $pakamurosUser->getName(),
                ]);
            } else {
                // Crear usuario nuevo
                $user = User::create([
                    'name' => $pakamurosUser->getName(),
                    'email' => $pakamurosUser->getEmail(),
                    'pakamuros_id' => $pakamurosUser->getId(),
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => now(),
                ]);
            }
        } else {
            $user->update(['name' => $pakamurosUser->getName()]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
```

## 8. Rutas

Agregar en `routes/web.php` (o `routes/auth.php`) dentro del middleware `guest`:

```php
use App\Http\Controllers\Auth\PakamurosController;

Route::middleware('guest')->group(function () {
    Route::get('auth/pakamuros/redirect', [PakamurosController::class, 'redirect'])
        ->name('auth.pakamuros.redirect');

    Route::get('auth/pakamuros/callback', [PakamurosController::class, 'callback'])
        ->name('auth.pakamuros.callback');
});
```

## 9. Boton en la vista de login

Agregar en tu vista de login:

```blade
<a href="{{ route('auth.pakamuros.redirect') }}"
   style="background-color: #124A71;"
   class="w-full inline-flex items-center justify-center gap-3 px-4 py-2 rounded-md text-white font-medium text-sm">
    <img src="{{ asset('img/logo-pakamuros.png') }}" alt="Pakamuros" class="h-8 w-8 object-contain">
    Continuar con Pakamuros
</a>
```

## Datos del usuario

La API de Pakamuros (`/api/oauth/user`) devuelve la siguiente estructura:

```json
{
    "data": {
        "id": 7777,
        "usuario": "77777777",
        "nombres": "ROMEL HAMMERLIN",
        "apellido_paterno": "DIAZ",
        "apellido_materno": "RAMOS",
        "nombre_completo": "DIAZ RAMOS ROMEL HAMMERLIN",
        "dni": "77777777",
        "email": "romel@example.com",
        "es_activo": true
    }
}
```

Los datos accesibles via Socialite despues del login:

| Metodo Socialite           | Campo Pakamuros      |
|----------------------------|----------------------|
| `$user->getId()`           | `id`                 |
| `$user->getName()`         | `nombre_completo`    |
| `$user->getEmail()`        | `email`              |
| `$user->getNickname()`     | `usuario`            |
| `$user->getRaw()`          | Array completo       |

Para acceder a campos adicionales como `dni` o `es_activo`:

```php
$raw = $pakamurosUser->getRaw();
$dni = $raw['dni'];
$activo = $raw['es_activo'];
```

## Requisito en el servidor Pakamuros

El administrador de Pakamuros debe registrar un OAuth Client con:

- **Redirect URI:** `http://tu-app.test/auth/pakamuros/callback` (debe coincidir exactamente con `PAKAMUROS_REDIRECT_URI`)
- **Grant type:** Authorization Code

El `client_id` y `client_secret` generados se colocan en el `.env` del proyecto cliente.
