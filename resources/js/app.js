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
