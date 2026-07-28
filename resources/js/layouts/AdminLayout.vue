<template>
    <div class="admin-layout">
        <button class="sidebar-toggle" @click="sidebarOpen=!sidebarOpen">
            <i class="fas fa-bars"></i>
        </button>

        <aside class="sidebar" :class="{'show': sidebarOpen}">
            <div class="sidebar-logo">
                <h3><i class="fas fa-cog"></i> Admin Panel</h3>
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

        <div class="main-content" :class="{'sidebar-open': sidebarOpen}">
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

const capitalize = s => s.charAt(0).toUpperCase() + s.slice(1);
const contentTypes = ['berita', 'artikel', 'buletin', 'jurnal', 'kliping', 'pengumuman', 'galeri', 'unduhan', 'profil'];

const allMenuGroups = [
    {
        key: 'konten', label: 'Konten', icon: 'fas fa-newspaper', open: false,
        items: [
            ...contentTypes.map(j => ({ label: capitalize(j), to: `/admin/konten/${j}` })),
            { label: 'Export/Import', to: '/admin/export-import/konten', icon: 'fas fa-exchange-alt' }
        ]
    },
    {
        key: 'kategori', label: 'Kategori', icon: 'fas fa-tags', open: false,
        items: [
            ...contentTypes.map(j => ({ label: capitalize(j), to: `/admin/kategori/${j}` })),
            { label: 'Export/Import', to: '/admin/export-import/kategori', icon: 'fas fa-exchange-alt' }
        ]
    },
    {
        key: 'media', label: 'Media', icon: 'fas fa-photo-video', open: false,
        items: [
            { label: 'Sliders', to: '/admin/sliders' },
            { label: 'Layanan', to: '/admin/layanans' },
            { label: 'Link Eksternal', to: '/admin/external-links' },
            { label: 'Export/Import', to: '/admin/export-import/media', icon: 'fas fa-exchange-alt' }
        ]
    },
    {
        key: 'chatbot', label: 'Si Intan', icon: 'fas fa-robot', open: false,
        items: [
            { label: 'Dashboard', to: '/admin/chatbot' },
            { label: 'Intent', to: '/admin/chatbot/intent' },
            { label: 'Live Chat', to: '/admin/chatbot/livechat' },
            { label: 'Analytics', to: '/admin/chatbot/analytics' },
            { label: 'Knowledge Base', to: '/admin/chatbot/knowledge-base' },
            { label: 'Konfigurasi AI', to: '/admin/ai-config' },
            { label: 'WhatsApp Gateway', to: '/admin/chatbot/whatsapp' },
            { label: 'Export/Import', to: '/admin/export-import/chatbot', icon: 'fas fa-exchange-alt' }
        ]
    },
    {
        key: 'broadcast', label: 'Broadcast', icon: 'fas fa-bullhorn', open: false,
        items: [
            { label: 'WhatsApp Broadcast', to: '/admin/wa-broadcast' },
            { label: 'Export/Import', to: '/admin/export-import/broadcast', icon: 'fas fa-exchange-alt' }
        ]
    },
    {
        key: 'ppid', label: 'PPID', icon: 'fas fa-university', open: false,
        items: [
            { label: 'Kelola PPID', to: '/admin/ppid' },
            { label: 'Export/Import', to: '/admin/export-import/ppid', icon: 'fas fa-exchange-alt' }
        ]
    },
    {
        key: 'pengaturan', label: 'Pengaturan', icon: 'fas fa-cog', open: false,
        items: [
            { label: 'Website', to: '/admin/settings' },
            { label: 'Tema Website', to: '/admin/theme' },
            { label: 'Users', to: '/admin/users' },
            { label: 'Export/Import', to: '/admin/export-import/pengaturan', icon: 'fas fa-exchange-alt' }
        ]
    }
];

const menuGroups = computed(() => {
    if (!allowedMenus.value.length) return [];
    return allMenuGroups
        .filter(g => allowedMenus.value.includes(g.key))
        .map(g => reactive({ ...g }));
});

function closeMobile() {
    if (window.innerWidth <= 991) sidebarOpen.value = false;
}

onMounted(async () => {
    try { user.value = JSON.parse(localStorage.getItem('user')); } catch (e) {}
    try { const { data } = await api.get('/settings'); setting.value = data; } catch (e) {}
    try {
        const { data } = await api.get('/my-menu-access');
        allowedMenus.value = data || [];
    } catch (e) {
        allowedMenus.value = ['dashboard','konten','kategori','media','chatbot','broadcast','ppid','pengaturan'];
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
    z-index: 1000;
    transition: left 0.3s;
    display: flex;
    flex-direction: column;
}
@media (max-width: 991.98px) {
    .sidebar { left: -250px; }
    .sidebar.show { left: 0; }
    .main-content { margin-left: 0; padding-top: 60px; }
    .sidebar-toggle { display: block; }
}

.sidebar-logo {
    text-align: center;
    padding: 20px 0;
    color: #fff;
    flex-shrink: 0;
}
.sidebar-logo h3 {
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0;
}
.sidebar-logo i {
    margin-right: 6px;
}

.sidebar-scroll {
    flex: 1;
    max-height: calc(100vh - 70px);
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

/* ── TOGGLE ── */
.sidebar-toggle {
    display: none;
    position: fixed;
    top: 16px;
    left: 16px;
    z-index: 1100;
    background: #1c1c1c;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 1.1rem;
    cursor: pointer;
    transition: left 0.3s;
}
.sidebar.show ~ .sidebar-toggle,
.sidebar.show ~ .main-content .sidebar-toggle {
    left: 266px;
}

/* ── MAIN ── */
.main-content {
    margin-left: 250px;
    padding: 20px;
    transition: margin-left 0.3s;
    min-height: 100vh;
}

.content-body {
    max-width: 1400px;
    margin: 0 auto;
}

@media (max-width: 575.98px) {
    .main-content h2 {
        margin-left: 45px;
    }
}
</style>
