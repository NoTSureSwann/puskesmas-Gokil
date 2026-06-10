@extends('layouts.app')

@section('title', 'Masuk Akun - SI Puskesmas & Klinik')

@section('styles')
<style>
    .login-container {
        max-width: 480px;
        margin: 2rem auto;
    }
    .role-tabs {
        background: #f1f5f9;
        padding: 6px;
        border-radius: 12px;
        display: flex;
        gap: 4px;
        margin-bottom: 2rem;
    }
    .role-tab-btn {
        flex: 1;
        border: none;
        background: transparent;
        padding: 0.6rem 0.4rem;
        border-radius: 8px;
        font-family: var(--font-display);
        font-weight: 600;
        font-size: 0.85rem;
        color: #64748b;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 3px;
    }
    .role-tab-btn i {
        font-size: 1.1rem;
    }
    .role-tab-btn.active {
        background: white;
        color: var(--primary);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }
    .input-group-text {
        background-color: transparent;
        border-right: none;
        color: #94a3b8;
    }
    .form-control-has-icon {
        border-left: none;
    }
    .form-control-has-icon:focus {
        border-color: #dee2e6;
        box-shadow: none;
    }
    .input-group:focus-within {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.15);
        border-radius: 8px;
    }
    .input-group:focus-within .input-group-text,
    .input-group:focus-within .form-control {
        border-color: var(--primary);
    }
</style>
@endsection

@section('content')
<div class="login-container animated-fade" x-data="{ activeRole: '{{ old('role', $role ?? 'pasien') }}', showPassword: false }">
    <div class="card card-premium p-4 p-md-5">
        <div class="text-center mb-4">
            <span class="text-primary fs-1"><i class="fa-solid fa-stethoscope"></i></span>
            <h3 class="fw-bold mt-2">{{ __('messages.login_title') }}</h3>
            <p class="text-muted small">{{ __('messages.pilih_peran') }}</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-premium mb-4 py-2 px-3">
                <ul class="mb-0 small ps-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ROLE TAB SELECTOR (Alpine.js) -->
        <div class="role-tabs">
            <button type="button" class="role-tab-btn" :class="activeRole === 'pasien' ? 'active' : ''" @click="activeRole = 'pasien'">
                <i class="fa-solid fa-user-injured"></i>
                <span>{{ __('messages.pasien') }}</span>
            </button>
            <button type="button" class="role-tab-btn" :class="activeRole === 'dokter' ? 'active' : ''" @click="activeRole = 'dokter'">
                <i class="fa-solid fa-user-doctor"></i>
                <span>{{ __('messages.dokter') }}</span>
            </button>
            <button type="button" class="role-tab-btn" :class="activeRole === 'farmasi' ? 'active' : ''" @click="activeRole = 'farmasi'">
                <i class="fa-solid fa-prescription-bottle-medical"></i>
                <span>{{ __('messages.farmasi') }}</span>
            </button>
            <button type="button" class="role-tab-btn" :class="activeRole === 'admin' ? 'active' : ''" @click="activeRole = 'admin'">
                <i class="fa-solid fa-user-gear"></i>
                <span>{{ __('messages.admin') }}</span>
            </button>
        </div>

        <!-- DUMMY LOGIN CREDENTIALS -->
        <div class="alert py-2 px-3 small mb-4" style="background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px;">
            <div class="d-flex align-items-center mb-1">
                <i class="fa-solid fa-laptop-code text-primary me-2"></i>
                <strong class="text-dark">{{ __('messages.kredensial_prototipe') }}</strong>
            </div>
            <div x-show="activeRole === 'pasien'">
                Email: <code class="user-select-all" style="cursor: pointer;" @click="$event.target.select(); navigator.clipboard.writeText('pasien.bpjs@gmail.com');">pasien.bpjs@gmail.com</code> <br> 
                Password: <code class="user-select-all" style="cursor: pointer;" @click="$event.target.select(); navigator.clipboard.writeText('password123');">password123</code>
            </div>
            <div x-show="activeRole === 'dokter'" style="display: none;">
                Email: <code class="user-select-all" style="cursor: pointer;" @click="$event.target.select(); navigator.clipboard.writeText('dokter@puskesmas.go.id');">dokter@puskesmas.go.id</code> <br> 
                Password: <code class="user-select-all" style="cursor: pointer;" @click="$event.target.select(); navigator.clipboard.writeText('password123');">password123</code>
            </div>
            <div x-show="activeRole === 'farmasi'" style="display: none;">
                Email: <code class="user-select-all" style="cursor: pointer;" @click="$event.target.select(); navigator.clipboard.writeText('farmasi@puskesmas.go.id');">farmasi@puskesmas.go.id</code> <br> 
                Password: <code class="user-select-all" style="cursor: pointer;" @click="$event.target.select(); navigator.clipboard.writeText('password123');">password123</code>
            </div>
            <div x-show="activeRole === 'admin'" style="display: none;">
                Email: <code class="user-select-all" style="cursor: pointer;" @click="$event.target.select(); navigator.clipboard.writeText('admin@puskesmas.go.id');">admin@puskesmas.go.id</code> <br> 
                Password: <code class="user-select-all" style="cursor: pointer;" @click="$event.target.select(); navigator.clipboard.writeText('password123');">password123</code>
            </div>
        </div>

        <!-- LOGIN FORM -->
        <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            
            <!-- Hidden role input connected to Alpine.js -->
            <input type="hidden" name="role" :value="activeRole">

            <!-- EMAIL -->
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold small">{{ __('messages.email') }}</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" class="form-control form-control-has-icon" id="email" name="email" value="{{ old('email') }}" placeholder="email@contoh.com" required autocomplete="email" autofocus>
                </div>
            </div>

            <!-- PASSWORD -->
            <div class="mb-4">
                <div class="d-flex justify-content-between">
                    <label for="password" class="form-label fw-semibold small">{{ __('messages.password') }}</label>
                    <a href="#" class="small text-decoration-none text-primary">Lupa Password?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input :type="showPassword ? 'text' : 'password'" class="form-control form-control-has-icon" id="password" name="password" placeholder="Masukkan kata sandi" required autocomplete="current-password">
                    <button class="btn btn-outline-secondary border-start-0" type="button" style="border-color: #dee2e6; color: #94a3b8;" @click="showPassword = !showPassword">
                        <i class="fa-solid" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
            </div>

            <!-- REMEMBER ME -->
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label text-muted small" for="remember">
                    {{ __('messages.ingat_saya') }}
                </label>
            </div>

            <!-- SUBMIT BUTTON -->
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary btn-lg text-white"><i class="fa-solid fa-right-to-bracket me-2"></i> {{ __('messages.btn_masuk') }}</button>
            </div>

            <!-- DYNAMIC REGISTER LINK -->
            <div class="text-center">
                <span class="text-muted small">{{ __('messages.belum_punya_akun') }} <a :href="`/register/${activeRole}`" class="text-primary fw-medium text-decoration-none">{{ __('messages.daftar_sebagai') }}</a></span>
            </div>
        </form>
    </div>
</div>
@endsection
