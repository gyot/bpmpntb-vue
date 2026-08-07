<template>
    <PublicLayout>
        <div v-if="loading" class="article-loading"><i class="fas fa-spinner fa-spin"></i><span>Memuat postingan...</span></div>
        <template v-else-if="post">
            <header class="article-header">
                <div class="article-header-grid"></div>
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                    <nav class="article-breadcrumb" aria-label="Breadcrumb">
                        <router-link to="/">Beranda</router-link><i class="fas fa-chevron-right"></i>
                        <router-link :to="`/post/${jenis}`">{{ jenisLabel }}</router-link><i class="fas fa-chevron-right"></i>
                        <span>Detail</span>
                    </nav>
                    <span class="article-category">{{ post.Kategori?.title || jenisLabel }}</span>
                    <h1>{{ post.title }}</h1>
                    <p v-if="post.teaser" class="article-lead">{{ post.teaser }}</p>
                    <div class="article-meta">
                        <span class="author-avatar"><i class="fas fa-user"></i></span>
                        <div><strong>{{ post.writer || 'BPMP NTB' }}</strong><small><i class="fas fa-calendar"></i>{{ formatDate(post.tanggal) }}</small></div>
                    </div>
                </div>
            </header>

            <main class="article-shell">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="article-layout">
                        <article class="article-card">
                            <div class="article-toolbar print:hidden">
                                <router-link :to="`/post/${jenis}`"><i class="fas fa-arrow-left"></i>Kembali</router-link>
                                <div>
                                    <button @click="copyLink" :aria-label="copied?'Tautan disalin':'Salin tautan'"><i :class="copied?'fas fa-check':'fas fa-link'"></i>{{ copied ? 'Disalin' : 'Salin' }}</button>
                                    <button @click="window.print()"><i class="fas fa-print"></i>Cetak</button>
                                    <a :href="`/post/${jenis}/${route.params.id}/pdf`" target="_blank" rel="noopener noreferrer"><i class="fas fa-file-pdf"></i>PDF</a>
                                </div>
                            </div>
                            <figure v-if="post.images" class="article-cover">
                                <img :src="'/upload/'+jenis+'/'+post.images" :alt="post.title" width="1000" height="620" @error="$event.target.parentElement.style.display='none'">
                            </figure>
                            <div class="article-content" v-html="post.content"></div>
                            <div v-if="post.file&&post.file!=='-'" class="article-download print:hidden">
                                <div><i class="fas fa-file-arrow-down"></i><span><strong>Dokumen lampiran</strong><small>Unduh berkas yang menyertai postingan ini.</small></span></div>
                                <a :href="'/upload/'+jenis+'/'+post.file" class="btn-primary" download>Unduh File</a>
                            </div>
                            <footer v-if="post.tags" class="article-tags">
                                <span>Topik:</span><router-link v-for="tag in tagList" :key="tag" :to="`/post/${jenis}`">#{{ tag }}</router-link>
                            </footer>
                        </article>

                        <aside class="article-sidebar print:hidden">
                            <div class="sidebar-card">
                                <div class="sidebar-heading"><span><i class="fas fa-sparkles"></i></span><div><small>Berikutnya</small><h2>{{ jenis === 'berita' ? 'Berita Terbaru' : 'Publikasi Terbaru' }}</h2></div></div>
                                <router-link v-for="item in lasts" :key="item.id" :to="`/post/${jenis}/${item.id}/${item.slug}`" class="recent-post">
                                    <span>{{ formatDay(item.tanggal) }}</span><div><h3>{{ item.title }}</h3><small>{{ formatDate(item.tanggal) }}</small></div>
                                </router-link>
                                <router-link :to="`/post/${jenis}`" class="sidebar-all">Lihat semua {{ jenisLabel.toLowerCase() }} <i class="fas fa-arrow-right"></i></router-link>
                            </div>
                            <div class="sidebar-card quick-docs">
                                <div class="sidebar-heading"><span><i class="fas fa-folder-open"></i></span><div><small>Dokumen</small><h2>Kinerja Lembaga</h2></div></div>
                                <router-link v-for="item in quickDocs" :key="item.to" :to="item.to"><img :src="item.image" :alt="item.label"><span>{{ item.label }}</span><i class="fas fa-chevron-right"></i></router-link>
                            </div>
                            <div v-if="externalLinks.length" class="sidebar-card">
                                <div class="sidebar-heading"><span><i class="fas fa-link"></i></span><div><small>Referensi</small><h2>Tautan Penting</h2></div></div>
                                <a v-for="link in externalLinks.slice(0,5)" :key="link.id" :href="link.link" target="_blank" rel="noopener noreferrer" class="external-link"><span>{{ link.title }}</span><i class="fas fa-arrow-up-right-from-square"></i></a>
                            </div>
                        </aside>
                    </div>
                </div>
            </main>
        </template>
        <div v-else class="article-not-found"><i class="fas fa-file-circle-xmark"></i><h1>Postingan tidak ditemukan</h1><p>Konten mungkin telah dipindahkan atau tidak lagi tersedia.</p><router-link to="/" class="btn-primary">Kembali ke Beranda</router-link></div>
    </PublicLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/bootstrap.js';
import PublicLayout from '@/layouts/PublicLayout.vue';

const route = useRoute();
const post = ref(null);
const lasts = ref([]);
const externalLinks = ref([]);
const loading = ref(true);
const copied = ref(false);
const jenis = ref(route.params.jenis);
const bulanIndo=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const jenisLabel = computed(() => String(jenis.value || '').replaceAll('_',' ').replace(/\b\w/g,c=>c.toUpperCase()));
const tagList = computed(() => (post.value?.tags || '').split(',').map(tag=>tag.trim()).filter(Boolean));
const quickDocs=[{label:'Laporan Kinerja',image:'/lakin.png',to:'/post/lakin'},{label:'Rencana Strategis',image:'/renstra.png',to:'/post/renstra'},{label:'Perjanjian Kinerja',image:'/handshake.png',to:'/post/perjanjian_kinerja'}];
function formatDate(tgl){if(!tgl)return'-';const d=new Date(tgl);return isNaN(d)?tgl:`${d.getDate()} ${bulanIndo[d.getMonth()]} ${d.getFullYear()}`;}
function formatDay(tgl){if(!tgl)return'--';const d=new Date(tgl);return isNaN(d)?'--':String(d.getDate()).padStart(2,'0');}
async function copyLink(){try{await navigator.clipboard.writeText(window.location.href);copied.value=true;setTimeout(()=>copied.value=false,1800);}catch{}}

async function loadPost(){
    loading.value=true;
    const[postRes,linksRes]=await Promise.allSettled([api.get(`/posts-front/${jenis.value}/${route.params.id}`),api.get('/external-links')]);
    if(postRes.status==='fulfilled'){post.value=postRes.value.data.data;lasts.value=postRes.value.data.lasts||[];}else{post.value=null;lasts.value=[];}
    if(linksRes.status==='fulfilled')externalLinks.value=linksRes.value.data||[];
    loading.value=false;
}
watch(()=>[route.params.jenis,route.params.id],([nextJenis])=>{jenis.value=nextJenis;loadPost();window.scrollTo({top:0,behavior:'smooth'});});
onMounted(()=>loadPost());
</script>

<style scoped>
.article-loading,.article-not-found{display:flex;min-height:60vh;align-items:center;justify-content:center;flex-direction:column;gap:14px;text-align:center;color:#78909C}.article-loading i{color:var(--color-primary);font-size:32px}.article-not-found i{color:#CBD5E1;font-size:56px}.article-not-found h1{color:#334155;font-size:26px}.article-not-found p{margin-bottom:12px}
.article-header{position:relative;overflow:hidden;padding:62px 0 72px;background:#1E40AF}.article-header-grid{position:absolute;inset:0;opacity:.07;background-image:linear-gradient(rgba(255,255,255,.2) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.2) 1px,transparent 1px);background-size:48px 48px}.article-breadcrumb{display:flex;align-items:center;gap:9px;margin-bottom:30px;color:rgba(255,255,255,.5);font-size:11px}.article-breadcrumb a:hover{color:#fff}.article-breadcrumb i{font-size:7px}.article-category{display:inline-flex;margin-bottom:18px;padding:7px 12px;border:1px solid rgba(255,255,255,.16);border-radius:999px;background:rgba(255,255,255,.09);color:rgba(255,255,255,.78);font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}.article-header h1{max-width:900px;color:#fff;font-size:clamp(2.1rem,5.6vw,4.4rem);line-height:1.08}.article-lead{max-width:780px;margin-top:20px;color:rgba(255,255,255,.65);font-size:17px;line-height:1.8}.article-meta{display:flex;align-items:center;gap:12px;margin-top:28px;color:rgba(255,255,255,.7)}.author-avatar{display:grid;place-items:center;width:44px;height:44px;border:1px solid rgba(255,255,255,.16);border-radius:14px;background:rgba(255,255,255,.1)}.article-meta strong,.article-meta small{display:block}.article-meta strong{font-size:13px}.article-meta small{display:flex;align-items:center;gap:7px;margin-top:3px;color:rgba(255,255,255,.45);font-size:11px}
.article-shell{padding:56px 0 92px;background:#F8FAFC}.article-layout{display:grid;align-items:start;gap:30px}.article-card{min-width:0;overflow:hidden;border:1px solid #E2E8F0;border-radius:28px;background:#fff;box-shadow:0 22px 65px rgba(15,23,42,.08)}.article-toolbar{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 20px;border-bottom:1px solid #EEF2F7;background:#FCFDFE}.article-toolbar>div{display:flex;gap:5px}.article-toolbar a,.article-toolbar button{display:inline-flex;align-items:center;gap:7px;padding:9px 11px;border:0;border-radius:9px;background:transparent;color:#64748B;font-size:11px;font-weight:600;transition:.2s}.article-toolbar a:hover,.article-toolbar button:hover{background:#EFF6FF;color:var(--color-primary)}.article-cover{overflow:hidden;margin:0;background:#EAF1F8}.article-cover img{display:block;width:100%;max-height:620px;object-fit:cover}.article-content{max-width:760px;margin:0 auto;padding:44px 34px 52px;color:#455A64;font-family:Georgia,'Times New Roman',serif;font-size:18px;line-height:1.95}.article-download{display:flex;align-items:center;justify-content:space-between;gap:20px;margin:0 34px 34px;padding:20px;border:1px solid #BFDBFE;border-radius:18px;background:#EFF6FF}.article-download>div{display:flex;align-items:center;gap:14px}.article-download>div>i{display:grid;place-items:center;width:44px;height:44px;border-radius:13px;background:#fff;color:var(--color-primary)}.article-download strong,.article-download small{display:block}.article-download strong{color:#334155;font-size:13px}.article-download small{margin-top:3px;color:#78909C;font-size:11px}.article-download .btn-primary{padding:10px 17px;font-size:11px}.article-tags{display:flex;align-items:center;flex-wrap:wrap;gap:8px;padding:0 34px 34px;color:#94A3B8;font-size:11px}.article-tags a{padding:6px 10px;border-radius:999px;background:#F1F5F9;color:#64748B}.article-tags a:hover{background:#EFF6FF;color:var(--color-primary)}
.article-sidebar{display:grid;gap:20px}.sidebar-card{padding:22px;border:1px solid #E2E8F0;border-radius:22px;background:#fff;box-shadow:0 14px 40px rgba(15,23,42,.05)}.sidebar-heading{display:flex;align-items:center;gap:12px;margin-bottom:18px}.sidebar-heading>span{display:grid;place-items:center;width:42px;height:42px;border-radius:13px;background:#EFF6FF;color:var(--color-primary)}.sidebar-heading small,.sidebar-heading h2{display:block}.sidebar-heading small{margin-bottom:2px;color:#94A3B8;font-size:9px;font-weight:700;letter-spacing:.1em;text-transform:uppercase}.sidebar-heading h2{color:#334155;font-size:14px}.recent-post{display:flex;gap:12px;padding:13px 0;border-top:1px solid #EEF2F7}.recent-post>span{display:grid;place-items:center;width:38px;height:38px;flex:0 0 38px;border-radius:11px;background:#F8FAFC;color:var(--color-primary);font-size:13px;font-weight:800}.recent-post h3{display:-webkit-box;overflow:hidden;color:#455A64;font-size:12px;line-height:1.45;-webkit-box-orient:vertical;-webkit-line-clamp:2}.recent-post:hover h3{color:var(--color-primary)}.recent-post small{display:block;margin-top:4px;color:#94A3B8;font-size:9px}.sidebar-all{display:flex;align-items:center;justify-content:space-between;margin-top:10px;padding-top:14px;border-top:1px solid #EEF2F7;color:var(--color-primary);font-size:11px;font-weight:700}.quick-docs>a{display:flex;align-items:center;gap:11px;padding:11px 0;border-top:1px solid #EEF2F7;color:#64748B;font-size:12px;font-weight:600}.quick-docs img{width:30px;height:30px;object-fit:contain}.quick-docs>a>i{margin-left:auto;color:#CBD5E1;font-size:9px}.quick-docs>a:hover{color:var(--color-primary)}.external-link{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 0;border-top:1px solid #EEF2F7;color:#64748B;font-size:11px}.external-link i{color:#CBD5E1;font-size:9px}.external-link:hover{color:var(--color-primary)}
:deep(.article-content p){margin:0 0 1.45em}:deep(.article-content h2),:deep(.article-content h3),:deep(.article-content h4){margin:1.65em 0 .7em;color:#263238;font-family:'Inter',sans-serif;line-height:1.3}:deep(.article-content h2){font-size:1.65em}:deep(.article-content h3){font-size:1.35em}:deep(.article-content a){color:var(--color-primary);text-decoration:underline;text-decoration-thickness:1px;text-underline-offset:3px}:deep(.article-content img){display:block;max-width:100%;height:auto;margin:2em auto;border-radius:16px;box-shadow:0 14px 36px rgba(15,23,42,.1)}:deep(.article-content blockquote){margin:2em 0;padding:18px 22px;border-left:4px solid var(--color-primary);border-radius:0 14px 14px 0;background:#EFF6FF;color:#475569;font-style:italic}:deep(.article-content ul),:deep(.article-content ol){margin:1em 0 1.5em;padding-left:1.4em}:deep(.article-content li){margin:.45em 0}:deep(.article-content table){display:block;max-width:100%;overflow-x:auto;margin:2em 0;border-collapse:collapse;font-family:'Inter',sans-serif;font-size:13px}:deep(.article-content th),:deep(.article-content td){padding:11px 13px;border:1px solid #E2E8F0}:deep(.article-content th){background:#EFF6FF;color:#334155}:deep(.article-content iframe){display:block;max-width:100%;margin:2em auto;border-radius:16px}
@media(min-width:1024px){.article-layout{grid-template-columns:minmax(0,1fr) 310px}.article-sidebar{position:sticky;top:96px}}
@media(max-width:640px){.article-header{padding:48px 0 58px}.article-shell{padding:34px 0 68px}.article-card{border-radius:20px}.article-toolbar{align-items:flex-start;padding:12px 14px}.article-toolbar>div{flex-wrap:wrap;justify-content:flex-end}.article-toolbar a,.article-toolbar button{padding:8px;font-size:0}.article-toolbar i{font-size:12px}.article-toolbar>a{font-size:10px}.article-content{padding:32px 22px 40px;font-size:16px;line-height:1.9}.article-download{align-items:flex-start;flex-direction:column;margin:0 20px 24px}.article-download .btn-primary{width:100%}.article-tags{padding:0 22px 28px}}
@media print{.article-header{padding:20px 0;background:#fff}.article-header h1{color:#111}.article-category,.article-lead,.article-meta,.article-breadcrumb{color:#444}.article-shell{padding:0;background:#fff}.article-card{border:0;box-shadow:none}.article-content{max-width:none;padding:30px 0}.article-cover img{max-height:none}.article-layout{display:block}}
</style>
