<template>
    <PublicLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div v-if="loading" class="text-center py-16"><i class="fas fa-spinner fa-spin text-3xl" style="color:var(--color-primary)"></i></div>
            <div v-else-if="item" class="flex flex-col lg:flex-row gap-8">
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl md:text-3xl font-bold leading-tight mb-5" style="color:var(--color-text-primary)">{{ item.title }}</h1>
                    <div class="flex flex-wrap items-center gap-3 text-sm mb-6" style="color:var(--color-text-secondary)">
                        <span v-if="item.writer" class="badge badge-primary"><i class="fas fa-user mr-1.5"></i>{{ item.writer }}</span>
                        <span v-if="item.tanggal"><i class="fas fa-calendar mr-1.5"></i>{{ formatDate(item.tanggal) }}</span>
                    </div>
                    <img v-if="item.image" :src="'/upload/layanans/'+item.image" class="w-full rounded-2xl mb-4 shadow-sm max-h-96 object-cover" @error="$event.target.style.display='none'">
                    <a v-if="item.pos_file" :href="'/upload/layanans/'+item.pos_file" target="_blank" class="inline-flex items-center gap-2 mb-8 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:shadow-lg" style="background:var(--color-primary)"><i class="fas fa-file-pdf"></i>Lihat POS</a>
                    <article class="prose max-w-none leading-relaxed" style="color:var(--color-text-primary)" v-html="item.content"></article>
                    <div v-if="item.tags" class="mt-8 flex flex-wrap gap-2">
                        <span v-for="tag in item.tags.split(',')" :key="tag" class="badge badge-primary"><i class="fas fa-tag mr-1 text-[10px]"></i>{{ tag.trim() }}</span>
                    </div>
                </div>
                <aside class="w-full lg:w-80 flex-shrink-0">
                    <div class="card p-5 mb-5">
                        <h3 class="text-sm font-bold mb-4" style="color:var(--color-text-primary)"><i class="fas fa-th-large mr-2 text-xs" style="color:var(--color-primary)"></i>Layanan Lainnya</h3>
                        <ul class="space-y-3">
                            <li v-for="l in lasts" :key="l.id">
                                <router-link :to="l.link" class="block group">
                                    <p class="text-sm font-medium leading-snug group-hover:text-[var(--color-primary)] transition-colors line-clamp-2" style="color:var(--color-text-primary)">{{ l.title }}</p>
                                </router-link>
                            </li>
                        </ul>
                    </div>
                </aside>
            </div>
            <div v-else class="text-center py-16 card"><i class="fas fa-exclamation-triangle text-5xl text-gray-200 mb-4"></i><p style="color:var(--color-text-secondary)">Data tidak ditemukan.</p></div>
        </div>
    </PublicLayout>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/bootstrap.js';
import PublicLayout from '@/layouts/PublicLayout.vue';

const route = useRoute();
const item = ref(null); const lasts = ref([]); const loading = ref(true);
const bulanIndo=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
function formatDate(tgl){if(!tgl)return'-';const d=new Date(tgl);return isNaN(d)?tgl:`${d.getDate()} ${bulanIndo[d.getMonth()]} ${d.getFullYear()}`;}

async function load(){loading.value=true;try{const{data}=await api.get(`/layanans/${route.params.id}`);item.value=data.item;lasts.value=(data.lasts||[]).map(l=>({...l,link:`/layanan/${l.id}/${l.slug}`}));}catch(e){item.value=null;}loading.value=false;}
watch(()=>route.params.id,()=>load());
onMounted(load);
</script>

<style scoped>.line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}</style>
