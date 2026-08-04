<template>
    <div>
        <form @submit.prevent="save" class="card p-6 mb-6">
            <h3 class="text-sm font-semibold mb-5" style="color:var(--color-text-primary)">{{ editId?'Edit':'Tambah' }} Kategori</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                <div><label class="input-label">Nama Kategori</label><input v-model="form.title" type="text" class="input-field" required></div>
                <div><label class="input-label">Status</label><select v-model="form.status" class="input-field"><option :value="1">Publish</option><option :value="2">Non Publish</option></select></div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary"><i class="fas fa-save mr-2"></i>{{ editId?'Update':'Simpan' }}</button>
                <button v-if="editId" type="button" @click="cancelEdit" class="btn-ghost">Batal</button>
            </div>
        </form>
        <div class="card overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50/80"><tr><th class="table-header">#</th><th class="table-header">Nama</th><th class="table-header">Status</th><th class="table-header">Aksi</th></tr></thead>
                <tbody>
                    <tr v-for="k in kategoris" :key="k.id" class="border-t border-gray-100 hover:bg-gray-50/50 transition">
                        <td class="table-cell text-gray-400">{{ k.id }}</td>
                        <td class="table-cell font-medium" style="color:var(--color-text-primary)">{{ k.title }}</td>
                        <td class="table-cell"><span :class="k.status===1?'badge-success':'badge-warning'" class="badge">{{ k.status===1?'Publish':'Draft' }}</span></td>
                        <td class="table-cell"><div class="flex gap-1">
                            <button @click="edit(k)" class="p-2 rounded-lg hover:bg-blue-50 transition" style="color:var(--color-primary)"><i class="fas fa-edit text-sm"></i></button>
                            <button @click="destroy(k.id)" class="p-2 rounded-lg hover:bg-red-50 text-red-500 transition"><i class="fas fa-trash text-sm"></i></button>
                        </div></td>
                    </tr>
                </tbody>
            </table>
            <div v-if="kategoris.length===0" class="text-center py-12"><i class="fas fa-tags text-4xl text-gray-200 mb-3"></i><p class="text-sm" style="color:var(--color-text-secondary)">Belum ada kategori.</p></div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/bootstrap.js';
import { swalConfirm, swalError, swalSuccess } from '@/swal.js';

const route = useRoute(); const jenis = ref(route.params.jenis); const kategoris = ref([]); const editId = ref(null);
const form = reactive({title:'',status:1});

async function load(){try{const{data}=await api.get(`/kategori/${jenis.value}`);kategoris.value=data;}catch(e){}}
function edit(k){editId.value=k.id;form.title=k.title;form.status=k.status;}
function cancelEdit(){editId.value=null;form.title='';form.status=1;}
async function save(){try{if(editId.value)await api.put(`/kategori/${jenis.value}/${editId.value}`,form);else await api.post(`/kategori/${jenis.value}`,form);swalSuccess('Kategori berhasil disimpan!');cancelEdit();load();}catch(e){swalError(e.response?.data?.message||'Gagal');}}
async function destroy(id){if(!await swalConfirm('Hapus kategori ini?'))return;try{await api.delete(`/kategori/${jenis.value}/${id}`);swalSuccess('Kategori dihapus!');load();}catch(e){swalError('Gagal');}}
watch(()=>route.params.jenis,(v)=>{jenis.value=v;load();});
onMounted(load);
</script>

<style scoped>.input-label{@apply block text-xs font-semibold uppercase tracking-wider mb-1.5; color:var(--color-text-secondary);}</style>
