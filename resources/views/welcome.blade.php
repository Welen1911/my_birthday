<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('Welcome') }} - {{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <!-- Styles -->
        <style>
            .hero {
                background: rgba(0,0,0,0.6);
            }
        </style>
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
        <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
            @if (Route::has('login'))
                <nav class="flex items-center justify-end gap-4">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
                        >
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal"
                        >
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>
        <div class="flex flex-col items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
            <div class="relative w-full max-w-4xl mx-auto"
            style="max-width: 700px; margin: 0 auto; padding: 0 16px;">

                {{-- IMAGEM --}}
                <img
                    src="{{ asset('images/capa.jpeg') }}"
                    alt="Thaís"
                    class="w-full h-[350px] md:h-[450px] lg:h-[500px] object-cover rounded-2xl"
                     style="
                        width: 100%;
                        aspect-ratio: 16/9;
                        object-fit: cover;
                        border-radius: 16px;
                    "
                >

                {{-- OVERLAY ESCURO (pra melhorar leitura) --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent rounded-2xl"></div>

                {{-- TEXTO EM CIMA --}}
                <div class="absolute inset-0 flex items-end justify-start p-6 md:p-10">
                    <div class="text-white max-w-md space-y-3 hero rounded-lg p-6">
                        <h1 class="text-3xl md:text-5xl lg:text-6xl font-medium leading-tight">
                            Thaís 🎂
                        </h1>

                        <p class="text-lg md:text-2xl font-medium opacity-90">
                            29 anos
                        </p>

                        <p class="text-sm md:text-lg opacity-90">
                            Escolha um presente especial e faça parte desse momento incrível ✨
                        </p>
                    </div>
                </div>
            </div>

            {{-- PRODUTOS --}}
            <div class="w-full max-w-6xl px-4">
                @livewire('products.guest-products')
            </div>
        </div>
        @include('partials.toast')

        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif
    </body>
</html>
