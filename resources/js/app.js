import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { loadTheme } from './theme.js';

const app = createApp(App);
app.use(router);

Promise.race([loadTheme(), new Promise(r => setTimeout(r, 3000))]).finally(() => {
    app.mount('#app');
});

// Register Service Worker for PWA offline support
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then((registration) => {
                // Check for updates periodically
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'activated') {
                            // New content available, notify user
                            if (document.visibilityState === 'visible') {
                                showUpdateNotification();
                            }
                        }
                    });
                });

                // Check for updates every 60 minutes
                setInterval(() => registration.update(), 60 * 60 * 1000);
            })
            .catch((err) => console.log('SW registration failed:', err));
    });

    // Listen for controller change (new SW activated)
    let refreshing = false;
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (!refreshing) {
            refreshing = true;
            window.location.reload();
        }
    });
}

function showUpdateNotification() {
    const banner = document.createElement('div');
    banner.id = 'sw-update-banner';
    banner.innerHTML = `
        <div style="position:fixed;bottom:20px;left:50%;transform:translateX(-50%);z-index:99999;
            background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff;padding:14px 24px;
            border-radius:14px;box-shadow:0 8px 30px rgba(37,99,235,0.3);display:flex;align-items:center;
            gap:12px;font-family:'Quicksand',sans-serif;font-size:14px;font-weight:600;
            animation:slideUp .3s ease-out">
            <span>&#128260; Versi baru tersedia!</span>
            <button onclick="window.location.reload()" style="background:#fff;color:#2563eb;border:none;
                padding:8px 16px;border-radius:8px;font-weight:700;cursor:pointer;font-size:13px;
                font-family:'Quicksand',sans-serif">Perbarui</button>
            <button onclick="this.closest('#sw-update-banner').remove()" style="background:rgba(255,255,255,0.2);
                border:none;color:#fff;padding:8px 12px;border-radius:8px;cursor:pointer;font-size:16px">&times;</button>
        </div>
    `;
    document.body.appendChild(banner);
}
