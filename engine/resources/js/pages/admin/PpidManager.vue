<template>
<div>
    <h2 class="text-2xl font-bold mb-6" style="color:var(--color-text-primary)">Kelola PPID</h2>

    <div class="flex flex-wrap gap-2 mb-6">
        <button v-for="t in tabs" :key="t.id" @click="activeTab=t.id"
            class="px-4 py-2 rounded-full text-sm font-semibold transition-all"
            :class="activeTab===t.id?'text-white shadow':'bg-gray-100 hover:bg-gray-200'"
            :style="activeTab===t.id?{background:'var(--color-primary)'}:{color:'var(--color-text-secondary)'}">
            <i :class="t.icon" class="mr-1.5"></i>{{ t.label }}
        </button>
    </div>

    <!-- PROFIL -->
    <div v-if="activeTab==='profile'" class="card p-6 animate-fade-in">
        <h3 class="text-sm font-bold mb-4" style="color:var(--color-text-primary)">Profil PPID</h3>
        <div class="space-y-4">
            <div><label class="input-label">Judul</label><input v-model="profileForm.title" class="input-field"></div>
            <div><label class="input-label">Tentang</label><QuillEditor style="height: 400px" v-model="profileForm.about" placeholder="Tentang PPID..." /></div><br><br> 
            <div><label class="input-label">Visi</label><QuillEditor style="height: 400px" v-model="profileForm.visi" placeholder="Visi..." /></div><br><br>
            <div><label class="input-label">Misi</label><QuillEditor style="height: 400px" v-model="profileForm.misi" placeholder="Misi..." /></div><br><br>
            <div><label class="input-label">Tugas & Fungsi</label><QuillEditor style="height: 400px" v-model="profileForm.tupoksi" placeholder="Tugas dan fungsi..." /></div><br><br>
            <div><label class="input-label">Kontak</label><QuillEditor style="height: 400px" v-model="profileForm.kontak" placeholder="Informasi kontak..." /></div><br><br>
            <div><label class="input-label">Profil Pejabat</label><QuillEditor style="height: 400px" v-model="profileForm.profil_pejabat" placeholder="Profil pejabat..." /></div><br><br>
            <div><label class="input-label">Profil SDM / Pegawai</label><QuillEditor style="height: 400px" v-model="profileForm.profil_sdm" placeholder="Profil SDM..." /></div><br><br>
            <div>
                <label class="input-label">Gambar Struktur Organisasi</label>
                <div v-if="profileForm.existingImage" class="mb-2"><img :src="'/upload/ppid/'+profileForm.existingImage" class="h-20 rounded"><button @click="profileForm.existingImage=null" class="ml-2 text-red-500 text-xs"><i class="fas fa-times"></i> Hapus</button></div>
                <input type="file" @change="e=>profileForm.struktur_image=e.target.files[0]" accept="image/*" class="input-field">
            </div>
            <button @click="saveProfile" class="btn-primary"><i class="fas fa-save mr-2"></i>Simpan Profil</button>
        </div>
    </div>

    <!-- BERANDA PPID -->
    <div v-if="activeTab==='beranda'" class="card p-6 animate-fade-in">
        <h3 class="text-sm font-bold mb-4" style="color:var(--color-text-primary)">Beranda PPID</h3>
        <div class="space-y-4">
            <div>
                <label class="input-label">Gambar Beranda</label>
                <div v-if="berandaForm.existingImage && !berandaForm.beranda_image" class="mb-2">
                    <img :src="'/upload/ppid/'+berandaForm.existingImage" class="h-32 rounded-xl">
                    <button @click="berandaForm.existingImage=null" class="ml-2 text-red-500 text-xs"><i class="fas fa-times"></i> Hapus</button>
                </div>
                <div class="relative border-2 border-dashed rounded-xl p-6 text-center transition-colors cursor-pointer hover:border-blue-400 hover:bg-blue-50/30" :class="berandaForm.beranda_image?'border-green-400 bg-green-50/30':'border-gray-300'" @dragover.prevent="$event.currentTarget.classList.add('border-blue-400','bg-blue-50/30')" @dragleave="$event.currentTarget.classList.remove('border-blue-400','bg-blue-50/30')" @drop.prevent="handleBerandaImageDrop($event)" @click="$refs.berandaFileInput.click()">
                    <input ref="berandaFileInput" type="file" accept="image/*" class="hidden" @change="e=>berandaForm.beranda_image=e.target.files[0]">
                    <i class="fas fa-cloud-upload-alt text-2xl mb-2" :class="berandaForm.beranda_image?'text-green-500':'text-gray-400'"></i>
                    <p class="text-sm font-semibold" :class="berandaForm.beranda_image?'text-green-700':'text-gray-500'">{{ berandaForm.beranda_image?berandaForm.beranda_image.name:'Drag & drop gambar atau klik untuk pilih' }}</p>
                    <p v-if="berandaForm.beranda_image" class="text-xs text-gray-400 mt-1">Klik untuk ganti file</p>
                </div>
            </div>
            <div><label class="input-label">Judul Beranda</label><input v-model="berandaForm.beranda_title" class="input-field" placeholder="Judul halaman beranda PPID..."></div>
            <div><label class="input-label">Deskripsi Beranda</label><QuillEditor style="height: 300px" v-model="berandaForm.beranda_description" placeholder="Deskripsi beranda PPID..." /></div><br><br>
            <button @click="saveBeranda" class="btn-primary"><i class="fas fa-save mr-2"></i>Simpan Beranda</button>
        </div>
    </div>

    <!-- JENIS INFORMASI -->
    <div v-if="activeTab==='informations'" class="animate-fade-in">
        <div class="card p-6 mb-6">
            <h3 class="text-sm font-bold mb-4" style="color:var(--color-text-primary)">{{ editInfoId?'Edit':'Tambah' }} Jenis Informasi</h3>
            <div class="space-y-4 mb-4">
                <div><label class="input-label">Judul</label><input v-model="infoForm.title" class="input-field" required></div>
                <div><label class="input-label">Isi Konten</label><QuillEditor v-model="infoForm.description" placeholder="Tulis isi informasi..." /></div>
            </div>
            <div class="flex gap-2">
                <button @click="saveInfo" class="btn-primary"><i class="fas fa-save mr-2"></i>{{ editInfoId?'Update':'Simpan' }}</button>
                <button v-if="editInfoId" @click="editInfoId=null;infoForm={category:'informasi',title:'',description:'',link:'',file:null,status:1}" class="btn-ghost border border-gray-200">Batal</button>
            </div>
        </div>
        <div class="card overflow-hidden">
            <table class="w-full"><thead class="bg-gray-50"><tr><th class="table-header">#</th><th class="table-header">Judul</th><th class="table-header">Aksi</th></tr></thead>
            <tbody><tr v-for="item in allInformations" :key="item.id" class="border-t"><td class="table-cell text-gray-400">{{ item.id }}</td><td class="table-cell font-medium" style="color:var(--color-text-primary)">{{ item.title }}</td><td class="table-cell"><div class="flex gap-1"><button @click="editInfo(item)" class="p-2 rounded-lg hover:bg-blue-50" style="color:var(--color-primary)"><i class="fas fa-edit text-sm"></i></button><button @click="deleteInfo(item.id)" class="p-2 rounded-lg hover:bg-red-50 text-red-500"><i class="fas fa-trash text-sm"></i></button></div></td></tr></tbody></table>
            <div v-if="!allInformations.length" class="text-center py-8 text-gray-400">Belum ada data</div>
        </div>
    </div>

    <!-- STANDAR PELAYANAN -->
    <div v-if="activeTab==='standards'" class="animate-fade-in">
        <div class="card p-6 mb-6">
            <h3 class="text-sm font-bold mb-4" style="color:var(--color-text-primary)">{{ editStdId?'Edit':'Tambah' }} Standar Pelayanan</h3>
            <div class="space-y-4 mb-4">
                <div><label class="input-label">Judul</label><input v-model="stdForm.title" class="input-field" required></div>
                <div><label class="input-label">Konten</label><QuillEditor v-model="stdForm.content" placeholder="Konten standar pelayanan..." /></div>
                <div>
                    <label class="input-label">File POS (PDF)</label>
                    <div v-if="stdForm.existingFile && !stdForm.file" class="flex items-center gap-2 mb-2 p-2 bg-gray-50 rounded-lg">
                        <i class="fas fa-file-pdf text-red-500"></i>
                        <a :href="'/upload/ppid/'+stdForm.existingFile" target="_blank" class="text-sm text-blue-600 underline">{{ stdForm.existingFile }}</a>
                        <button @click="stdForm.existingFile=null" class="ml-auto text-red-500 text-xs"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="relative border-2 border-dashed rounded-xl p-6 text-center transition-colors cursor-pointer hover:border-blue-400 hover:bg-blue-50/30" :class="stdForm.file?'border-green-400 bg-green-50/30':'border-gray-300'" @dragover.prevent="$event.currentTarget.classList.add('border-blue-400','bg-blue-50/30')" @dragleave="$event.currentTarget.classList.remove('border-blue-400','bg-blue-50/30')" @drop.prevent="handleStdFileDrop($event)" @click="$refs.stdFileInput.click()">
                        <input ref="stdFileInput" type="file" accept=".pdf" class="hidden" @change="e=>stdForm.file=e.target.files[0]">
                        <i class="fas fa-cloud-upload-alt text-2xl mb-2" :class="stdForm.file?'text-green-500':'text-gray-400'"></i>
                        <p class="text-sm font-semibold" :class="stdForm.file?'text-green-700':'text-gray-500'">{{ stdForm.file?stdForm.file.name:'Drag & drop PDF atau klik untuk pilih' }}</p>
                        <p v-if="stdForm.file" class="text-xs text-gray-400 mt-1">Klik untuk ganti file</p>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <button @click="saveStd" class="btn-primary"><i class="fas fa-save mr-2"></i>{{ editStdId?'Update':'Simpan' }}</button>
                <button v-if="editStdId" @click="editStdId=null;stdForm={title:'',content:'',file:null,existingFile:null,status:1}" class="btn-ghost border border-gray-200">Batal</button>
            </div>
        </div>
        <div class="card overflow-hidden">
            <table class="w-full"><thead class="bg-gray-50"><tr><th class="table-header">#</th><th class="table-header">Judul</th><th class="table-header">POS</th><th class="table-header">Aksi</th></tr></thead>
            <tbody><tr v-for="item in allStandards" :key="item.id" class="border-t"><td class="table-cell text-gray-400">{{ item.id }}</td><td class="table-cell font-medium" style="color:var(--color-text-primary)">{{ item.title }}</td><td class="table-cell"><a v-if="item.file" :href="'/upload/ppid/'+item.file" target="_blank" class="text-blue-600 text-xs underline"><i class="fas fa-file-pdf mr-1"></i>PDF</a><span v-else class="text-gray-300 text-xs">-</span></td><td class="table-cell"><div class="flex gap-1"><button @click="editStd(item)" class="p-2 rounded-lg hover:bg-blue-50" style="color:var(--color-primary)"><i class="fas fa-edit text-sm"></i></button><button @click="deleteStd(item.id)" class="p-2 rounded-lg hover:bg-red-50 text-red-500"><i class="fas fa-trash text-sm"></i></button></div></td></tr></tbody></table>
            <div v-if="!allStandards.length" class="text-center py-8 text-gray-400">Belum ada data</div>
        </div>
    </div>

    <!-- REGULASI -->
    <div v-if="activeTab==='regulations'" class="animate-fade-in">
        <div class="card p-6 mb-6">
            <h3 class="text-sm font-bold mb-4" style="color:var(--color-text-primary)">{{ editRegId?'Edit':'Tambah' }} Regulasi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div><label class="input-label">Judul</label><input v-model="regForm.title" class="input-field" required></div>
                <div><label class="input-label">Nomor</label><input v-model="regForm.nomor" class="input-field"></div>
                <div class="md:col-span-2"><label class="input-label">Deskripsi</label><QuillEditor v-model="regForm.description" placeholder="Deskripsi regulasi..." /></div>
                <div><label class="input-label">Tanggal</label><input v-model="regForm.tanggal" type="date" class="input-field"></div>
                <div><label class="input-label">Link URL</label><input v-model="regForm.link" class="input-field" placeholder="https://..."></div>
                <div><label class="input-label">File</label><input type="file" @change="e=>regForm.file=e.target.files[0]" class="input-field"></div>
                <div><label class="input-label">Status</label><select v-model="regForm.status" class="input-field"><option :value="1">Aktif</option><option :value="0">Nonaktif</option></select></div>
            </div>
            <div class="flex gap-2">
                <button @click="saveReg" class="btn-primary"><i class="fas fa-save mr-2"></i>{{ editRegId?'Update':'Simpan' }}</button>
                <button v-if="editRegId" @click="editRegId=null;regForm={title:'',nomor:'',description:'',link:'',file:null,tanggal:'',status:1}" class="btn-ghost border border-gray-200">Batal</button>
            </div>
        </div>
        <div class="card overflow-hidden">
            <table class="w-full"><thead class="bg-gray-50"><tr><th class="table-header">#</th><th class="table-header">Judul</th><th class="table-header">Nomor</th><th class="table-header">Tanggal</th><th class="table-header">Status</th><th class="table-header">Aksi</th></tr></thead>
            <tbody><tr v-for="item in allRegulations" :key="item.id" class="border-t"><td class="table-cell text-gray-400">{{ item.id }}</td><td class="table-cell font-medium" style="color:var(--color-text-primary)">{{ item.title }}</td><td class="table-cell text-sm" style="color:var(--color-text-secondary)">{{ item.nomor||'-' }}</td><td class="table-cell text-sm" style="color:var(--color-text-secondary)">{{ item.tanggal||'-' }}</td><td class="table-cell"><span :class="item.status===1?'badge-success':'badge-warning'" class="badge text-[10px]">{{ item.status===1?'Aktif':'Nonaktif' }}</span></td><td class="table-cell"><div class="flex gap-1"><button @click="editReg(item)" class="p-2 rounded-lg hover:bg-blue-50" style="color:var(--color-primary)"><i class="fas fa-edit text-sm"></i></button><button @click="deleteReg(item.id)" class="p-2 rounded-lg hover:bg-red-50 text-red-500"><i class="fas fa-trash text-sm"></i></button></div></td></tr></tbody></table>
            <div v-if="!allRegulations.length" class="text-center py-8 text-gray-400">Belum ada data</div>
        </div>
    </div>

    <!-- LINK EKSTERNAL -->
    <div v-if="activeTab==='externalLinks'" class="animate-fade-in">
        <div class="card p-6 mb-6">
            <h3 class="text-sm font-bold mb-4" style="color:var(--color-text-primary)">{{ editExtId?'Edit':'Tambah' }} Link Eksternal</h3>
            <div class="space-y-4 mb-4">
                <div><label class="input-label">Judul</label><input v-model="extForm.title" class="input-field" placeholder="Nama link eksternal..." required></div>
                <div><label class="input-label">URL Link</label><input v-model="extForm.link" class="input-field" placeholder="https://..." required></div>
                <div>
                    <label class="input-label">Gambar</label>
                    <div v-if="extForm.existingImage && !extForm.image" class="mb-2 flex items-center gap-2">
                        <img :src="'/upload/ppid/'+extForm.existingImage" class="h-16 rounded">
                        <button @click="extForm.existingImage=null" class="text-red-500 text-xs"><i class="fas fa-times"></i> Hapus</button>
                    </div>
                    <div class="relative border-2 border-dashed rounded-xl p-6 text-center transition-colors cursor-pointer hover:border-blue-400 hover:bg-blue-50/30" :class="extForm.image?'border-green-400 bg-green-50/30':'border-gray-300'" @dragover.prevent="$event.currentTarget.classList.add('border-blue-400','bg-blue-50/30')" @dragleave="$event.currentTarget.classList.remove('border-blue-400','bg-blue-50/30')" @drop.prevent="handleExtImageDrop($event)" @click="$refs.extFileInput.click()">
                        <input ref="extFileInput" type="file" accept="image/*" class="hidden" @change="e=>extForm.image=e.target.files[0]">
                        <i class="fas fa-cloud-upload-alt text-2xl mb-2" :class="extForm.image?'text-green-500':'text-gray-400'"></i>
                        <p class="text-sm font-semibold" :class="extForm.image?'text-green-700':'text-gray-500'">{{ extForm.image?extForm.image.name:'Drag & drop gambar atau klik untuk pilih' }}</p>
                        <p v-if="extForm.image" class="text-xs text-gray-400 mt-1">Klik untuk ganti file</p>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <button @click="saveExternalLink" class="btn-primary"><i class="fas fa-save mr-2"></i>{{ editExtId?'Update':'Simpan' }}</button>
                <button v-if="editExtId" @click="resetExtForm" class="btn-ghost border border-gray-200">Batal</button>
            </div>
        </div>
        <div class="card overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50"><tr><th class="table-header"></th><th class="table-header">Gambar</th><th class="table-header">Judul</th><th class="table-header">Link</th><th class="table-header">Status</th><th class="table-header">Aksi</th></tr></thead>
                <tbody>
                    <tr v-for="(item,idx) in externalLinks" :key="item.id" class="border-t"
                        draggable="true"
                        @dragstart="onExtDragStart($event,idx)"
                        @dragover.prevent="onExtDragOver($event,idx)"
                        @drop.prevent="onExtDrop($event,idx)"
                        @dragend="extDragIdx=null">
                        <td class="table-cell cursor-grab"><i class="fas fa-grip-vertical text-gray-300"></i></td>
                        <td class="table-cell"><img v-if="item.image" :src="'/upload/ppid/'+item.image" class="h-10 w-10 object-cover rounded"><span v-else class="text-gray-300 text-xs">-</span></td>
                        <td class="table-cell font-medium" style="color:var(--color-text-primary)">{{ item.title }}</td>
                        <td class="table-cell text-sm"><a :href="item.link" target="_blank" class="text-blue-600 underline truncate max-w-[200px] inline-block">{{ item.link }}</a></td>
                        <td class="table-cell"><span :class="item.status===1?'badge-success':'badge-warning'" class="badge text-[10px]">{{ item.status===1?'Aktif':'Nonaktif' }}</span></td>
                        <td class="table-cell"><div class="flex gap-1"><button @click="editExternalLink(item)" class="p-2 rounded-lg hover:bg-blue-50" style="color:var(--color-primary)"><i class="fas fa-edit text-sm"></i></button><button @click="deleteExternalLink(item.id)" class="p-2 rounded-lg hover:bg-red-50 text-red-500"><i class="fas fa-trash text-sm"></i></button></div></td>
                    </tr>
                </tbody>
            </table>
            <div v-if="!externalLinks.length" class="text-center py-8 text-gray-400">Belum ada link eksternal</div>
        </div>
    </div>

    <!-- PERMOHONAN -->
    <div v-if="activeTab==='permohonan'" class="card p-6 animate-fade-in">
        <h3 class="text-sm font-bold mb-4" style="color:var(--color-text-primary)">Pengaturan Permohonan Informasi</h3>
        <div class="space-y-4">
            <div><label class="input-label">Link Formulir Permohonan</label><input v-model="permohonanForm.permohonan_link" class="input-field" placeholder="https://form.example.com/permohonan"></div>
            <div><label class="input-label">Email Permohonan</label><input v-model="permohonanForm.permohonan_email" type="email" class="input-field" placeholder="ppid@example.com"></div>
            <div><label class="input-label">Telepon Permohonan</label><input v-model="permohonanForm.permohonan_phone" type="tel" class="input-field" placeholder="0812-xxxx-xxxx"></div>
            <button @click="savePermohonan" class="btn-primary"><i class="fas fa-save mr-2"></i>Simpan Pengaturan</button>
        </div>
    </div>
</div>
</template>

<script setup>
import {ref,reactive,onMounted} from 'vue';
import api from '@/bootstrap.js';
import {swalConfirm,swalError,swalSuccess} from '@/swal.js';
import QuillEditor from '@/components/QuillEditor.vue';

const activeTab=ref('profile');
const tabs=[
    {id:'profile',label:'Profil',icon:'fas fa-building'},
    {id:'beranda',label:'Beranda PPID',icon:'fas fa-home'},
    {id:'informations',label:'Jenis Informasi',icon:'fas fa-database'},
    {id:'standards',label:'Standar Pelayanan',icon:'fas fa-clipboard-list'},
    {id:'regulations',label:'Regulasi',icon:'fas fa-gavel'},
    {id:'externalLinks',label:'Link Eksternal',icon:'fas fa-external-link-alt'},
    {id:'permohonan',label:'Permohonan',icon:'fas fa-paper-plane'},
];

const profile=ref(null);
const profileForm=reactive({title:'',about:'',visi:'',misi:'',tupoksi:'',kontak:'',profil_pejabat:'',profil_sdm:'',struktur_image:null,existingImage:null});
const allInformations=ref([]);const editInfoId=ref(null);const infoForm=reactive({category:'informasi',title:'',description:'',link:'',file:null,status:1});
const allStandards=ref([]);const editStdId=ref(null);const stdForm=reactive({title:'',content:'',file:null,existingFile:null,status:1});
const allRegulations=ref([]);const editRegId=ref(null);const regForm=reactive({title:'',nomor:'',description:'',link:'',file:null,tanggal:'',status:1});

const berandaForm=reactive({beranda_image:null,beranda_title:'',beranda_description:'',existingImage:null});
const navItems=ref([]);
const navForm=reactive({title:'',link:''});
const externalLinks=ref([]);const editExtId=ref(null);const extForm=reactive({title:'',link:'',image:null,existingImage:null,status:1});
const permohonanForm=reactive({permohonan_link:'',permohonan_email:'',permohonan_phone:''});

const BASE='/ppid';

function genId(){return Math.random().toString(36).substring(2,10);}

async function loadAll(){
    try{
        const[p,i,s,r]=await Promise.all([api.get(BASE+'/profile'),api.get(BASE+'/informations'),api.get(BASE+'/standards'),api.get(BASE+'/regulations')]);
        profile.value=p.data;
        Object.assign(profileForm,{...p.data,existingImage:p.data.struktur_image,struktur_image:null});
        Object.assign(berandaForm,{beranda_image:null,beranda_title:p.data.beranda_title||'',beranda_description:p.data.beranda_description||'',existingImage:p.data.beranda_image||null});
        const parsed=JSON.parse(p.data.navigations||'[]');
        navItems.value=Array.isArray(parsed)?parsed.map(n=>({...n,_id:n._id||genId()})):[];
        Object.assign(permohonanForm,{permohonan_link:p.data.permohonan_link||'',permohonan_email:p.data.permohonan_email||'',permohonan_phone:p.data.permohonan_phone||''});
        allInformations.value=i.data.filter(x=>x.category==='informasi');allStandards.value=s.data;allRegulations.value=r.data;
    }catch(e){console.error(e);}
    try{
        const el=await api.get(BASE+'/external-links');
        externalLinks.value=el.data;
    }catch(e){console.error(e);}
}

async function saveProfile(){
    const fd=new FormData();
    Object.entries(profileForm).forEach(([k,v])=>{if(v!==null&&k!=='existingImage')fd.append(k,v);});
    try{await api.post(BASE+'/profile',fd,{headers:{'Content-Type':'multipart/form-data'}});swalSuccess('Profil disimpan!');loadAll();}catch(e){swalError('Gagal');}
}

function handleBerandaImageDrop(e){const f=e.dataTransfer.files[0];if(f&&f.type.startsWith('image/'))berandaForm.beranda_image=f;}
async function saveBeranda(){
    const fd=new FormData();
    if(berandaForm.beranda_image)fd.append('beranda_image',berandaForm.beranda_image);
    fd.append('beranda_title',berandaForm.beranda_title);
    fd.append('beranda_description',berandaForm.beranda_description);
    try{await api.post(BASE+'/profile',fd,{headers:{'Content-Type':'multipart/form-data'}});swalSuccess('Beranda disimpan!');loadAll();}catch(e){swalError('Gagal');}
}

function addNavItem(){
    if(!navForm.title.trim())return;
    navItems.value.push({_id:genId(),title:navForm.title.trim(),link:navForm.link.trim(),level:0});
    navForm.title='';navForm.link='';
}
function removeNavItem(idx){navItems.value.splice(idx,1);}
function indentNav(idx){if(navItems.value[idx])navItems.value[idx].level=1;}
function outdentNav(idx){if(navItems.value[idx])navItems.value[idx].level=0;}

let navDragIdx=null;
function onNavDragStart(e,idx){navDragIdx=idx;e.dataTransfer.effectAllowed='move';}
function onNavDragOver(e,idx){e.dataTransfer.dropEffect='move';}
function onNavDrop(e,idx){
    if(navDragIdx===null||navDragIdx===idx)return;
    const item=navItems.value.splice(navDragIdx,1)[0];
    navItems.value.splice(idx,0,item);
    navDragIdx=null;
}

async function saveNavigations(){
    const fd=new FormData();
    fd.append('navigations',JSON.stringify(navItems.value.map(({_id,...rest})=>rest)));
    try{await api.post(BASE+'/profile',fd,{headers:{'Content-Type':'multipart/form-data'}});swalSuccess('Navigasi disimpan!');loadAll();}catch(e){swalError('Gagal');}
}

function editInfo(item){editInfoId.value=item.id;Object.assign(infoForm,{category:'informasi',title:item.title,description:item.description||'',link:'',file:null,status:1});}
async function saveInfo(){
    const fd=new FormData();fd.append('category','informasi');fd.append('title',infoForm.title);fd.append('description',infoForm.description);fd.append('status','1');
    try{if(editInfoId.value)await api.post(`${BASE}/informations/${editInfoId.value}`,fd,{headers:{'Content-Type':'multipart/form-data'}});else await api.post(`${BASE}/informations`,fd,{headers:{'Content-Type':'multipart/form-data'}});swalSuccess('Tersimpan!');editInfoId.value=null;infoForm.title='';infoForm.description='';loadAll();}catch(e){swalError('Gagal');}
}
async function deleteInfo(id){if(!await swalConfirm('Hapus?'))return;try{await api.delete(`${BASE}/informations/${id}`);swalSuccess('Dihapus!');loadAll();}catch(e){swalError('Gagal');}}

function handleStdFileDrop(e){const f=e.dataTransfer.files[0];if(f&&f.type==='application/pdf')stdForm.file=f;}
function editStd(item){editStdId.value=item.id;Object.assign(stdForm,{title:item.title,content:item.content||'',file:null,existingFile:item.file||null,status:item.status});}
async function saveStd(){
    const fd=new FormData();fd.append('title',stdForm.title);fd.append('content',stdForm.content);fd.append('status',String(stdForm.status));
    if(stdForm.file)fd.append('file',stdForm.file);
    try{if(editStdId.value){fd.append('_method','PUT');await api.post(`${BASE}/standards/${editStdId.value}`,fd,{headers:{'Content-Type':'multipart/form-data'}});}else await api.post(`${BASE}/standards`,fd,{headers:{'Content-Type':'multipart/form-data'}});swalSuccess('Tersimpan!');editStdId.value=null;stdForm.title='';stdForm.content='';stdForm.file=null;stdForm.existingFile=null;stdForm.status=1;loadAll();}catch(e){swalError('Gagal');}
}
async function deleteStd(id){if(!await swalConfirm('Hapus?'))return;try{await api.delete(`${BASE}/standards/${id}`);swalSuccess('Dihapus!');loadAll();}catch(e){swalError('Gagal');}}

function editReg(item){editRegId.value=item.id;Object.assign(regForm,{title:item.title,nomor:item.nomor||'',description:item.description||'',link:item.link||'',file:null,tanggal:item.tanggal?.substring(0,10)||'',status:item.status});}
async function saveReg(){
    const fd=new FormData();Object.entries(regForm).forEach(([k,v])=>{if(v!==null)fd.append(k,v);});
    try{if(editRegId.value)await api.post(`${BASE}/regulations/${editRegId.value}`,fd,{headers:{'Content-Type':'multipart/form-data'}});else await api.post(`${BASE}/regulations`,fd,{headers:{'Content-Type':'multipart/form-data'}});swalSuccess('Tersimpan!');editRegId.value=null;regForm.title='';regForm.nomor='';regForm.description='';regForm.link='';regForm.file=null;regForm.tanggal='';regForm.status=1;loadAll();}catch(e){swalError('Gagal');}
}
async function deleteReg(id){if(!await swalConfirm('Hapus?'))return;try{await api.delete(`${BASE}/regulations/${id}`);swalSuccess('Dihapus!');loadAll();}catch(e){swalError('Gagal');}}

function resetExtForm(){editExtId.value=null;extForm.title='';extForm.link='';extForm.image=null;extForm.existingImage=null;extForm.status=1;}
function handleExtImageDrop(e){const f=e.dataTransfer.files[0];if(f&&f.type.startsWith('image/'))extForm.image=f;}
function editExternalLink(item){editExtId.value=item.id;Object.assign(extForm,{title:item.title,link:item.link,image:null,existingImage:item.image||null,status:item.status});}
async function saveExternalLink(){
    const fd=new FormData();
    fd.append('title',extForm.title);fd.append('link',extForm.link);
    if(extForm.image)fd.append('image',extForm.image);
    if(editExtId.value){
        fd.append('_method','PUT');
        try{await api.post(`${BASE}/external-links/${editExtId.value}`,fd,{headers:{'Content-Type':'multipart/form-data'}});swalSuccess('Tersimpan!');resetExtForm();loadAll();}catch(e){swalError('Gagal');}
    }else{
        try{await api.post(`${BASE}/external-links`,fd,{headers:{'Content-Type':'multipart/form-data'}});swalSuccess('Tersimpan!');resetExtForm();loadAll();}catch(e){swalError('Gagal');}
    }
}
async function deleteExternalLink(id){if(!await swalConfirm('Hapus?'))return;try{await api.delete(`${BASE}/external-links/${id}`);swalSuccess('Dihapus!');loadAll();}catch(e){swalError('Gagal');}}

let extDragIdx=null;
function onExtDragStart(e,idx){extDragIdx=idx;e.dataTransfer.effectAllowed='move';}
function onExtDragOver(e,idx){e.dataTransfer.dropEffect='move';}
async function onExtDrop(e,idx){
    if(extDragIdx===null||extDragIdx===idx)return;
    const items=[...externalLinks.value];
    const moved=items.splice(extDragIdx,1)[0];
    items.splice(idx,0,moved);
    externalLinks.value=items;
    extDragIdx=null;
    try{await api.post(`${BASE}/external-links/reorder`,{order:items.map(i=>i.id)});}catch(e){console.error(e);}
}

async function savePermohonan(){
    const fd=new FormData();
    fd.append('permohonan_link',permohonanForm.permohonan_link);
    fd.append('permohonan_email',permohonanForm.permohonan_email);
    fd.append('permohonan_phone',permohonanForm.permohonan_phone);
    try{await api.post(BASE+'/profile',fd,{headers:{'Content-Type':'multipart/form-data'}});swalSuccess('Pengaturan permohonan disimpan!');loadAll();}catch(e){swalError('Gagal');}
}

onMounted(loadAll);
</script>
<style scoped>.input-label{@apply block text-xs font-semibold uppercase tracking-wider mb-1.5;color:var(--color-text-secondary);}</style>
