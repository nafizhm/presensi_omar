<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — {{ config('app.name', 'Presensi') }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap">
    <style>
        :root {
            --ink: #0E2A2E;
            --ink-soft: #4A5D5A;
            --paper: #F3F6F4;
            --teal: #0D9488;
            --teal-dark: #0B7A70;
            --teal-tint: #E3F3F1;
            --coral: #E4572E;
            --line: #DDE4E1;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: #DEE4E1;
            color: var(--ink);
            display: flex;
            justify-content: center;
        }

        .display { font-family: 'Space Grotesk', sans-serif; }

        .phone-frame {
            width: 100%;
            max-width: 430px;
            min-height: 100dvh;
            background: var(--paper);
            display: flex;
            flex-direction: column;
        }

        @media (min-width: 640px) {
            .phone-frame {
                min-height: 780px;
                margin: 2rem 0;
                border: 8px solid #16231F;
                border-radius: 2.5rem;
                overflow: hidden;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, .35);
            }
        }

        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 3.5rem 1.75rem 2rem;
        }

        .logo-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo-wrap img {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
            margin-bottom: 1rem;
        }

        .company-name { font-size: 18px; font-weight: 600; }
        .app-desc { font-size: 12px; color: var(--ink-soft); margin-top: 4px; }

        .status-msg, .error-box {
            font-size: 12px;
            padding: 10px 16px;
            border-radius: 12px;
            margin-bottom: 1rem;
        }

        .status-msg { background: var(--teal-tint); color: var(--teal-dark); }
        .error-box { background: #FBE7E0; color: var(--coral); }

        form { display: flex; flex-direction: column; gap: 1rem; }

        label {
            font-size: 12px;
            font-weight: 500;
            color: var(--ink-soft);
            display: block;
            margin-bottom: 6px;
        }

        .row-between {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .link {
            font-size: 12px;
            color: var(--teal-dark);
            font-weight: 500;
            text-decoration: none;
        }

        .field { position: relative; }

        .field svg.icon-left {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .field input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: #fff;
            font-size: 14px;
            color: var(--ink);
            outline: none;
            font-family: inherit;
        }

        .field input::placeholder { color: rgba(74, 93, 90, .6); }
        .field input:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(13, 148, 136, .15); }
        .field input.has-toggle { padding-right: 44px; }
        .field input.invalid { border-color: var(--coral); }

        .toggle-eye {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            line-height: 0;
        }

        .error-msg { font-size: 12px; color: var(--coral); margin-top: 6px; }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 2px;
            user-select: none;
        }

        .remember input { width: 16px; height: 16px; accent-color: var(--teal-dark); }
        .remember span { font-size: 13px; color: var(--ink-soft); }

        .btn-primary {
            width: 100%;
            padding: 14px;
            border-radius: 16px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: opacity .15s;
        }

        .btn-primary {
            margin-top: 4px;
            border: none;
            background: var(--teal-dark);
            color: #fff;
        }

        .btn-primary:disabled { cursor: wait; opacity: .75; }

        .spinner {
            display: none;
            width: 14px;
            height: 14px;
            margin-right: 7px;
            vertical-align: -2px;
            border: 2px solid rgba(255, 255, 255, .45);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        .is-loading .spinner { display: inline-block; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .footer { margin-top: auto; padding-top: 2.5rem; text-align: center; }
        .footer span { font-size: 11px; color: var(--ink-soft); }
    </style>
</head>
<body>
<div class="phone-frame">
    <div class="content">
        <div class="logo-wrap">
            @if ($companyProfile?->logo)
                <img src="{{ asset('storage/'.$companyProfile->logo) }}" alt="Logo {{ $companyProfile->company_name }}">
            @else
                <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iOTYiIGhlaWdodD0iOTYiIHZpZXdCb3g9IjAgMCA5NiA5NiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8ZGVmcz4KICAgIDxsaW5lYXJHcmFkaWVudCBpZD0iZyIgeDE9IjAiIHkxPSIwIiB4Mj0iMSIgeTI9IjEiPgogICAgICA8c3RvcCBvZmZzZXQ9IjAlIiBzdG9wLWNvbG9yPSIjMEQ5NDg4Ii8+CiAgICAgIDxzdG9wIG9mZnNldD0iMTAwJSIgc3RvcC1jb2xvcj0iIzBCN0E3MCIvPgogICAgPC9saW5lYXJHcmFkaWVudD4KICA8L2RlZnM+CiAgPHJlY3Qgd2lkdGg9Ijk2IiBoZWlnaHQ9Ijk2IiByeD0iMjQiIGZpbGw9IiMwRTJBMkUiLz4KICA8cGF0aCBkPSJNNDggMjBjLTEwLjUgMC0xOSA4LjUtMTkgMTkgMCAxNC4yNSAxOSAzMyAxOSAzM3MxOS0xOC43NSAxOS0zM2MwLTEwLjUtOC41LTE5LTE5LTE5WiIgZmlsbD0idXJsKCNnKSIvPgogIDxjaXJjbGUgY3g9IjQ4IiBjeT0iMzkiIHI9IjgiIGZpbGw9IiMwRTJBMkUiLz4KICA8cGF0aCBkPSJNNDQuNSAzOS41IDQ3IDQybDUtNS41IiBzdHJva2U9IiNmZmYiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBmaWxsPSJub25lIi8+Cjwvc3ZnPg==" alt="Logo perusahaan">
            @endif
            <div class="company-name display">{{ $companyProfile?->company_name ?? config('app.name', 'Presensi Mobile') }}</div>
            <div class="app-desc">Aplikasi Presensi Karyawan</div>
        </div>

        @if (session('status'))
            <div class="status-msg">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="error-box">Data login tidak valid. Silakan periksa kembali.</div>
        @endif

        <form id="loginForm" method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <label for="employee_code">Kode Karyawan</label>
                <div class="field">
                    <svg class="icon-left" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5D5A" stroke-width="2">
                        <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>
                    </svg>
                    <input type="text" id="employee_code" name="employee_code"
                           class="{{ $errors->has('employee_code') ? 'invalid' : '' }}"
                           value="{{ old('employee_code') }}" placeholder="Contoh: KRY001"
                           autocomplete="username" autocapitalize="characters" required autofocus>
                </div>
                @error('employee_code')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <div class="row-between">
                    <label for="password" style="margin-bottom:0">Kata sandi</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="link">Lupa kata sandi?</a>
                    @endif
                </div>
                <div class="field">
                    <svg class="icon-left" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5D5A" stroke-width="2">
                        <rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                    </svg>
                    <input type="password" id="password" name="password"
                           class="has-toggle {{ $errors->has('password') ? 'invalid' : '' }}"
                           placeholder="••••••••" autocomplete="current-password" required>
                    <button type="button" class="toggle-eye" id="passwordToggle" aria-label="Tampilkan kata sandi">
                        <svg id="eyeOpen" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5D5A" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg id="eyeClosed" style="display:none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5D5A" stroke-width="2"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a19.9 19.9 0 0 1 4.22-5.44M9.9 4.24A10.4 10.4 0 0 1 12 4c7 0 11 8 11 8a19.86 19.86 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
                @error('password')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <label class="remember">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                <span>Ingat saya di perangkat ini</span>
            </label>

            <button type="submit" class="btn-primary" id="loginButton">
                <span class="spinner"></span><span class="button-text">Masuk</span>
            </button>
        </form>

        <div class="footer">
            <span>© {{ date('Y') }} {{ $companyProfile?->company_name ?? config('app.name', 'Presensi Mobile') }} · v1.0</span>
        </div>
    </div>
</div>

<script>
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('passwordToggle');
    const eyeOpen = document.getElementById('eyeOpen');
    const eyeClosed = document.getElementById('eyeClosed');
    const loginForm = document.getElementById('loginForm');
    const loginButton = document.getElementById('loginButton');

    passwordToggle.addEventListener('click', function () {
        const showPassword = passwordInput.type === 'password';
        passwordInput.type = showPassword ? 'text' : 'password';
        eyeOpen.style.display = showPassword ? 'none' : 'block';
        eyeClosed.style.display = showPassword ? 'block' : 'none';
        passwordToggle.setAttribute('aria-label', showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    });

    loginForm.addEventListener('submit', function () {
        loginButton.disabled = true;
        loginButton.classList.add('is-loading');
        loginButton.querySelector('.button-text').textContent = 'Memproses...';
    });
</script>
</body>
</html>
