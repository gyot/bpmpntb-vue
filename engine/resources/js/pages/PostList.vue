<template>
    <PublicLayout>
        <section class="post-masthead">
            <div class="post-masthead-grid"></div>
            <div class="post-masthead-orb"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <nav class="breadcrumb" aria-label="Breadcrumb">
                    <router-link to="/">Beranda</router-link>
                    <i class="fas fa-chevron-right"></i>
                    <span>{{ pageTitle }}</span>
                </nav>
                <div class="post-masthead-copy">
                    <div class="post-masthead-icon"><i :class="pageMeta.icon"></i></div>
                    <div>
                        <span>{{ pageMeta.label }}</span>
                        <h1>{{ pageTitle }}</h1>
                        <p>{{ pageMeta.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="post-list-section">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-if="loading" class="post-loading-grid" aria-label="Memuat postingan">
                    <article v-for="i in 6" :key="i" class="post-skeleton-card">
                        <div class="skeleton post-skeleton-image"></div>
                        <div class="space-y-3 p-5"><div class="skeleton h-3 w-24"></div><div class="skeleton h-5 w-full"></div><div class="skeleton h-4 w-4/5"></div></div>
                    </article>
                </div>

                <template v-else-if="posts.length">
                    <router-link v-if="featuredPost" :to="postUrl(featuredPost)" class="featured-post group">
                        <div class="featured-media">
                            <img v-if="featuredPost.image_url" :src="featuredPost.image_url" :alt="featuredPost.title" loading="eager" width="900" height="560" @error="$event.target.style.display='none'">
                            <div v-else class="post-image-placeholder"><i class="fas fa-newspaper"></i></div>
                            <span class="featured-badge">Pilihan Terbaru</span>
                        </div>
                        <div class="featured-copy">
                            <span class="badge badge-primary">{{ featuredPost.kategori || pageTitle }}</span>
                            <h2>{{ featuredPost.title }}</h2>
                            <p>{{ featuredPost.teaser || 'Baca informasi selengkapnya dari BPMP Provinsi NTB.' }}</p>
                            <div class="post-meta">
                                <span><i class="fas fa-user"></i>{{ featuredPost.writer || 'BPMP NTB' }}</span>
                                <span><i class="fas fa-calendar"></i>{{ formatDate(featuredPost.tanggal) }}</span>
                            </div>
                            <span class="read-link">Baca selengkapnya <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </router-link>

                    <div class="post-section-head">
                        <div><span>Arsip Publikasi</span><h2>Postingan lainnya</h2></div>
                        <p>{{ posts.length }} postingan pada halaman ini</p>
                    </div>

                    <div class="post-grid">
                        <router-link v-for="item in remainingPosts" :key="item.id" :to="postUrl(item)" class="post-card group">
                            <div class="post-card-media">
                                <img v-if="item.image_url" :src="item.image_url" :alt="item.title" loading="lazy" width="480" height="300" @error="$event.target.style.display='none'">
                                <div v-else class="post-image-placeholder"><i class="fas fa-newspaper"></i></div>
                                <span class="badge badge-primary">{{ item.kategori || pageTitle }}</span>
                            </div>
                            <div class="post-card-copy">
                                <div class="post-meta"><span><i class="fas fa-calendar"></i>{{ formatDate(item.tanggal) }}</span><span><i class="fas fa-user"></i>{{ item.writer || 'BPMP NTB' }}</span></div>
                                <h3>{{ item.title }}</h3>
                                <p>{{ item.teaser || 'Baca informasi selengkapnya dari BPMP Provinsi NTB.' }}</p>
                                <span class="read-link">Baca selengkapnya <i class="fas fa-arrow-right"></i></span>
                            </div>
                        </router-link>
                    </div>

                    <nav v-if="lastPage>1" class="pagination" aria-label="Navigasi halaman">
                        <button @click="loadPage(currentPage-1)" :disabled="currentPage<=1" aria-label="Halaman sebelumnya"><i class="fas fa-angle-left"></i></button>
                        <template v-for="p in paginationRange" :key="p">
                            <span v-if="p==='...'" class="pagination-gap">...</span>
                            <button v-else @click="loadPage(p)" :class="{'active':p===currentPage}" :aria-current="p===currentPage?'page':undefined">{{ p }}</button>
                        </template>
                        <button @click="loadPage(currentPage+1)" :disabled="currentPage>=lastPage" aria-label="Halaman berikutnya"><i class="fas fa-angle-right"></i></button>
                        <span class="pagination-summary">{{ currentPage }} / {{ lastPage }}</span>
                    </nav>
                </template>

                <div v-else class="empty-state">
                    <div><i class="fas fa-inbox"></i></div>
                    <h2>Belum ada {{ pageTitle.toLowerCase() }}</h2>
                    <p>Konten akan ditampilkan setelah tersedia.</p>
                    <router-link to="/" class="btn-outline">Kembali ke Beranda</router-link>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/bootstrap.js';
import PublicLayout from '@/layouts/PublicLayout.vue';

const route = useRoute();
const posts = ref([]);
const loading = ref(true);
const currentPage = ref(1);
const lastPage = ref(1);
const jenis = ref(route.params.jenis);
const bulanIndo=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const metaByType = {
    berita: { label:'Informasi Aktual', icon:'fas fa-newspaper', description:'Kabar terbaru, kegiatan, dan informasi resmi BPMP Provinsi NTB.' },
    artikel: { label:'Wawasan Pendidikan', icon:'fas fa-pen-nib', description:'Gagasan, praktik baik, dan wawasan seputar mutu pendidikan.' },
    jurnal: { label:'Publikasi Ilmiah', icon:'fas fa-book-open', description:'Kumpulan jurnal dan publikasi ilmiah bidang pendidikan.' },
    pengumuman: { label:'Informasi Resmi', icon:'fas fa-bullhorn', description:'Pengumuman dan pemberitahuan resmi BPMP Provinsi NTB.' },
    unduhan: { label:'Pusat Dokumen', icon:'fas fa-download', description:'Dokumen dan berkas resmi yang dapat diunduh.' },
    buletin: { label:'Terbitan Berkala', icon:'fas fa-book', description:'Buletin dan publikasi berkala BPMP Provinsi NTB.' },
    galeri: { label:'Dokumentasi', icon:'fas fa-images', description:'Dokumentasi visual kegiatan BPMP Provinsi NTB.' },
};

const pageTitle = computed(() => String(jenis.value || '').replaceAll('_',' ').replace(/\b\w/g, c => c.toUpperCase()));
const pageMeta = computed(() => metaByType[jenis.value] || { label:'Publikasi', icon:'fas fa-layer-group', description:`Kumpulan ${pageTitle.value.toLowerCase()} BPMP Provinsi NTB.` });
const featuredPost = computed(() => posts.value[0] || null);
const remainingPosts = computed(() => posts.value.slice(1));
function formatDate(tgl){if(!tgl)return'-';const d=new Date(tgl);return isNaN(d)?tgl:`${d.getDate()} ${bulanIndo[d.getMonth()]} ${d.getFullYear()}`;}
function postUrl(item){return `/post/${item.jenis}/${item.id}/${item.slug}`;}

const paginationRange = computed(() => {
    const pages=[]; const cur=currentPage.value; const last=lastPage.value;
    if(last<=7){for(let i=1;i<=last;i++)pages.push(i);return pages;}
    pages.push(1); if(cur>3)pages.push('...');
    for(let i=Math.max(2,cur-1);i<=Math.min(last-1,cur+1);i++)pages.push(i);
    if(cur<last-2)pages.push('...'); pages.push(last); return pages;
});

async function loadPosts(page=1){
    loading.value=true;
    try{const{data}=await api.get(`/posts-front/${jenis.value}`,{params:{page}});posts.value=data.data||[];currentPage.value=data.current_page||1;lastPage.value=data.last_page||1;}
    catch(e){console.error(e);posts.value=[];}
    finally{loading.value=false;}
}
function loadPage(p){if(p<1||p>lastPage.value||p===currentPage.value)return;loadPosts(p);window.scrollTo({top:0,behavior:'smooth'});}
watch(()=>route.params.jenis,(v)=>{jenis.value=v;loadPosts();});
onMounted(()=>loadPosts());
</script>

<style scoped>
.post-masthead{position:relative;overflow:hidden;padding:72px 0 68px;background:#1E40AF;color:#fff}
.post-masthead-grid{position:absolute;inset:0;opacity:.08;background-image:linear-gradient(rgba(255,255,255,.22) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.22) 1px,transparent 1px);background-size:48px 48px}
.post-masthead-orb{position:absolute;right:7%;top:-170px;width:430px;height:430px;border-radius:50%;background:radial-gradient(circle,rgba(96,165,250,.6),transparent 68%);filter:blur(50px)}
.breadcrumb{display:flex;align-items:center;gap:9px;margin-bottom:30px;color:rgba(255,255,255,.55);font-size:12px}.breadcrumb a:hover{color:#fff}.breadcrumb i{font-size:8px}
.post-masthead-copy{display:flex;align-items:flex-start;gap:22px;max-width:760px}.post-masthead-icon{display:grid;place-items:center;width:66px;height:66px;flex:0 0 66px;border:1px solid rgba(255,255,255,.16);border-radius:20px;background:rgba(255,255,255,.1);font-size:24px;backdrop-filter:blur(10px)}
.post-masthead-copy span{display:block;margin-bottom:8px;color:rgba(255,255,255,.58);font-size:11px;font-weight:700;letter-spacing:.13em;text-transform:uppercase}.post-masthead-copy h1{color:#fff;font-size:clamp(2.2rem,5vw,4rem);margin-bottom:14px}.post-masthead-copy p{color:rgba(255,255,255,.68);font-size:16px;line-height:1.8}
.post-list-section{padding:72px 0 92px;background:#F8FAFC}.post-loading-grid,.post-grid{display:grid;gap:24px}.post-skeleton-card,.post-card{overflow:hidden;border:1px solid #E5EAF0;border-radius:22px;background:#fff}.post-skeleton-image{height:230px}
.featured-post{display:grid;overflow:hidden;margin-bottom:72px;border:1px solid #E2E8F0;border-radius:28px;background:#fff;box-shadow:0 24px 70px rgba(15,23,42,.09)}.featured-media{position:relative;min-height:320px;overflow:hidden;background:#EAF1F8}.featured-media img{width:100%;height:100%;object-fit:cover;transition:transform .7s ease}.featured-post:hover .featured-media img{transform:scale(1.035)}.featured-badge{position:absolute;left:20px;top:20px;padding:8px 13px;border-radius:999px;background:var(--color-primary);color:#fff;font-size:11px;font-weight:700;box-shadow:0 8px 22px rgba(37,99,235,.25)}
.featured-copy{display:flex;flex-direction:column;justify-content:center;padding:34px}.featured-copy h2{margin:18px 0 15px;color:#263238;font-size:clamp(1.65rem,3.2vw,2.55rem);line-height:1.18}.featured-copy>p{margin-bottom:22px;color:#64748B;font-size:15px;line-height:1.8}.post-meta{display:flex;flex-wrap:wrap;gap:14px;color:#78909C;font-size:11px}.post-meta span{display:inline-flex;align-items:center;gap:6px}.post-meta i{color:var(--color-primary)}.read-link{display:inline-flex;align-items:center;gap:8px;margin-top:24px;color:var(--color-primary);font-size:13px;font-weight:700}.group:hover .read-link{gap:12px}
.post-section-head{display:flex;align-items:end;justify-content:space-between;gap:20px;margin-bottom:28px}.post-section-head span{display:block;margin-bottom:6px;color:var(--color-primary);font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}.post-section-head h2{color:#263238;font-size:28px}.post-section-head p{color:#94A3B8;font-size:12px}
.post-card{display:flex;flex-direction:column;min-width:0;box-shadow:0 12px 36px rgba(15,23,42,.045);transition:transform .3s ease,box-shadow .3s ease,border-color .3s ease}.post-card:hover{transform:translateY(-6px);border-color:rgba(37,99,235,.2);box-shadow:0 22px 55px rgba(15,23,42,.1)}.post-card-media{position:relative;height:220px;overflow:hidden;background:#EAF1F8}.post-card-media img{width:100%;height:100%;object-fit:cover;transition:transform .6s ease}.post-card:hover .post-card-media img{transform:scale(1.05)}.post-card-media .badge{position:absolute;left:16px;top:16px}.post-image-placeholder{display:grid;place-items:center;width:100%;height:100%;color:#C5D1DD;font-size:44px;background:#F1F5F9}.post-card-copy{display:flex;flex:1;flex-direction:column;padding:22px}.post-card-copy h3{margin:13px 0 10px;color:#334155;font-size:17px;line-height:1.42}.post-card-copy>p{display:-webkit-box;overflow:hidden;margin-bottom:auto;color:#78909C;font-size:13px;line-height:1.75;-webkit-box-orient:vertical;-webkit-line-clamp:3}
.pagination{display:flex;align-items:center;justify-content:center;flex-wrap:wrap;gap:7px;margin-top:52px}.pagination button,.pagination-gap{display:grid;place-items:center;width:40px;height:40px;border:1px solid #E2E8F0;border-radius:11px;background:#fff;color:#64748B;font-size:13px;transition:.2s}.pagination button:hover:not(:disabled),.pagination button.active{border-color:var(--color-primary);background:var(--color-primary);color:#fff;box-shadow:0 8px 18px rgba(37,99,235,.2)}.pagination button:disabled{opacity:.35;cursor:not-allowed}.pagination-gap{border:0;background:transparent}.pagination-summary{margin-left:9px;color:#94A3B8;font-size:11px}
.empty-state{padding:80px 24px;text-align:center;border:1px dashed #CBD5E1;border-radius:24px;background:#fff}.empty-state>div{display:grid;place-items:center;width:70px;height:70px;margin:0 auto 20px;border-radius:20px;background:#EFF6FF;color:var(--color-primary);font-size:28px}.empty-state h2{margin-bottom:8px;color:#334155}.empty-state p{margin-bottom:26px;color:#78909C}
@media(min-width:768px){.post-grid,.post-loading-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.featured-post{grid-template-columns:1.2fr .8fr}.featured-media{min-height:460px}}
@media(min-width:1100px){.post-grid,.post-loading-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:640px){.post-masthead{padding:56px 0 52px}.post-masthead-copy{display:block}.post-masthead-icon{margin-bottom:20px}.post-list-section{padding:48px 0 70px}.featured-post{margin-bottom:52px}.featured-media{min-height:250px}.featured-copy{padding:25px 22px}.post-section-head{display:block}.post-section-head p{margin-top:8px}.pagination-summary{width:100%;margin:8px 0 0;text-align:center}}
</style>
