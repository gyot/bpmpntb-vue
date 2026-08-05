<template>
    <div class="admin-layout">
        <aside class="sidebar" :class="{'show': sidebarOpen}">
            <div class="sidebar-logo">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-name">{{ user?.name || 'Admin' }}</div>
                <div class="user-role" :class="'role-' + (user?.role || 'user')">{{ user?.role || 'user' }}</div>
            </div>
            <div class="sidebar-scroll">
                <ul class="sidebar-menu">
                    <li v-if="allowedMenus.includes('dashboard')">
                        <router-link to="/admin" class="menu-link" active-class="active" @click="closeMobile">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </router-link>
                    </li>

                    <li v-for="group in menuGroups" :key="group.label">
                        <button @click="group.open=!group.open" class="menu-link menu-parent">
                            <span><i :class="group.icon"></i> {{ group.label }}</span>
                            <i class="fas fa-chevron-down menu-arrow" :class="{'open':group.open}"></i>
                        </button>
                        <ul v-show="group.open" class="submenu">
                            <li v-for="item in group.items" :key="item.label">
                                <router-link :to="item.to" class="submenu-link" active-class="active" @click="closeMobile">
                                    {{ item.label }}
                                </router-link>
                            </li>
                        </ul>
                    </li>

                    <li class="menu-divider"></li>
                    <li>
                        <a href="#" class="menu-link" @click.prevent="logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <div v-if="sidebarOpen" class="sidebar-overlay" @click="sidebarOpen=false"></div>

        <button class="sidebar-toggle" @click="sidebarOpen=!sidebarOpen">
            <i class="fas fa-bars"></i>
        </button>

        <div class="main-content">
            <main class="content-body">
                <router-view />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/bootstrap.js';

const router = useRouter();
const sidebarOpen = ref(false);
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

function closeMobile() {
    if (window.innerWidth <= 991) sidebarOpen.value = false;
}

onMounted(async () => {
    try { user.value = JSON.parse(localStorage.getItem('user')); } catch (e) {}
    try { const { data } = await api.get('/settings'); setting.value = data; } catch (e) {}
    try {
        const { data } = await api.get('/my-menu-access');
        allowedMenus.value = data.menus || data || [];
        allowedSubMenus.value = data.subMenus || {};
    } catch (e) {
        allowedMenus.value = ['dashboard','konten','kategori','media','chatbot','broadcast','ppid','pengaturan'];
        allowedSubMenus.value = {};
    }
});

async function logout() {
    try { await api.post('/logout'); } catch (e) {}
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    router.push('/login');
}
</script>

<style scoped>
.admin-layout {
    min-height: 100vh;
    background: #f8f9fa;
    font-family: 'Segoe UI', sans-serif;
}

/* ── SIDEBAR ── */
.sidebar {
    width: 250px;
    height: 100vh;
    background-color: #1c1c1c;
    color: #fff;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1050;
    transition: left 0.3s;
    display: flex;
    flex-direction: column;
}

.sidebar-logo {
    text-align: center;
    padding: 24px 16px 20px;
    color: #fff;
    flex-shrink: 0;
}
.user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255,255,255,.12);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    font-size: 1.15rem;
    color: #fff;
}
.user-name {
    font-size: 0.9rem;
    font-weight: 700;
    line-height: 1.3;
    word-break: break-word;
    margin-bottom: 4px;
}
.user-role {
    display: inline-block;
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    padding: 2px 10px;
    border-radius: 999px;
}
.role-admin { background: rgba(59,130,246,.2); color: #93c5fd; }
.role-superadmin { background: rgba(168,85,247,.2); color: #d8b4fe; }
.role-user { background: rgba(255,255,255,.1); color: #9ca3af; }

.sidebar-scroll {
    flex: 1;
    max-height: calc(100vh - 120px);
    overflow-y: auto;
    overflow-x: hidden;
    padding: 0 0 16px;
}
.sidebar-scroll::-webkit-scrollbar { width: 6px; }
.sidebar-scroll::-webkit-scrollbar-thumb { background: #343a40; border-radius: 3px; }
.sidebar-scroll::-webkit-scrollbar-track { background: transparent; }

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.menu-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    color: #ccc;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
}
.menu-link:hover,
.menu-link.active {
    background-color: #343a40;
    color: #fff;
}
.menu-link i {
    width: 18px;
    text-align: center;
    font-size: 14px;
}

.menu-parent {
    justify-content: space-between;
}
.menu-arrow {
    font-size: 10px;
    transition: transform 0.2s;
}
.menu-arrow.open {
    transform: rotate(180deg);
}

.submenu {
    list-style: none;
    padding: 0;
    margin: 0;
}
.submenu-link {
    display: block;
    padding: 9px 20px 9px 48px;
    color: #aaa;
    text-decoration: none;
    font-size: 13px;
    transition: all 0.2s;
}
.submenu-link:hover,
.submenu-link.active {
    background-color: #343a40;
    color: #fff;
}

.menu-divider {
    border-top: 1px solid #343a40;
    margin: 10px 16px;
}

/* ── OVERLAY ── */
.sidebar-overlay {
    display: none;
}

/* ── TOGGLE ── */
.sidebar-toggle {
    display: none;
    position: fixed;
    top: 12px;
    left: 12px;
    z-index: 1100;
    background: #1c1c1c;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 1rem;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,.2);
}

/* ── MAIN ── */
.main-content {
    margin-left: 250px;
    padding: 24px;
    min-height: 100vh;
}

.content-body {
    max-width: 1400px;
    margin: 0 auto;
}

/* ── MOBILE ── */
@media (max-width: 991.98px) {
    .sidebar {
        left: -260px;
        transition: left 0.3s ease;
    }
    .sidebar.show {
        left: 0;
    }
    .sidebar-overlay {
        display: block;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.45);
        z-index: 1040;
        animation: fadeIn .2s ease;
    }
    .sidebar-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .main-content {
        margin-left: 0;
        padding: 16px;
        padding-top: 60px;
    }
}

@media (max-width: 575.98px) {
    .main-content {
        padding: 12px;
        padding-top: 56px;
    }
    .content-body {
        max-width: 100%;
    }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>
