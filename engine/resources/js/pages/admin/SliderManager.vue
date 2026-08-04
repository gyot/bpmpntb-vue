<template>
    <div>
        <form @submit.prevent="save" class="card p-6 mb-6">
            <h3 class="text-sm font-semibold mb-5" style="color:var(--color-text-primary)">{{ editId?'Edit':'Tambah' }} Slider</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div><label class="input-label">Judul</label><input v-model="form.title" type="text" class="input-field"></div>
                <div><label class="input-label">Link</label><input v-model="form.link" type="url" class="input-field"></div>
                <div><label class="input-label">Status</label><select v-model="form.status" class="input-field"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                <div class="md:col-span-2"><label class="input-label">Deskripsi</label><textarea v-model="form.description" rows="2" class="input-field"></textarea></div>
            </div>
            <div class="mb-5">
                <label class="input-label">Gambar {{ editId?'(kosongkan jika tidak diubah)':'*' }}</label>
                <div class="flex items-center gap-4">
                    <div v-if="imagePreview" class="relative group">
                        <img :src="imagePreview" class="w-40 h-24 object-cover rounded-xl border border-gray-200">
                        <button type="button" @click="clearImage" class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-red-500 text-white text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition shadow"><i class="fas fa-times"></i></button>
                        <div v-if="compressInfo" class="text-[11px] text-emerald-600 mt-1">{{ compressInfo }}</div>
                    </div>
                    <div class="flex-1">
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-[var(--color-primary)] cursor-pointer transition" @click="$refs.imgInput.click()" @dragover.prevent @drop.prevent="handleDrop" @paste="handlePaste" tabindex="0">
                            <i class="fas fa-cloud-upload-alt text-xl text-gray-300 mb-1"></i>
                            <p class="text-xs" style="color:var(--color-text-secondary)">Klik, drag, atau paste gambar</p>
                        </div>
                        <input ref="imgInput" type="file" accept="image/*" class="hidden" @change="handleFile">
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" :disabled="saving" class="btn-primary"><i :class="saving?'fa-spinner fa-spin':'fa-save'" class="fas mr-2"></i>{{ saving?'Menyimpan...':(editId?'Update':'Simpan') }}</button>
                <button v-if="editId" type="button" @click="cancelEdit" class="btn-ghost">Batal</button>
            </div>
        </form>
        <div class="space-y-3">
            <div v-for="(s,i) in sliders" :key="s.id" class="card p-4 flex items-center gap-4 hover:shadow-md transition-all group" draggable="true" @dragstart="onDragStart(i,$event)" @dragover.prevent="onDragOver(i)" @dragend="onDragEnd" @drop.prevent="onDrop(i)">
                <div class="text-gray-300 cursor-grab active:cursor-grabbing"><i class="fas fa-grip-vertical"></i></div>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold" style="background:color-mix(in srgb, var(--color-primary) 10%, transparent); color:var(--color-primary)">{{ i+1 }}</div>
                <img :src="'/upload/sliders/'+s.image" class="h-14 w-20 object-cover rounded-lg border border-gray-100">
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-semibold truncate" style="color:var(--color-text-primary)">{{ s.title||'Tanpa Judul' }}</h4>
                    <span :class="s.status==='active'?'badge-success':'badge-danger'" class="badge text-[10px] mt-1">{{ s.status }}</span>
                </div>
                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition">
                    <button @click="moveSlider(i,-1)" :disabled="i===0" class="p-2 rounded-lg hover:bg-gray-100 disabled:opacity-30 transition"><i class="fas fa-chevron-up text-xs"></i></button>
                    <button @click="moveSlider(i,1)" :disabled="i===sliders.length-1" class="p-2 rounded-lg hover:bg-gray-100 disabled:opacity-30 transition"><i class="fas fa-chevron-down text-xs"></i></button>
                    <button @click="edit(s)" class="p-2 rounded-lg hover:bg-blue-50 transition" style="color:var(--color-primary)"><i class="fas fa-edit text-xs"></i></button>
                    <button @click="destroy(s.id)" class="p-2 rounded-lg hover:bg-red-50 text-red-500 transition"><i class="fas fa-trash text-xs"></i></button>
                </div>
            </div>
            <div v-if="sliders.length===0" class="card text-center py-12"><i class="fas fa-images text-4xl text-gray-200 mb-3"></i><p class="text-sm" style="color:var(--color-text-secondary)">Belum ada slider.</p></div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '@/bootstrap.js';
import { swalConfirm, swalError, swalSuccess, swalWarning } from '@/swal.js';

const sliders = ref([]); const editId = ref(null); const saving = ref(false); const imgInput = ref(null);
const imagePreview = ref(null); const compressInfo = ref(''); const form = reactive({title:'',link:'',description:'',status:'active',image:null});
let dragIndex = null;

function formatFileSize(b){if(!b)return'';if(b<1024)return b+' B';if(b<1024*1024)return(b/1024).toFixed(1)+' KB';return(b/(1024*1024)).toFixed(1)+' MB';}
async function compressImage(file,maxW=1920,q=0.8){const orig=file.size;return new Promise(r=>{const reader=new FileReader();reader.onload=e=>{const img=new Image();img.onload=()=>{const c=document.createElement('canvas');let w=img.width,h=img.height;if(w>maxW){h=Math.round(h*maxW/w);w=maxW;}c.width=w;c.height=h;c.getContext('2d').drawImage(img,0,0,w,h);c.toBlob(blob=>{const f=new File([blob],file.name,{type:'image/jpeg'});compressInfo.value=`${formatFileSize(orig)} → ${formatFileSize(blob.size)}`;r(f);},'image/jpeg',q);};img.src=e.target.result;};reader.readAsDataURL(file);});}
async function processImage(file){if(!file.type.startsWith('image/')){swalWarning('File harus gambar');return;}const c=await compressImage(file);form.image=c;imagePreview.value=URL.createObjectURL(c);}
function clearImage(){form.image=null;if(imagePreview.value)URL.revokeObjectURL(imagePreview.value);imagePreview.value=null;compressInfo.value='';if(imgInput.value)imgInput.value.value='';}
function handleFile(e){const f=e.target.files?.[0];if(f)processImage(f);}
function handleDrop(e){const f=e.dataTransfer?.files?.[0];if(f)processImage(f);}
function handlePaste(e){const items=e.clipboardData?.items;if(!items)return;for(const i of items)if(i.type.startsWith('image/')){e.preventDefault();processImage(i.getAsFile());return;}}
async function load(){try{const{data}=await api.get('/sliders');sliders.value=data;}catch(e){}}
function edit(s){editId.value=s.id;form.title=s.title;form.link=s.link;form.description=s.description;form.status=s.status;form.image=null;imagePreview.value=s.image?`/upload/sliders/${s.image}`:null;compressInfo.value='';}
function cancelEdit(){editId.value=null;form.title='';form.link='';form.description='';form.status='active';clearImage();}
async function save(){if(!editId.value&&!form.image){swalWarning('Gambar wajib diisi');return;}saving.value=true;const fd=new FormData();if(form.title)fd.append('title',form.title);if(form.link)fd.append('link',form.link);if(form.description)fd.append('description',form.description);fd.append('status',form.status);if(form.image)fd.append('image',form.image);if(editId.value&&!form.image)fd.append('_method','PUT');try{await api.post(editId.value?`/sliders/${editId.value}`:'/sliders',fd,{headers:{'Content-Type':'multipart/form-data'}});swalSuccess('Slider berhasil disimpan!');cancelEdit();load();}catch(e){swalError(e.response?.data?.message||'Gagal');}saving.value=false;}
async function destroy(id){if(!await swalConfirm('Hapus slider ini?'))return;try{await api.delete(`/sliders/${id}`);swalSuccess('Slider dihapus!');load();}catch(e){swalError('Gagal');}}
function onDragStart(i,e){dragIndex=i;e.dataTransfer.effectAllowed='move';}
function onDragOver(i){}
function onDrop(i){if(dragIndex!==null&&dragIndex!==i){const item=sliders.value.splice(dragIndex,1)[0];sliders.value.splice(i,0,item);sliders.value=[...sliders.value];saveOrder();}dragIndex=null;}
function onDragEnd(){dragIndex=null;}
async function moveSlider(i,d){const n=i+d;if(n<0||n>=sliders.value.length)return;const t=sliders.value[i];sliders.value[i]=sliders.value[n];sliders.value[n]=t;sliders.value=[...sliders.value];await saveOrder();}
async function saveOrder(){try{await api.post('/sliders/reorder',{order:sliders.value.map(s=>s.id)});}catch(e){swalError('Gagal menyimpan urutan');}}
onMounted(load);
</script>

<style scoped>.input-label{@apply block text-xs font-semibold uppercase tracking-wider mb-1.5; color:var(--color-text-secondary);}</style>
