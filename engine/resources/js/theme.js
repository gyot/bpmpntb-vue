import { reactive } from 'vue';
import api from '@/bootstrap.js';

const DEFAULTS = {
    primary_color: '#2563eb',
    secondary_color: '#1e40af',
    accent_color: '#f59e0b',
    background_color: '#ffffff',
    surface_color: '#ffffff',
    text_primary_color: '#37474F',
    text_secondary_color: '#78909C',
    sidebar_bg_color: '#1e40af',
    sidebar_text_color: '#e3f2fd',
    navbar_bg_color: '#2563eb',
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
        if (data) { applyTheme(data); return data; }
    } catch (e) {
        applyTheme(DEFAULTS);
    }
    return null;
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

function rgbToHsl(r, g, b) {
    r /= 255; g /= 255; b /= 255;
    const max = Math.max(r, g, b), min = Math.min(r, g, b);
    let h, s, l = (max + min) / 2;
    if (max === min) { h = s = 0; }
    else {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        switch (max) {
            case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
            case g: h = ((b - r) / d + 2) / 6; break;
            case b: h = ((r - g) / d + 4) / 6; break;
        }
    }
    return { h: Math.round(h * 360), s: Math.round(s * 100), l: Math.round(l * 100) };
}

function hslToHex(h, s, l) {
    s /= 100; l /= 100;
    const a = s * Math.min(l, 1 - l);
    const f = n => {
        const k = (n + h / 30) % 12;
        const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
        return Math.round(255 * color).toString(16).padStart(2, '0');
    };
    return `#${f(0)}${f(8)}${f(4)}`;
}

function extractColorsFromLogo(imageUrl) {
    return new Promise((resolve) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            try {
                const canvas = document.createElement('canvas');
                const size = 64;
                canvas.width = size;
                canvas.height = size;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, size, size);
                const data = ctx.getImageData(0, 0, size, size).data;

                const colorCounts = {};
                for (let i = 0; i < data.length; i += 4) {
                    const r = data[i], g = data[i + 1], b = data[i + 2], a = data[i + 3];
                    if (a < 128) continue;
                    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
                    if (brightness > 240 || brightness < 15) continue;
                    const qr = Math.round(r / 32) * 32;
                    const qg = Math.round(g / 32) * 32;
                    const qb = Math.round(b / 32) * 32;
                    const key = `${qr},${qg},${qb}`;
                    colorCounts[key] = (colorCounts[key] || 0) + 1;
                }

                const sorted = Object.entries(colorCounts).sort((a, b) => b[1] - a[1]);
                if (sorted.length === 0) { resolve(null); return; }

                const [mainR, mainG, mainB] = sorted[0][0].split(',').map(Number);
                const hsl = rgbToHsl(mainR, mainG, mainB);

                const primary = hslToHex(hsl.h, Math.min(hsl.s + 10, 100), Math.max(hsl.l - 10, 25));
                const accent = hslToHex((hsl.h + 30) % 360, Math.min(hsl.s + 20, 100), 55);
                const secondary = hslToHex(hsl.h, Math.max(hsl.s - 40, 10), 15);

                resolve({
                    primary_color: primary,
                    accent_color: accent,
                    secondary_color: secondary,
                });
            } catch { resolve(null); }
        };
        img.onerror = () => resolve(null);
        img.src = imageUrl;
    });
}

export { theme, applyTheme, loadTheme, resetTheme, DEFAULTS, hexToRgb, getContrastColor, extractColorsFromLogo };
export default theme;
