<template>
    <div class="lp">
        <div class="lp-glow lp-glow-1"></div>
        <div class="lp-glow lp-glow-2"></div>
        <div class="lp-glow lp-glow-3"></div>

        <aside class="lp-left">
            <div class="lp-left-inner">
                <div class="lp-orb lp-orb-1"></div>
                <div class="lp-orb lp-orb-2"></div>
                <div class="lp-orb lp-orb-3"></div>
                <div class="lp-grid"></div>
                <div class="lp-ring lp-ring-1"></div>
                <div class="lp-ring lp-ring-2"></div>
                <div class="lp-ring lp-ring-3"></div>
                <div class="lp-hex lp-hex-1"><i class="fas fa-shield-halved"></i></div>
                <div class="lp-hex lp-hex-2"><i class="fas fa-lock"></i></div>
                <div class="lp-hex lp-hex-3"><i class="fas fa-fingerprint"></i></div>
                <div class="lp-left-content">
                    <div class="lp-left-badge"><i class="fas fa-bolt"></i> Secure Access</div>
                    <h2>Panel Administrasi<br><span>Terpadu & Aman</span></h2>
                    <p>Kelola seluruh konten dan layanan website Anda dari satu dashboard yang modern dan intuitif.</p>
                </div>
            </div>
        </aside>

        <main class="lp-right">
            <div class="lp-card animate-slide-up">
                <div class="lp-card-header">
                    <div class="lp-logo-wrap">
                        <img v-if="setting?.logo" :src="'/upload/settings/' + setting.logo" class="lp-logo-img" alt="Logo">
                        <i v-else class="fas fa-shield-halved lp-logo-icon"></i>
                    </div>
                    <h1 class="lp-title">{{ setting?.title || 'BPMP Provinsi NTB' }}</h1>
                    <p class="lp-subtitle">Masuk ke panel admin</p>
                </div>

                <div v-if="error" class="lp-alert">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>{{ error }}</span>
                </div>

                <form @submit.prevent="handleLogin" class="lp-form" autocomplete="on">
                    <div class="lp-field">
                        <label class="lp-label">Email</label>
                        <div class="lp-input-box">
                            <i class="fas fa-envelope lp-input-icon"></i>
                            <input
                                v-model="form.email"
                                type="email"
                                name="email"
                                placeholder="admin@bpmpntb.id"
                                autocomplete="email"
                                required
                            >
                        </div>
                    </div>

                    <div class="lp-field">
                        <label class="lp-label">Password</label>
                        <div class="lp-input-box">
                            <i class="fas fa-lock lp-input-icon"></i>
                            <input
                                v-model="form.password"
                                :type="showPass ? 'text' : 'password'"
                                name="password"
                                placeholder="Masukkan password"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="lp-toggle-pass" @click="showPass = !showPass" tabindex="-1">
                                <i :class="showPass ? 'fa-eye-slash' : 'fa-eye'" class="fas"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" :disabled="submitting" class="lp-btn">
                        <span v-if="submitting" class="lp-btn-loader"></span>
                        <i v-else class="fas fa-arrow-right-to-bracket"></i>
                        <span>{{ submitting ? 'Memproses...' : 'Masuk' }}</span>
                    </button>
                </form>

                <div class="lp-footer">
                    <router-link to="/" class="lp-back-link">
                        <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                    </router-link>
                    <p class="lp-copy">&copy; {{ new Date().getFullYear() }} {{ setting?.title || 'BPMP Provinsi NTB' }}</p>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api, { ensureCsrf } from '@/bootstrap.js';

const router = useRouter();
const form = ref({ email: '', password: '' });
const error = ref('');
const submitting = ref(false);
const setting = ref(null);
const showPass = ref(false);

onMounted(async () => {
    try { const { data } = await api.get('/settings'); setting.value = data; } catch (e) {}
});

async function handleLogin() {
    error.value = '';
    submitting.value = true;
    try {
        await ensureCsrf();
        const { data } = await api.post('/login', form.value);
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
        router.push('/admin');
    } catch (e) {
        error.value = e.response?.data?.message || 'Login gagal';
    }
    submitting.value = false;
}
</script>

<style scoped>
.lp {
    --lp-primary: var(--color-primary, #7CD4FD);
    --lp-secondary: var(--color-secondary, #094A6E);
    --lp-accent: var(--color-accent, #F3B81A);
    --lp-radius: 20px;
}

.lp {
    min-height: 100vh;
    display: flex;
    background: #020617;
    overflow: hidden;
    position: relative;
}

.lp-glow {
    position: fixed;
    border-radius: 50%;
    filter: blur(120px);
    pointer-events: none;
    z-index: 0;
}
.lp-glow-1 {
    width: 600px; height: 600px;
    background: var(--lp-primary);
    opacity: 0.12;
    top: -200px; left: -100px;
    animation: glowFloat 12s ease-in-out infinite alternate;
}
.lp-glow-2 {
    width: 500px; height: 500px;
    background: var(--lp-accent);
    opacity: 0.07;
    bottom: -150px; right: -100px;
    animation: glowFloat 15s ease-in-out infinite alternate-reverse;
}
.lp-glow-3 {
    width: 350px; height: 350px;
    background: var(--lp-primary);
    opacity: 0.06;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    animation: glowFloat 10s ease-in-out infinite alternate;
}

@keyframes glowFloat {
    0%   { transform: translate(0, 0) scale(1); }
    50%  { transform: translate(40px, -30px) scale(1.1); }
    100% { transform: translate(-20px, 20px) scale(0.95); }
}

.lp-left {
    display: none;
    position: relative;
    width: 55%;
    z-index: 1;
    overflow: hidden;
    align-items: center;
    justify-content: center;
    background: linear-gradient(160deg, #020617 0%, #0c1a3a 40%, #0f2847 100%);
}
@media (min-width: 1024px) {
    .lp-left { display: flex; }
    .lp-right { width: 45%; }
}

.lp-left-inner {
    position: relative;
    z-index: 2;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.lp-orb {
    position: absolute;
    border-radius: 50%;
    border: 1px solid rgba(124, 212, 253, 0.15);
    animation: orbSpin linear infinite;
}
.lp-orb-1 { width: 420px; height: 420px; top: 50%; left: 50%; transform: translate(-50%, -50%); animation-duration: 30s; }
.lp-orb-2 { width: 300px; height: 300px; top: 50%; left: 50%; transform: translate(-50%, -50%); animation-duration: 22s; animation-direction: reverse; border-color: rgba(243, 184, 26, 0.1); }
.lp-orb-3 { width: 180px; height: 180px; top: 50%; left: 50%; transform: translate(-50%, -50%); animation-duration: 16s; }
@keyframes orbSpin { to { transform: translate(-50%, -50%) rotate(360deg); } }

.lp-grid {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(124, 212, 253, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(124, 212, 253, 0.03) 1px, transparent 1px);
    background-size: 60px 60px;
    mask-image: radial-gradient(ellipse 70% 70% at 50% 50%, black, transparent);
}

.lp-ring {
    position: absolute;
    border-radius: 50%;
    border: 1px dashed;
    opacity: 0.08;
}
.lp-ring-1 { width: 500px; height: 500px; top: 50%; left: 50%; transform: translate(-50%, -50%); border-color: var(--lp-primary); }
.lp-ring-2 { width: 360px; height: 360px; top: 50%; left: 50%; transform: translate(-50%, -50%); border-color: var(--lp-accent); }
.lp-ring-3 { width: 220px; height: 220px; top: 50%; left: 50%; transform: translate(-50%, -50%); border-color: var(--lp-primary); }

.lp-hex {
    position: absolute;
    width: 56px; height: 56px;
    display: flex; align-items: center; justify-content: center;
    background: rgba(124, 212, 253, 0.06);
    border: 1px solid rgba(124, 212, 253, 0.12);
    border-radius: 16px;
    color: var(--lp-primary);
    font-size: 1.1rem;
    backdrop-filter: blur(8px);
    animation: hexFloat 6s ease-in-out infinite;
}
.lp-hex-1 { top: 18%; left: 15%; animation-delay: 0s; }
.lp-hex-2 { bottom: 22%; right: 12%; animation-delay: 2s; background: rgba(243, 184, 26, 0.06); border-color: rgba(243, 184, 26, 0.12); color: var(--lp-accent); }
.lp-hex-3 { top: 60%; left: 70%; animation-delay: 4s; }
@keyframes hexFloat {
    0%, 100% { transform: translateY(0); }
    50%      { transform: translateY(-12px); }
}

.lp-left-content {
    position: relative;
    z-index: 3;
    max-width: 440px;
    padding: 2rem;
    text-align: center;
}
.lp-left-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 16px;
    border-radius: 99px;
    background: rgba(124, 212, 253, 0.08);
    border: 1px solid rgba(124, 212, 253, 0.15);
    color: var(--lp-primary);
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 1.5rem;
}
.lp-left-content h2 {
    font-size: 2rem;
    font-weight: 800;
    color: #fff;
    line-height: 1.2;
    letter-spacing: -0.02em;
    margin-bottom: 1rem;
}
.lp-left-content h2 span {
    background: linear-gradient(135deg, var(--lp-primary), var(--lp-accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.lp-left-content p {
    font-size: 0.9375rem;
    color: rgba(255, 255, 255, 0.5);
    line-height: 1.7;
}

.lp-right {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    position: relative;
    z-index: 1;
    background: #020617;
}

.lp-card {
    width: 100%;
    max-width: 420px;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(24px) saturate(150%);
    border: 1px solid rgba(124, 212, 253, 0.08);
    border-radius: var(--lp-radius);
    padding: 2.5rem;
    box-shadow:
        0 0 0 1px rgba(124, 212, 253, 0.04),
        0 8px 40px rgba(0, 0, 0, 0.4),
        0 0 80px rgba(124, 212, 253, 0.03);
}

.lp-card-header {
    text-align: center;
    margin-bottom: 2rem;
}
.lp-logo-wrap {
    width: 64px;
    height: 64px;
    margin: 0 auto 1.25rem;
    background: linear-gradient(135deg, rgba(124, 212, 253, 0.12), rgba(243, 184, 26, 0.08));
    border: 1px solid rgba(124, 212, 253, 0.15);
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}
.lp-logo-wrap::after {
    content: '';
    position: absolute;
    inset: -1px;
    border-radius: 18px;
    background: linear-gradient(135deg, rgba(124, 212, 253, 0.2), transparent);
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask-composite: xor;
    -webkit-mask-composite: xor;
    padding: 1px;
}
.lp-logo-img {
    height: 40px;
    width: 40px;
    object-fit: contain;
}
.lp-logo-icon {
    font-size: 1.5rem;
    color: var(--lp-primary);
}
.lp-title {
    font-size: 1.375rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -0.02em;
    margin-bottom: 0.25rem;
}
.lp-subtitle {
    font-size: 0.8125rem;
    color: rgba(148, 163, 184, 0.7);
}

.lp-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: rgba(239, 68, 68, 0.08);
    border: 1px solid rgba(239, 68, 68, 0.2);
    border-radius: 12px;
    color: #fca5a5;
    font-size: 0.8125rem;
    margin-bottom: 1.25rem;
    animation: fadeIn 0.3s ease-out;
}
.lp-alert i { font-size: 0.9rem; flex-shrink: 0; }

.lp-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
.lp-label {
    display: block;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgba(148, 163, 184, 0.6);
    margin-bottom: 8px;
}
.lp-input-box {
    position: relative;
    display: flex;
    align-items: center;
}
.lp-input-icon {
    position: absolute;
    left: 16px;
    font-size: 0.8rem;
    color: rgba(148, 163, 184, 0.4);
    pointer-events: none;
    transition: color 0.25s;
}
.lp-input-box input {
    width: 100%;
    padding: 14px 16px 14px 46px;
    background: rgba(15, 23, 42, 0.5);
    border: 1.5px solid rgba(124, 212, 253, 0.08);
    border-radius: 14px;
    font-size: 0.875rem;
    font-weight: 500;
    color: #f1f5f9;
    outline: none;
    transition: all 0.25s ease;
    font-family: 'Quicksand', sans-serif;
}
.lp-input-box input::placeholder {
    color: rgba(148, 163, 184, 0.3);
}
.lp-input-box input:focus {
    border-color: var(--lp-primary);
    background: rgba(15, 23, 42, 0.7);
    box-shadow: 0 0 0 4px rgba(124, 212, 253, 0.06), 0 0 20px rgba(124, 212, 253, 0.04);
}
.lp-input-box:has(input:focus) .lp-input-icon {
    color: var(--lp-primary);
}

.lp-toggle-pass {
    position: absolute;
    right: 14px;
    background: none;
    border: none;
    color: rgba(148, 163, 184, 0.4);
    cursor: pointer;
    padding: 4px;
    font-size: 0.85rem;
    transition: color 0.2s;
    display: flex;
    align-items: center;
}
.lp-toggle-pass:hover { color: var(--lp-primary); }

.lp-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 14px;
    font-size: 0.9375rem;
    font-weight: 700;
    font-family: 'Quicksand', sans-serif;
    color: #fff;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, var(--lp-primary), #3b82f6, var(--lp-primary));
    background-size: 200% 200%;
    transition: all 0.3s ease;
    margin-top: 0.5rem;
    box-shadow: 0 4px 20px rgba(124, 212, 253, 0.2);
}
.lp-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, transparent 30%, rgba(255, 255, 255, 0.15) 50%, transparent 70%);
    background-size: 200% 200%;
    opacity: 0;
    transition: opacity 0.3s;
}
.lp-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(124, 212, 253, 0.3);
    background-position: 100% 0;
}
.lp-btn:hover:not(:disabled)::before {
    opacity: 1;
    animation: shimmer 1.5s infinite;
}
@keyframes shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.lp-btn:active:not(:disabled) {
    transform: translateY(0);
    box-shadow: 0 2px 12px rgba(124, 212, 253, 0.2);
}
.lp-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.lp-btn-loader {
    width: 18px;
    height: 18px;
    border: 2.5px solid rgba(255, 255, 255, 0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.lp-footer {
    margin-top: 2rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.lp-back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8125rem;
    font-weight: 600;
    color: rgba(148, 163, 184, 0.5);
    text-decoration: none;
    transition: color 0.2s;
}
.lp-back-link:hover { color: var(--lp-primary); }
.lp-copy {
    font-size: 0.6875rem;
    color: rgba(148, 163, 184, 0.3);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

@media (max-width: 768px) {
    .lp-right {
        padding: 1.25rem;
    }
    .lp-card {
        padding: 2rem 1.5rem;
        border-radius: 16px;
    }
    .lp-title {
        font-size: 1.25rem;
    }
}
@media (max-width: 400px) {
    .lp-card {
        padding: 1.5rem 1.25rem;
    }
}
</style>
