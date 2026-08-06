let quillPromise = null;

export function loadQuill() {
    if (quillPromise) return quillPromise;
    quillPromise = new Promise((resolve, reject) => {
        if (window.Quill) return resolve(window.Quill);

        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = '/vendor/quill/quill.snow.css';
        document.head.appendChild(link);

        const style = document.createElement('style');
        style.textContent = `.ql-toolbar.ql-snow{border:1px solid #e5e7eb!important;border-radius:16px 16px 0 0!important;background:#f9fafb;}.ql-container.ql-snow{border:1px solid #e5e7eb!important;border-top:none!important;border-radius:0 0 16px 16px!important;font-size:15px;font-family:'Quicksand',sans-serif;}.ql-editor{min-height:250px;line-height:1.8;}.ql-editor img{max-width:100%;height:auto;border-radius:12px;cursor:pointer;transition:box-shadow .2s;}.ql-editor img:hover{box-shadow:0 0 0 3px rgba(0,144,216,.3);}`;
        document.head.appendChild(style);

        const s = document.createElement('script');
        s.src = '/vendor/quill/quill.js';
        s.onload = () => resolve(window.Quill);
        s.onerror = () => reject(new Error('Failed to load Quill'));
        document.head.appendChild(s);
    });
    return quillPromise;
}
