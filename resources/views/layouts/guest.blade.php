<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Local Sponsorship Portal') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            .guest-stage {
                min-height: 100vh;
                overflow: hidden;
                background-image:
                    linear-gradient(135deg, rgba(15, 94, 184, 0.74), rgba(15, 23, 42, 0.5)),
                    url('{{ asset('images/auth-children-bg.png') }}');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            }

            .guest-form-card {
                border: 1px solid rgba(255, 255, 255, 0.68);
                border-radius: 2rem;
                background: rgba(255, 255, 255, 0.94);
                box-shadow: 0 32px 70px -34px rgba(15, 23, 42, 0.38);
                backdrop-filter: blur(18px);
            }

            .guest-form-card input,
            .guest-form-card select {
                min-height: 52px;
                border-radius: 1rem;
                border-color: #cbd5e1;
                background: #fff;
                font-weight: 700;
                color: #0f172a;
            }

            .guest-form-card input:focus,
            .guest-form-card select:focus {
                border-color: #3b82f6;
                box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
            }

            .guest-form-card input::placeholder,
            .guest-form-card select::placeholder {
                color: #64748b;
                font-weight: 600;
            }

            .guest-note {
                border: 1px solid #dbeafe;
                border-radius: 1.5rem;
                background: linear-gradient(90deg, #eff6ff 0%, #ffffff 100%);
            }

            .guest-auth-shell {
                width: 100%;
                max-width: 38rem;
                margin: 0 auto;
            }

            .guest-topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1.75rem;
            }

            .guest-brand-chip {
                display: inline-flex;
                align-items: center;
                gap: 0.85rem;
                padding: 0.7rem 0.95rem;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.14);
                border: 1px solid rgba(255, 255, 255, 0.22);
                color: #fff;
                box-shadow: 0 18px 36px -26px rgba(15, 23, 42, 0.45);
                backdrop-filter: blur(14px);
            }

            .guest-brand-chip img {
                width: 2.8rem;
                height: 2.8rem;
                border-radius: 999px;
                background: #fff;
                padding: 0.4rem;
                object-fit: cover;
            }

            .guest-brand-chip strong {
                display: block;
                font-size: 0.95rem;
                line-height: 1.2;
            }

            .guest-brand-chip span {
                display: block;
                margin-top: 0.12rem;
                font-size: 0.72rem;
                color: rgba(219, 234, 254, 0.96);
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .guest-home-link {
                display: inline-flex;
                align-items: center;
                gap: 0.55rem;
                border-radius: 999px;
                padding: 0.9rem 1.1rem;
                border: 1px solid rgba(255, 255, 255, 0.24);
                background: rgba(255, 255, 255, 0.14);
                color: #fff;
                font-weight: 700;
                text-decoration: none;
                backdrop-filter: blur(14px);
                transition: 0.2s ease;
            }

            .guest-home-link:hover {
                background: rgba(255, 255, 255, 0.2);
            }

            .guest-copy {
                margin-bottom: 1.5rem;
                color: #fff;
                text-shadow: 0 10px 28px rgba(15, 23, 42, 0.28);
            }

            .guest-copy p:first-child {
                color: #dbeafe !important;
            }

            .guest-copy h1 {
                color: #fff !important;
            }

            .guest-copy p:last-child {
                color: rgba(239, 246, 255, 0.95) !important;
                max-width: 34rem;
            }

            .guest-field {
                display: block;
            }

            .guest-field-head {
                display: flex;
                align-items: center;
                gap: 0.6rem;
                margin-bottom: 0.7rem;
            }

            .guest-field-icon {
                width: 2.25rem;
                height: 2.25rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                background: linear-gradient(135deg, #dbeafe, #bfdbfe);
                color: #1d4ed8;
                box-shadow: 0 12px 24px -18px rgba(37, 99, 235, 0.45);
                flex-shrink: 0;
            }

            .guest-field-label {
                font-size: 0.92rem;
                font-weight: 800;
                color: #0f172a;
                letter-spacing: 0.01em;
            }

            .guest-remember {
                font-weight: 700;
                color: #334155;
            }

            .guest-link {
                font-weight: 800 !important;
            }

            .guest-role-option {
                display: flex;
                align-items: flex-start;
                gap: 0.85rem;
                border: 1px solid #dbeafe;
                border-radius: 1.25rem;
                background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
                padding: 1rem;
                cursor: pointer;
                transition: 0.2s ease;
            }

            .guest-role-option:hover {
                border-color: #93c5fd;
                box-shadow: 0 18px 35px -30px rgba(37, 99, 235, 0.45);
            }

            .guest-role-option input {
                margin-top: 0.2rem;
                min-height: auto;
                transform: scale(1.15);
            }

            .guest-role-option strong {
                display: block;
                font-size: 0.92rem;
                color: #0f172a;
            }

            .guest-role-option small {
                display: block;
                margin-top: 0.25rem;
                font-size: 0.78rem;
                line-height: 1.5;
                color: #64748b;
            }

            @media (max-width: 768px) {
                .guest-topbar {
                    flex-direction: column;
                    align-items: stretch;
                }

                .guest-home-link {
                    justify-content: center;
                }
            }
        </style>
    </head>
    <body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
        @php
            $guestPortalTitle = \App\Models\User::defaultPortalTitle();
            $guestPortalSubtitle = \App\Models\User::defaultPortalSubtitle();
            $guestRotatingLogos = [
                asset('images/welcome-rotating-logo-1.jpeg'),
                asset('images/welcome-rotating-logo-2.jpeg'),
            ];
        @endphp
        <div class="guest-stage relative">
            <div class="relative mx-auto flex min-h-screen max-w-7xl items-center px-4 py-8 sm:px-6 lg:px-8">
                <div class="guest-auth-shell">
                    <div class="guest-topbar">
                        <a href="{{ url('/') }}" class="guest-brand-chip">
                            <img
                                id="guest-rotating-logo"
                                src="{{ $guestRotatingLogos[0] }}"
                                alt="Local Sponsorship Portal"
                                data-logos='@json($guestRotatingLogos)'>
                            <span>
                                <strong>{{ $guestPortalTitle }}</strong>
                                <span>{{ $guestPortalSubtitle }}</span>
                            </span>
                        </a>

                        <a href="{{ url('/') }}" class="guest-home-link">
                            <i class="bi bi-house-door"></i>
                            <span>Welcome Home</span>
                        </a>
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </div>

        <x-system-footer />

        <script>
            (() => {
                const logo = document.getElementById('guest-rotating-logo');

                if (!logo) {
                    return;
                }

                const logos = JSON.parse(logo.dataset.logos || '[]');

                if (logos.length < 2) {
                    return;
                }

                let currentIndex = 0;

                setInterval(() => {
                    currentIndex = (currentIndex + 1) % logos.length;
                    logo.style.opacity = '0';

                    setTimeout(() => {
                        logo.src = logos[currentIndex];
                        logo.style.opacity = '1';
                    }, 250);
                }, 5000);
            })();
        </script>
    </body>
</html>
