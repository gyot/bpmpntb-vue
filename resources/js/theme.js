import { reactive } from 'vue';
import api from '@/bootstrap.js';

const DEFAULTS = {
    primary_color: '#2563eb',
    secondary_color: '#1f2937',
    accent_color: '#f59e0b',
    background_color: '#f9fafb',
    surface_color: '#ffffff',
    text_primary_color: '#1f293b',
    text_secondary_color: '#6b7280',
    sidebar_bg_color: '#1f2937',
    sidebar_text_color: '#e5e7eb',
    navbar_bg_color: '#1e40af',
    navbar_text_color: '#ffffff',
};

const theme = reactive({ ...DEFAULTS });

function applyTheme(colors) {
    const root = document.documentElement;
    const cssVarMap = {
        primary_color: '--color-primary',
        secondary_color: '--color-secondary',
        accent_color: '--color-accent',
        background_color: '--color-background',
        surface_color: '--color-surface',
        text_primary_color: '--color-text-primary',
        text_secondary_color: '--color-text-secondary',
        sidebar_bg_color: '--sidebar-bg',
        sidebar_text_color: '--sidebar-text',
        navbar_bg_color: '--navbar-bg',
        navbar_text_color: '--navbar-text',
    };

    Object.entries(cssVarMap).forEach(([key, cssVar]) => {
        const value = colors[key] || DEFAULTS[key];
        if (value) root.style.setProperty(cssVar, value);
    });

    Object.assign(theme, { ...DEFAULTS, ...colors });
}

async function loadTheme() {
    try {
        const { data } = await api.get('/theme');
        if (data) applyTheme(data);
    } catch (e) {
        applyTheme(DEFAULTS);
    }
}

function resetTheme() {
    applyTheme(DEFAULTS);
}

function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? { r: parseInt(result[1], 16), g: parseInt(result[2], 16), b: parseInt(result[3], 16) } : null;
}

function getContrastColor(hex) {
    const rgb = hexToRgb(hex);
    if (!rgb) return '#000000';
    const luminance = (0.299 * rgb.r + 0.587 * rgb.g + 0.114 * rgb.b) / 255;
    return luminance > 0.5 ? '#000000' : '#ffffff';
}

export { theme, applyTheme, loadTheme, resetTheme, DEFAULTS, hexToRgb, getContrastColor };
export default theme;
