import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    { path: '/', name: 'home', component: () => import('@/pages/HomePage.vue') },
    { path: '/post/:jenis', name: 'post-list', component: () => import('@/pages/PostList.vue') },
    { path: '/post/:jenis/:id/:slug?', name: 'post-detail', component: () => import('@/pages/PostDetail.vue') },
    { path: '/layanan/:id/:slug?', name: 'layanan-detail', component: () => import('@/pages/LayananDetail.vue') },
    { path: '/ppid', name: 'ppid', component: () => import('@/pages/PpidPage.vue') },
    { path: '/login', name: 'login', component: () => import('@/pages/Login.vue') },
    {
        path: '/admin',
        component: () => import('@/layouts/AdminLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            { path: '', name: 'admin.dashboard', component: () => import('@/pages/admin/Dashboard.vue') },
            { path: 'settings', name: 'admin.settings', component: () => import('@/pages/admin/WebsiteSettings.vue') },
            { path: 'sliders', name: 'admin.sliders', component: () => import('@/pages/admin/SliderManager.vue') },
            { path: 'users', name: 'admin.users', component: () => import('@/pages/admin/UserManager.vue') },
            { path: 'external-links', name: 'admin.external-links', component: () => import('@/pages/admin/ExternalLinkManager.vue') },
            { path: 'layanans', name: 'admin.layanans', component: () => import('@/pages/admin/LayananManager.vue') },
            { path: 'konten/:jenis', name: 'admin.konten', component: () => import('@/pages/admin/KontenManager.vue') },
            { path: 'konten/:jenis/create', redirect: to => ({ path: `/admin/konten/${to.params.jenis}` }) },
            { path: 'konten/:jenis/:id/edit', name: 'admin.konten.edit', component: () => import('@/pages/admin/KontenManager.vue') },
            { path: 'theme', name: 'admin.theme', component: () => import('@/pages/admin/ThemeSettings.vue') },
            { path: 'chatbot', name: 'admin.chatbot', component: () => import('@/pages/admin/ChatbotManager.vue') },
            { path: 'chatbot/intent', name: 'admin.intent', component: () => import('@/pages/admin/IntentManager.vue') },
            { path: 'chatbot/livechat', name: 'admin.livechat', component: () => import('@/pages/admin/LiveChatAdmin.vue') },
            { path: 'chatbot/analytics', name: 'admin.chatbot-analytics', component: () => import('@/pages/admin/ChatbotAnalytics.vue') },
            { path: 'chatbot/knowledge-base', name: 'admin.knowledge-base', component: () => import('@/pages/admin/KnowledgeBase.vue') },
            { path: 'ai-config', name: 'admin.ai-config', component: () => import('@/pages/admin/AiConfigManager.vue') },
            { path: 'chatbot/whatsapp', name: 'admin.whatsapp', component: () => import('@/pages/admin/WhatsAppSettings.vue') },
            { path: 'wa-broadcast', name: 'admin.wa-broadcast', component: () => import('@/pages/admin/WaBroadcast.vue') },
            { path: 'ppid', name: 'admin.ppid', component: () => import('@/pages/admin/PpidManager.vue') },
            { path: 'kategori/:jenis', name: 'admin.kategori', component: () => import('@/pages/admin/KategoriManager.vue') },
            { path: 'export-import/:group', name: 'admin.export-import', component: () => import('@/pages/admin/ExportImport.vue') },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() { return { top: 0 }; },
});

router.beforeEach(async (to, from, next) => {
    if (to.meta.requiresAuth) {
        const token = localStorage.getItem('token');
        if (!token) {
            return next({ name: 'login' });
        }
    }
    next();
});

export default router;
