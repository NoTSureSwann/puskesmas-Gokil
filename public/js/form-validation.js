/**
 * =============================================================
 * Form Validation Real-Time — SI Puskesmas & Klinik Metopen
 * =============================================================
 * Validasi client-side real-time untuk semua form CRUD.
 * Menyediakan feedback visual langsung tanpa reload halaman.
 * Auto-save draft ke localStorage untuk form panjang.
 */

(function () {
    'use strict';

    // ===========================
    // VALIDATION RULES REGISTRY
    // ===========================

    const rules = {
        required(value) {
            return value !== null && value !== undefined && String(value).trim() !== '';
        },
        email(value) {
            if (!value) return true;
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        },
        minLength(value, min) {
            if (!value) return true;
            return String(value).length >= min;
        },
        maxLength(value, max) {
            if (!value) return true;
            return String(value).length <= max;
        },
        exactLength(value, len) {
            if (!value) return true;
            return String(value).replace(/\D/g, '').length === len;
        },
        numeric(value) {
            if (!value) return true;
            return /^\d+$/.test(String(value));
        },
        phone(value) {
            if (!value) return true;
            return /^08[0-9]{8,13}$/.test(String(value));
        },
        nik(value) {
            if (!value) return true;
            const digits = String(value).replace(/\D/g, '');
            return digits.length === 16;
        },
        bpjs(value) {
            if (!value) return true;
            const digits = String(value).replace(/\D/g, '');
            return digits.length === 13;
        },
        noKK(value) {
            if (!value) return true;
            const digits = String(value).replace(/\D/g, '');
            return digits.length === 16;
        },
        passwordMatch(value, confirmField) {
            const confirmInput = document.querySelector(`[name="${confirmField}"]`);
            if (!confirmInput) return true;
            return value === confirmInput.value;
        },
        minValue(value, min) {
            if (!value && value !== 0) return true;
            return parseFloat(value) >= min;
        },
        maxValue(value, max) {
            if (!value && value !== 0) return true;
            return parseFloat(value) <= max;
        },
        dateNotPast(value) {
            if (!value) return true;
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const selected = new Date(value);
            return selected >= today;
        },
        dateNotFuture(value) {
            if (!value) return true;
            const today = new Date();
            today.setHours(23, 59, 59, 999);
            const selected = new Date(value);
            return selected <= today;
        },
        alphaNumDash(value) {
            if (!value) return true;
            return /^[A-Za-z0-9\-]+$/.test(value);
        }
    };

    // ===========================
    // MESSAGES
    // ===========================

    const messages = {
        required:       'Field ini wajib diisi.',
        email:          'Format email tidak valid.',
        minLength:      'Minimal {param} karakter.',
        maxLength:      'Maksimal {param} karakter.',
        exactLength:    'Harus tepat {param} digit.',
        numeric:        'Hanya boleh berupa angka.',
        phone:          'Format nomor HP tidak valid (contoh: 081234567890).',
        nik:            'NIK harus 16 digit angka.',
        bpjs:           'Nomor BPJS harus 13 digit angka.',
        noKK:           'Nomor KK harus 16 digit angka.',
        passwordMatch:  'Konfirmasi password tidak cocok.',
        minValue:       'Nilai minimal {param}.',
        maxValue:       'Nilai maksimal {param}.',
        dateNotPast:    'Tanggal tidak boleh di masa lalu.',
        dateNotFuture:  'Tanggal tidak boleh di masa depan.',
        alphaNumDash:   'Hanya boleh huruf, angka, dan tanda hubung.',
    };

    // ===========================
    // VALIDATION CONFIG PER FORM
    // ===========================

    const formConfigs = {
        // Registrasi Pasien
        'form-register-pasien': {
            name:               [{ rule: 'required' }, { rule: 'maxLength', param: 100 }],
            email:              [{ rule: 'required' }, { rule: 'email' }],
            phone:              [{ rule: 'required' }, { rule: 'phone' }],
            password:           [{ rule: 'required' }, { rule: 'minLength', param: 8 }],
            password_confirmation: [{ rule: 'required' }, { rule: 'passwordMatch', param: 'password' }],
            nik:                [{ rule: 'required' }, { rule: 'nik' }],
            no_bpjs:            [{ rule: 'bpjs' }],
            no_kk:              [{ rule: 'noKK' }],
            jenis_kelamin:      [{ rule: 'required' }],
            tanggal_lahir:      [{ rule: 'required' }, { rule: 'dateNotFuture' }],
            tempat_lahir:       [{ rule: 'required' }],
            alamat:             [{ rule: 'required' }],
            kelurahan:          [{ rule: 'required' }],
            kecamatan:          [{ rule: 'required' }],
            jenis_pasien:       [{ rule: 'required' }],
        },

        // Login
        'form-login': {
            email:    [{ rule: 'required' }, { rule: 'email' }],
            password: [{ rule: 'required' }],
        },

        // CRUD Poli (Admin)
        'form-poli-store': {
            kode_poli: [{ rule: 'required' }, { rule: 'maxLength', param: 10 }, { rule: 'alphaNumDash' }],
            nama_poli: [{ rule: 'required' }, { rule: 'maxLength', param: 100 }],
            deskripsi: [{ rule: 'maxLength', param: 500 }],
        },

        // CRUD Obat (Admin)
        'form-obat-store': {
            kode_obat:     [{ rule: 'required' }, { rule: 'maxLength', param: 20 }, { rule: 'alphaNumDash' }],
            nama_obat:     [{ rule: 'required' }, { rule: 'maxLength', param: 100 }],
            satuan:        [{ rule: 'required' }, { rule: 'maxLength', param: 50 }],
            kategori:      [{ rule: 'required' }],
            stok:          [{ rule: 'required' }, { rule: 'numeric' }, { rule: 'minValue', param: 0 }],
            stok_minimum:  [{ rule: 'required' }, { rule: 'numeric' }, { rule: 'minValue', param: 0 }],
            harga_satuan:  [{ rule: 'required' }, { rule: 'minValue', param: 0 }],
        },

        // Create User (Admin)
        'form-user-store': {
            name:     [{ rule: 'required' }, { rule: 'maxLength', param: 255 }],
            email:    [{ rule: 'required' }, { rule: 'email' }],
            password: [{ rule: 'required' }, { rule: 'minLength', param: 8 }],
            password_confirmation: [{ rule: 'required' }, { rule: 'passwordMatch', param: 'password' }],
        },

        // Daftar Kunjungan (Pasien)
        'form-kunjungan-daftar': {
            poli_id:           [{ rule: 'required' }],
            keluhan:           [{ rule: 'required' }, { rule: 'maxLength', param: 2000 }],
            jenis_kunjungan:   [{ rule: 'required' }],
            tanggal_kunjungan: [{ rule: 'required' }, { rule: 'dateNotPast' }],
        },

        // Profil Pasien Update
        'form-profil-pasien': {
            name:         [{ rule: 'required' }, { rule: 'maxLength', param: 100 }],
            phone:        [{ rule: 'required' }, { rule: 'phone' }],
            nik:          [{ rule: 'required' }, { rule: 'nik' }],
            no_bpjs:      [{ rule: 'bpjs' }],
            no_kk:        [{ rule: 'noKK' }],
            jenis_kelamin: [{ rule: 'required' }],
            tanggal_lahir: [{ rule: 'required' }, { rule: 'dateNotFuture' }],
            tempat_lahir:  [{ rule: 'required' }],
            alamat:        [{ rule: 'required' }],
            kelurahan:     [{ rule: 'required' }],
            kecamatan:     [{ rule: 'required' }],
            jenis_pasien:  [{ rule: 'required' }],
        },

        // Profil Dokter Update
        'form-profil-dokter': {
            name:         [{ rule: 'required' }, { rule: 'maxLength', param: 100 }],
            phone:        [{ rule: 'required' }, { rule: 'phone' }],
            spesialisasi: [{ rule: 'required' }, { rule: 'maxLength', param: 100 }],
        },
    };

    // ===========================
    // CORE VALIDATION ENGINE
    // ===========================

    function validateField(input, fieldRules) {
        const value = input.value;
        for (const { rule, param } of fieldRules) {
            const validator = rules[rule];
            if (!validator) continue;

            const isValid = param !== undefined ? validator(value, param) : validator(value);
            if (!isValid) {
                const msg = messages[rule]
                    ? messages[rule].replace('{param}', String(param))
                    : 'Input tidak valid.';
                return { valid: false, message: msg };
            }
        }
        return { valid: true, message: '' };
    }

    function showFeedback(input, result) {
        // Remove existing feedback
        const parent = input.closest('.mb-3') || input.parentElement;
        const existingFeedback = parent.querySelector('.realtime-feedback');
        if (existingFeedback) existingFeedback.remove();

        // Remove existing state classes
        input.classList.remove('is-valid', 'is-invalid');

        if (!input.value && !result.valid) {
            // Don't show invalid state on empty optional fields
            const fieldRules = getFieldRules(input);
            const isRequired = fieldRules && fieldRules.some(r => r.rule === 'required');
            if (!isRequired) return;
        }

        if (result.valid) {
            if (input.value) {
                input.classList.add('is-valid');
            }
        } else {
            input.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'realtime-feedback invalid-feedback d-block';
            feedback.textContent = result.message;
            feedback.style.cssText = 'font-size: 0.8rem; margin-top: 4px; animation: fadeIn 0.2s ease-in;';
            parent.appendChild(feedback);
        }
    }

    function getFieldRules(input) {
        const form = input.closest('form');
        if (!form || !form.id) return null;
        const config = formConfigs[form.id];
        if (!config) return null;
        return config[input.name] || null;
    }

    // ===========================
    // EVENT BINDING
    // ===========================

    function bindValidation() {
        Object.keys(formConfigs).forEach(formId => {
            const form = document.getElementById(formId);
            if (!form) return;

            const config = formConfigs[formId];

            Object.keys(config).forEach(fieldName => {
                const input = form.querySelector(`[name="${fieldName}"]`);
                if (!input) return;

                // Real-time validation on input/change
                const eventType = (input.tagName === 'SELECT' || input.type === 'date' || input.type === 'checkbox')
                    ? 'change' : 'input';

                input.addEventListener(eventType, function () {
                    const result = validateField(this, config[fieldName]);
                    showFeedback(this, result);
                });

                // Also validate on blur
                input.addEventListener('blur', function () {
                    const result = validateField(this, config[fieldName]);
                    showFeedback(this, result);
                });
            });

            // Prevent submit if invalid
            form.addEventListener('submit', function (e) {
                let hasError = false;
                Object.keys(config).forEach(fieldName => {
                    const input = form.querySelector(`[name="${fieldName}"]`);
                    if (!input) return;
                    const result = validateField(input, config[fieldName]);
                    showFeedback(input, result);
                    if (!result.valid) hasError = true;
                });

                if (hasError) {
                    e.preventDefault();
                    // Scroll to first error
                    const firstError = form.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                }
            });
        });
    }

    // ===========================
    // AUTO-SAVE DRAFT (localStorage)
    // ===========================

    const autoSaveForms = [
        'form-register-pasien',
        'form-profil-pasien',
        'form-user-store',
    ];

    function initAutoSave() {
        autoSaveForms.forEach(formId => {
            const form = document.getElementById(formId);
            if (!form) return;

            const storageKey = `metopen_draft_${formId}`;

            // Restore draft
            try {
                const saved = localStorage.getItem(storageKey);
                if (saved) {
                    const data = JSON.parse(saved);
                    Object.keys(data).forEach(name => {
                        const input = form.querySelector(`[name="${name}"]`);
                        if (input && input.type !== 'password' && input.type !== 'hidden' && input.name !== '_token') {
                            if (input.type === 'radio' || input.type === 'checkbox') {
                                if (input.value === data[name]) input.checked = true;
                            } else {
                                input.value = data[name];
                            }
                        }
                    });

                    // Show subtle restore notification
                    const notice = document.createElement('div');
                    notice.className = 'alert alert-info alert-dismissible fade show py-2 px-3 mb-3';
                    notice.style.cssText = 'font-size: 0.85rem; animation: fadeIn 0.3s ease-in;';
                    notice.innerHTML = `
                        <i class="fa-solid fa-rotate-left me-1"></i>
                        Draft sebelumnya berhasil dipulihkan.
                        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    form.prepend(notice);
                }
            } catch (e) {
                // Ignore parse errors
            }

            // Save draft on input
            let saveTimeout;
            form.addEventListener('input', function () {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(() => {
                    const formData = new FormData(form);
                    const data = {};
                    for (const [key, value] of formData.entries()) {
                        if (key !== '_token' && key !== 'password' && key !== 'password_confirmation') {
                            data[key] = value;
                        }
                    }
                    try {
                        localStorage.setItem(storageKey, JSON.stringify(data));
                    } catch (e) {
                        // Storage full, ignore
                    }
                }, 500); // Debounce 500ms
            });

            // Clear draft on successful submit
            form.addEventListener('submit', function () {
                try {
                    localStorage.removeItem(storageKey);
                } catch (e) {
                    // Ignore
                }
            });
        });
    }

    // ===========================
    // PASSWORD STRENGTH METER
    // ===========================

    function initPasswordStrength() {
        const passwordInputs = document.querySelectorAll('input[name="password"]');
        passwordInputs.forEach(input => {
            const form = input.closest('form');
            if (!form || !form.id) return;
            if (!formConfigs[form.id]) return;

            const meter = document.createElement('div');
            meter.className = 'password-strength-meter mt-1';
            meter.style.cssText = 'height: 4px; border-radius: 2px; transition: all 0.3s ease; background: #e9ecef;';
            const bar = document.createElement('div');
            bar.style.cssText = 'height: 100%; border-radius: 2px; transition: all 0.3s ease; width: 0%;';
            meter.appendChild(bar);

            const label = document.createElement('small');
            label.className = 'text-muted d-block mt-1';
            label.style.fontSize = '0.75rem';

            const parent = input.closest('.mb-3') || input.parentElement;
            parent.appendChild(meter);
            parent.appendChild(label);

            input.addEventListener('input', function () {
                const val = this.value;
                let score = 0;
                if (val.length >= 8) score++;
                if (val.length >= 12) score++;
                if (/[a-z]/.test(val) && /[A-Z]/.test(val)) score++;
                if (/[0-9]/.test(val)) score++;
                if (/[^a-zA-Z0-9]/.test(val)) score++;

                const levels = [
                    { width: '0%', color: '#e9ecef', text: '' },
                    { width: '20%', color: '#dc3545', text: 'Sangat Lemah' },
                    { width: '40%', color: '#fd7e14', text: 'Lemah' },
                    { width: '60%', color: '#ffc107', text: 'Sedang' },
                    { width: '80%', color: '#20c997', text: 'Kuat' },
                    { width: '100%', color: '#198754', text: 'Sangat Kuat' },
                ];

                const level = levels[Math.min(score, 5)];
                bar.style.width = level.width;
                bar.style.background = level.color;
                label.textContent = val ? `Kekuatan: ${level.text}` : '';
                label.style.color = level.color;
            });
        });
    }

    // ===========================
    // CHARACTER COUNTER
    // ===========================

    function initCharCounters() {
        const textareas = document.querySelectorAll('textarea[maxlength], input[maxlength]');
        textareas.forEach(input => {
            const max = input.getAttribute('maxlength');
            if (!max) return;

            const counter = document.createElement('small');
            counter.className = 'text-muted d-block text-end';
            counter.style.fontSize = '0.75rem';
            counter.textContent = `0 / ${max}`;

            const parent = input.closest('.mb-3') || input.parentElement;
            parent.appendChild(counter);

            input.addEventListener('input', function () {
                const len = this.value.length;
                counter.textContent = `${len} / ${max}`;
                counter.style.color = len > max * 0.9 ? '#dc3545' : '';
            });
        });
    }

    // ===========================
    // INJECT FADE-IN CSS
    // ===========================

    function injectStyles() {
        if (document.getElementById('metopen-validation-styles')) return;

        const style = document.createElement('style');
        style.id = 'metopen-validation-styles';
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-4px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .realtime-feedback {
                animation: fadeIn 0.2s ease-in;
            }
            input.is-valid, select.is-valid, textarea.is-valid {
                border-color: #198754 !important;
                box-shadow: 0 0 0 0.15rem rgba(25, 135, 84, 0.15) !important;
            }
            input.is-invalid, select.is-invalid, textarea.is-invalid {
                border-color: #dc3545 !important;
                box-shadow: 0 0 0 0.15rem rgba(220, 53, 69, 0.15) !important;
            }
        `;
        document.head.appendChild(style);
    }

    // ===========================
    // INITIALIZE
    // ===========================

    function init() {
        injectStyles();
        bindValidation();
        initAutoSave();
        initPasswordStrength();
        initCharCounters();
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
