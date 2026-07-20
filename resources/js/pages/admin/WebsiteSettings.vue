<template>
    <div>
        <div v-if="success" class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm flex items-center animate-fade-in"><i class="fas fa-check-circle mr-2"></i>{{ success }}</div>
        <div v-if="error" class="mb-5 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm flex items-center animate-fade-in"><i class="fas fa-exclamation-circle mr-2"></i>{{ error }}</div>
        <form @submit.prevent="save" class="space-y-6">
            <div class="card p-6">
                <h3 class="text-sm font-semibold mb-5 flex items-center gap-2" style="color:var(--color-text-primary)"><i class="fas fa-image" style="color:var(--color-primary)"></i>Logo & Favicon</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--color-text-secondary)">Logo Website</label>
                        <div v-if="setting?.logo" class="mb-3"><img :src="'/upload/settings/'+setting.logo" class="h-14"></div>
                        <input type="file" @change="e=>form.logo=e.target.files[0]" accept="image/*" class="input-field text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--color-text-secondary)">Favicon</label>
                        <div v-if="setting?.favicon" class="mb-3"><img :src="'/upload/settings/'+setting.favicon" class="h-8"></div>
                        <input type="file" @change="e=>form.favicon=e.target.files[0]" accept="image/*" class="input-field text-sm">
                    </div>
                </div>
            </div>
            <div class="card p-6">
                <h3 class="text-sm font-semibold mb-5 flex items-center gap-2" style="color:var(--color-text-primary)"><i class="fas fa-globe" style="color:var(--color-primary)"></i>Informasi Website</h3>
                <div class="space-y-4">
                    <div><label class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--color-text-secondary)">Nama Website</label><input v-model="form.title" type="text" class="input-field"></div>
                    <div><label class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--color-text-secondary)">Deskripsi</label><textarea v-model="form.description" rows="3" class="input-field"></textarea></div>
                    <div><label class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--color-text-secondary)">Footer Text</label><textarea v-model="form.footer" rows="2" class="input-field"></textarea></div>
                    <div><label class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--color-text-secondary)">Alamat</label><textarea v-model="form.alamat" rows="2" class="input-field"></textarea></div>
                </div>
            </div>
            <div class="card p-6">
                <h3 class="text-sm font-semibold mb-5 flex items-center gap-2" style="color:var(--color-text-primary)"><i class="fas fa-phone-alt" style="color:var(--color-primary)"></i>Kontak</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--color-text-secondary)">Telepon</label><input v-model="form.phone" type="text" class="input-field"></div>
                    <div><label class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--color-text-secondary)">HP</label><input v-model="form.hp" type="text" class="input-field"></div>
                    <div><label class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--color-text-secondary)">Email</label><input v-model="form.email" type="email" class="input-field"></div>
                    <div><label class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--color-text-secondary)">WhatsApp</label><input v-model="form.whatsapp" type="text" class="input-field"></div>
                </div>
            </div>
            <div class="card p-6">
                <h3 class="text-sm font-semibold mb-5 flex items-center gap-2" style="color:var(--color-text-primary)"><i class="fas fa-share-alt" style="color:var(--color-primary)"></i>Social Media</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--color-text-secondary)">Facebook</label><input v-model="form.facebook" type="url" class="input-field" placeholder="https://"></div>
                    <div><label class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--color-text-secondary)">Twitter</label><input v-model="form.twitter" type="url" class="input-field" placeholder="https://"></div>
                    <div><label class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--color-text-secondary)">Instagram</label><input v-model="form.instagram" type="url" class="input-field" placeholder="https://"></div>
                    <div><label class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--color-text-secondary)">YouTube</label><input v-model="form.youtube" type="url" class="input-field" placeholder="https://"></div>
                </div>
            </div>
            <div class="card p-6">
                <h3 class="text-sm font-semibold mb-5 flex items-center gap-2" style="color:var(--color-text-primary)"><i class="fas fa-map-marker-alt" style="color:var(--color-primary)"></i>Google Maps</h3>
                <input v-model="form.map" type="text" class="input-field" placeholder="Embed URL Google Maps">
            </div>
            <div class="card p-6">
                <h3 class="text-sm font-semibold mb-5 flex items-center gap-2" style="color:var(--color-text-primary)"><i class="fas fa-star" style="color:var(--color-accent)"></i>Indeks Kepuasan Masyarakat (IKM)</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div><label class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--color-text-secondary)">Nilai IKM</label><input v-model="form.ikm_score" type="number" step="0.01" min="0" max="100" class="input-field" placeholder="92.67"></div>
                    <div><label class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--color-text-secondary)">Periode</label><input v-model="form.ikm_period" type="text" class="input-field" placeholder="Triwulan I Tahun 2026"></div>
                    <div><label class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--color-text-secondary)">Link Survey</label><input v-model="form.ikm_link" type="url" class="input-field" placeholder="https://forms.gle/..."></div>
                </div>
            </div>
            <div class="card p-6">
                <h3 class="text-sm font-semibold mb-5 flex items-center gap-2" style="color:var(--color-text-primary)"><i class="fas fa-bars" style="color:var(--color-primary)"></i>Navigasi Website</h3>
                <p class="text-xs mb-4" style="color:var(--color-text-secondary)">Tambah menu dan submenu yang akan tampil di navigasi utama website. Geser untuk mengurutkan, gunakan indent untuk membuat submenu.</p>
                <div class="flex gap-2 mb-4">
                    <input v-model="navForm.title" class="input-field flex-1" placeholder="Judul menu">
                    <input v-model="navForm.link" class="input-field flex-1" placeholder="Link (cth: /page atau https://...)">
                    <button type="button" @click="addNavItem" class="btn-primary whitespace-nowrap"><i class="fas fa-plus mr-1"></i>Tambah</button>
                </div>
                <div v-if="navItems.length" class="space-y-1">
                    <div v-for="(item,idx) in navItems" :key="item._id"
                        class="flex items-center gap-2 p-3 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 transition"
                        :style="{paddingLeft: (item.level * 1.5 + 0.75) + 'rem'}"
                        draggable="true"
                        @dragstart="onNavDragStart($event,idx)"
                        @dragover.prevent="onNavDragOver($event,idx)"
                        @drop.prevent="onNavDrop($event,idx)"
                        @dragend="navDragIdx=null">
                        <i class="fas fa-grip-vertical text-gray-300 cursor-grab mr-1"></i>
                        <span v-if="item.level===1" class="text-gray-300 mr-1">└</span>
                        <div class="flex-1 min-w-0">
                            <span class="font-semibold text-sm" style="color:var(--color-text-primary)">{{ item.title }}</span>
                            <span class="text-xs text-gray-400 ml-2">{{ item.link }}</span>
                        </div>
                        <button type="button" @click="outdentNav(idx)" :disabled="item.level===0" class="p-1.5 rounded hover:bg-gray-200 text-gray-400 disabled:opacity-30" title="Kurangi level"><i class="fas fa-outdent text-xs"></i></button>
                        <button type="button" @click="indentNav(idx)" :disabled="item.level>=5" class="p-1.5 rounded hover:bg-gray-200 text-gray-400 disabled:opacity-30" title="Tambah level"><i class="fas fa-indent text-xs"></i></button>
                        <button type="button" @click="removeNavItem(idx)" class="p-1.5 rounded hover:bg-red-50 text-red-400" title="Hapus"><i class="fas fa-trash text-xs"></i></button>
                    </div>
                </div>
                <div v-else class="text-center py-6 text-gray-400 text-sm">Belum ada item navigasi custom</div>
            </div>
            <button type="submit" :disabled="saving" class="btn-primary"><i :class="saving?'fa-spinner fa-spin':'fa-save'" class="fas mr-2"></i>{{ saving?'Menyimpan...':'Simpan Pengaturan' }}</button>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '@/bootstrap.js';
import { swalSuccess, swalError } from '@/swal.js';

const setting = ref(null); const success = ref(''); const error = ref(''); const saving = ref(false);
const form = reactive({title:'',description:'',footer:'',alamat:'',phone:'',hp:'',email:'',whatsapp:'',facebook:'',twitter:'',instagram:'',youtube:'',map:'',ikm_score:'',ikm_period:'',ikm_link:'',logo:null,favicon:null});
const navItems = ref([]); const navForm = reactive({title:'',link:''});
function genId(){return Math.random().toString(36).substring(2,10);}
function addNavItem(){if(!navForm.title.trim())return;navItems.value.push({_id:genId(),title:navForm.title.trim(),link:navForm.link.trim(),level:0});navForm.title='';navForm.link='';}
function removeNavItem(idx){navItems.value.splice(idx,1);}
function indentNav(idx){if(navItems.value[idx]&&navItems.value[idx].level<5)navItems.value[idx].level++;}
function outdentNav(idx){if(navItems.value[idx])navItems.value[idx].level=0;}
let navDragIdx=null;
function onNavDragStart(e,idx){navDragIdx=idx;e.dataTransfer.effectAllowed='move';}
function onNavDragOver(e,idx){e.dataTransfer.dropEffect='move';}
function onNavDrop(e,idx){if(navDragIdx===null||navDragIdx===idx)return;const item=navItems.value.splice(navDragIdx,1)[0];navItems.value.splice(idx,0,item);navDragIdx=null;}

onMounted(async()=>{
    try{const{data}=await api.get('/settings-admin');setting.value=data;Object.keys(form).forEach(k=>{if(data[k]&&typeof form[k]==='string')form[k]=data[k];});}catch(e){console.error(e);}
    try{const{data}=await api.get('/ppid/profile');const parsed=JSON.parse(data.navigations||'[]');navItems.value=Array.isArray(parsed)?parsed.map(n=>({...n,_id:n._id||genId()})):[];}catch(e){console.error(e);}
});

async function save(){
    success.value='';error.value='';saving.value=true;
    try{
        const fd=new FormData();
        Object.entries(form).forEach(([k,v])=>{if(v!==null&&v!=='')fd.append(k,v);});
        fd.append('_method','POST');
        await api.post('/settings-admin',fd,{headers:{'Content-Type':'multipart/form-data'}});
        try{const nfd=new FormData();nfd.append('navigations',JSON.stringify(navItems.value.map(({_id,...rest})=>rest)));await api.post('/ppid/profile',nfd,{headers:{'Content-Type':'multipart/form-data'}});}catch(e){console.error(e);}
        swalSuccess('Pengaturan berhasil disimpan!');
    }catch(e){swalError(e.response?.data?.message||'Gagal menyimpan');}
    saving.value=false;
}
</script>
