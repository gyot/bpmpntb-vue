<template>
    <PublicLayout>
        <section class="py-16" style="background:linear-gradient(135deg,#f0f7ff,#e8f0fe)">
            <div class="max-w-7xl mx-auto px-4">
                <div class="grid md:grid-cols-2 gap-8 items-center">
                    <div v-if="profile?.beranda_image" class="rounded-2xl overflow-hidden shadow-lg">
                        <img :src="'/upload/ppid/'+profile.beranda_image" class="w-full h-full object-cover" alt="PPID BPMP NTB">
                    </div>
                    <div :class="profile?.beranda_image ? '' : 'md:col-span-2 text-center'">
                        <h1 class="text-4xl font-bold mb-3" style="color:var(--color-text-primary)">{{ profile?.beranda_title || 'PPID BPMP Provinsi NTB' }}</h1>
                        <div v-if="profile?.beranda_description" class="text-lg prose max-w-none" style="color:var(--color-text-secondary)" v-html="profile.beranda_description"></div>
                        <p v-else class="text-lg" style="color:var(--color-text-secondary)">Pejabat Pengelola Informasi dan Dokumentasi</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <div v-if="loading" class="text-center py-16"><i class="fas fa-spinner fa-spin text-3xl" style="color:var(--color-primary)"></i></div>

            <div v-else>
                <div class="flex flex-wrap gap-2 mb-8 border-b border-gray-200 pb-4">
                    <button v-for="tab in tabs" :key="tab.id" @click="activeTab=tab.id"
                        class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all"
                        :class="activeTab===tab.id ? 'text-white shadow-md' : 'bg-gray-100 hover:bg-gray-200'"
                        :style="activeTab===tab.id ? {background:'var(--color-primary)'} : {color:'var(--color-text-secondary)'}">
                        <i :class="tab.icon" class="mr-1.5"></i>{{ tab.label }}
                    </button>
                </div>

                <div v-if="activeTab==='profil'" class="card p-8 animate-fade-in">
                    <h2 class="text-2xl font-bold mb-6" style="color:var(--color-text-primary)">{{ profile?.title || 'Profil PPID' }}</h2>
                    <div v-if="profile?.about" class="mb-8"><div class="prose max-w-none" v-html="profile.about"></div></div>
                    <div class="grid md:grid-cols-2 gap-8 mb-8">
                        <div class="card p-6 border-t-4" style="border-color:var(--color-primary)">
                            <h3 class="text-lg font-bold mb-3" style="color:var(--color-primary)">Visi</h3>
                            <div class="prose max-w-none" v-html="profile?.visi || '-'"></div>
                        </div>
                        <div class="card p-6 border-t-4" style="border-color:var(--color-accent)">
                            <h3 class="text-lg font-bold mb-3" style="color:var(--color-accent)">Misi</h3>
                            <div class="prose max-w-none" v-html="profile?.misi || '-'"></div>
                        </div>
                    </div>
                    <div v-if="profile?.tupoksi" class="mb-8">
                        <h3 class="text-lg font-bold mb-3" style="color:var(--color-text-primary)">Tugas & Fungsi</h3>
                        <div class="prose max-w-none" v-html="profile.tupoksi"></div>
                    </div>
                    <div v-if="profile?.struktur_image" class="mb-8 text-center">
                        <h3 class="text-lg font-bold mb-3" style="color:var(--color-text-primary)">Struktur Organisasi</h3>
                        <img :src="'/upload/ppid/'+profile.struktur_image" class="max-w-full mx-auto rounded-xl shadow">
                    </div>
                    <div v-if="profile?.kontak">
                        <h3 class="text-lg font-bold mb-3" style="color:var(--color-text-primary)">Kontak</h3>
                        <div class="prose max-w-none" v-html="profile.kontak"></div>
                    </div>
                </div>

                <div v-if="activeTab==='pejabat'" class="card p-8 animate-fade-in">
                    <h2 class="text-2xl font-bold mb-6" style="color:var(--color-text-primary)">Profil Pejabat</h2>
                    <div v-if="profile?.profil_pejabat" class="prose max-w-none" v-html="profile.profil_pejabat"></div>
                    <div v-else class="text-center py-12"><i class="fas fa-user-tie text-5xl text-gray-200 mb-4"></i><p style="color:var(--color-text-secondary)">Belum ada data profil pejabat.</p></div>
                </div>

                <div v-if="activeTab==='sdm'" class="card p-8 animate-fade-in">
                    <h2 class="text-2xl font-bold mb-6" style="color:var(--color-text-primary)">Profil SDM / Pegawai</h2>
                    <div v-if="profile?.profil_sdm" class="prose max-w-none" v-html="profile.profil_sdm"></div>
                    <div v-else class="text-center py-12"><i class="fas fa-users text-5xl text-gray-200 mb-4"></i><p style="color:var(--color-text-secondary)">Belum ada data profil SDM.</p></div>
                </div>

                <div v-if="activeTab==='informasi'" class="animate-fade-in">
                    <h2 class="text-2xl font-bold mb-6" style="color:var(--color-text-primary)">Jenis Informasi</h2>
                    <div v-if="allInformations.length" class="grid md:grid-cols-2 gap-5">
                        <div v-for="item in allInformations" :key="item.id" class="card p-6 hover:shadow-md transition">
                            <h3 class="text-lg font-bold mb-3" style="color:var(--color-text-primary)">{{ item.title }}</h3>
                            <div v-if="item.description" class="prose max-w-none text-sm" style="color:var(--color-text-secondary)" v-html="item.description"></div>
                        </div>
                    </div>
                    <div v-else class="card p-12 text-center"><i class="fas fa-folder-open text-4xl text-gray-300 mb-3"></i><p style="color:var(--color-text-secondary)">Belum ada informasi</p></div>
                </div>

                <div v-if="activeTab==='standar'" class="animate-fade-in">
                    <template v-if="!viewStd">
                        <h2 class="text-2xl font-bold mb-6" style="color:var(--color-text-primary)">Standar Pelayanan</h2>
                        <div v-if="standards.length" class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="item in standards" :key="item.id" @click="viewStd=item" class="card p-5 cursor-pointer hover:shadow-lg hover:-translate-y-1 transition-all group">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:color-mix(in srgb, var(--color-primary) 10%, transparent)"><i class="fas fa-clipboard-list" style="color:var(--color-primary)"></i></div>
                                    <h3 class="font-bold group-hover:text-[var(--color-primary)] transition-colors" style="color:var(--color-text-primary)">{{ item.title }}</h3>
                                </div>
                            </div>
                        </div>
                        <div v-else class="card p-12 text-center"><i class="fas fa-clipboard-list text-4xl text-gray-300 mb-3"></i><p style="color:var(--color-text-secondary)">Belum ada standar pelayanan</p></div>
                    </template>
                    <template v-else>
                        <button @click="viewStd=null" class="mb-4 btn-ghost border border-gray-200"><i class="fas fa-arrow-left mr-2"></i>Kembali</button>
                        <div class="card p-8">
                            <h2 class="text-2xl font-bold mb-6" style="color:var(--color-text-primary)">{{ viewStd.title }}</h2>
                            <div v-if="viewStd.content" class="prose max-w-none mb-6" style="color:var(--color-text-primary)" v-html="viewStd.content"></div>
                            <a v-if="viewStd.file" :href="'/upload/ppid/'+viewStd.file" target="_blank" class="btn-primary"><i class="fas fa-file-pdf mr-2"></i>Lihat POS</a>
                        </div>
                    </template>
                </div>

                <div v-if="activeTab==='regulasi'" class="animate-fade-in">
                    <h2 class="text-2xl font-bold mb-6" style="color:var(--color-text-primary)">Regulasi</h2>
                    <div v-if="regulations.length" class="space-y-3">
                        <div v-for="item in regulations" :key="item.id" class="card p-5 flex items-center gap-4 hover:shadow-md transition">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:color-mix(in srgb, var(--color-secondary) 10%, transparent)"><i class="fas fa-gavel" style="color:var(--color-secondary)"></i></div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold" style="color:var(--color-text-primary)">{{ item.title }}</h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <span v-if="item.nomor" class="text-xs font-mono px-2 py-0.5 rounded bg-gray-100" style="color:var(--color-text-secondary)">{{ item.nomor }}</span>
                                    <span v-if="item.tanggal" class="text-xs" style="color:var(--color-text-secondary)">{{ formatDate(item.tanggal) }}</span>
                                </div>
                            </div>
                            <div class="flex gap-2 flex-shrink-0">
                                <a v-if="item.file" :href="'/upload/ppid/'+item.file" class="btn-ghost text-xs" download><i class="fas fa-download mr-1"></i>Download</a>
                                <a v-if="item.link" :href="item.link" target="_blank" class="btn-ghost text-xs"><i class="fas fa-external-link-alt mr-1"></i>Link</a>
                            </div>
                        </div>
                    </div>
                    <div v-else class="card p-12 text-center"><i class="fas fa-gavel text-4xl text-gray-300 mb-3"></i><p style="color:var(--color-text-secondary)">Belum ada regulasi</p></div>
                </div>

                <div v-if="activeTab==='maklumat'" class="animate-fade-in">
                    <h2 class="text-2xl font-bold mb-6" style="color:var(--color-text-primary)">Maklumat Pelayanan</h2>
                    <div class="card p-8 text-center border-t-4" style="border-color:var(--color-accent)">
                        <div class="text-6xl mb-4">&#x1F4DC;</div>
                        <blockquote class="text-xl italic font-medium leading-relaxed max-w-3xl mx-auto" style="color:var(--color-text-primary)">
                            "Dengan ini, kami sanggup menyelenggarakan pelayanan sesuai standar pelayanan yang telah ditetapkan dan apabila tidak menepati janji ini, kami siap menerima sanksi sesuai peraturan perundang-undangan yang berlaku"
                        </blockquote>
                        <p class="mt-6 font-semibold" style="color:var(--color-text-secondary)">BPMP Provinsi Nusa Tenggara Barat</p>
                    </div>
                </div>

                <div v-if="activeTab==='permohonan'" class="animate-fade-in">
                    <h2 class="text-2xl font-bold mb-6" style="color:var(--color-text-primary)">Permohonan Informasi</h2>
                    <div class="card p-8">
                        <p class="mb-6" style="color:var(--color-text-secondary)">Untuk mengajukan permohonan informasi publik, silakan hubungi PPID BPMP Provinsi NTB melalui:</p>
                        <div class="grid md:grid-cols-2 gap-6 mb-6">
                            <div class="p-5 rounded-xl bg-gray-50 border border-gray-100">
                                <div class="flex items-center gap-3 mb-3"><div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:color-mix(in srgb, var(--color-primary) 10%, transparent)"><i class="fas fa-envelope" style="color:var(--color-primary)"></i></div><span class="font-semibold" style="color:var(--color-text-primary)">Email</span></div>
                                <p style="color:var(--color-text-secondary)">{{ profile?.permohonan_email || setting?.email || 'ntblpmp@gmail.com' }}</p>
                            </div>
                            <div class="p-5 rounded-xl bg-gray-50 border border-gray-100">
                                <div class="flex items-center gap-3 mb-3"><div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:color-mix(in srgb, var(--color-accent) 10%, transparent)"><i class="fas fa-phone" style="color:var(--color-accent)"></i></div><span class="font-semibold" style="color:var(--color-text-primary)">Telepon</span></div>
                                <p style="color:var(--color-text-secondary)">{{ profile?.permohonan_phone || setting?.phone || '0811-390-6669' }}</p>
                            </div>
                        </div>
                        <div v-if="profile?.permohonan_link" class="text-center">
                            <a :href="profile.permohonan_link" target="_blank" class="btn-primary inline-flex items-center gap-2 text-base px-8 py-3">
                                <i class="fas fa-file-alt"></i> Isi Formulir Permohonan Informasi
                            </a>
                        </div>
                    </div>
                </div>

                <div v-if="externalLinks.length" class="mt-12">
                    <h2 class="text-2xl font-bold mb-6" style="color:var(--color-text-primary)">Link Eksternal</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <a v-for="item in externalLinks" :key="item.id" :href="item.link" target="_blank" class="card group hover:shadow-lg hover:-translate-y-1 transition-all overflow-hidden">
                            <div class="aspect-square bg-gray-100 overflow-hidden">
                                <img v-if="item.image" :src="'/upload/ppid/'+item.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform" :alt="item.title">
                                <div v-else class="w-full h-full flex items-center justify-center"><i class="fas fa-link text-3xl text-gray-300"></i></div>
                            </div>
                            <div class="p-3 text-center">
                                <h4 class="text-sm font-semibold truncate" style="color:var(--color-text-primary)">{{ item.title }}</h4>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<script setup>
import {ref,onMounted} from 'vue';
import api from '@/bootstrap.js';
import PublicLayout from '@/layouts/PublicLayout.vue';

const activeTab = ref('profil');
const loading = ref(true);
const profile = ref(null);
const informations = ref({berkala:[],setiap_saat:[],serta_merta:[]});
const allInformations = ref([]);
const standards = ref([]);
const regulations = ref([]);
const externalLinks = ref([]);
const setting = ref(null);
const viewStd = ref(null);

const tabs = [
    {id:'profil',label:'Profil',icon:'fas fa-building'},
    {id:'pejabat',label:'Profil Pejabat',icon:'fas fa-user-tie'},
    {id:'sdm',label:'Profil SDM',icon:'fas fa-users'},
    {id:'informasi',label:'Jenis Informasi',icon:'fas fa-info-circle'},
    {id:'standar',label:'Standar Pelayanan',icon:'fas fa-clipboard-list'},
    {id:'regulasi',label:'Regulasi',icon:'fas fa-gavel'},
    {id:'maklumat',label:'Maklumat',icon:'fas fa-scroll'},
    {id:'permohonan',label:'Permohonan Info',icon:'fas fa-paper-plane'},
];

const bulanIndo=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
function formatDate(tgl){if(!tgl)return'-';const d=new Date(tgl);return isNaN(d)?tgl:`${d.getDate()} ${bulanIndo[d.getMonth()]} ${d.getFullYear()}`;}

onMounted(async()=>{
    const[ppid,st]=await Promise.allSettled([api.get('/ppid'),api.get('/settings')]);
    if(ppid.status==='fulfilled'){
        profile.value=ppid.value.data.profile;
        informations.value=ppid.value.data.informations||{};
        allInformations.value=ppid.value.data.informations?.informasi||[];
        standards.value=ppid.value.data.standards||[];
        regulations.value=ppid.value.data.regulations||[];
        externalLinks.value=ppid.value.data.externalLinks||[];
    }
    if(st.status==='fulfilled')setting.value=st.value.data;
    loading.value=false;
});
</script>
