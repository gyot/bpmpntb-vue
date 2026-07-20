<template>
    <PublicLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div v-if="loading" class="text-center py-16"><i class="fas fa-spinner fa-spin text-3xl" style="color:var(--color-primary)"></i></div>
            <div v-else-if="post" class="flex flex-col lg:flex-row gap-8">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                        <h1 class="text-2xl md:text-3xl font-bold leading-tight" style="color:var(--color-text-primary)">{{ post.title }}</h1>
                        <div class="flex items-center gap-2 print:hidden flex-shrink-0">
                            <button @click="window.print()" class="btn-ghost border border-gray-200 text-xs"><i class="fas fa-print mr-1.5"></i>Cetak</button>
                            <a :href="`/post/${jenis}/${route.params.id}/pdf`" target="_blank" class="btn-danger py-2 px-4 text-xs"><i class="fas fa-file-pdf mr-1.5"></i>PDF</a>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-sm mb-6" style="color:var(--color-text-secondary)">
                        <span class="badge badge-primary"><i class="fas fa-user mr-1.5"></i>{{ post.writer }}</span>
                        <span><i class="fas fa-calendar mr-1.5"></i>{{ formatDate(post.tanggal) }}</span>
                        <span class="badge badge-accent">{{ post.Kategori?.title||'' }}</span>
                    </div>
                    <img v-if="post.images" :src="'/upload/'+jenis+'/'+post.images" class="w-full rounded-2xl mb-8 shadow-sm" @error="$event.target.style.display='none'">
                    <article class="prose max-w-none leading-relaxed post-content" style="color:var(--color-text-primary)" v-html="post.content"></article>
                    <div v-if="post.file&&post.file!=='-'" class="mt-8 print:hidden"><a :href="'/upload/'+jenis+'/'+post.file" class="btn-primary" download><i class="fas fa-download mr-2"></i>Download File</a></div>
                    <div v-if="post.tags" class="mt-8 flex flex-wrap gap-2">
                        <span v-for="tag in post.tags.split(',')" :key="tag" class="badge badge-primary"><i class="fas fa-tag mr-1 text-[10px]"></i>{{ tag.trim() }}</span>
                    </div>
                </div>

                <aside class="w-full lg:w-80 flex-shrink-0 print:hidden">
                    <div class="grid grid-cols-3 gap-4 mb-6 text-center">
                        <router-link to="/post/lakin" class="group">
                            <div class="w-12 h-12 mx-auto mb-2 rounded-xl flex items-center justify-center" style="background:color-mix(in srgb, var(--color-primary) 8%, transparent)">
                                <img src="/lakin.png" class="h-7 w-7 object-contain" @error="$event.target.style.display='none'">
                            </div>
                            <div class="text-[11px] font-semibold" style="color:var(--color-text-primary)">Lap. Kinerja</div>
                        </router-link>
                        <router-link to="/post/renstra" class="group">
                            <div class="w-12 h-12 mx-auto mb-2 rounded-xl flex items-center justify-center" style="background:color-mix(in srgb, var(--color-primary) 8%, transparent)">
                                <img src="/renstra.png" class="h-7 w-7 object-contain" @error="$event.target.style.display='none'">
                            </div>
                            <div class="text-[11px] font-semibold" style="color:var(--color-text-primary)">Renstra</div>
                        </router-link>
                        <router-link to="/post/perjanjian_kinerja" class="group">
                            <div class="w-12 h-12 mx-auto mb-2 rounded-xl flex items-center justify-center" style="background:color-mix(in srgb, var(--color-primary) 8%, transparent)">
                                <img src="/handshake.png" class="h-7 w-7 object-contain" @error="$event.target.style.display='none'">
                            </div>
                            <div class="text-[11px] font-semibold" style="color:var(--color-text-primary)">Perj. Kinerja</div>
                        </router-link>
                    </div>

                    <div class="card p-5 mb-5">
                        <h3 class="text-sm font-bold mb-4 flex items-center gap-2" style="color:var(--color-text-primary)">
                            <i class="fas fa-chart-bar text-xs" style="color:var(--color-primary)"></i>Visitor
                        </h3>
                        <ul class="space-y-2.5 text-sm">
                            <li class="flex justify-between"><span style="color:var(--color-text-secondary)">Total Pengunjung</span><span class="font-bold" style="color:var(--color-text-primary)">{{ visitorStats?.totalVisitors||0 }}</span></li>
                            <li class="flex justify-between"><span style="color:var(--color-text-secondary)">Hari Ini</span><span class="font-bold" style="color:var(--color-text-primary)">{{ visitorStats?.todayVisitors||0 }}</span></li>
                            <li class="flex justify-between"><span style="color:var(--color-text-secondary)">Bulan Ini</span><span class="font-bold" style="color:var(--color-text-primary)">{{ visitorStats?.thismonthVisitors||0 }}</span></li>
                            <li class="flex justify-between"><span style="color:var(--color-text-secondary)">Online</span><span class="font-bold" style="color:var(--color-accent)">{{ visitorStats?.onlineVisitors||0 }}</span></li>
                        </ul>
                    </div>

                    <div class="card p-5 mb-5" style="background:color-mix(in srgb, var(--color-primary) 4%, transparent)">
                        <h3 class="text-sm font-bold mb-4 flex items-center gap-2" style="color:var(--color-text-primary)">
                            <i class="fas fa-newspaper text-xs" style="color:var(--color-primary)"></i>{{ jenis === 'berita' ? 'Berita Terbaru' : 'Artikel Terbaru' }}
                        </h3>
                        <ul class="space-y-3">
                            <li v-for="item in lasts" :key="item.id">
                                <router-link :to="`/post/${jenis}/${item.id}/${item.slug}`" class="block group">
                                    <p class="text-sm font-medium leading-snug group-hover:text-[var(--color-primary)] transition-colors line-clamp-2" style="color:var(--color-text-primary)">{{ item.title }}</p>
                                    <p class="text-xs mt-1" style="color:var(--color-text-secondary)"><i class="fas fa-calendar mr-1"></i>{{ formatDate(item.tanggal) }}</p>
                                </router-link>
                            </li>
                        </ul>
                    </div>

                    <div class="card p-5" v-if="externalLinks.length">
                        <h3 class="text-sm font-bold mb-4 flex items-center gap-2" style="color:var(--color-text-primary)">
                            <i class="fas fa-external-link-alt text-xs" style="color:var(--color-primary)"></i>Tautan Eksternal
                        </h3>
                        <ul class="space-y-2">
                            <li v-for="link in externalLinks" :key="link.id">
                                <a :href="link.link" target="_blank" class="flex items-center gap-2 text-sm hover:underline transition" style="color:var(--color-primary)">
                                    <i class="fas fa-arrow-up-right-from-square text-[10px] opacity-50"></i>
                                    <span>{{ link.title }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </aside>
            </div>
            <div v-else class="text-center py-16 card">
                <i class="fas fa-exclamation-triangle text-5xl text-gray-200 mb-4"></i>
                <p style="color:var(--color-text-secondary)">Data tidak ditemukan.</p>
            </div>
        </div>
    </PublicLayout>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/bootstrap.js';
import PublicLayout from '@/layouts/PublicLayout.vue';

const route = useRoute();
const post = ref(null); const lasts = ref([]); const externalLinks = ref([]); const visitorStats = ref(null); const loading = ref(true); const jenis = ref(route.params.jenis);
const bulanIndo=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
function formatDate(tgl){if(!tgl)return'-';const d=new Date(tgl);return isNaN(d)?tgl:`${d.getDate()} ${bulanIndo[d.getMonth()]} ${d.getFullYear()}`;}

async function loadPost(){
    loading.value=true;
    const[postRes,linksRes,statsRes]=await Promise.allSettled([
        api.get(`/posts-front/${jenis.value}/${route.params.id}`),
        api.get('/external-links'),
        api.get('/visitor-stats'),
    ]);
    if(postRes.status==='fulfilled'){post.value=postRes.value.data.data;lasts.value=postRes.value.data.lasts||[];}else{post.value=null;}
    if(linksRes.status==='fulfilled')externalLinks.value=linksRes.value.data||[];
    if(statsRes.status==='fulfilled')visitorStats.value=statsRes.value.data;
    loading.value=false;
}

watch(()=>route.params.id,()=>loadPost());
onMounted(()=>loadPost());
</script>

<style scoped>
.line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
</style>
