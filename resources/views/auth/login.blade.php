<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if ($errors->has('pakamuros'))
        <div class="mb-4 font-medium text-sm text-red-600">
            {{ $errors->first('pakamuros') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <!-- Divider -->
    <div class="relative flex items-center justify-center mt-6">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
        </div>
        <div class="relative bg-white px-4 text-sm text-gray-500">
            {{ __('O continua con') }}
        </div>
    </div>

    <!-- Pakamuros OAuth Button -->
    <div class="mt-6">
        <a href="{{ route('auth.pakamuros.redirect') }}"
           class="w-full inline-flex items-center justify-center gap-3 px-4 py-0.5 border border-transparent rounded-md shadow-sm text-white font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150"
           style="background-color: #124A71;"
           onmouseover="this.style.backgroundColor='#0e3a5a'" onmouseout="this.style.backgroundColor='#124A71'">
            <img src="{{ asset('img/logo-pakamuros.png') }}" alt="Pakamuros" class="h-10 w-10 object-contain">
            {{ __('Continuar con Pakamuros') }}
        </a>
    </div>
</x-guest-layout>
