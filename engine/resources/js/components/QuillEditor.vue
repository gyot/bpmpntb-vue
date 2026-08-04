<template>
    <div style="position:relative" class="quill-editor-sm">
        <div ref="editorEl"></div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import api from '@/bootstrap.js';

const props = defineProps({ modelValue: { type: String, default: '' }, placeholder: { type: String, default: 'Tulis konten di sini...' } });
const emit = defineEmits(['update:modelValue']);

const editorEl = ref(null);
let quill = null;
let activeImg = null;
let overlayEl = null;
let mousedownHandler = null;
let isInternalUpdate = false;

async function uploadQuillImage(file) {
    try {
        const base64 = await new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
        const { data } = await api.post('/quil-upload-image', { images: [base64] });
        if (data.success && data.urls?.length) return data.urls[0];
    } catch (e) { console.error('Upload failed', e); }
    return null;
}

function applyImgStyle(imgEl, overrides) {
    const base = { maxWidth:'100%', height:'auto', borderRadius:'4px', display:'block', margin:'8px auto', float:'none', clear:'both', width: imgEl.dataset.imgWidth || '100%' };
    const cur = { ...base, ...JSON.parse(imgEl.dataset.imgStyle || '{}'), ...overrides };
    imgEl.dataset.imgStyle = JSON.stringify(cur);
    if (cur.width) imgEl.dataset.imgWidth = cur.width;
    imgEl.style.cssText = `max-width:${cur.maxWidth};height:${cur.height};border-radius:${cur.borderRadius};display:${cur.display};float:${cur.float};clear:${cur.clear};width:${cur.width};margin:${cur.margin};`;
}

function showImageOverlay(imgEl) {
    hideOverlay();
    activeImg = imgEl;
    const wrap = editorEl.value?.parentElement;
    if (!wrap) return;
    if (!imgEl.dataset.imgStyle) applyImgStyle(imgEl, {});
    imgEl.style.outline = '2px solid #3b82f6';
    overlayEl = document.createElement('div');
    overlayEl.className = 'quill-img-overlay';
    overlayEl.style.cssText = 'position:absolute;display:flex;flex-direction:column;gap:3px;background:white;border-radius:6px;padding:4px 5px;box-shadow:0 2px 10px rgba(0,0,0,.18);z-index:10;';

    function makeRow(label, items, applyFn) {
        const row = document.createElement('div');
        row.style.cssText = 'display:flex;gap:2px;align-items:center;flex-wrap:wrap;';
        const lbl = document.createElement('span');
        lbl.textContent = label;
        lbl.style.cssText = 'font-size:9px;font-weight:700;color:#6b7280;margin-right:2px;min-width:24px;font-family:Quicksand,sans-serif;';
        row.appendChild(lbl);
        items.forEach(item => {
            const btn = document.createElement('button');
            btn.textContent = item.label;
            btn.style.cssText = 'padding:1px 5px;font-size:9px;font-weight:600;border:1px solid #d1d5db;border-radius:3px;cursor:pointer;background:#f9fafb;color:#374151;white-space:nowrap;font-family:Quicksand,sans-serif;';
            btn.onmousedown = (e) => { e.preventDefault(); applyFn(item.value); emit('update:modelValue', quill.root.innerHTML); };
            row.appendChild(btn);
        });
        return row;
    }

    overlayEl.appendChild(makeRow('Size', [
        { label:'10%', value:'10%' }, { label:'20%', value:'20%' }, { label:'30%', value:'30%' }, { label:'40%', value:'40%' },
        { label:'50%', value:'50%' }, { label:'75%', value:'75%' }, { label:'100%', value:'100%' },
    ], (w) => {
        const s = JSON.parse(imgEl.dataset.imgStyle || '{}');
        const isFloat = s.float === 'left' || s.float === 'right';
        applyImgStyle(imgEl, { width: w, margin: isFloat ? s.margin : (w === '100%' ? '8px 0' : '8px auto') });
    }));

    overlayEl.appendChild(makeRow('Posisi', [
        { label:'Tengah', value:'center' }, { label:'Kiri', value:'left' }, { label:'Kanan', value:'right' },
    ], (pos) => {
        if (pos === 'center') applyImgStyle(imgEl, { float:'none', clear:'both', margin:'8px auto' });
        else if (pos === 'left') applyImgStyle(imgEl, { float:'left', clear:'none', margin:'0 16px 8px 0' });
        else applyImgStyle(imgEl, { float:'right', clear:'none', margin:'0 0 8px 16px' });
    }));

    const delBtn = document.createElement('button');
    delBtn.textContent = '✕ Hapus';
    delBtn.style.cssText = 'padding:1px 5px;font-size:9px;font-weight:700;border:1px solid #fecaca;border-radius:3px;cursor:pointer;background:#fef2f2;color:#dc2626;font-family:Quicksand,sans-serif;margin-left:auto;';
    delBtn.onmousedown = (e) => { e.preventDefault(); imgEl.remove(); emit('update:modelValue', quill.root.innerHTML); hideOverlay(); };
    const delRow = document.createElement('div');
    delRow.style.cssText = 'display:flex;justify-content:flex-end;';
    delRow.appendChild(delBtn);
    overlayEl.appendChild(delRow);

    const wrapRect = wrap.getBoundingClientRect();
    const imgRect = imgEl.getBoundingClientRect();
    overlayEl.style.top = (imgRect.top - wrapRect.top - 60) + 'px';
    overlayEl.style.left = Math.max(0, imgRect.left - wrapRect.left) + 'px';
    wrap.appendChild(overlayEl);
}

function hideOverlay() {
    if (activeImg) { activeImg.style.outline = ''; activeImg = null; }
    if (overlayEl) { overlayEl.remove(); overlayEl = null; }
}

function initQuill() {
    if (!window.Quill || !editorEl.value) return;
    quill = new Quill(editorEl.value, {
        theme: 'snow',
        placeholder: props.placeholder,
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }, { 'list': 'ordered' }, { 'list': 'bullet' }],
                ['link', 'image', 'video'],
                ['clean'],
            ],
        }
    });
    if (props.modelValue) quill.root.innerHTML = props.modelValue;

    quill.getModule('toolbar').addHandler('image', () => {
        const input = document.createElement('input');
        input.setAttribute('type', 'file'); input.setAttribute('accept', 'image/*'); input.setAttribute('multiple', 'multiple');
        input.click();
        input.onchange = async () => {
            for (const file of input.files) {
                const range = quill.getSelection(true);
                const idx = range ? range.index : quill.getLength() - 1;
                quill.insertText(idx, '⏳ Upload...', { color: '#999' });
                const url = await uploadQuillImage(file);
                quill.deleteText(idx, 13);
                if (url) { quill.insertEmbed(idx, 'image', url); quill.setSelection(idx + 1); }
            }
        };
    });

    quill.root.addEventListener('paste', async (e) => {
        const items = e.clipboardData?.items;
        if (!items) return;
        for (const item of items) {
            if (item.type.startsWith('image/')) {
                e.preventDefault();
                const file = item.getAsFile();
                const range = quill.getSelection(true);
                const idx = range ? range.index : quill.getLength() - 1;
                quill.insertText(idx, '⏳ Upload...', { color: '#999' });
                const url = await uploadQuillImage(file);
                quill.deleteText(idx, 13);
                if (url) { quill.insertEmbed(idx, 'image', url); quill.setSelection(idx + 1); }
                return;
            }
        }
    });

    quill.root.addEventListener('click', (e) => { if (e.target.tagName === 'IMG') { e.preventDefault(); showImageOverlay(e.target); } });
    mousedownHandler = (e) => { if (overlayEl && !e.target.closest('.quill-img-overlay') && !e.target.closest('.ql-toolbar')) hideOverlay(); };
    document.addEventListener('mousedown', mousedownHandler);

    quill.on('text-change', () => { isInternalUpdate = true; emit('update:modelValue', quill.root.innerHTML); nextTick(() => isInternalUpdate = false); });
}

watch(() => props.modelValue, (val) => {
    if (quill && !isInternalUpdate && quill.root.innerHTML !== val) {
        quill.root.innerHTML = val || '';
    }
});

onMounted(() => setTimeout(initQuill, 150));
onBeforeUnmount(() => { hideOverlay(); if (mousedownHandler) { document.removeEventListener('mousedown', mousedownHandler); mousedownHandler = null; } quill = null; });

defineExpose({ getQuill: () => quill });
</script>

<style>
.quill-editor-sm .ql-editor {
    min-height: 100px;
    color: #1f2937;
    font-family: 'Quicksand', sans-serif;
    font-size: 14px;
    line-height: 1.7;
}
.quill-editor-sm .ql-container {
    min-height: 100px;
    font-size: 14px;
    border: 1px solid #e5e7eb;
    border-top: none;
    border-radius: 0 0 12px 12px;
}
.quill-editor-sm .ql-toolbar {
    padding: 4px 6px;
    border: 1px solid #e5e7eb;
    border-radius: 12px 12px 0 0;
    background: #f9fafb;
}
.quill-editor-sm .ql-toolbar .ql-formats { margin-right: 6px; }
.quill-editor-sm .ql-toolbar button { width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; padding: 0; }
.quill-editor-sm .ql-toolbar button svg { display: block; width: 18px; height: 18px; }
.quill-editor-sm .ql-toolbar .ql-picker-label { height: 28px; display: inline-flex; align-items: center; }
.quill-editor-sm .ql-editor img { max-width: 100%; height: auto; border-radius: 6px; cursor: pointer; transition: box-shadow .2s; }
.quill-editor-sm .ql-editor img:hover { box-shadow: 0 0 0 3px rgba(59,130,246,.3); }
</style>
