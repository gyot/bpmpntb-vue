<template>
    <PublicLayout>
        <div v-if="loading" class="service-loading"><i class="fas fa-spinner fa-spin"></i><span>Memuat layanan...</span></div>
        <template v-else-if="item">
            <header class="service-header">
                <div class="service-header-grid"></div>
                <div class="service-header-orb"></div>
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                    <nav class="service-breadcrumb" aria-label="Breadcrumb">
                        <router-link to="/">Beranda</router-link><i class="fas fa-chevron-right"></i>
                        <router-link to="/layanan">Layanan</router-link><i class="fas fa-chevron-right"></i>
                        <span>Detail</span>
                    </nav>
                    <span class="service-category"><i :class="item.jenis_layanan==='standar_pelayanan'?'fas fa-shield-check':'fas fa-concierge-bell'"></i>{{ item.jenis_layanan==='standar_pelayanan'?'Standar Pelayanan':'Layanan BPMP NTB' }}</span>
                    <h1>{{ item.title }}</h1>
                    <p class="service-lead">Informasi lengkap, standar, dan prosedur layanan BPMP Provinsi Nusa Tenggara Barat.</p>
                    <div class="service-meta">
                        <span v-if="item.writer"><i class="fas fa-user"></i>{{ item.writer }}</span>
                        <span v-if="item.tanggal"><i class="fas fa-calendar"></i>{{ formatDate(item.tanggal) }}</span>
                        <span><i class="fas fa-shield-check"></i>Layanan resmi</span>
                    </div>
                </div>
            </header>

            <main class="service-shell">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="service-layout">
                        <article class="service-article">
                            <div class="service-toolbar">
                                <router-link to="/layanan"><i class="fas fa-arrow-left"></i>Kembali ke daftar layanan</router-link>
                                <div>
                                    <button @click="copyLink"><i :class="copied?'fas fa-check':'fas fa-link'"></i>{{ copied?'Disalin':'Salin' }}</button>
                                    <button @click="window.print()"><i class="fas fa-print"></i>Cetak</button>
                                </div>
                            </div>
                            <figure v-if="item.image" class="service-cover">
                                <img :src="'/upload/layanans/'+item.image" :alt="item.title" width="1000" height="620" @error="$event.target.parentElement.style.display='none'">
                            </figure>
                            <div class="service-content" v-html="item.content"></div>
                            <section v-if="isStandard" class="standard-infographic">
                                <header class="infographic-header"><div><span>Standar Pelayanan BPMP NTB</span><h2>Prosedur layanan yang jelas, mudah, dan terukur</h2></div><b>SP</b></header>

                                <div v-if="requirements.length" class="infographic-section requirements-section">
                                    <div class="infographic-title"><span>01</span><div><small>Yang perlu disiapkan</small><h2>Persyaratan</h2></div></div>
                                    <div class="requirements-grid">
                                        <article v-for="(requirement,index) in requirements" :key="index"><span><i :class="iconClass(requirement.icon)"></i></span><div><b>{{ String(index+1).padStart(2,'0') }}</b><p>{{ requirement.text }}</p></div></article>
                                    </div>
                                </div>

                                <div v-if="flow.length" class="infographic-section flow-section">
                                    <div class="infographic-title"><span>02</span><div><small>Tahapan proses</small><h2>Alur Pelayanan</h2></div></div>
                                    <div class="flow-track">
                                        <article v-for="(step,index) in flow" :key="index"><div class="flow-icon"><i :class="iconClass(step.icon)"></i><b>{{ index+1 }}</b></div><p>{{ step.title }}</p><i v-if="index<flow.length-1" class="fas fa-chevron-right flow-arrow"></i></article>
                                    </div>
                                </div>

                                <div v-if="hasInformation" class="infographic-section information-section">
                                    <div class="infographic-title"><span>03</span><div><small>Ringkasan layanan</small><h2>Informasi Utama</h2></div></div>
                                    <div class="information-grid">
                                        <article v-if="information.duration" class="info-duration"><span><i class="fas fa-clock"></i></span><div><small>Jangka Waktu</small><strong>{{ information.duration }}</strong></div></article>
                                        <article v-if="information.price" class="info-price"><span><i class="fas fa-coins"></i></span><div><small>Biaya / Tarif</small><strong>{{ information.price }}</strong></div></article>
                                        <article v-if="information.product" class="info-product"><span><i class="fas fa-box-open"></i></span><div><small>Produk Layanan</small><strong>{{ information.product }}</strong></div></article>
                                    </div>
                                </div>

                                <div v-if="item.file_pendukung||item.url_permohonan" class="service-actions print:hidden">
                                    <div><small>Siap menggunakan layanan?</small><h2>Lengkapi dokumen dan ajukan permohonan</h2></div>
                                    <div><a v-if="item.file_pendukung" :href="'/upload/layanans/'+item.file_pendukung" target="_blank" rel="noopener noreferrer" class="support-action"><i class="fas fa-file-pdf"></i>File Pendukung</a><a v-if="item.url_permohonan" :href="item.url_permohonan" target="_blank" rel="noopener noreferrer" class="request-action"><i class="fas fa-paper-plane"></i>Ajukan Permohonan</a></div>
                                </div>
                            </section>
                            <section v-if="item.pos_file" class="service-document print:hidden">
                                <div class="document-icon"><i class="fas fa-file-shield"></i></div>
                                <div><small>Dokumen Pendukung</small><h2>Prosedur Operasional Standar</h2><p>Pelajari alur, ketentuan, dan standar layanan sebelum mengajukan permohonan.</p></div>
                                <a :href="'/upload/layanans/'+item.pos_file" target="_blank" rel="noopener noreferrer" class="btn-primary">Lihat POS <i class="fas fa-arrow-up-right-from-square text-xs"></i></a>
                            </section>
                            <footer v-if="tagList.length" class="service-tags"><span>Topik:</span><span v-for="tag in tagList" :key="tag">#{{ tag }}</span></footer>
                        </article>

                        <aside class="service-sidebar print:hidden">
                            <section class="service-contact">
                                <span class="contact-icon"><i class="fas fa-headset"></i></span>
                                <small>Perlu bantuan?</small>
                                <h2>Tanyakan kepada INTAN</h2>
                                <p>Dapatkan informasi awal sebelum menggunakan layanan ini.</p>
                                <span class="contact-hint">Gunakan tombol Tanya INTAN di kanan bawah.</span>
                            </section>
                            <section class="sidebar-card">
                                <div class="sidebar-title"><span><i class="fas fa-grid-2"></i></span><div><small>Jelajahi</small><h2>Layanan Lainnya</h2></div></div>
                                <router-link v-for="(service, idx) in lasts" :key="service.id" :to="service.link" class="other-service">
                                    <b>{{ String(idx + 1).padStart(2,'0') }}</b><span>{{ service.title }}</span><i class="fas fa-chevron-right"></i>
                                </router-link>
                                <router-link to="/layanan" class="all-services">Lihat semua layanan <i class="fas fa-arrow-right"></i></router-link>
                            </section>
                        </aside>
                    </div>
                </div>
            </main>
        </template>
        <div v-else class="service-not-found"><i class="fas fa-circle-exclamation"></i><h1>Layanan tidak ditemukan</h1><p>Informasi layanan mungkin telah dipindahkan atau tidak tersedia.</p><router-link to="/layanan" class="btn-primary">Lihat Daftar Layanan</router-link></div>
    </PublicLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/bootstrap.js';
import PublicLayout from '@/layouts/PublicLayout.vue';

const route = useRoute();
const item = ref(null);
const lasts = ref([]);
const loading = ref(true);
const copied = ref(false);
const bulanIndo=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const tagList = computed(() => (item.value?.tags || '').split(',').map(tag => tag.trim()).filter(Boolean));
const isStandard = computed(() => item.value?.jenis_layanan === 'standar_pelayanan');
const requirements = computed(() => Array.isArray(item.value?.requirements) ? item.value.requirements : []);
const flow = computed(() => Array.isArray(item.value?.flow) ? item.value.flow : []);
const information = computed(() => item.value?.information || {});
const hasInformation = computed(() => Object.values(information.value).some(Boolean));
const icons={document:'fa-file-lines',assignment:'fa-clipboard-list','id-card':'fa-id-card',building:'fa-building',clipboard:'fa-clipboard-check',folder:'fa-folder-open',shield:'fa-shield-check',team:'fa-people-group','document-check':'fa-file-circle-check',calendar:'fa-calendar-days',location:'fa-location-dot'};
function iconClass(icon){return `fas ${icons[icon]||'fa-circle-check'}`;}
function formatDate(tgl){if(!tgl)return'-';const d=new Date(tgl);return isNaN(d)?tgl:`${d.getDate()} ${bulanIndo[d.getMonth()]} ${d.getFullYear()}`;}
async function copyLink(){try{await navigator.clipboard.writeText(window.location.href);copied.value=true;setTimeout(()=>copied.value=false,1800);}catch{}}
async function load(){
    loading.value=true;
    try{const{data}=await api.get(`/layanans/${route.params.id}`);item.value=data.item;lasts.value=(data.lasts||[]).map(l=>({...l,link:`/layanan/${l.id}/${l.slug}`}));}
    catch{item.value=null;lasts.value=[];}
    finally{loading.value=false;}
}
watch(()=>route.params.id,()=>{load();window.scrollTo({top:0,behavior:'smooth'});});
onMounted(load);
</script>

<style scoped>
.service-loading,.service-not-found{display:flex;min-height:60vh;align-items:center;justify-content:center;flex-direction:column;gap:14px;text-align:center;color:#78909c}.service-loading i{color:var(--color-primary);font-size:32px}.service-not-found i{color:#cbd5e1;font-size:56px}.service-not-found h1{color:#334155;font-size:26px}.service-not-found p{margin-bottom:12px}
.service-header{position:relative;overflow:hidden;padding:62px 0 72px;background:#1e40af}.service-header-grid{position:absolute;inset:0;opacity:.07;background-image:linear-gradient(rgba(255,255,255,.2) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.2) 1px,transparent 1px);background-size:48px 48px}.service-header-orb{position:absolute;right:5%;top:-180px;width:460px;height:460px;border-radius:50%;background:radial-gradient(circle,rgba(96,165,250,.55),transparent 70%);filter:blur(55px)}.service-breadcrumb{display:flex;align-items:center;gap:9px;margin-bottom:30px;color:rgba(255,255,255,.5);font-size:11px}.service-breadcrumb a:hover{color:#fff}.service-breadcrumb i{font-size:7px}.service-category{display:inline-flex;align-items:center;gap:7px;margin-bottom:18px;padding:7px 12px;border:1px solid rgba(255,255,255,.16);border-radius:999px;background:rgba(255,255,255,.09);color:rgba(255,255,255,.78);font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}.service-header h1{max-width:900px;color:#fff;font-size:clamp(2.1rem,5.6vw,4.3rem);line-height:1.08}.service-lead{max-width:720px;margin-top:18px;color:rgba(255,255,255,.62);font-size:16px;line-height:1.75}.service-meta{display:flex;flex-wrap:wrap;gap:16px;margin-top:25px;color:rgba(255,255,255,.58);font-size:10px}.service-meta span{display:inline-flex;align-items:center;gap:7px}.service-meta i{color:#93c5fd}
.service-shell{padding:56px 0 92px;background:#f8fafc}.service-layout{display:grid;align-items:start;gap:30px}.service-article{min-width:0;overflow:hidden;border:1px solid #e2e8f0;border-radius:28px;background:#fff;box-shadow:0 22px 65px rgba(15,23,42,.08)}.service-toolbar{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 20px;border-bottom:1px solid #eef2f7;background:#fcfdfe}.service-toolbar>div{display:flex;gap:5px}.service-toolbar a,.service-toolbar button{display:inline-flex;align-items:center;gap:7px;padding:9px 11px;border:0;border-radius:9px;background:transparent;color:#64748b;font-family:inherit;font-size:11px;font-weight:600;transition:.2s}.service-toolbar a:hover,.service-toolbar button:hover{background:#eff6ff;color:var(--color-primary)}.service-cover{overflow:hidden;margin:0;background:#eaf1f8}.service-cover img{display:block;width:100%;max-height:620px;object-fit:cover}.service-content{max-width:760px;margin:0 auto;padding:44px 34px 52px;color:#455a64;font-family:Georgia,'Times New Roman',serif;font-size:18px;line-height:1.95}
.service-document{display:grid;align-items:center;gap:16px;margin:0 34px 34px;padding:22px;border:1px solid #bfdbfe;border-radius:19px;background:#eff6ff}.document-icon{display:grid;place-items:center;width:48px;height:48px;border-radius:14px;background:#fff;color:var(--color-primary);font-size:18px}.service-document small{display:block;margin-bottom:3px;color:var(--color-primary);font-size:8px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.service-document h2{color:#334155;font-size:15px;margin-bottom:4px}.service-document p{color:#78909c;font-size:11px;line-height:1.55}.service-document .btn-primary{padding:10px 16px;font-size:10px}.service-tags{display:flex;align-items:center;flex-wrap:wrap;gap:8px;padding:0 34px 34px;color:#94a3b8;font-size:11px}.service-tags span:not(:first-child){padding:6px 10px;border-radius:999px;background:#f1f5f9;color:#64748b}
.standard-infographic{margin:0 28px 34px;overflow:hidden;border:1px solid #cbdcf5;border-radius:24px;background:#fff;box-shadow:0 18px 55px rgba(30,64,175,.08)}.infographic-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:26px 28px;background:#153e8f;color:#fff}.infographic-header span{color:#93c5fd;font-size:9px;font-weight:800;letter-spacing:.13em;text-transform:uppercase}.infographic-header h2{max-width:600px;margin-top:5px;color:#fff;font-size:22px}.infographic-header>b{display:grid;place-items:center;width:56px;height:56px;border:2px solid rgba(255,255,255,.25);border-radius:18px;background:rgba(255,255,255,.1);font-size:18px}.infographic-section{padding:28px}.infographic-section+.infographic-section{border-top:1px solid #dbe7f5}.infographic-title{display:flex;align-items:center;gap:12px;margin-bottom:20px}.infographic-title>span{display:grid;place-items:center;width:42px;height:42px;border-radius:50%;background:#153e8f;color:#fff;font-size:13px;font-weight:900;box-shadow:0 0 0 6px #eaf2ff}.infographic-title small{display:block;color:#78909c;font-size:8px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.infographic-title h2{margin-top:2px;color:#17376c;font-size:20px}.requirements-grid{display:grid;gap:12px}.requirements-grid article{display:flex;align-items:center;gap:14px;padding:15px;border:1px solid #d9e6f7;border-radius:16px;background:#f8fbff}.requirements-grid article>span{display:grid;place-items:center;width:46px;height:46px;flex:0 0 46px;border-radius:50%;background:#153e8f;color:#fff;font-size:17px}.requirements-grid b{display:block;color:#93a6bd;font-size:8px}.requirements-grid p{margin-top:2px;color:#334155;font-size:11px;font-weight:650;line-height:1.5}.flow-track{display:grid;gap:18px}.flow-track article{position:relative;display:flex;align-items:center;gap:14px}.flow-icon{position:relative;display:grid;place-items:center;width:58px;height:58px;flex:0 0 58px;border:2px solid #1e40af;border-radius:50%;background:#eff6ff;color:#153e8f;font-size:20px}.flow-icon b{position:absolute;right:-3px;top:-5px;display:grid;place-items:center;width:20px;height:20px;border-radius:50%;background:#e8a600;color:#fff;font-size:8px}.flow-track p{color:#334155;font-size:11px;font-weight:650;line-height:1.5}.flow-arrow{display:none;color:#e8a600}.information-grid{display:grid;gap:12px}.information-grid article{display:flex;align-items:center;gap:14px;padding:18px;border-radius:17px}.information-grid article>span{display:grid;place-items:center;width:50px;height:50px;flex:0 0 50px;border-radius:50%;font-size:19px}.information-grid small,.information-grid strong{display:block}.information-grid small{margin-bottom:3px;font-size:8px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.information-grid strong{color:#263238;font-size:12px;line-height:1.45}.info-duration{background:#edf5ff}.info-duration>span{background:#153e8f;color:#fff}.info-duration small{color:#1e40af}.info-price{background:#edf8ed}.info-price>span{background:#3a9b41;color:#fff}.info-price small{color:#33813a}.info-product{background:#fff7e6}.info-product>span{background:#e8a600;color:#fff}.info-product small{color:#bc8200}.service-actions{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:18px;padding:24px 28px;background:#153e8f;color:#fff}.service-actions small{color:#93c5fd;font-size:8px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.service-actions h2{margin-top:4px;color:#fff;font-size:16px}.service-actions>div:last-child{display:flex;flex-wrap:wrap;gap:9px}.service-actions a{display:inline-flex;align-items:center;gap:8px;padding:11px 14px;border-radius:11px;font-size:10px;font-weight:700}.support-action{border:1px solid rgba(255,255,255,.2);color:#fff}.request-action{background:#e8a600;color:#fff;box-shadow:0 9px 22px rgba(232,166,0,.25)}
.service-sidebar{display:grid;gap:20px}.service-contact,.sidebar-card{padding:22px;border:1px solid #e2e8f0;border-radius:22px;background:#fff;box-shadow:0 14px 40px rgba(15,23,42,.05)}.service-contact{overflow:hidden;background:#1e40af;color:#fff}.contact-icon{display:grid;place-items:center;width:48px;height:48px;margin-bottom:19px;border:1px solid rgba(255,255,255,.14);border-radius:15px;background:rgba(255,255,255,.1);font-size:18px}.service-contact>small{color:rgba(255,255,255,.42);font-size:8px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.service-contact h2{margin:5px 0 9px;color:#fff;font-size:18px}.service-contact p{color:rgba(255,255,255,.58);font-size:11px;line-height:1.65}.contact-hint{display:block;margin-top:17px;padding-top:14px;border-top:1px solid rgba(255,255,255,.11);color:#bfdbfe;font-size:9px}.sidebar-title{display:flex;align-items:center;gap:12px;margin-bottom:17px}.sidebar-title>span{display:grid;place-items:center;width:42px;height:42px;border-radius:13px;background:#eff6ff;color:var(--color-primary)}.sidebar-title small,.sidebar-title h2{display:block}.sidebar-title small{margin-bottom:2px;color:#94a3b8;font-size:8px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.sidebar-title h2{color:#334155;font-size:14px}.other-service{display:grid;grid-template-columns:30px minmax(0,1fr) auto;align-items:center;gap:10px;padding:12px 0;border-top:1px solid #eef2f7}.other-service b{display:grid;place-items:center;width:28px;height:28px;border-radius:9px;background:#f8fafc;color:var(--color-primary);font-size:9px}.other-service span{display:-webkit-box;overflow:hidden;color:#64748b;font-size:11px;font-weight:600;line-height:1.45;-webkit-box-orient:vertical;-webkit-line-clamp:2}.other-service>i{color:#cbd5e1;font-size:8px}.other-service:hover span{color:var(--color-primary)}.all-services{display:flex;align-items:center;justify-content:space-between;margin-top:10px;padding-top:14px;border-top:1px solid #eef2f7;color:var(--color-primary);font-size:10px;font-weight:700}
:deep(.service-content p){margin:0 0 1.45em}:deep(.service-content h2),:deep(.service-content h3),:deep(.service-content h4){margin:1.65em 0 .7em;color:#263238;font-family:'Inter',sans-serif;line-height:1.3}:deep(.service-content h2){font-size:1.65em}:deep(.service-content h3){font-size:1.35em}:deep(.service-content a){color:var(--color-primary);text-decoration:underline;text-underline-offset:3px}:deep(.service-content img){display:block;max-width:100%;height:auto;margin:2em auto;border-radius:16px;box-shadow:0 14px 36px rgba(15,23,42,.1)}:deep(.service-content blockquote){margin:2em 0;padding:18px 22px;border-left:4px solid var(--color-primary);border-radius:0 14px 14px 0;background:#eff6ff;color:#475569;font-style:italic}:deep(.service-content ul),:deep(.service-content ol){margin:1em 0 1.5em;padding-left:1.4em}:deep(.service-content li){margin:.45em 0}:deep(.service-content table){display:block;max-width:100%;overflow-x:auto;margin:2em 0;border-collapse:collapse;font-family:'Inter',sans-serif;font-size:13px}:deep(.service-content th),:deep(.service-content td){padding:11px 13px;border:1px solid #e2e8f0}:deep(.service-content th){background:#eff6ff;color:#334155}
@media(min-width:760px){.service-document{grid-template-columns:auto 1fr auto}.requirements-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.information-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(min-width:900px){.flow-track{grid-template-columns:repeat(auto-fit,minmax(90px,1fr));align-items:start;gap:12px}.flow-track article{flex-direction:column;text-align:center}.flow-track p{font-size:9px}.flow-arrow{position:absolute;top:23px;right:-10px;display:block}}
@media(min-width:1024px){.service-layout{grid-template-columns:minmax(0,1fr) 310px}.service-sidebar{position:sticky;top:96px}}
@media(max-width:640px){.service-header{padding:48px 0 58px}.service-shell{padding:34px 0 68px}.service-article{border-radius:20px}.service-toolbar{align-items:flex-start;padding:12px 14px}.service-toolbar>div{justify-content:flex-end}.service-toolbar a,.service-toolbar button{padding:8px;font-size:0}.service-toolbar i{font-size:12px}.service-toolbar>a{font-size:10px}.service-content{padding:32px 22px 40px;font-size:16px;line-height:1.9}.standard-infographic{margin:0 14px 24px;border-radius:18px}.infographic-header,.infographic-section,.service-actions{padding:20px}.infographic-header>b{display:none}.service-actions>div:last-child,.service-actions a{width:100%;justify-content:center}.service-document{margin:0 20px 24px}.service-document .btn-primary{width:100%}.service-tags{padding:0 22px 28px}}
@media print{.service-header{padding:20px 0;background:#fff}.service-header h1{color:#111}.service-category,.service-lead,.service-meta,.service-breadcrumb{color:#444}.service-shell{padding:0;background:#fff}.service-article{border:0;box-shadow:none}.service-content{max-width:none;padding:30px 0}.service-layout{display:block}}
</style>
