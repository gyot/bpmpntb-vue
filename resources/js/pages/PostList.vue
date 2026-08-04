<template>
    <PublicLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-2xl font-bold mb-8 capitalize" style="color:var(--color-text-primary)">{{ jenis }}</h1>
            <div v-if="loading" class="text-center py-16"><i class="fas fa-spinner fa-spin text-3xl" style="color:var(--color-primary)"></i></div>
            <div v-else>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="item in posts" :key="item.id" class="card overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all group">
                        <div class="h-48 bg-gray-100 relative overflow-hidden">
                            <img v-if="item.image_url" :src="item.image_url" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" @error="$event.target.style.display='none'">
                            <div v-else class="w-full h-full flex items-center justify-center bg-gray-50"><i class="fas fa-newspaper text-4xl text-gray-200"></i></div>
                            <div class="absolute top-3 left-3 badge badge-primary text-xs font-bold shadow-sm">{{ item.kategori }}</div>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center gap-3 text-xs mb-3" style="color:var(--color-text-secondary)">
                                <span><i class="fas fa-user mr-1"></i>{{ item.writer }}</span>
                                <span class="text-gray-300">·</span>
                                <span><i class="fas fa-calendar mr-1"></i>{{ formatDate(item.tanggal) }}</span>
                            </div>
                            <h3 class="text-base font-bold mb-2 line-clamp-2 group-hover:text-[var(--color-primary)] transition-colors" style="color:var(--color-text-primary)">{{ item.title }}</h3>
                            <p class="text-sm line-clamp-3 mb-4" style="color:var(--color-text-secondary)">{{ item.teaser }}</p>
                            <router-link :to="`/post/${item.jenis}/${item.id}/${item.slug}`" class="inline-flex items-center text-sm font-semibold gap-1 group-hover:gap-2 transition-all" style="color:var(--color-primary)">Baca selengkapnya <i class="fas fa-arrow-right text-xs"></i></router-link>
                        </div>
                    </div>
                </div>

                <div v-if="posts.length===0" class="text-center py-16 card">
                    <i class="fas fa-inbox text-5xl text-gray-200 mb-4"></i>
                    <p style="color:var(--color-text-secondary)">Belum ada data.</p>
                </div>
<!-- /// -->
                <div v-if="lastPage>1" class="flex justify-center items-center gap-1.5 mt-10">
                    <button @click="loadPage(1)" :disabled="currentPage<=1"
                        class="w-9 h-9 rounded-lg flex items-center justify-center text-xs transition-all disabled:opacity-30 disabled:cursor-not-allowed"
                        style="color:var(--color-text-secondary)">
                        <i class="fas fa-angle-double-left"></i>
                    </button>
                    <button @click="loadPage(currentPage-1)" :disabled="currentPage<=1"
                        class="w-9 h-9 rounded-lg flex items-center justify-center text-xs transition-all disabled:opacity-30 disabled:cursor-not-allowed"
                        style="color:var(--color-text-secondary)">
                        <i class="fas fa-angle-left"></i>
                    </button>

                    <template v-for="p in paginationRange" :key="p">
                        <span v-if="p==='...'" class="w-9 h-9 flex items-center justify-center text-xs" style="color:var(--color-text-secondary)">···</span>
                        <button v-else @click="loadPage(p)"
                            class="w-9 h-9 rounded-lg flex items-center justify-center text-sm font-semibold transition-all"
                            :class="p===currentPage ? 'shadow-md' : 'hover:bg-gray-100'"
                            :style="p===currentPage ? {background:'var(--color-primary)', color:'#fff'} : {color:'var(--color-text-secondary)'}">
                            {{ p }}
                        </button>
                    </template>

                    <button @click="loadPage(currentPage+1)" :disabled="currentPage>=lastPage"
                        class="w-9 h-9 rounded-lg flex items-center justify-center text-xs transition-all disabled:opacity-30 disabled:cursor-not-allowed"
                        style="color:var(--color-text-secondary)">
                        <i class="fas fa-angle-right"></i>
                    </button>
                    <button @click="loadPage(lastPage)" :disabled="currentPage>=lastPage"
                        class="w-9 h-9 rounded-lg flex items-center justify-center text-xs transition-all disabled:opacity-30 disabled:cursor-not-allowed"
                        style="color:var(--color-text-secondary)">
                        <i class="fas fa-angle-double-right"></i>
                    </button>

                    <span class="ml-3 text-xs" style="color:var(--color-text-secondary)">Halaman {{ currentPage }} dari {{ lastPage }}</span>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/bootstrap.js';
import PublicLayout from '@/layouts/PublicLayout.vue';

const route = useRoute();
const posts = ref([]); const loading = ref(true); const currentPage = ref(1); const lastPage = ref(1); const jenis = ref(route.params.jenis);
const bulanIndo=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
function formatDate(tgl){if(!tgl)return'-';const d=new Date(tgl);return isNaN(d)?tgl:`${d.getDate()} ${bulanIndo[d.getMonth()]} ${d.getFullYear()}`;}

const paginationRange = computed(() => {
    const pages = []; const cur = currentPage.value; const last = lastPage.value;
    if (last <= 7) { for (let i=1;i<=last;i++) pages.push(i); return pages; }
    pages.push(1);
    if (cur > 3) pages.push('...');
    for (let i=Math.max(2,cur-1); i<=Math.min(last-1,cur+1); i++) pages.push(i);
    if (cur < last-2) pages.push('...');
    pages.push(last);
    return pages;
});

async function loadPosts(page=1){loading.value=true;try{const{data}=await api.get(`/posts-front/${jenis.value}`,{params:{page}});posts.value=data.data||[];currentPage.value=data.current_page||1;lastPage.value=data.last_page||1;}catch(e){console.error(e);posts.value=[];}loading.value=false;}
function loadPage(p){if(p<1||p>lastPage.value)return;loadPosts(p);window.scrollTo({top:0,behavior:'smooth'});}
watch(()=>route.params.jenis,(v)=>{jenis.value=v;loadPosts();});
onMounted(()=>loadPosts());
</script>

<style scoped>
.line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.line-clamp-3{display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}
</style>
