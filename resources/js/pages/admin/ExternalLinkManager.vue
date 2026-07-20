<template>
    <div>
        <form @submit.prevent="save" class="card p-6 mb-6">
            <h3 class="text-sm font-semibold mb-5" style="color:var(--color-text-primary)">{{ editId?'Edit':'Tambah' }} Link</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                <div><label class="input-label">Judul</label><input v-model="form.title" type="text" class="input-field" required></div>
                <div><label class="input-label">URL</label><input v-model="form.link" type="url" class="input-field" required></div>
                <div><label class="input-label">Emoji/Icon</label><input v-model="form.images" type="text" class="input-field" placeholder="🔗"></div>
                <div><label class="input-label">Status</label><select v-model="form.status" class="input-field"><option :value="1">Aktif</option><option :value="0">Nonaktif</option></select></div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary"><i class="fas fa-save mr-2"></i>{{ editId?'Update':'Simpan' }}</button>
                <button v-if="editId" type="button" @click="cancelEdit" class="btn-ghost">Batal</button>
            </div>
        </form>
        <div class="card overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50/80"><tr><th class="table-header">#</th><th class="table-header">Icon</th><th class="table-header">Judul</th><th class="table-header">Link</th><th class="table-header">Aksi</th></tr></thead>
                <tbody>
                    <tr v-for="item in items" :key="item.id" class="border-t border-gray-100 hover:bg-gray-50/50 transition">
                        <td class="table-cell text-gray-400">{{ item.id }}</td>
                        <td class="table-cell text-2xl">{{ item.images }}</td>
                        <td class="table-cell font-medium" style="color:var(--color-text-primary)">{{ item.title }}</td>
                        <td class="table-cell"><a :href="item.link" target="_blank" class="text-sm hover:underline" style="color:var(--color-primary)">{{ item.link }}</a></td>
                        <td class="table-cell"><div class="flex gap-1">
                            <button @click="editItem(item)" class="p-2 rounded-lg hover:bg-blue-50 transition" style="color:var(--color-primary)"><i class="fas fa-edit text-sm"></i></button>
                            <button @click="destroy(item.id)" class="p-2 rounded-lg hover:bg-red-50 text-red-500 transition"><i class="fas fa-trash text-sm"></i></button>
                        </div></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '@/bootstrap.js';
import { swalConfirm, swalError, swalSuccess } from '@/swal.js';

const items = ref([]); const editId = ref(null);
const form = reactive({title:'',link:'',images:'',status:1});

async function load(){try{const{data}=await api.get('/external-links');items.value=data;}catch(e){}}
function editItem(item){editId.value=item.id;form.title=item.title;form.link=item.link;form.images=item.images;form.status=item.status;}
function cancelEdit(){editId.value=null;form.title='';form.link='';form.images='';form.status=1;}
async function save(){try{if(editId.value)await api.put(`/external-links/${editId.value}`,form);else await api.post('/external-links',form);swalSuccess('Link berhasil disimpan!');cancelEdit();load();}catch(e){swalError(e.response?.data?.message||'Gagal');}}
async function destroy(id){if(!await swalConfirm('Hapus link ini?'))return;try{await api.delete(`/external-links/${id}`);swalSuccess('Link dihapus!');load();}catch(e){swalError('Gagal');}}
onMounted(load);
</script>

<style scoped>.input-label{@apply block text-xs font-semibold uppercase tracking-wider mb-1.5; color:var(--color-text-secondary);}</style>
