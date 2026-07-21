<template>
    <PublicLayout>
        <section class="relative w-full overflow-hidden" style="height:100vh;min-height:600px;max-height:1000px">
            <div class="absolute inset-0 flex transition-transform duration-700 ease-out" :style="{transform:`translateX(-${slide*100}%)`}">
                <div class="flex-shrink-0 w-full h-full relative">
                    <img src="/kantor_depan.jpg" alt="Kantor BPMP Provinsi NTB" class="w-full h-full object-cover" loading="eager" @error="$event.target.style.display='none'">
                    <div class="absolute inset-0" style="background:linear-gradient(to right, rgba(0,0,0,0.65) 0%, rgba(0,0,0,0.3) 50%, transparent 100%)"></div>
                    <div class="absolute inset-0 flex items-center">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                            <div class="max-w-lg">
                                <div class="inline-block mb-4 px-4 py-1.5 rounded-full text-xs font-bold tracking-wider text-white/90" style="background:rgba(255,255,255,0.12);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.15)">
                                    SELAMAT DATANG
                                </div>
                                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-[1.1] mb-6">
                                    BPMP<br><span style="color:var(--color-accent)">Provinsi NTB</span>
                                </h1>
                                <p class="text-base md:text-lg text-white/75 mb-8 leading-relaxed max-w-md">Bersama Menjamin Mutu, Melayani Sepenuh Hati. Balai Penjaminan Mutu Pendidikan Provinsi Nusa Tenggara Barat.</p>
                                <div class="flex flex-wrap gap-3">
                                    <router-link to="/post/berita" class="btn-primary py-3.5 px-8 text-sm shadow-lg">
                                        <i class="fas fa-newspaper mr-2"></i>Berita Terkini
                                    </router-link>
                                    <a href="#layanan" class="inline-flex items-center gap-2 py-3.5 px-8 rounded-full text-sm font-bold text-white border-2 border-white/30 hover:bg-white/10 transition-all">
                                        <i class="fas fa-th-large mr-1"></i>Layanan Kami
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-for="s in sliders" :key="s.id" class="flex-shrink-0 w-full h-full relative">
                    <img :src="s.image_url" :alt="s.title || 'Slider BPMP NTB'" class="w-full h-full object-cover" loading="lazy">
                    <div class="absolute inset-0" style="background:linear-gradient(to right, rgba(0,0,0,0.65) 0%, rgba(0,0,0,0.3) 50%, transparent 100%)"></div>
                    <div class="absolute inset-0 flex items-center">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                            <div class="max-w-lg">
                                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-[1.1] mb-6">{{ s.title }}</h1>
                                <p v-if="s.description" class="text-base md:text-lg text-white/75 mb-8 leading-relaxed max-w-md">{{ s.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button @click="slide=(slide-1+total)%total" class="absolute left-4 lg:left-8 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur flex items-center justify-center text-white transition border border-white/15 z-10">
                <i class="fas fa-chevron-left text-sm"></i>
            </button>
            <button @click="slide=(slide+1)%total" class="absolute right-4 lg:right-8 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur flex items-center justify-center text-white transition border border-white/15 z-10">
                <i class="fas fa-chevron-right text-sm"></i>
            </button>

            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                <button v-for="i in total" :key="i" @click="slide=i-1" class="h-1.5 rounded-full transition-all duration-300" :class="i-1===slide?'bg-white w-7':'bg-white/35 w-1.5 hover:bg-white/60'"></button>
            </div>

            <a href="#kinerja" class="absolute bottom-20 left-1/2 -translate-x-1/2 z-10 w-9 h-9 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur flex items-center justify-center text-white border border-white/15 animate-bounce">
                <i class="fas fa-chevron-down text-xs"></i>
            </a>
        </section>

        <section id="kinerja" class="py-12 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8">
                    <h2 class="section-title">Kinerja Lembaga</h2>
                    <p class="section-subtitle mx-auto">Dokumen perencanaan dan pelaporan kinerja BPMP Provinsi NTB</p>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 max-w-4xl mx-auto">
                    <router-link v-for="item in kinerja" :key="item.label" :to="item.to" class="card p-6 text-center group">
                        <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center transition-all group-hover:scale-110" style="background:color-mix(in srgb, var(--color-primary) 8%, transparent)">
                            <img :src="item.img" :alt="item.label" class="h-8 w-8 object-contain" loading="lazy" @error="$event.target.style.display='none'">
                        </div>
                        <div class="text-sm font-bold" style="color:var(--color-text-primary)">{{ item.label }}</div>
                    </router-link>
                    <a :href="setting?.ikm_link || '#'" target="_blank" class="card p-6 text-center group border-2" style="border-color:color-mix(in srgb, var(--color-accent) 30%, transparent)">
                        <div class="w-14 h-14 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:color-mix(in srgb, var(--color-accent) 10%, transparent)">
                            <img src="/ikm-2025.png" alt="Indeks Kepuasan Masyarakat" class="h-10 w-16 object-contain" loading="lazy" @error="$event.target.style.display='none'">
                        </div>
                        <div class="text-sm font-bold mb-1" style="color:var(--color-text-primary)">Indeks Kepuasan Masyarakat</div>
                        <div class="text-3xl font-extrabold" style="color:var(--color-accent)">{{ setting?.ikm_score || '0' }}</div>
                        <div class="text-xs mt-1" style="color:var(--color-text-secondary)">{{ setting?.ikm_period || '' }}</div>
                        <div class="text-xs mt-2 font-semibold underline" style="color:var(--color-primary)">Beri Penilaian</div>
                    </a>
                </div>
            </div>
        </section>

        <section id="layanan" class="py-12" style="background:var(--color-background)">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8">
                    <h2 class="section-title">Layanan BPMP Provinsi NTB</h2>
                    <p class="section-subtitle mx-auto">Berbagai layanan penjaminan mutu pendidikan untuk masyarakat NTB</p>
                </div>
                <div class="max-w-3xl mx-auto mb-8">
                    <div class="card p-8 md:p-10 text-center border-t-4" style="border-color:var(--color-accent)">
                        <h3 class="text-xl font-bold mb-4" style="color:var(--color-accent)">MAKLUMAT PELAYANAN</h3>
                        <p class="text-base italic leading-relaxed" style="color:var(--color-text-primary)">"Dengan ini, kami sanggup menyelenggarakan pelayanan sesuai standar pelayanan yang telah ditetapkan dan apabila tidak menepati janji ini, kami siap menerima sanksi sesuai peraturan perundang-undangan yang berlaku"</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                    <component v-for="svc in layanan" :key="svc.id" :is="svc.link_type==='external'?'a':'router-link'" :href="svc.link_type==='external'?svc.link_url:undefined" :to="svc.link_type!=='external'?`/layanan/${svc.id}/${svc.slug}`:undefined" :target="svc.link_type==='external'?'_blank':undefined" class="card p-5 text-center group">
                        <img v-if="svc.image" :src="'/upload/layanans/'+svc.image" :alt="svc.title" class="h-14 mx-auto mb-3 group-hover:scale-110 transition-transform" loading="lazy" @error="$event.target.style.display='none'">
                        <div v-else class="w-14 h-14 mx-auto mb-3 rounded-xl flex items-center justify-center" style="background:color-mix(in srgb, var(--color-primary) 8%, transparent)"><i class="fas fa-concierge-bell text-2xl" style="color:var(--color-primary)"></i></div>
                        <h3 class="text-[11px] font-bold leading-tight" style="color:var(--color-text-primary)">{{ svc.title }}</h3>
                    </component>
                </div>
            </div>
        </section>

        <section class="py-12 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between mb-6">
                    <div>
                        <h2 class="section-title">Postingan Terkini</h2>
                        <p class="section-subtitle">Berita dan artikel terbaru dari BPMP NTB</p>
                    </div>
                    <router-link to="/post/berita" class="btn-secondary hidden md:inline-flex py-2.5 px-6 text-xs">
                        Lihat Semua <i class="fas fa-arrow-right ml-2 text-[10px]"></i>
                    </router-link>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div v-for="item in lastPost" :key="item.id+item.jenis" class="card overflow-hidden group">
                        <div class="h-52 bg-gray-100 relative overflow-hidden">
                            <img v-if="item.image_url" :src="item.image_url" :alt="item.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" @error="handleThumbError($event,item)">
                            <div v-else class="w-full h-full flex items-center justify-center bg-gray-50">
                                <i class="fas fa-newspaper text-4xl text-gray-200"></i>
                            </div>
                            <div class="absolute top-4 left-4 badge badge-primary shadow-sm">{{ item.kategori }}</div>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center gap-3 text-xs mb-3" style="color:var(--color-text-secondary)">
                                <span class="flex items-center gap-1"><i class="fas fa-user text-[10px]"></i>{{ item.writer }}</span>
                                <span class="text-gray-300">·</span>
                                <span class="flex items-center gap-1"><i class="fas fa-calendar text-[10px]"></i>{{ formatDate(item.tanggal) }}</span>
                            </div>
                            <h3 class="text-base font-bold mb-2 line-clamp-2 group-hover:text-[var(--color-primary)] transition-colors leading-snug" style="color:var(--color-text-primary)">{{ item.title }}</h3>
                            <p class="text-sm leading-relaxed line-clamp-3 mb-4" style="color:var(--color-text-secondary)">{{ item.teaser }}</p>
                            <router-link :to="`/post/${item.jenis}/${item.id}/${item.slug}`" class="inline-flex items-center gap-1.5 text-sm font-bold group-hover:gap-2.5 transition-all" style="color:var(--color-primary)">
                                Baca selengkapnya <i class="fas fa-arrow-right text-[10px]"></i>
                            </router-link>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-5 md:hidden">
                    <router-link to="/post/berita" class="btn-secondary py-2.5 px-6 text-xs">
                        Lihat Semua <i class="fas fa-arrow-right ml-2 text-[10px]"></i>
                    </router-link>
                </div>
            </div>
        </section>

        <section class="py-12" style="background:var(--color-background)">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-6">
                    <h2 class="section-title">Tautan Penting</h2>
                    <p class="section-subtitle mx-auto">Link terkait layanan pendidikan nasional</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 max-w-5xl mx-auto">
                    <a v-for="item in externalLinks" :key="item.id" :href="item.link" target="_blank" class="card p-5 text-center group">
                        <span class="text-3xl block mb-3 group-hover:scale-110 transition-transform">{{ item.images }}</span>
                        <span class="text-[11px] font-bold leading-tight" style="color:var(--color-text-primary)">{{ item.title }}</span>
                    </a>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import api from '@/bootstrap.js';
import PublicLayout from '@/layouts/PublicLayout.vue';

const sliders = ref([]); const lastPost = ref([]); const externalLinks = ref([]); const setting = ref(null); const layanan = ref([]); const slide = ref(0); let timer = null;
const total = computed(() => sliders.value.length + 1);
const bulanIndo=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
function formatDate(tgl){if(!tgl)return'-';const d=new Date(tgl);return isNaN(d)?tgl:`${d.getDate()} ${bulanIndo[d.getMonth()]} ${d.getFullYear()}`;}
function handleThumbError(e,item){if(item.image_fallback&&e.target.src!==item.image_fallback)e.target.src=item.image_fallback;else e.target.style.display='none';}
const kinerja=[{label:'Laporan Kinerja',img:'/lakin.png',to:'/post/lakin'},{label:'Rencana Strategis',img:'/renstra.png',to:'/post/renstra'},{label:'Perjanjian Kinerja',img:'/handshake.png',to:'/post/perjanjian_kinerja'}];
onMounted(async()=>{const[bd,st,lyn]=await Promise.allSettled([api.get('/beranda'),api.get('/settings'),api.get('/layanans-public')]);if(bd.status==='fulfilled'){sliders.value=bd.value.data.sliders||[];lastPost.value=bd.value.data.lastPost||[];externalLinks.value=bd.value.data.externalLinks||[];}if(st.status==='fulfilled')setting.value=st.value.data;if(lyn.status==='fulfilled')layanan.value=lyn.value.data||[];timer=setInterval(()=>{slide.value=(slide.value+1)%total.value;},7000);});
onUnmounted(()=>{if(timer)clearInterval(timer);});
</script>

<style scoped>
.line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.line-clamp-3{display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}
</style>
