import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { loadTheme, applyTheme, DEFAULTS } from './theme.js';

const cachedTheme = (() => {
    try { return JSON.parse(localStorage.getItem('bpmp_theme')); } catch { return null; }
})();

if (cachedTheme) applyTheme(cachedTheme);

const app = createApp(App);
app.use(router);
app.mount('#app');

const loader = document.getElementById('app-loader');
if (loader) {
    loader.classList.add('fade-out');
    setTimeout(() => loader.remove(), 400);
}

loadTheme().then(themeData => {
    if (themeData) {
        try { localStorage.setItem('bpmp_theme', JSON.stringify(themeData)); } catch {}
    }
});
