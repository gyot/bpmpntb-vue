<template>
<div>
    <div class="um-card mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="um-title" style="margin-bottom:0">Manajemen User SIAMIN</h3>
            <button @click="load" class="um-btn um-btn-gray"><i class="fas fa-sync-alt mr-1"></i>Refresh</button>
        </div>
        <div class="flex flex-col md:flex-row gap-3 mb-4">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input v-model="search" type="text" placeholder="Cari nama, email, atau NIP..." class="um-input" style="padding-left:2.2rem">
            </div>
            <select v-model="filterRole" class="um-input" style="width:auto">
                <option value="all">Semua Role SIAMIN</option>
                <option v-for="r in siaminRoles" :key="r" :value="r">{{ r }}</option>
            </select>
            <select v-model="filterLocal" class="um-input" style="width:auto">
                <option value="all">Semua Status</option>
                <option value="registered">Sudah Terdaftar</option>
                <option value="unregistered">Belum Terdaftar</option>
            </select>
            <select v-model="filterStatus" class="um-input" style="width:auto">
                <option value="all">Semua</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs" style="color:var(--color-text-secondary)">{{ filteredUsers.length }} user ditemukan</span>
            <select v-model.number="pageSize" class="um-input" style="width:auto;padding:5px 10px;font-size:12px">
                <option :value="10">10 / halaman</option>
                <option :value="25">25 / halaman</option>
                <option :value="50">50 / halaman</option>
                <option :value="100">100 / halaman</option>
            </select>
        </div>
        <div class="overflow-x-auto">
            <table class="um-table">
                <thead><tr>
                    <th class="um-th-sort" @click="sortBy('id_user')"># <i class="fas um-sort-icon" :class="sortIcon('id_user')"></i></th>
                    <th class="um-th-sort" @click="sortBy('nama')">Nama <i class="fas um-sort-icon" :class="sortIcon('nama')"></i></th>
                    <th class="um-th-sort" @click="sortBy('email')">Email <i class="fas um-sort-icon" :class="sortIcon('email')"></i></th>
                    <th>NIP</th>
                    <th class="um-th-sort" @click="sortBy('role')">Role SIAMIN <i class="fas um-sort-icon" :class="sortIcon('role')"></i></th>
                    <th class="um-th-sort" @click="sortBy('status')">Status <i class="fas um-sort-icon" :class="sortIcon('status')"></i></th>
                    <th class="um-th-sort" @click="sortBy('local_role')">Role Lokal <i class="fas um-sort-icon" :class="sortIcon('local_role')"></i></th>
                    <th>Aksi</th>
                </tr></thead>
                <tbody>
                    <tr v-for="(u,idx) in pagedUsers" :key="u.id_user">
                        <td style="color:#94a3b8;font-weight:600;">{{ u.id_user }}</td>
                        <td class="fw-semibold" style="color:#0f172a;">{{ u.pegawai?.nama || u.name || '-' }}</td>
                        <td style="color:#64748b;">{{ u.email || u.user || '-' }}</td>
                        <td style="color:#64748b;font-size:12px;">{{ u.pegawai?.nip || '-' }}</td>
                        <td><span class="um-badge um-badge-gray">{{ u.role }}</span></td>
                        <td><span class="um-badge" :class="u.status==='aktif'?'um-badge-green':'um-badge-red'">{{ u.status }}</span></td>
                        <td>
                            <span v-if="u.local_role" class="um-badge um-badge-blue">{{ u.local_role }}</span>
                            <span v-else class="um-badge um-badge-yellow">Belum Terdaftar</span>
                        </td>
                        <td>
                            <div class="flex gap-1">
                                <button @click="openSetRole(u)" class="um-action um-action-edit" :title="u.local_role?'Edit Role':'Set Role'">
                                    <i class="fas" :class="u.local_role?'fa-edit':'fa-plus'"></i>
                                </button>
                                <button v-if="u.local_role" @click="revokeAccess(u)" class="um-action um-action-delete" title="Cabut Akses">
                                    <i class="fas fa-user-slash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="!loading && !filteredUsers.length" class="text-center py-8 text-gray-400">
            {{ search || filterRole !== 'all' || filterLocal !== 'all' || filterStatus !== 'all' ? 'Tidak ada user yang cocok' : 'Belum ada data user' }}
        </div>
        <div v-if="loading" class="text-center py-8"><i class="fas fa-spinner fa-spin text-xl text-gray-400"></i></div>
        <div v-if="totalPages > 1" class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
            <span class="text-xs" style="color:var(--color-text-secondary)">Halaman {{ currentPage }} dari {{ totalPages }}</span>
            <div class="flex gap-1">
                <button @click="currentPage=1" :disabled="currentPage===1" class="um-page-btn"><i class="fas fa-angle-double-left"></i></button>
                <button @click="currentPage=Math.max(1,currentPage-1)" :disabled="currentPage===1" class="um-page-btn"><i class="fas fa-angle-left"></i></button>
                <button v-for="p in visiblePages" :key="p" @click="currentPage=p" class="um-page-btn" :class="{'um-page-active':p===currentPage}">{{ p }}</button>
                <button @click="currentPage=Math.min(totalPages,currentPage+1)" :disabled="currentPage===totalPages" class="um-page-btn"><i class="fas fa-angle-right"></i></button>
                <button @click="currentPage=totalPages" :disabled="currentPage===totalPages" class="um-page-btn"><i class="fas fa-angle-double-right"></i></button>
            </div>
        </div>
    </div>

    <!-- Modal Set Role -->
    <div v-if="showModal" class="um-modal-overlay" @click.self="showModal=false">
        <div class="um-modal">
            <div class="um-modal-header">
                <h5><i class="fas fa-user-gear mr-2" style="color:#2563eb;"></i>{{ selectedUser?.local_role ? 'Edit' : 'Set' }} Role User</h5>
                <button @click="showModal=false" class="um-modal-close">&times;</button>
            </div>
            <div class="um-modal-body">
                <div class="space-y-3">
                    <div><label class="um-label">Nama</label><div class="um-readonly">{{ selectedUser?.pegawai?.nama || selectedUser?.name || '-' }}</div></div>
                    <div><label class="um-label">Email</label><div class="um-readonly">{{ selectedUser?.email || selectedUser?.user || '-' }}</div></div>
                    <div><label class="um-label">Role SIAMIN</label><div class="um-readonly">{{ selectedUser?.role }}</div></div>
                    <div>
                        <label class="um-label">Role Lokal</label>
                        <select v-model="roleForm.role" class="um-input">
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="um-modal-footer">
                <button @click="showModal=false" class="um-btn um-btn-gray">Batal</button>
                <button @click="saveRole" class="um-btn um-btn-blue"><i class="fas fa-save mr-1"></i> Simpan</button>
            </div>
        </div>
    </div>
</div>
</template>

<script setup>
import {ref,reactive,computed,onMounted,watch} from 'vue';
import api from '@/bootstrap.js';
import {swalConfirm,swalError,swalSuccess} from '@/swal.js';

const users=ref([]);
const loading=ref(false);
const search=ref('');
const filterRole=ref('all');
const filterLocal=ref('all');
const filterStatus=ref('all');
const showModal=ref(false);
const selectedUser=ref(null);
const roleForm=reactive({role:'user'});

const sortKey=ref('id_user');
const sortDir=ref('asc');
const currentPage=ref(1);
const pageSize=ref(10);

watch([search,filterRole,filterLocal,filterStatus,pageSize],()=>{currentPage.value=1;});

const siaminRoles=computed(()=>{const s=new Set(users.value.map(u=>u.role).filter(Boolean));return[...s].sort();});

function getSortVal(u,key){
    if(key==='nama')return(u.pegawai?.nama||u.name||'').toLowerCase();
    if(key==='email')return(u.email||u.user||'').toLowerCase();
    if(key==='local_role')return(u.local_role||'zzz').toLowerCase();
    return u[key]??'';
}
function sortBy(key){
    if(sortKey.value===key)sortDir.value=sortDir.value==='asc'?'desc':'asc';
    else{sortKey.value=key;sortDir.value='asc';}
}
function sortIcon(key){
    if(sortKey.value!==key)return'fa-sort text-gray-300';
    return sortDir.value==='asc'?'fa-sort-up text-blue-500':'fa-sort-down text-blue-500';
}

const filteredUsers=computed(()=>{
    let list=[...users.value];
    if(filterRole.value!=='all')list=list.filter(u=>u.role===filterRole.value);
    if(filterLocal.value==='registered')list=list.filter(u=>u.local_role);
    if(filterLocal.value==='unregistered')list=list.filter(u=>!u.local_role);
    if(filterStatus.value!=='all')list=list.filter(u=>u.status===filterStatus.value);
    if(search.value.trim()){
        const q=search.value.toLowerCase();
        list=list.filter(u=>
            (u.pegawai?.nama||u.name||'').toLowerCase().includes(q)||
            (u.email||u.user||'').toLowerCase().includes(q)||
            (u.pegawai?.nip||'').toLowerCase().includes(q)
        );
    }
    list.sort((a,b)=>{
        const va=getSortVal(a,sortKey.value),vb=getSortVal(b,sortKey.value);
        if(va<vb)return sortDir.value==='asc'?-1:1;
        if(va>vb)return sortDir.value==='asc'?1:-1;
        return 0;
    });
    return list;
});

const totalPages=computed(()=>Math.max(1,Math.ceil(filteredUsers.value.length/pageSize.value)));
const pagedUsers=computed(()=>{
    const start=(currentPage.value-1)*pageSize.value;
    return filteredUsers.value.slice(start,start+pageSize.value);
});
const visiblePages=computed(()=>{
    const p=currentPage.value,tp=totalPages.value,pages=[];
    let s=Math.max(1,p-2),e=Math.min(tp,p+2);
    if(e-s<4){if(s===1)e=Math.min(tp,5);else s=Math.max(1,e-4);}
    for(let i=s;i<=e;i++)pages.push(i);
    return pages;
});

async function load(){
    loading.value=true;
    try{const{data}=await api.get('/siamin/users');users.value=data;}catch(e){swalError('Gagal memuat data user SIAMIN');}
    loading.value=false;
}

function openSetRole(u){
    selectedUser.value=u;
    roleForm.role=u.local_role||'user';
    showModal.value=true;
}

async function saveRole(){
    try{
        await api.post('/siamin/set-role',{
            id_user:selectedUser.value.id_user,
            role:roleForm.role,
            name:selectedUser.value.pegawai?.nama||selectedUser.value.name||null,
            email:selectedUser.value.email||selectedUser.value.user||null,
        });
        swalSuccess('Role berhasil disimpan!');
        showModal.value=false;
        load();
    }catch(e){swalError(e.response?.data?.message||'Gagal menyimpan role');}
}

async function revokeAccess(u){
    if(!await swalConfirm(`Cabut akses user "${u.pegawai?.nama||u.email}"?`))return;
    try{await api.delete(`/siamin/revoke-role/${u.id_user}`);swalSuccess('Akses user berhasil dicabut!');load();}catch(e){swalError('Gagal mencabut akses');}
}

onMounted(load);
</script>

<style scoped>
.um-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;box-shadow:0 4px 12px rgba(15,23,42,.06)}
.um-title{font-size:14px;font-weight:700;color:#0f172a;margin-bottom:16px}
.um-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:6px}
.um-input{width:100%;padding:9px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:13px;outline:none;font-family:'Quicksand',sans-serif;transition:all .2s}
.um-input:focus{border-color:#60a5fa;box-shadow:0 0 0 3px rgba(59,130,246,.1)}
.um-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:10px;font-size:13px;font-weight:600;border:none;cursor:pointer;transition:all .15s;font-family:'Quicksand',sans-serif}
.um-btn:hover{transform:translateY(-1px)}
.um-btn-blue{background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff}
.um-btn-gray{background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0}
.um-table{width:100%;border-collapse:collapse;font-size:13px}
.um-table th{font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;border-bottom:2px solid #e2e8f0;padding:10px 14px;text-align:left;white-space:nowrap}
.um-table td{padding:10px 14px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.um-table tr:hover td{background:#f8fafc}
.um-th-sort{cursor:pointer;user-select:none;transition:color .15s}
.um-th-sort:hover{color:#2563eb}
.um-sort-icon{font-size:10px;margin-left:2px}
.fw-semibold{font-weight:600}
.um-badge{display:inline-block;font-size:11px;font-weight:600;padding:3px 10px;border-radius:999px}
.um-badge-blue{background:#dbeafe;color:#2563eb}
.um-badge-gray{background:#f1f5f9;color:#64748b}
.um-badge-green{background:#dcfce7;color:#16a34a}
.um-badge-red{background:#fee2e2;color:#dc2626}
.um-badge-yellow{background:#fef9c3;color:#ca8a04}
.um-action{width:30px;height:30px;border-radius:8px;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;transition:all .15s}
.um-action:hover{transform:translateY(-1px)}
.um-action-edit{background:#dbeafe;color:#2563eb}
.um-action-delete{background:#fee2e2;color:#dc2626}
.um-page-btn{min-width:32px;height:32px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;color:#64748b;font-size:12px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:all .15s}
.um-page-btn:hover:not(:disabled){background:#f0f7ff;border-color:#93c5fd;color:#2563eb}
.um-page-btn:disabled{opacity:.4;cursor:not-allowed}
.um-page-active{background:#2563eb!important;border-color:#2563eb!important;color:#fff!important}
.um-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(4px);z-index:10000;display:flex;align-items:center;justify-content:center;padding:1rem}
.um-modal{background:#fff;border-radius:16px;box-shadow:0 24px 48px rgba(2,6,23,.25);width:100%;max-width:440px;max-height:90vh;display:flex;flex-direction:column;overflow:hidden}
.um-modal-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e2e8f0}
.um-modal-header h5{font-size:15px;font-weight:700;color:#0f172a;margin:0;display:flex;align-items:center}
.um-modal-close{background:none;border:none;font-size:22px;color:#94a3b8;cursor:pointer;padding:4px 8px;border-radius:6px}
.um-modal-close:hover{background:#f1f5f9}
.um-modal-body{padding:20px;overflow-y:auto;flex:1}
.um-modal-footer{display:flex;justify-content:flex-end;gap:8px;padding:12px 20px;border-top:1px solid #e2e8f0}
.um-readonly{padding:9px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;font-size:13px;color:#334155}
</style>
