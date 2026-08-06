<template>
    <PublicLayout>
        <section class="relative overflow-hidden pt-28 pb-20" style="background:linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%)">
            <div class="absolute inset-0 opacity-[0.05]" style="background-image:radial-gradient(rgba(255,255,255,0.3) 1px, transparent 1px);background-size:32px 32px"></div>
            <div class="absolute top-10 right-[15%] w-[400px] h-[400px] rounded-full opacity-10 animate-float-slow" style="background:radial-gradient(circle, #60A5FA 0%, transparent 70%);filter:blur(80px)"></div>
            <div class="absolute bottom-10 left-[10%] w-[300px] h-[300px] rounded-full opacity-10 animate-float" style="background:radial-gradient(circle, #93C5FD 0%, transparent 70%);filter:blur(60px)"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative text-center">
                <div class="section-label justify-center !text-white/40 !before:bg-white/20">Layanan</div>
                <h1 class="text-4xl md:text-5xl font-extrabold mb-5" style="color:rgba(255,255,255,0.95);letter-spacing:-0.03em">Layanan BPMP Provinsi NTB</h1>
                <p class="text-lg max-w-2xl mx-auto" style="color:rgba(255,255,255,0.55);line-height:1.7">Seluruh layanan penjaminan mutu pendidikan yang tersedia untuk masyarakat Nusa Tenggara Barat</p>
            </div>
            <div class="absolute bottom-0 left-0 right-0"><svg viewBox="0 0 1440 80" fill="none" preserveAspectRatio="none" class="w-full h-12"><path d="M0,40 C360,80 720,0 1080,40 C1260,60 1380,50 1440,40 L1440,80 L0,80 Z" fill="white"/></svg></div>
        </section>

        <section class="py-20 bg-white relative overflow-hidden">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] rounded-full opacity-[0.03] animate-float-slow" style="background:radial-gradient(circle, var(--color-primary) 0%, transparent 70%);filter:blur(120px)"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="max-w-4xl mx-auto mb-16 text-center reveal" :class="{'is-visible': vis.main}">
                    <div class="relative rounded-2xl overflow-hidden" style="background:linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)">
                        <div class="absolute inset-0 opacity-[0.06]" style="background-image:radial-gradient(rgba(255,255,255,0.4) 1px, transparent 1px);background-size:20px 20px"></div>
                        <div class="absolute top-4 right-6 text-[120px] leading-none font-black opacity-[0.06] select-none" style="color:white">"</div>
                        <div class="relative px-8 py-12 md:px-14 md:py-16 text-center">
                            <div class="w-16 h-16 rounded-2xl mx-auto mb-6 flex items-center justify-center" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
                                <i class="fas fa-shield-halved text-2xl text-white"></i>
                            </div>
                            <div class="inline-flex items-center gap-2 mb-4 px-4 py-1.5 rounded-full text-[11px] font-bold tracking-[0.15em] uppercase text-white/80" style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15)">
                                <i class="fas fa-award text-[10px]"></i>Komitmen Pelayanan
                            </div>
                            <h2 class="text-2xl md:text-3xl font-extrabold text-white mb-6 tracking-tight">MAKLUMAT PELAYANAN</h2>
                            <div class="max-w-2xl mx-auto">
                                <p class="text-base md:text-lg leading-relaxed text-white/85 font-medium italic">"Dengan ini, kami sanggup menyelenggarakan pelayanan sesuai standar pelayanan yang telah ditetapkan dan apabila tidak menepati janji ini, kami siap menerima sanksi sesuai peraturan perundang-undangan yang berlaku"</p>
                            </div>
                            <div class="mt-8 flex items-center justify-center gap-6 text-white/50 text-xs font-semibold">
                                <span class="flex items-center gap-2"><i class="fas fa-check-circle text-white/40"></i>Standar Pelayanan</span>
                                <span class="w-1 h-1 rounded-full bg-white/20"></span>
                                <span class="flex items-center gap-2"><i class="fas fa-check-circle text-white/40"></i>Jaminan Mutu</span>
                                <span class="w-1 h-1 rounded-full bg-white/20"></span>
                                <span class="flex items-center gap-2"><i class="fas fa-check-circle text-white/40"></i>Akuntabilitas</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="i in 6" :key="i" class="card overflow-hidden"><div class="skeleton h-48"></div><div class="p-6 space-y-3"><div class="skeleton h-4 w-3/4"></div><div class="skeleton h-3 w-full"></div><div class="skeleton h-3 w-1/2"></div></div></div>
                </div>

                <div v-else-if="items.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <component v-for="(svc, idx) in items" :key="svc.id" :is="svc.link_type==='external'?'a':'router-link'" :href="svc.link_type==='external'?svc.link_url:undefined" :to="svc.link_type!=='external'?`/layanan/${svc.id}/${svc.slug}`:undefined" :target="svc.link_type==='external'?'_blank':undefined" :rel="svc.link_type==='external'?'noopener noreferrer':undefined" class="card group overflow-hidden reveal" :class="[`delay-${Math.min(idx % 3 + 1, 3)}`, {'is-visible': vis.main}]">
                        <div class="h-48 relative overflow-hidden" style="background:linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%)">
                            <img v-if="svc.image" :src="'/upload/layanans/'+svc.image" :alt="svc.title" class="w-full h-full object-contain p-6 group-hover:scale-110 transition-transform duration-500" loading="lazy" @error="$event.target.style.display='none'">
                            <div v-else class="w-full h-full flex items-center justify-center"><i class="fas fa-concierge-bell text-5xl" style="color:rgba(37,99,235,0.2)"></i></div>
                            <div class="absolute bottom-4 right-4 w-10 h-10 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0" style="background:var(--color-primary);color:white">
                                <i class="fas fa-arrow-right text-sm"></i>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-base font-bold mb-2 group-hover:text-[var(--color-primary)] transition-colors" style="color:#37474F">{{ svc.title }}</h3>
                            <p class="text-sm leading-relaxed" style="color:var(--color-text-secondary)">Layanan penjaminan mutu pendidikan dari BPMP Provinsi NTB untuk masyarakat Nusa Tenggara Barat.</p>
                            <span class="inline-flex items-center gap-1.5 text-sm font-semibold mt-4 group-hover:gap-2.5 transition-all" style="color:var(--color-primary)">Selengkapnya <i class="fas fa-arrow-right text-[10px]"></i></span>
                        </div>
                    </component>
                </div>

                <div v-else class="text-center py-20">
                    <div class="w-20 h-20 rounded-full mx-auto mb-6 flex items-center justify-center" style="background:#EFF6FF"><i class="fas fa-concierge-bell text-3xl" style="color:rgba(37,99,235,0.3)"></i></div>
                    <p class="text-lg font-semibold mb-2" style="color:#455A64">Belum Ada Layanan</p>
                    <p class="text-sm" style="color:var(--color-text-secondary)">Layanan akan segera tersedia.</p>
                </div>
            </div>
        </section>

        <section class="py-20 relative overflow-hidden" style="background:#EFF6FF">
            <div class="absolute inset-0 opacity-[0.05]" style="background-image:radial-gradient(rgba(37,99,235,0.5) 1px, transparent 1px);background-size:24px 24px"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative text-center">
                <h2 class="text-2xl font-bold mb-4 reveal" :class="{'is-visible': vis.cta}" style="color:#263238">Butuh Informasi Lebih Lanjut?</h2>
                <p class="text-base mb-8 max-w-lg mx-auto reveal delay-1" :class="{'is-visible': vis.cta}" style="color:var(--color-text-secondary)">Hubungi kami melalui chatbot Si Intan atau kunjungi kantor BPMP Provinsi NTB langsung.</p>
                <div class="flex flex-wrap justify-center gap-4 reveal delay-2" :class="{'is-visible': vis.cta}">
                    <a v-if="setting?.whatsapp" :href="'https://wa.me/'+setting.whatsapp.replace(/[^0-9]/g,'')" target="_blank" rel="noopener noreferrer" class="btn-primary"><i class="fab fa-whatsapp mr-2"></i>Hubungi WhatsApp</a>
                    <router-link to="/ppid" class="btn-outline"><i class="fas fa-building mr-2"></i>Informasi PPID</router-link>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import api from '@/bootstrap.js';
import PublicLayout from '@/layouts/PublicLayout.vue';

const items = ref([]); const setting = ref(null); const loading = ref(true);
const vis = reactive({ main: false, cta: false }); let observers = [];
const prefersReducedMotion = typeof window !== 'undefined' ? window.matchMedia('(prefers-reduced-motion: reduce)').matches : false;

function setupReveal() {
    if (prefersReducedMotion) { Object.keys(vis).forEach(k => vis[k] = true); return; }
    document.querySelectorAll('[data-reveal-key]').forEach(el => {
        const key = el.dataset.revealKey;
        const obs = new IntersectionObserver(([e]) => { if (e.isIntersecting) { vis[key] = true; obs.disconnect(); } }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
        obs.observe(el); observers.push(obs);
    });
}

onMounted(async () => {
    const [s, l] = await Promise.allSettled([api.get('/settings'), api.get('/layanans-public')]);
    if (s.status === 'fulfilled') setting.value = s.value.data;
    if (l.status === 'fulfilled') items.value = l.value.data || [];
    loading.value = false;
    setTimeout(setupReveal, 150);
});
onUnmounted(() => { observers.forEach(o => o.disconnect()); });
</script>
