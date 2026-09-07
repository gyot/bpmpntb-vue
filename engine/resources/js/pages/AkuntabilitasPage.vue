<template>
    <PublicLayout>
        <section class="akun-hero">
            <div class="akun-hero-grid"></div>
            <div class="akun-orb akun-orb-a"></div>
            <div class="akun-orb akun-orb-b"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <nav class="akun-breadcrumb" aria-label="Breadcrumb">
                    <router-link to="/">Beranda</router-link>
                    <i class="fas fa-chevron-right"></i>
                    <span>Akuntabilitas</span>
                </nav>
                <div class="akun-hero-copy">
                    <span class="akun-eyebrow"><i class="fas fa-chart-line"></i>Transparansi Kinerja</span>
                    <h1>Dokumen Kinerja Lembaga</h1>
                    <p>Dokumen perencanaan, pelaporan, dan capaian kinerja BPMP Provinsi Nusa Tenggara Barat sebagai wujud akuntabilitas publik.</p>
                </div>
            </div>
        </section>

        <section class="akun-cards">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="akun-card-grid">
                    <router-link v-for="(item, idx) in kinerjaCards" :key="item.label" :to="item.to" class="akun-card group reveal" :class="[`delay-${idx+1}`, {'is-visible': vis.cards}]">
                        <div class="akun-card-icon">
                            <img :src="item.img" :alt="item.label" class="h-10 w-10 object-contain" loading="lazy" width="40" height="40" @error="$event.target.style.display='none'">
                        </div>
                        <div>
                            <h3>{{ item.label }}</h3>
                            <p>{{ item.desc }}</p>
                        </div>
                        <i class="fas fa-arrow-right akun-card-arrow"></i>
                    </router-link>
                    <a :href="setting?.ikm_link || '#'" target="_blank" rel="noopener noreferrer" class="akun-card akun-card-ikm group reveal delay-4" :class="{'is-visible': vis.cards}">
                        <div class="akun-card-icon" style="background:rgba(240,168,0,0.08)">
                            <img src="/ikm-2025.png" alt="IKM" class="h-10 w-16 object-contain" loading="lazy" width="64" height="40" @error="$event.target.style.display='none'">
                        </div>
                        <div>
                            <h3>Indeks Kepuasan Masyarakat</h3>
                            <p>Skor kepuasan masyarakat terhadap layanan BPMP NTB</p>
                        </div>
                        <div class="akun-ikm-score">
                            <strong>{{ setting?.ikm_score || '0' }}</strong>
                            <small>{{ setting?.ikm_period || '' }}</small>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <section v-for="section in documentSections" :key="section.key" class="akun-section reveal" :class="{'is-visible': vis[section.key]}" :data-reveal-key="section.key">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <header class="akun-section-header">
                    <div>
                        <span><i :class="section.icon"></i>{{ section.badge }}</span>
                        <h2>{{ section.title }}</h2>
                        <p>{{ section.subtitle }}</p>
                    </div>
                    <router-link :to="section.to" class="akun-see-all">Lihat Semua <i class="fas fa-arrow-right"></i></router-link>
                </header>

                <div v-if="section.loading" class="akun-doc-grid">
                    <div v-for="i in 3" :key="i" class="akun-doc-skeleton">
                        <div class="skeleton h-5 w-3/4 mb-3"></div>
                        <div class="skeleton h-3 w-full mb-2"></div>
                        <div class="skeleton h-3 w-2/3"></div>
                    </div>
                </div>

                <div v-else-if="section.items.length" class="akun-doc-grid">
                    <router-link v-for="(doc, idx) in section.items" :key="doc.id" :to="`/post/${section.jenis}/${doc.id}/${doc.slug}`" class="akun-doc-card group">
                        <div class="akun-doc-number">{{ String(idx + 1).padStart(2, '0') }}</div>
                        <h3>{{ doc.title }}</h3>
                        <p v-if="doc.teaser">{{ doc.teaser }}</p>
                        <div class="akun-doc-meta">
                            <span v-if="doc.writer"><i class="fas fa-user"></i>{{ doc.writer }}</span>
                            <span v-if="doc.tanggal"><i class="fas fa-calendar"></i>{{ formatDate(doc.tanggal) }}</span>
                        </div>
                        <span class="akun-doc-link">Baca selengkapnya <i class="fas fa-arrow-right"></i></span>
                    </router-link>
                </div>

                <div v-else class="akun-empty">
                    <i :class="section.icon"></i>
                    <p>Belum ada {{ section.title.toLowerCase() }} yang dipublikasikan.</p>
                </div>
            </div>
        </section>

        <section class="akun-cta reveal" :class="{'is-visible': vis.cta}" data-reveal-key="cta">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2>Butuh Informasi Lebih Lanjut?</h2>
                <p>Hubungi kami untuk pertanyaan terkait dokumen kinerja dan akuntabilitas BPMP Provinsi NTB.</p>
                <div class="akun-cta-actions">
                    <router-link to="/ppid" class="akun-btn-primary"><i class="fas fa-building mr-2"></i>Informasi PPID</router-link>
                    <router-link to="/" class="akun-btn-outline"><i class="fas fa-home mr-2"></i>Kembali ke Beranda</router-link>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import api from '@/bootstrap.js';
import PublicLayout from '@/layouts/PublicLayout.vue';

const setting = ref(null);
const bulanIndo = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

const kinerjaCards = [
    { label: 'Laporan Kinerja', img: '/lakin.png', to: '/post/lakin', desc: 'Laporan pelaksanaan dan capaian kinerja tahunan BPMP NTB.' },
    { label: 'Rencana Strategis', img: '/renstra.png', to: '/post/renstra', desc: 'Rencana strategis jangka panjang dan menengah BPMP NTB.' },
    { label: 'Perjanjian Kinerja', img: '/handshake.png', to: '/post/perjanjian_kinerja', desc: 'Perjanjian kinerja antara pimpinan dan unit kerja.' },
];

const documentSections = reactive([
    { key: 'lakin', jenis: 'lakin', badge: 'Laporan', icon: 'fas fa-file-lines', title: 'Laporan Kinerja', subtitle: 'Dokumen pelaksanaan dan capaian kinerja tahunan.', to: '/post/lakin', items: [], loading: true },
    { key: 'renstra', jenis: 'renstra', badge: 'Perencanaan', icon: 'fas fa-map', title: 'Rencana Strategis', subtitle: 'Rencana strategis jangka panjang dan menengah.', to: '/post/renstra', items: [], loading: true },
    { key: 'pk', jenis: 'perjanjian_kinerja', badge: 'Komintmen', icon: 'fas fa-handshake', title: 'Perjanjian Kinerja', subtitle: 'Perjanjian kinerja sebagai komitmen pelaksanaan tugas.', to: '/post/perjanjian_kinerja', items: [], loading: true },
]);

const vis = reactive({ cards: false, lakin: false, renstra: false, pk: false, cta: false });
let observers = [];
const prefersReducedMotion = typeof window !== 'undefined' ? window.matchMedia('(prefers-reduced-motion: reduce)').matches : false;

function formatDate(tgl) {
    if (!tgl) return '-';
    const d = new Date(tgl);
    return isNaN(d) ? tgl : `${d.getDate()} ${bulanIndo[d.getMonth()]} ${d.getFullYear()}`;
}

function setupReveal() {
    if (prefersReducedMotion) { Object.keys(vis).forEach(k => vis[k] = true); return; }
    document.querySelectorAll('[data-reveal-key]').forEach(el => {
        const key = el.dataset.revealKey;
        const obs = new IntersectionObserver(([e]) => { if (e.isIntersecting) { vis[key] = true; obs.disconnect(); } }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
        obs.observe(el);
        observers.push(obs);
    });
}

onMounted(async () => {
    const promises = documentSections.map(s =>
        api.get(`/posts-front/${s.jenis}`, { params: { per_page: 3 } })
            .then(r => ({ key: s.key, items: r.data?.data || [] }))
            .catch(() => ({ key: s.key, items: [] }))
    );

    const [st, ...results] = await Promise.allSettled([
        api.get('/settings'),
        ...promises,
    ]);

    if (st.status === 'fulfilled') setting.value = st.value.data;

    for (const r of results) {
        if (r.status === 'fulfilled') {
            const sec = documentSections.find(s => s.key === r.value.key);
            if (sec) { sec.items = r.value.items; sec.loading = false; }
        }
    }
    documentSections.forEach(s => { if (s.loading) s.loading = false; });

    setTimeout(setupReveal, 150);
});

onUnmounted(() => { observers.forEach(o => o.disconnect()); });
</script>

<style scoped>
.akun-hero{position:relative;overflow:hidden;padding:68px 0 56px;background:#1e40af;color:#fff}
.akun-hero-grid{position:absolute;inset:0;opacity:.07;background-image:linear-gradient(rgba(255,255,255,.2) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.2) 1px,transparent 1px);background-size:48px 48px}
.akun-orb{position:absolute;border-radius:50%;filter:blur(55px)}
.akun-orb-a{right:-120px;top:-220px;width:580px;height:580px;background:rgba(96,165,250,.32)}
.akun-orb-b{left:-170px;bottom:-260px;width:460px;height:460px;background:rgba(14,165,233,.18)}
.akun-breadcrumb{display:flex;align-items:center;gap:9px;margin-bottom:32px;color:rgba(255,255,255,.48);font-size:11px}
.akun-breadcrumb a:hover{color:#fff}
.akun-breadcrumb i{font-size:7px}
.akun-eyebrow{display:inline-flex;align-items:center;gap:8px;margin-bottom:18px;padding:7px 12px;border:1px solid rgba(255,255,255,.15);border-radius:999px;background:rgba(255,255,255,.08);color:rgba(255,255,255,.72);font-size:10px;font-weight:700;letter-spacing:.09em;text-transform:uppercase}
.akun-hero-copy h1{max-width:700px;color:#fff;font-size:clamp(2.2rem,5vw,4.2rem);line-height:1.06;margin-bottom:16px}
.akun-hero-copy p{max-width:640px;color:rgba(255,255,255,.63);font-size:16px;line-height:1.8}

.akun-cards{padding:48px 0 24px;background:#f6f8fc}
.akun-card-grid{display:grid;gap:16px}
.akun-card{display:grid;grid-template-columns:64px 1fr auto;align-items:center;gap:18px;padding:22px;border:1px solid #e2e8f0;border-radius:20px;background:#fff;text-decoration:none;transition:.25s;box-shadow:0 8px 28px rgba(15,23,42,.04)}
.akun-card:hover{transform:translateY(-3px);border-color:#bfdbfe;box-shadow:0 16px 40px rgba(15,23,42,.08)}
.akun-card-icon{display:grid;place-items:center;width:64px;height:64px;border-radius:16px;background:#eff6ff}
.akun-card h3{margin:0 0 4px;color:#263238;font-size:16px;font-weight:700}
.akun-card p{margin:0;color:#78909c;font-size:13px;line-height:1.5}
.akun-card-arrow{color:#cbd5e1;font-size:14px;transition:.2s}
.akun-card:hover .akun-card-arrow{color:var(--color-primary);transform:translateX(3px)}
.akun-card-ikm{border-color:rgba(240,168,0,.15)}
.akun-card-ikm:hover{border-color:rgba(240,168,0,.3)}
.akun-ikm-score{text-align:center}
.akun-ikm-score strong{display:block;color:var(--color-accent);font-size:28px;font-weight:900;line-height:1.1}
.akun-ikm-score small{color:#94a3b8;font-size:10px}

.akun-section{padding:56px 0;background:#fff}
.akun-section:nth-child(even){background:#f6f8fc}
.akun-section-header{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:32px}
.akun-section-header span{display:inline-flex;align-items:center;gap:7px;margin-bottom:8px;color:var(--color-primary);font-size:9px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
.akun-section-header span i{font-size:10px}
.akun-section-header h2{color:#263238;font-size:clamp(1.6rem,3.5vw,2.4rem);line-height:1.15}
.akun-section-header p{max-width:560px;margin-top:8px;color:#78909c;font-size:13px;line-height:1.6}
.akun-see-all{display:inline-flex;align-items:center;gap:7px;color:var(--color-primary);font-size:12px;font-weight:700;text-decoration:none;white-space:nowrap;transition:.2s}
.akun-see-all:hover{gap:10px}
.akun-see-all i{font-size:10px}

.akun-doc-grid{display:grid;gap:16px}
.akun-doc-card{display:flex;flex-direction:column;padding:22px;border:1px solid #e2e8f0;border-radius:18px;background:#fff;text-decoration:none;transition:.25s;box-shadow:0 8px 28px rgba(15,23,42,.04)}
.akun-doc-card:hover{transform:translateY(-3px);border-color:#bfdbfe;box-shadow:0 16px 40px rgba(15,23,42,.08)}
.akun-doc-number{color:#cbd5e1;font-size:22px;font-weight:900;margin-bottom:10px}
.akun-doc-card h3{margin:0 0 8px;color:#334155;font-size:15px;line-height:1.4;font-weight:700}
.akun-doc-card p{margin:0 0 12px;color:#78909c;font-size:12px;line-height:1.65;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden}
.akun-doc-meta{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:14px;color:#94a3b8;font-size:10px}
.akun-doc-meta span{display:inline-flex;align-items:center;gap:5px}
.akun-doc-meta i{color:var(--color-primary)}
.akun-doc-link{display:inline-flex;align-items:center;gap:7px;color:var(--color-primary);font-size:11px;font-weight:700;margin-top:auto;transition:.2s}
.akun-doc-card:hover .akun-doc-link{gap:10px}
.akun-doc-skeleton{padding:22px;border:1px solid #e2e8f0;border-radius:18px;background:#fff}

.akun-empty{padding:48px 24px;text-align:center;border:1px dashed #cbd5e1;border-radius:18px;background:#fff}
.akun-empty i{font-size:28px;color:#cbd5e1;margin-bottom:12px}
.akun-empty p{color:#94a3b8;font-size:13px}

.akun-cta{padding:64px 0;background:#f6f8fc}
.akun-cta h2{color:#263238;font-size:clamp(1.5rem,3vw,2.2rem);font-weight:800;margin-bottom:10px}
.akun-cta p{max-width:480px;margin:0 auto 28px;color:#78909c;font-size:14px;line-height:1.7}
.akun-cta-actions{display:flex;flex-wrap:wrap;justify-content:center;gap:12px}
.akun-btn-primary{display:inline-flex;align-items:center;gap:8px;padding:13px 22px;border-radius:12px;background:var(--color-primary);color:#fff;font-size:13px;font-weight:700;text-decoration:none;transition:.2s}
.akun-btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(37,99,235,.25)}
.akun-btn-outline{display:inline-flex;align-items:center;gap:8px;padding:13px 22px;border:1px solid #dce4ec;border-radius:12px;background:#fff;color:#455a64;font-size:13px;font-weight:700;text-decoration:none;transition:.2s}
.akun-btn-outline:hover{border-color:#93c5fd;color:var(--color-primary)}

.reveal{opacity:0;transform:translateY(20px);transition:opacity .6s ease,transform .6s ease}
.reveal.is-visible{opacity:1;transform:none}
.delay-1{transition-delay:.1s}
.delay-2{transition-delay:.2s}
.delay-3{transition-delay:.3s}
.delay-4{transition-delay:.4s}

@media(min-width:768px){
    .akun-card-grid{grid-template-columns:repeat(2,1fr)}
    .akun-card:last-child{grid-column:1/-1}
    .akun-doc-grid{grid-template-columns:repeat(2,1fr)}
}
@media(min-width:1100px){
    .akun-card-grid{grid-template-columns:repeat(4,1fr)}
    .akun-card:last-child{grid-column:auto}
    .akun-doc-grid{grid-template-columns:repeat(3,1fr)}
}
@media(max-width:767px){
    .akun-hero{padding:52px 0 40px}
    .akun-hero-copy h1{font-size:clamp(1.8rem,6vw,2.6rem)}
    .akun-card{grid-template-columns:48px 1fr;gap:12px;padding:16px}
    .akun-card-icon{width:48px;height:48px;border-radius:12px}
    .akun-card-arrow{display:none}
    .akun-ikm-score{grid-column:1/-1;text-align:left;padding-top:8px;border-top:1px solid #f1f5f9;margin-top:4px}
    .akun-section-header{flex-direction:column;align-items:flex-start}
    .akun-section{padding:40px 0}
}
</style>
