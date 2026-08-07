<template>
    <div class="admin-layout">
        <aside class="sidebar" :class="{'show': sidebarOpen}">
            <div class="brand-panel">
                <router-link to="/admin" class="brand-mark">
                    <img v-if="setting?.logo" :src="'/upload/settings/' + setting.logo" :alt="setting?.title || 'BPMP NTB'">
                    <span v-else><i class="fas fa-graduation-cap"></i></span>
                </router-link>
                <div class="brand-copy">
                    <strong>{{ setting?.title || 'BPMP NTB' }}</strong>
                    <small>Control Center</small>
                </div>
            </div>
            <div class="sidebar-search">
                <i class="fas fa-magnifying-glass"></i>
                <input v-model="menuQuery" type="search" placeholder="Cari menu..." aria-label="Cari menu admin">
                <kbd>⌘K</kbd>
            </div>
            <div class="sidebar-scroll">
                <ul class="sidebar-menu">
                    <li class="menu-section-label">Utama</li>
                    <li v-if="allowedMenus.includes('dashboard')">
                        <router-link to="/admin" class="menu-link" active-class="active" @click="closeMobile">
                            <span class="menu-icon"><i class="fas fa-grid-2"></i></span><span>Dashboard</span>
                        </router-link>
                    </li>

                    <li class="menu-section-label">Manajemen</li>
                    <li v-for="group in filteredMenuGroups" :key="group.label">
                        <button @click="group.open=!group.open" class="menu-link menu-parent">
                            <span><span class="menu-icon"><i :class="group.icon"></i></span>{{ group.label }}</span>
                            <i class="fas fa-chevron-down menu-arrow" :class="{'open':group.open}"></i>
                        </button>
                        <ul v-show="group.open" class="submenu">
                            <li v-for="item in group.items" :key="item.label">
                                <router-link :to="item.to" class="submenu-link" active-class="active" @click="closeMobile">
                                    <span></span>{{ item.label }}
                                </router-link>
                            </li>
                        </ul>
                    </li>

                    <li v-if="filteredMenuGroups.length===0" class="empty-menu"><i class="fas fa-search"></i>Menu tidak ditemukan</li>
                </ul>
            </div>
            <div class="sidebar-user">
                <div class="user-avatar">{{ userInitial }}</div>
                <div class="user-details"><strong>{{ userName }}</strong><span>{{ nipLabel }}</span><small>{{ roleLabel }}</small></div>
                <button @click="logout" title="Logout" aria-label="Logout"><i class="fas fa-arrow-right-from-bracket"></i></button>
            </div>
        </aside>

        <div v-if="sidebarOpen" class="sidebar-overlay" @click="sidebarOpen=false"></div>

        <button class="sidebar-toggle" @click="sidebarOpen=!sidebarOpen">
            <i class="fas fa-bars"></i>
        </button>

        <div class="main-content">
            <header class="admin-topbar">
                <div class="page-context">
                    <small>Admin Workspace</small>
                    <h1>{{ currentPageTitle }}</h1>
                </div>
                <div class="topbar-actions">
                    <span class="system-status"><i></i>Sistem aktif</span>
                    <a href="/" target="_blank" rel="noopener noreferrer" class="view-site"><i class="fas fa-arrow-up-right-from-square"></i><span>Lihat Website</span></a>
                    <div class="topbar-user-copy"><strong>{{ userName }}</strong><span>{{ nipLabel }}</span></div>
                    <div class="topbar-avatar">{{ userInitial }}</div>
                </div>
            </header>
            <main class="content-body">
                <router-view />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from '@/bootstrap.js';

const router = useRouter();
const route = useRoute();
const sidebarOpen = ref(false);
const menuQuery = ref('');
const user = ref(null);
const setting = ref(null);
const allowedMenus = ref([]);
const allowedSubMenus = ref({});

const capitalize = s => s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
const contentTypes = ['berita', 'artikel', 'buletin', 'jurnal', 'kliping', 'pengumuman', 'galeri', 'unduhan', 'profil', 'renstra', 'lakin', 'perjanjian_kinerja'];

const allMenuGroups = [
    {
        key: 'konten', label: 'Konten', icon: 'fas fa-newspaper', open: false,
        items: [
            ...contentTypes.map(j => ({ label: capitalize(j), to: `/admin/konten/${j}`, subKey: j })),
            { label: 'Export/Import', to: '/admin/export-import/konten', icon: 'fas fa-exchange-alt' }
        ]
    },
    {
        key: 'kategori', label: 'Kategori', icon: 'fas fa-tags', open: false,
        items: [
            ...contentTypes.map(j => ({ label: capitalize(j), to: `/admin/kategori/${j}`, subKey: j })),
            { label: 'Export/Import', to: '/admin/export-import/kategori', icon: 'fas fa-exchange-alt' }
        ]
    },
    {
        key: 'media', label: 'Media', icon: 'fas fa-photo-video', open: false,
        items: [
            { label: 'Sliders', to: '/admin/sliders', subKey: 'sliders' },
            { label: 'Layanan', to: '/admin/layanans', subKey: 'layanan' },
            { label: 'Link Eksternal', to: '/admin/external-links', subKey: 'link_eksternal' },
            { label: 'Export/Import', to: '/admin/export-import/media', icon: 'fas fa-exchange-alt' }
        ]
    },
    {
        key: 'chatbot', label: 'Si Intan', icon: 'fas fa-robot', open: false,
        items: [
            { label: 'Dashboard', to: '/admin/chatbot', subKey: 'chatbot_dashboard' },
            { label: 'Intent', to: '/admin/chatbot/intent', subKey: 'intent' },
            { label: 'Live Chat', to: '/admin/chatbot/livechat', subKey: 'livechat' },
            { label: 'Analytics', to: '/admin/chatbot/analytics', subKey: 'analytics' },
            { label: 'Knowledge Base', to: '/admin/chatbot/knowledge-base', subKey: 'knowledge_base' },
            { label: 'Konfigurasi AI', to: '/admin/ai-config', subKey: 'konfigurasi_ai' },
            { label: 'WhatsApp Gateway', to: '/admin/chatbot/whatsapp', subKey: 'whatsapp' },
            { label: 'Export/Import', to: '/admin/export-import/chatbot', icon: 'fas fa-exchange-alt' }
        ]
    },
    {
        key: 'broadcast', label: 'Broadcast', icon: 'fas fa-bullhorn', open: false,
        items: [
            { label: 'WhatsApp Broadcast', to: '/admin/wa-broadcast', subKey: 'wa_broadcast' },
            { label: 'Export/Import', to: '/admin/export-import/broadcast', icon: 'fas fa-exchange-alt' }
        ]
    },
    {
        key: 'ppid', label: 'PPID', icon: 'fas fa-university', open: false,
        items: [
            { label: 'Kelola PPID', to: '/admin/ppid', subKey: 'kelola_ppid' },
            { label: 'Export/Import', to: '/admin/export-import/ppid', icon: 'fas fa-exchange-alt' }
        ]
    },
    {
        key: 'pengaturan', label: 'Pengaturan', icon: 'fas fa-cog', open: false,
        items: [
            { label: 'Website', to: '/admin/settings', subKey: 'website' },
            { label: 'Tema Website', to: '/admin/theme', subKey: 'tema_website' },
            { label: 'Users', to: '/admin/users', subKey: 'users' },
            { label: 'Export/Import', to: '/admin/export-import/pengaturan', icon: 'fas fa-exchange-alt' }
        ]
    }
];

const menuGroups = computed(() => {
    if (!allowedMenus.value.length) return [];
    return allMenuGroups
        .filter(g => allowedMenus.value.includes(g.key))
        .map(g => {
            const group = reactive({ ...g });
            const subs = allowedSubMenus.value[g.key];
            if (subs && subs.length) {
                group.items = g.items.filter(item => {
                    if (!item.subKey) return true;
                    return subs.includes(item.subKey);
                });
            }
            return group;
        });
});

const filteredMenuGroups = computed(() => {
    const query = menuQuery.value.trim().toLowerCase();
    if (!query) return menuGroups.value;
    return menuGroups.value.map(group => {
        const groupMatches = group.label.toLowerCase().includes(query);
        return {
            ...group,
            open: true,
            items: groupMatches ? group.items : group.items.filter(item => item.label.toLowerCase().includes(query)),
        };
    }).filter(group => group.items.length);
});
const userName = computed(() => user.name || user.value?.nama || user.value?.name || user.value?.pegawai?.nama || user.value?.email || user.name || 'Admin');
const userNip = computed(() => user.value?.nip || user.value?.pegawai?.nip || user.user ||'');
const nipLabel = computed(() => userNip.value ? `NIP ${userNip.value}` : 'NIP belum tersedia');
const userInitial = computed(() => userName.value.trim().charAt(0).toUpperCase());
const roleLabel = computed(() => capitalize(user.value?.role || 'user'));
const currentPageTitle = computed(() => {
    if (route.path === '/admin') return 'Dashboard';
    const matched = allMenuGroups.flatMap(group => group.items).find(item => item.to === route.path);
    if (matched) return matched.label;
    const last = route.path.split('/').filter(Boolean).pop() || 'Dashboard';
    return capitalize(last);
});

function closeMobile() {
    if (window.innerWidth <= 991) sidebarOpen.value = false;
}

onMounted(async () => {
    try {
        const { data } = await api.get('/user');
        user.value = data;
        localStorage.setItem('user', JSON.stringify(data));
    } catch (e) {
        try { user.value = JSON.parse(localStorage.getItem('user')); } catch (e2) {}
    }
    try { const { data } = await api.get('/settings'); setting.value = data; } catch (e) {}
    try {
        const { data } = await api.get('/my-menu-access');
        allowedMenus.value = data.menus || data || [];
        allowedSubMenus.value = data.subMenus || {};
    } catch (e) {
        allowedMenus.value = [];
        allowedSubMenus.value = {};
    }
});

watch(() => route.path, (path) => {
    allMenuGroups.forEach(group => {
        if (group.items.some(item => item.to === path || path.startsWith(item.to + '/'))) group.open = true;
    });
}, { immediate: true });

async function logout() {
    try { await api.post('/logout'); } catch (e) {}
    localStorage.removeItem('user');
    router.push('/login');
}
</script>

<style scoped>
.admin-layout{min-height:100vh;background:#f3f6fb;font-family:'Inter',system-ui,sans-serif}.sidebar{position:fixed;top:0;left:0;z-index:1050;display:flex;width:276px;height:100vh;flex-direction:column;background:#12265f;color:#fff;box-shadow:18px 0 55px rgba(15,35,85,.12);transition:transform .3s cubic-bezier(.4,0,.2,1);isolation:isolate}.sidebar::before{content:'';position:absolute;inset:0;z-index:-1;opacity:.075;background-image:linear-gradient(rgba(255,255,255,.25) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.25) 1px,transparent 1px);background-size:42px 42px;mask-image:linear-gradient(180deg,#000,transparent 78%)}
.brand-panel{display:flex;align-items:center;gap:13px;min-height:82px;padding:16px 18px;border-bottom:1px solid rgba(255,255,255,.09)}.brand-mark{display:grid;place-items:center;width:48px;height:48px;flex:0 0 48px;overflow:hidden;border:1px solid rgba(255,255,255,.15);border-radius:15px;background:rgba(255,255,255,.1)}.brand-mark img{width:38px;height:38px;object-fit:contain}.brand-mark span{color:#fff;font-size:19px}.brand-copy{min-width:0}.brand-copy strong,.brand-copy small{display:block}.brand-copy strong{overflow:hidden;color:#fff;font-size:13px;text-overflow:ellipsis;white-space:nowrap}.brand-copy small{margin-top:3px;color:rgba(255,255,255,.42);font-size:9px;font-weight:700;letter-spacing:.13em;text-transform:uppercase}
.sidebar-search{position:relative;margin:16px 16px 10px}.sidebar-search>i{position:absolute;top:50%;left:13px;color:rgba(255,255,255,.35);font-size:11px;transform:translateY(-50%)}.sidebar-search input{width:100%;height:42px;padding:0 45px 0 35px;border:1px solid rgba(255,255,255,.1);border-radius:13px;outline:0;background:rgba(255,255,255,.07);color:#fff;font-family:inherit;font-size:11px;transition:.2s}.sidebar-search input::placeholder{color:rgba(255,255,255,.33)}.sidebar-search input:focus{border-color:rgba(147,197,253,.45);background:rgba(255,255,255,.1);box-shadow:0 0 0 3px rgba(96,165,250,.1)}.sidebar-search kbd{position:absolute;top:50%;right:11px;padding:3px 5px;border:1px solid rgba(255,255,255,.1);border-radius:5px;color:rgba(255,255,255,.28);font-size:8px;transform:translateY(-50%)}
.sidebar-scroll{flex:1;overflow-x:hidden;overflow-y:auto;padding:4px 10px 14px}.sidebar-scroll::-webkit-scrollbar{width:4px}.sidebar-scroll::-webkit-scrollbar-thumb{border-radius:99px;background:rgba(255,255,255,.16)}.sidebar-menu{margin:0;padding:0;list-style:none}.menu-section-label{padding:13px 11px 7px;color:rgba(255,255,255,.28);font-size:8px;font-weight:800;letter-spacing:.17em;text-transform:uppercase}.menu-link{display:flex;align-items:center;gap:11px;width:100%;min-height:45px;margin:2px 0;padding:7px 10px;border:0;border-radius:13px;background:transparent;color:rgba(255,255,255,.62);font-family:inherit;font-size:12px;font-weight:600;text-align:left;text-decoration:none;cursor:pointer;transition:.2s}.menu-link:hover{background:rgba(255,255,255,.075);color:#fff}.menu-link.active{background:#2563eb;color:#fff;box-shadow:0 10px 24px rgba(4,18,62,.25)}.menu-icon{display:grid;place-items:center;width:31px;height:31px;flex:0 0 31px;border-radius:10px;background:rgba(255,255,255,.07);color:rgba(255,255,255,.68)}.menu-link.active .menu-icon{background:rgba(255,255,255,.16);color:#fff}.menu-parent{justify-content:space-between}.menu-parent>span{display:flex;align-items:center;gap:11px}.menu-arrow{margin-left:auto;color:rgba(255,255,255,.25);font-size:8px;transition:transform .25s ease}.menu-arrow.open{transform:rotate(180deg)}
.submenu{margin:3px 0 8px 25px;padding:2px 0 2px 19px;border-left:1px solid rgba(255,255,255,.09);list-style:none}.submenu-link{display:flex;align-items:center;gap:10px;min-height:35px;padding:7px 10px;border-radius:9px;color:rgba(255,255,255,.43);font-size:11px;font-weight:500;text-decoration:none;transition:.2s}.submenu-link>span{width:4px;height:4px;border-radius:50%;background:rgba(255,255,255,.2)}.submenu-link:hover,.submenu-link.active{background:rgba(255,255,255,.07);color:#fff}.submenu-link.active>span{background:#93c5fd;box-shadow:0 0 0 4px rgba(147,197,253,.1)}.empty-menu{display:flex;align-items:center;justify-content:center;flex-direction:column;gap:9px;padding:36px 16px;color:rgba(255,255,255,.3);font-size:11px}
.sidebar-user{display:flex;align-items:center;gap:11px;margin:10px;padding:12px;border:1px solid rgba(255,255,255,.09);border-radius:16px;background:rgba(255,255,255,.06)}.user-avatar,.topbar-avatar{display:grid;place-items:center;border-radius:13px;background:#fff;color:#1e40af;font-weight:800}.user-avatar{width:38px;height:38px;flex:0 0 38px;font-size:13px}.user-details{min-width:0;flex:1}.user-details strong,.user-details span,.user-details small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.user-details strong{color:#fff;font-size:11px}.user-details span{margin-top:3px;color:rgba(255,255,255,.54);font-size:8px;font-weight:600}.user-details small{margin-top:3px;color:rgba(255,255,255,.3);font-size:7px;font-weight:700;letter-spacing:.09em;text-transform:uppercase}.sidebar-user button{display:grid;place-items:center;width:31px;height:31px;border:0;border-radius:9px;background:rgba(255,255,255,.07);color:rgba(255,255,255,.45);cursor:pointer;transition:.2s}.sidebar-user button:hover{background:rgba(239,68,68,.18);color:#fca5a5}
.sidebar-overlay{display:none}.sidebar-toggle{display:none;position:fixed;top:14px;left:14px;z-index:1100;width:42px;height:42px;border:0;border-radius:13px;background:#1e40af;color:#fff;box-shadow:0 9px 25px rgba(30,64,175,.25);cursor:pointer}.main-content{min-height:100vh;margin-left:276px}.admin-topbar{position:sticky;top:0;z-index:40;display:flex;align-items:center;justify-content:space-between;min-height:78px;padding:13px 28px;border-bottom:1px solid rgba(148,163,184,.18);background:rgba(255,255,255,.86);box-shadow:0 5px 24px rgba(15,23,42,.035);backdrop-filter:blur(20px) saturate(160%)}.page-context small,.page-context h1{display:block}.page-context small{margin-bottom:3px;color:#94a3b8;font-size:8px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.page-context h1{color:#263238;font-size:19px;letter-spacing:-.02em}.topbar-actions{display:flex;align-items:center;gap:10px}.system-status{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid #dcfce7;border-radius:999px;background:#f0fdf4;color:#3f7354;font-size:9px;font-weight:700}.system-status i{width:7px;height:7px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 4px rgba(34,197,94,.1)}.view-site{display:inline-flex;align-items:center;gap:8px;padding:10px 13px;border:1px solid #e2e8f0;border-radius:11px;background:#fff;color:#64748b;font-size:10px;font-weight:700;text-decoration:none;transition:.2s}.view-site:hover{border-color:#bfdbfe;color:var(--color-primary);box-shadow:0 6px 16px rgba(37,99,235,.08)}.topbar-user-copy{text-align:right}.topbar-user-copy strong,.topbar-user-copy span{display:block;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.topbar-user-copy strong{color:#334155;font-size:10px}.topbar-user-copy span{margin-top:2px;color:#94a3b8;font-size:8px}.topbar-avatar{width:38px;height:38px;border:1px solid #dbeafe;background:#eff6ff;font-size:12px}.content-body{max-width:1440px;margin:0 auto;padding:26px 28px 42px}
:deep(.card-k),:deep(.card){border-color:#e4eaf1;box-shadow:0 10px 35px rgba(15,23,42,.045)}:deep(.card-k:hover),:deep(.card:hover){box-shadow:0 18px 48px rgba(15,23,42,.08)}:deep(.input-field),:deep(select),:deep(input[type="text"]),:deep(input[type="search"]),:deep(input[type="email"]),:deep(input[type="number"]),:deep(textarea){border-color:#dce4ec}:deep(.table-header),:deep(thead th){background:#f4f7fa;color:#64748b}
@media(max-width:991.98px){.sidebar{transform:translateX(-105%)}.sidebar.show{transform:translateX(0)}.sidebar-overlay{display:block;position:fixed;inset:0;z-index:1040;background:rgba(15,23,42,.56);backdrop-filter:blur(3px)}.sidebar-toggle{display:grid;place-items:center}.main-content{margin-left:0}.admin-topbar{padding-left:70px}.content-body{padding:22px 18px 36px}}
@media(max-width:760px){.topbar-user-copy{display:none}}
@media(max-width:575.98px){.admin-topbar{min-height:68px;padding:10px 12px 10px 66px}.page-context small{display:none}.page-context h1{max-width:145px;overflow:hidden;font-size:15px;text-overflow:ellipsis;white-space:nowrap}.system-status,.view-site span{display:none}.view-site{width:38px;height:38px;justify-content:center;padding:0}.content-body{padding:17px 12px 30px}}
</style>
