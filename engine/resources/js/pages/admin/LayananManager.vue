<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold" style="color:var(--color-text-primary)">Kelola Layanan</h2>
                <p class="text-sm mt-1" style="color:var(--color-text-secondary)">Geser untuk mengurutkan</p>
            </div>
            <button @click="openForm()" class="btn-primary"><i class="fas fa-plus mr-2"></i>Tambah Layanan</button>
        </div>

        <div v-if="showForm" class="card p-6 mb-6 animate-fade-in">
            <h3 class="text-sm font-semibold mb-5" style="color:var(--color-text-primary)">{{ editId ? 'Edit' : 'Tambah' }} Layanan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div><label class="input-label">Judul Layanan *</label><input v-model="form.title" type="text" class="input-field" required></div>
                <div>
                    <label class="input-label">Jenis Link *</label>
                    <select v-model="form.link_type" class="input-field">
                        <option value="post">Halaman Sendiri (Konten)</option>
                        <option value="external">Link Eksternal</option>
                    </select>
                </div>
                <div v-if="form.link_type==='external'"><label class="input-label">URL Eksternal *</label><input v-model="form.link_url" type="url" class="input-field" placeholder="https://..."></div>
                <div><label class="input-label">Penulis</label><input v-model="form.writer" type="text" class="input-field"></div>
                <div><label class="input-label">Tanggal</label><input v-model="form.tanggal" type="date" class="input-field"></div>
                <div><label class="input-label">Tags / Keyword</label><input v-model="form.tags" type="text" class="input-field" placeholder="Pisahkan dengan koma"></div>
                <div><label class="input-label">Status</label><select v-model="form.status" class="input-field"><option :value="1">Aktif</option><option :value="0">Nonaktif</option></select></div>
            </div>

            <div class="mb-4">
                <label class="input-label">Gambar / Icon</label>
                <div class="flex items-center gap-4">
                    <div v-if="imagePreview" class="relative group">
                        <img :src="imagePreview" class="h-20 w-20 object-contain rounded-xl border border-gray-200 p-1 bg-white">
                        <button type="button" @click="clearImage" class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition"><i class="fas fa-times"></i></button>
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

            <div class="mb-4">
                <label class="input-label">Unggah POS (opsional)</label>
                <div v-if="posPreview && !form.pos_file" class="flex items-center gap-2 mb-2 p-2 bg-gray-50 rounded-lg">
                    <i class="fas fa-file-pdf text-red-500"></i>
                    <a :href="posPreview" target="_blank" class="text-sm text-blue-600 underline">Lihat file POS saat ini</a>
                    <button @click="posPreview=null" class="ml-auto text-red-500 text-xs"><i class="fas fa-times"></i></button>
                </div>
                <div class="relative border-2 border-dashed rounded-xl p-4 text-center transition-colors cursor-pointer hover:border-blue-400 hover:bg-blue-50/30" :class="form.pos_file?'border-green-400 bg-green-50/30':'border-gray-200'" @dragover.prevent="$event.currentTarget.classList.add('border-blue-400','bg-blue-50/30')" @dragleave="$event.currentTarget.classList.remove('border-blue-400','bg-blue-50/30')" @drop.prevent="handlePosDrop($event)" @click="$refs.posInput.click()">
                    <input ref="posInput" type="file" accept=".pdf" class="hidden" @change="e=>{form.pos_file=e.target.files[0];}">
                        <i class="fas fa-cloud-upload-alt text-lg mb-1" :class="form.pos_file?'text-green-500':'text-gray-300'"></i>
                        <p class="text-xs font-semibold" :class="form.pos_file?'text-green-700':'text-gray-400'">{{ form.pos_file?form.pos_file.name:'Drag & drop PDF atau klik untuk pilih' }}</p>
                </div>
            </div>

            <div class="mb-5" v-if="form.link_type==='post'">
                <label class="input-label">Konten Layanan</label>
                <div ref="editorWrap" style="position:relative"><div ref="editorRef"></div></div>
                <p class="text-xs mt-1" style="color:var(--color-text-secondary)"><i class="fas fa-info-circle mr-1"></i>Paste gambar langsung ke editor untuk upload</p>
            </div>

            <div class="flex gap-2 pt-2 border-t border-gray-100">
                <button @click="save" :disabled="saving" class="btn-primary"><i :class="saving?'fa-spinner fa-spin':'fa-save'" class="fas mr-2"></i>{{ saving?'Menyimpan...':(editId?'Update':'Simpan') }}</button>
                <button @click="cancelEdit" class="btn-ghost border border-gray-200">Batal</button>
            </div>
        </div>

        <div class="space-y-3">
            <div v-for="(item,i) in items" :key="item.id" class="card p-4 flex items-center gap-4 hover:shadow-md transition group" draggable="true" @dragstart="dragIdx=i" @dragover.prevent @drop.prevent="onDrop(i)" @dragend="dragIdx=null">
                <div class="text-gray-300 cursor-grab"><i class="fas fa-grip-vertical"></i></div>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold" style="background:color-mix(in srgb, var(--color-primary) 10%, transparent); color:var(--color-primary)">{{ i+1 }}</div>
                <div class="w-12 h-12 rounded-xl border border-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0 bg-gray-50">
                    <img v-if="item.image" :src="'/upload/layanans/'+item.image" class="w-full h-full object-contain p-1">
                    <i v-else class="fas fa-concierge-bell text-gray-300"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-semibold truncate" style="color:var(--color-text-primary)">{{ item.title }}</h4>
                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                        <span class="badge text-[10px]" :class="item.link_type==='external'?'badge-warning':'badge-primary'">
                            <i :class="item.link_type==='external'?'fa-external-link-alt':'fa-file-alt'" class="fas mr-1 text-[8px]"></i>
                            {{ item.link_type==='external'?'Eksternal':'Konten' }}
                        </span>
                        <span v-if="item.link_type==='external'" class="text-[10px] truncate max-w-[200px]" style="color:var(--color-text-secondary)">{{ item.link_url }}</span>
                        <span v-if="item.content" class="badge badge-success text-[10px]"><i class="fas fa-align-left mr-1 text-[8px]"></i>Ada Konten</span>
                        <a v-if="item.pos_file" :href="'/upload/layanans/'+item.pos_file" target="_blank" class="badge badge-primary text-[10px] hover:opacity-80"><i class="fas fa-file-pdf mr-1 text-[8px]"></i>Lihat POS</a>
                        <span v-if="item.tags" class="text-[10px]" style="color:var(--color-text-secondary)"><i class="fas fa-tags mr-1"></i>{{ item.tags }}</span>
                        <span :class="item.status===1?'badge-success':'badge-danger'" class="badge text-[10px]">{{ item.status===1?'Aktif':'Nonaktif' }}</span>
                    </div>
                </div>
                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition">
                    <button @click="moveItem(i,-1)" :disabled="i===0" class="p-2 rounded-lg hover:bg-gray-100 disabled:opacity-30 transition"><i class="fas fa-chevron-up text-xs"></i></button>
                    <button @click="moveItem(i,1)" :disabled="i===items.length-1" class="p-2 rounded-lg hover:bg-gray-100 disabled:opacity-30 transition"><i class="fas fa-chevron-down text-xs"></i></button>
                    <button @click="openForm(item)" class="p-2 rounded-lg hover:bg-blue-50 transition" style="color:var(--color-primary)"><i class="fas fa-edit text-xs"></i></button>
                    <button @click="destroy(item.id)" class="p-2 rounded-lg hover:bg-red-50 text-red-500 transition"><i class="fas fa-trash text-xs"></i></button>
                </div>
            </div>
            <div v-if="items.length===0" class="card text-center py-12"><i class="fas fa-concierge-bell text-4xl text-gray-200 mb-3"></i><p class="text-sm" style="color:var(--color-text-secondary)">Belum ada layanan.</p></div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick, onBeforeUnmount } from 'vue';
import api from '@/bootstrap.js';
import { swalConfirm, swalError, swalSuccess, swalWarning } from '@/swal.js';

const items = ref([]); const editId = ref(null); const saving = ref(false); const showForm = ref(false);
const imgInput = ref(null); const imagePreview = ref(null); const posPreview = ref(null); const editorRef = ref(null); const editorWrap = ref(null);
const dragIdx = ref(null);
let quill = null; let activeImg = null; let overlayEl = null; let mousedownHandler = null;

const form = reactive({ title:'', link_type:'post', link_url:'', content:'', pos_file:null, tags:'', writer:'', tanggal:'', status:1, image:null });

async function load(){ try{const{data}=await api.get('/layanans');items.value=data;}catch(e){} }

function openForm(item=null){
    showForm.value=true;
    if(item){
        editId.value=item.id;form.title=item.title;form.link_type=item.link_type;form.link_url=item.link_url||'';
        form.content=item.content||'';form.tags=item.tags||'';form.writer=item.writer||'';form.tanggal=item.tanggal?.substring(0,10)||'';form.status=item.status;form.image=null;form.pos_file=null;
        imagePreview.value=item.image?`/upload/layanans/${item.image}`:null;posPreview.value=item.pos_file?`/upload/layanans/${item.pos_file}`:null;
    }else{
        editId.value=null;form.title='';form.link_type='post';form.link_url='';form.content='';form.pos_file=null;form.tags='';form.writer='';form.tanggal='';form.status=1;form.image=null;imagePreview.value=null;posPreview.value=null;
    }
    nextTick(()=>setTimeout(initEditor,200));
}

function cancelEdit(){ showForm.value=false;editId.value=null;destroyEditor(); }

function handleFile(e){const f=e.target.files?.[0];if(f){form.image=f;imagePreview.value=URL.createObjectURL(f);}}
function handleDrop(e){const f=e.dataTransfer?.files?.[0];if(f){form.image=f;imagePreview.value=URL.createObjectURL(f);}}
function handlePaste(e){const items=e.clipboardData?.items;if(!items)return;for(const i of items)if(i.type.startsWith('image/')){e.preventDefault();form.image=i.getAsFile();imagePreview.value=URL.createObjectURL(form.image);return;}}
function clearImage(){form.image=null;if(imagePreview.value)URL.revokeObjectURL(imagePreview.value);imagePreview.value=null;if(imgInput.value)imgInput.value.value='';}
function handlePosDrop(e){const f=e.dataTransfer.files[0];if(f&&f.type==='application/pdf')form.pos_file=f;}

async function uploadEditorImage(file){
    try{
        const base64=await new Promise((resolve,reject)=>{const r=new FileReader();r.onload=()=>resolve(r.result);r.onerror=reject;r.readAsDataURL(file);});
        const{data}=await api.post('/quil-upload-image',{images:[base64]});
        if(data.success&&data.urls?.length)return data.urls[0];
    }catch(e){console.error(e);}
    return null;
}

function initEditor(){
    if(!window.Quill||!editorRef.value||form.link_type!=='post')return;
    quill=new Quill(editorRef.value,{theme:'snow',placeholder:'Tulis konten layanan...',modules:{toolbar:[[{header:[1,2,3,4,5,6,false]}],[{font:[]}],['bold','italic','underline','strike'],[{color:[]},{background:[]}],[{align:[]}],[{list:'ordered'},{list:'bullet'}],['blockquote','code-block'],['link','image','video'],['clean']]}});
    quill.getModule('toolbar').addHandler('image',()=>{
        const input=document.createElement('input');input.setAttribute('type','file');input.setAttribute('accept','image/*');input.setAttribute('multiple','multiple');input.click();
        input.onchange=async()=>{for(const file of input.files){const range=quill.getSelection(true);const idx=range?range.index:quill.getLength()-1;quill.insertText(idx,'⏳ Upload...',{color:'#999'});const url=await uploadEditorImage(file);quill.deleteText(idx,13);if(url){quill.insertEmbed(idx,'image',url);quill.setSelection(idx+1);}}};
    });
    quill.root.addEventListener('paste',async e=>{const items=e.clipboardData?.items;if(!items)return;for(const item of items){if(item.type.startsWith('image/')){e.preventDefault();const file=item.getAsFile();const range=quill.getSelection(true);const idx=range?range.index:quill.getLength()-1;quill.insertText(idx,'⏳ Upload...',{color:'#999'});const url=await uploadEditorImage(file);quill.deleteText(idx,13);if(url){quill.insertEmbed(idx,'image',url);quill.setSelection(idx+1);}return;}}});
    quill.root.addEventListener('click',e=>{if(e.target.tagName==='IMG')showOverlay(e.target);});
    mousedownHandler=e=>{if(overlayEl&&!e.target.closest('.img-overlay')&&!e.target.closest('.ql-toolbar'))hideOverlay();};
    document.addEventListener('mousedown',mousedownHandler);
    quill.on('text-change',()=>{form.content=quill.root.innerHTML;});
    if(form.content)quill.root.innerHTML=form.content;
}

function showOverlay(img){hideOverlay();activeImg=img;const c=editorWrap.value;if(!c)return;const cr=c.getBoundingClientRect(),ir=img.getBoundingClientRect();
    const ov=document.createElement('div');ov.className='img-overlay';ov.style.cssText=`position:absolute;z-index:100;top:${ir.top-cr.top+c.scrollTop}px;left:${ir.left-cr.left+c.scrollLeft}px;width:${ir.width}px;height:${ir.height}px;`;
    ov.innerHTML=`<div style="position:absolute;inset:-2px;border:2px solid #3b82f6;border-radius:4px;pointer-events:none"></div><div style="position:absolute;bottom:-7px;right:-7px;width:14px;height:14px;background:#3b82f6;border:2px solid white;border-radius:50%;cursor:nwse-resize;z-index:10"></div>`;
    const handle=ov.lastChild;let sx,sw,sh;
    handle.addEventListener('mousedown',e=>{e.preventDefault();sx=e.clientX;sw=img.offsetWidth;sh=img.offsetHeight;const r=sw/sh;
        const onM=ev=>{const nw=Math.max(60,sw+ev.clientX-sx);img.style.width=nw+'px';img.style.height=Math.round(nw/r)+'px';ov.style.width=nw+'px';ov.style.height=Math.round(nw/r)+'px';};
        const onU=()=>{document.removeEventListener('mousemove',onM);document.removeEventListener('mouseup',onU);};
        document.addEventListener('mousemove',onM);document.addEventListener('mouseup',onU);
    });
    const popup=document.createElement('div');popup.style.cssText='position:absolute;top:-42px;left:50%;transform:translateX(-50%);background:#1f2937;border-radius:8px;padding:4px 6px;display:flex;gap:2px;z-index:50;box-shadow:0 4px 12px rgba(0,0,0,0.3);';
    [{l:'Kiri',d:'left'},{l:'Kanan',d:'right'},{l:'Full',d:''},{l:'Hapus',c:'#f87171',a:()=>{img.remove();hideOverlay();if(quill)quill.update('user');}}].forEach(x=>{
        const b=document.createElement('button');b.type='button';b.textContent=x.l;b.style.cssText=`color:${x.c||'white'};background:none;border:none;padding:4px 8px;border-radius:4px;cursor:pointer;font-size:11px;white-space:nowrap;font-family:Inter,sans-serif;`;
        b.addEventListener('mouseenter',()=>b.style.background='rgba(255,255,255,0.15)');b.addEventListener('mouseleave',()=>b.style.background='');
        b.addEventListener('click',e=>{e.preventDefault();if(x.a){x.a();return;}if(x.d==='left')img.style.cssText='max-width:100%;height:auto;border-radius:4px;float:left;width:45%;margin:0 16px 8px 0;display:block;';else if(x.d==='right')img.style.cssText='max-width:100%;height:auto;border-radius:4px;float:right;width:45%;margin:0 0 8px 16px;display:block;';else img.style.cssText='max-width:100%;height:auto;border-radius:4px;float:none;width:100%;margin:8px 0;display:block;';hideOverlay();});
        popup.appendChild(b);
    });
    ov.appendChild(popup);c.appendChild(ov);overlayEl=ov;
}
function hideOverlay(){if(overlayEl){overlayEl.remove();overlayEl=null;}activeImg=null;}

function destroyEditor(){hideOverlay();if(mousedownHandler){document.removeEventListener('mousedown',mousedownHandler);mousedownHandler=null;}quill=null;}

async function save(){
    if(!form.title.trim()){swalWarning('Judul wajib diisi');return;}
    if(form.link_type==='external'&&!form.link_url){swalWarning('URL eksternal wajib diisi');return;}
    if(quill)form.content=quill.root.innerHTML;
    saving.value=true;
    const fd=new FormData();
    fd.append('title',form.title);fd.append('link_type',form.link_type);fd.append('status',form.status);
    if(form.link_url)fd.append('link_url',form.link_url);
    if(form.content)fd.append('content',form.content);
    if(form.tags)fd.append('tags',form.tags);
    if(form.writer)fd.append('writer',form.writer);
    if(form.tanggal)fd.append('tanggal',form.tanggal);
    if(form.image)fd.append('image',form.image);
    if(form.pos_file)fd.append('pos_file',form.pos_file);
    try{
        if(editId.value){fd.append('_method','PUT');await api.post(`/layanans/${editId.value}`,fd,{headers:{'Content-Type':'multipart/form-data'}});}
        else await api.post('/layanans',fd,{headers:{'Content-Type':'multipart/form-data'}});
        swalSuccess('Layanan berhasil disimpan!');cancelEdit();load();
    }catch(e){swalError(e.response?.data?.message||'Gagal');}
    saving.value=false;
}

async function destroy(id){if(!await swalConfirm('Hapus layanan ini?'))return;try{await api.delete(`/layanans/${id}`);swalSuccess('Dihapus!');load();}catch(e){swalError('Gagal');}}

function onDrop(i){if(dragIdx.value!==null&&dragIdx.value!==i){const item=items.value.splice(dragIdx.value,1)[0];items.value.splice(i,0,item);items.value=[...items.value];saveOrder();}dragIdx.value=null;}
async function moveItem(i,d){const n=i+d;if(n<0||n>=items.value.length)return;const t=items.value[i];items.value[i]=items.value[n];items.value[n]=t;items.value=[...items.value];await saveOrder();}
async function saveOrder(){try{await api.post('/layanans/reorder',{order:items.value.map(s=>s.id)});}catch(e){swalError('Gagal simpan urutan');}}

onMounted(load);
onBeforeUnmount(destroyEditor);
</script>

<style scoped>.input-label{@apply block text-xs font-semibold uppercase tracking-wider mb-1.5; color:var(--color-text-secondary);}</style>
