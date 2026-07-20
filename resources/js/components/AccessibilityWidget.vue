<template>
<div class="a11y-wrap">
    <!-- Skip to content (always present, hidden visually) -->
    <a href="#main-content" class="a11y-skip">Langsung ke konten utama</a>

    <button @click="open=!open" class="a11y-btn" :class="{'active':open}" title="Aksesibilitas (Alt+U)" aria-label="Buka panel aksesibilitas" :aria-expanded="open">
        <i class="fas fa-universal-access"></i>
    </button>

    <transition name="a11y-slide">
        <div v-if="open" class="a11y-panel" role="dialog" aria-label="Pengaturan Aksesibilitas">
            <div class="a11y-header">
                <i class="fas fa-universal-access"></i>
                <span>Aksesibilitas</span>
                <button @click="resetAll" class="a11y-reset" title="Reset semua pengaturan" aria-label="Reset semua"><i class="fas fa-rotate-right"></i></button>
            </div>

            <div class="a11y-body">
                <!-- Font Size -->
                <div class="a11y-section">
                    <label class="a11y-label" id="lbl-font"><i class="fas fa-text-height"></i> Ukuran Teks</label>
                    <div class="a11y-btn-group" role="radiogroup" aria-labelledby="lbl-font">
                        <button @click="setFontSize(-1)" class="a11y-opt" :class="{'active':fontSize===-1}" role="radio" :aria-checked="fontSize===-1">A-</button>
                        <button @click="setFontSize(0)" class="a11y-opt" :class="{'active':fontSize===0}" role="radio" :aria-checked="fontSize===0">A</button>
                        <button @click="setFontSize(1)" class="a11y-opt" :class="{'active':fontSize===1}" role="radio" :aria-checked="fontSize===1">A+</button>
                        <button @click="setFontSize(2)" class="a11y-opt" :class="{'active':fontSize===2}" role="radio" :aria-checked="fontSize===2">A++</button>
                    </div>
                </div>

                <!-- Contrast -->
                <div class="a11y-section">
                    <label class="a11y-label" id="lbl-contrast"><i class="fas fa-circle-half-stroke"></i> Kontras</label>
                    <div class="a11y-btn-group" role="radiogroup" aria-labelledby="lbl-contrast">
                        <button @click="setContrast('normal')" class="a11y-opt" :class="{'active':contrast==='normal'}" role="radio" :aria-checked="contrast==='normal'">Normal</button>
                        <button @click="setContrast('high')" class="a11y-opt" :class="{'active':contrast==='high'}" role="radio" :aria-checked="contrast==='high'">Tinggi</button>
                        <button @click="setContrast('inverted')" class="a11y-opt" :class="{'active':contrast==='inverted'}" role="radio" :aria-checked="contrast==='inverted'">Terbalik</button>
                    </div>
                </div>

                <!-- Line Height -->
                <div class="a11y-section">
                    <label class="a11y-label" id="lbl-lh"><i class="fas fa-text-height"></i> Jarak Baris</label>
                    <div class="a11y-btn-group" role="radiogroup" aria-labelledby="lbl-lh">
                        <button @click="setLineHeight(1.5)" class="a11y-opt" :class="{'active':lineHeight===1.5}" role="radio" :aria-checked="lineHeight===1.5">Normal</button>
                        <button @click="setLineHeight(1.8)" class="a11y-opt" :class="{'active':lineHeight===1.8}" role="radio" :aria-checked="lineHeight===1.8">Lebar</button>
                        <button @click="setLineHeight(2.2)" class="a11y-opt" :class="{'active':lineHeight===2.2}" role="radio" :aria-checked="lineHeight===2.2">Ekstra</button>
                    </div>
                </div>

                <!-- Letter Spacing -->
                <div class="a11y-section">
                    <label class="a11y-label" id="lbl-ls"><i class="fas fa-text-width"></i> Jarak Huruf</label>
                    <div class="a11y-btn-group" role="radiogroup" aria-labelledby="lbl-ls">
                        <button @click="setLetterSpacing(0)" class="a11y-opt" :class="{'active':letterSpacing===0}">Normal</button>
                        <button @click="setLetterSpacing(0.05)" class="a11y-opt" :class="{'active':letterSpacing===0.05}">Lebar</button>
                        <button @click="setLetterSpacing(0.1)" class="a11y-opt" :class="{'active':letterSpacing===0.1}">Ekstra</button>
                    </div>
                </div>

                <!-- Word Spacing -->
                <div class="a11y-section">
                    <label class="a11y-label" id="lbl-ws"><i class="fas fa-arrows-left-right"></i> Jarak Kata</label>
                    <div class="a11y-btn-group" role="radiogroup" aria-labelledby="lbl-ws">
                        <button @click="setWordSpacing(0)" class="a11y-opt" :class="{'active':wordSpacing===0}">Normal</button>
                        <button @click="setWordSpacing(0.1)" class="a11y-opt" :class="{'active':wordSpacing===0.1}">Lebar</button>
                        <button @click="setWordSpacing(0.2)" class="a11y-opt" :class="{'active':wordSpacing===0.2}">Ekstra</button>
                    </div>
                </div>

                <!-- Toggles -->
                <div class="a11y-section">
                    <label class="a11y-label"><i class="fas fa-toggle-on"></i> Pengaturan Tambahan</label>
                    <label class="a11y-toggle" @click="toggleDyslexia">
                        <span><i class="fas fa-font"></i> Font Disleksia</span>
                        <div class="a11y-switch" :class="{'on':dyslexia}" role="switch" :aria-checked="dyslexia"></div>
                    </label>
                    <label class="a11y-toggle" @click="toggleBigCursor">
                        <span><i class="fas fa-arrow-pointer"></i> Kursor Besar</span>
                        <div class="a11y-switch" :class="{'on':bigCursor}" role="switch" :aria-checked="bigCursor"></div>
                    </label>
                    <label class="a11y-toggle" @click="toggleBigButtons">
                        <span><i class="fas fa-hand-pointer"></i> Tombol Besar</span>
                        <div class="a11y-switch" :class="{'on':bigButtons}" role="switch" :aria-checked="bigButtons"></div>
                    </label>
                    <label class="a11y-toggle" @click="toggleLinkHighlight">
                        <span><i class="fas fa-link"></i> Sorot Link</span>
                        <div class="a11y-switch" :class="{'on':linkHighlight}" role="switch" :aria-checked="linkHighlight"></div>
                    </label>
                    <label class="a11y-toggle" @click="toggleFocusRing">
                        <span><i class="fas fa-bullseye"></i> Fokus Jelas</span>
                        <div class="a11y-switch" :class="{'on':focusRing}" role="switch" :aria-checked="focusRing"></div>
                    </label>
                    <label class="a11y-toggle" @click="toggleSimpleMode">
                        <span><i class="fas fa-minimize"></i> Mode Sederhana</span>
                        <div class="a11y-switch" :class="{'on':simpleMode}" role="switch" :aria-checked="simpleMode"></div>
                    </label>
                    <label class="a11y-toggle" @click="toggleDataSaver">
                        <span><i class="fas fa-wifi"></i> Hemat Data</span>
                        <div class="a11y-switch" :class="{'on':dataSaver}" role="switch" :aria-checked="dataSaver"></div>
                    </label>
                    <label class="a11y-toggle" @click="toggleAnimation">
                        <span><i class="fas fa-pause"></i> Hentikan Animasi</span>
                        <div class="a11y-switch" :class="{'on':noAnimation}" role="switch" :aria-checked="noAnimation"></div>
                    </label>
                </div>

                <!-- Text to Speech -->
                <div class="a11y-section">
                    <label class="a11y-label"><i class="fas fa-volume-high"></i> Bacakan Teks</label>
                    <p class="a11y-hint">Aktifkan lalu arahkan kursor ke teks untuk dibacakan. Di HP, ketuk teks. Klik tombol lagi untuk berhenti.</p>
                    <div class="a11y-btn-group">
                        <button @click="toggleTTS" class="a11y-opt" :class="{'active':ttsActive}" style="flex:1" :aria-pressed="ttsActive">
                            <i :class="ttsActive?'fa-stop':'fa-play'" class="fas mr-1"></i>{{ ttsActive?'Stop':'Mulai Bacakan' }}
                        </button>
                    </div>
                </div>

                <!-- Readability -->
                <div class="a11y-section">
                    <label class="a11y-label"><i class="fas fa-book-open"></i> Tingkat Bacaan</label>
                    <div class="a11y-btn-group">
                        <button @click="setReadability('normal')" class="a11y-opt" :class="{'active':readability==='normal'}">Normal</button>
                        <button @click="setReadability('easy')" class="a11y-opt" :class="{'active':readability==='easy'}">Mudah</button>
                    </div>
                </div>
            </div>

            <div class="a11y-footer">
                <span class="a11y-footer-text">Pengaturan tersimpan otomatis</span>
                <span class="a11y-footer-text">Tekan <kbd>Alt+U</kbd> untuk buka/tutup</span>
            </div>
        </div>
    </transition>
</div>
</template>

<script setup>
import {ref,onMounted,onUnmounted} from 'vue';

const open=ref(false);
const fontSize=ref(0);
const contrast=ref('normal');
const lineHeight=ref(1.5);
const letterSpacing=ref(0);
const wordSpacing=ref(0);
const dyslexia=ref(false);
const bigCursor=ref(false);
const bigButtons=ref(false);
const linkHighlight=ref(false);
const focusRing=ref(false);
const simpleMode=ref(false);
const dataSaver=ref(false);
const noAnimation=ref(false);
const ttsActive=ref(false);
const readability=ref('normal');

const fontSizes={'-1':'87.5%','0':'100%','1':'115%','2':'135%'};
let ttsHandler=null;
let chromeResumeTimer=null;

function apply(){
    const r=document.documentElement.style;
    r.setProperty('--a11y-font-size',fontSizes[fontSize.value]||'100%');
    r.setProperty('--a11y-line-height',lineHeight.value);
    r.setProperty('--a11y-letter-spacing',letterSpacing.value+'em');
    r.setProperty('--a11y-word-spacing',wordSpacing.value+'em');
    const b=document.body.classList;
    b.toggle('a11y-dyslexia',dyslexia.value);
    b.toggle('a11y-big-cursor',bigCursor.value);
    b.toggle('a11y-big-buttons',bigButtons.value);
    b.toggle('a11y-link-highlight',linkHighlight.value);
    b.toggle('a11y-focus-ring',focusRing.value);
    b.toggle('a11y-simple-mode',simpleMode.value);
    b.toggle('a11y-data-saver',dataSaver.value);
    b.toggle('a11y-no-animation',noAnimation.value);
    b.toggle('a11y-readability-easy',readability.value==='easy');
    b.remove('a11y-contrast-high','a11y-contrast-inverted');
    if(contrast.value==='high')b.add('a11y-contrast-high');
    if(contrast.value==='inverted')b.add('a11y-contrast-inverted');
    if(dataSaver.value){document.querySelectorAll('img[data-src]').forEach(i=>{i.src=i.dataset.src;});}
    save();
}

function setFontSize(v){fontSize.value=v;apply();}
function setContrast(v){contrast.value=v;apply();}
function setLineHeight(v){lineHeight.value=v;apply();}
function setLetterSpacing(v){letterSpacing.value=v;apply();}
function setWordSpacing(v){wordSpacing.value=v;apply();}
function toggleDyslexia(){dyslexia.value=!dyslexia.value;apply();}
function toggleBigCursor(){bigCursor.value=!bigCursor.value;apply();}
function toggleBigButtons(){bigButtons.value=!bigButtons.value;apply();}
function toggleLinkHighlight(){linkHighlight.value=!linkHighlight.value;apply();}
function toggleFocusRing(){focusRing.value=!focusRing.value;apply();}
function toggleSimpleMode(){simpleMode.value=!simpleMode.value;apply();}
function toggleDataSaver(){dataSaver.value=!dataSaver.value;apply();}
function toggleAnimation(){noAnimation.value=!noAnimation.value;apply();}
function setReadability(v){readability.value=v;apply();}

function save(){
    try{localStorage.setItem('a11y',JSON.stringify({fontSize:fontSize.value,contrast:contrast.value,lineHeight:lineHeight.value,letterSpacing:letterSpacing.value,wordSpacing:wordSpacing.value,dyslexia:dyslexia.value,bigCursor:bigCursor.value,bigButtons:bigButtons.value,linkHighlight:linkHighlight.value,focusRing:focusRing.value,simpleMode:simpleMode.value,dataSaver:dataSaver.value,noAnimation:noAnimation.value,readability:readability.value}));}catch(e){}
}

function load(){
    try{
        const d=JSON.parse(localStorage.getItem('a11y'));
        if(!d)return;
        fontSize.value=d.fontSize??0;contrast.value=d.contrast??'normal';lineHeight.value=d.lineHeight??1.5;
        letterSpacing.value=d.letterSpacing??0;wordSpacing.value=d.wordSpacing??0;
        dyslexia.value=!!d.dyslexia;bigCursor.value=!!d.bigCursor;bigButtons.value=!!d.bigButtons;
        linkHighlight.value=!!d.linkHighlight;focusRing.value=!!d.focusRing;
        simpleMode.value=!!d.simpleMode;dataSaver.value=!!d.dataSaver;noAnimation.value=!!d.noAnimation;
        readability.value=d.readability??'normal';
        apply();
    }catch(e){}
}

function resetAll(){
    fontSize.value=0;contrast.value='normal';lineHeight.value=1.5;letterSpacing.value=0;wordSpacing.value=0;
    dyslexia.value=false;bigCursor.value=false;bigButtons.value=false;linkHighlight.value=false;
    focusRing.value=false;simpleMode.value=false;dataSaver.value=false;noAnimation.value=false;readability.value='normal';
    stopTTS();
    apply();
}

function speakText(text){
    if(!('speechSynthesis' in window))return;
    speechSynthesis.cancel();
    if(chromeResumeTimer){clearInterval(chromeResumeTimer);chromeResumeTimer=null;}
    const u=new SpeechSynthesisUtterance(text);
    u.lang='id-ID';u.rate=0.85;u.pitch=1;u.volume=1;
    const voices=speechSynthesis.getVoices();
    const idVoice=voices.find(v=>v.lang.startsWith('id'));
    if(idVoice)u.voice=idVoice;
    u.onerror=()=>{};
    u.onend=()=>{if(chromeResumeTimer){clearInterval(chromeResumeTimer);chromeResumeTimer=null;}};
    speechSynthesis.speak(u);
    if(navigator.userAgent.includes('Chrome')){
        chromeResumeTimer=setInterval(()=>{
            if(!speechSynthesis.speaking){clearInterval(chromeResumeTimer);chromeResumeTimer=null;}
            else{speechSynthesis.pause();speechSynthesis.resume();}
        },14000);
    }
}

function stopTTS(){
    speechSynthesis.cancel();
    if(chromeResumeTimer){clearInterval(chromeResumeTimer);chromeResumeTimer=null;}
    ttsActive.value=false;
    document.querySelectorAll('.a11y-tts-hover').forEach(el=>el.classList.remove('a11y-tts-hover'));
    if(ttsHandler){document.removeEventListener('mouseover',ttsHandler);document.removeEventListener('touchstart',ttsHandler);ttsHandler=null;}
}

function toggleTTS(){
    if(ttsActive.value){stopTTS();return;}
    if(!('speechSynthesis' in window)){alert('Browser tidak mendukung text-to-speech.');return;}
    if(speechSynthesis.getVoices().length===0){speechSynthesis.getVoices();}
    ttsActive.value=true;
    speakText('Mode bacakan aktif. Arahkan kursor ke teks untuk dibacakan.');
    let lastText='';
    let lastEl=null;
    ttsHandler=(e)=>{
        if(e.target.closest('.a11y-wrap'))return;
        const el=e.target.closest('p,h1,h2,h3,h4,h5,h6,li,a,span,td,th,label,button');
        if(lastEl&&lastEl!==el)lastEl.classList.remove('a11y-tts-hover');
        if(!el)return;
        el.classList.add('a11y-tts-hover');
        lastEl=el;
        const text=(el.textContent||'').trim().replace(/\s+/g,' ');
        if(text.length>1&&text.length<2000&&text!==lastText){
            lastText=text;
            speechSynthesis.cancel();
            speakText(text);
        }
    };
    document.addEventListener('mouseover',ttsHandler);
}

function onKey(e){
    if(e.altKey&&(e.key==='u'||e.key==='U'||e.code==='KeyU')){e.preventDefault();open.value=!open.value;}
    if(e.key==='Escape'&&open.value){open.value=false;}
}

onMounted(()=>{load();if('speechSynthesis' in window)speechSynthesis.getVoices();document.addEventListener('keydown',onKey);});
onUnmounted(()=>{document.removeEventListener('keydown',onKey);stopTTS();});
</script>

<style>
/* ===== GLOBAL ACCESSIBILITY ===== */
html{font-size:var(--a11y-font-size,100%)!important}
body{letter-spacing:var(--a11y-letter-spacing,0)!important;word-spacing:var(--a11y-word-spacing,0)!important}

/* Skip link */
.a11y-skip{position:fixed;top:-100px;left:16px;z-index:99999;background:#2563eb;color:#fff;padding:12px 20px;border-radius:0 0 12px 12px;font-size:14px;font-weight:700;text-decoration:none;transition:top .2s}
.a11y-skip:focus{top:0}

/* Font */
body.a11y-dyslexia,body.a11y-dyslexia *{font-family:'OpenDyslexic','Comic Sans MS',cursive!important}
@font-face{font-family:'OpenDyslexic';src:url('https://cdn.jsdelivr.net/npm/open-dyslexic@1.0.3/woff/OpenDyslexic-Regular.woff') format('woff');font-weight:normal}

/* Cursor */
body.a11y-big-cursor,body.a11y-big-cursor *{cursor:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32'%3E%3Ccircle cx='16' cy='16' r='14' fill='rgba(0,0,0,0.7)' stroke='white' stroke-width='2'/%3E%3C/svg%3E") 16 16,auto!important}

/* Big buttons */
body.a11y-big-buttons button,body.a11y-big-buttons a[role="button"],body.a11y-big-buttons .btn,body.a11y-big-buttons input[type="submit"]{min-height:48px!important;min-width:48px!important;font-size:16px!important;padding:12px 20px!important}

/* Link highlight */
body.a11y-link-highlight a:not(.a11y-btn):not(.a11y-opt):not(.a11y-reset){background:rgba(255,255,0,0.25)!important;outline:2px solid #f59e0b!important;outline-offset:2px!important;border-radius:4px!important}

/* Focus ring */
body.a11y-focus-ring *:focus{outline:3px solid #2563eb!important;outline-offset:3px!important;box-shadow:0 0 0 6px rgba(37,99,235,0.2)!important}

/* Contrast — high: override CSS vars; inverted: filter on main content only */
body.a11y-contrast-high{--color-background:#000!important;--color-surface:#000!important;--color-text-primary:#fff!important;--color-text-secondary:#e2e8f0!important;--color-primary:#60a5fa!important}
body.a11y-contrast-high .a11y-wrap{filter:none!important}
body.a11y-contrast-high header,body.a11y-contrast-high footer,body.a11y-contrast-high main{filter:contrast(1.4)}
body.a11y-contrast-inverted main{filter:invert(1) hue-rotate(180deg)}
body.a11y-contrast-inverted main img,body.a11y-contrast-inverted main video{filter:invert(1) hue-rotate(180deg)}

/* Simple mode */
body.a11y-simple-mode .chat-btn,body.a11y-simple-mode .chat-window{display:none!important}
body.a11y-simple-mode header{position:relative!important}
body.a11y-simple-mode .hero,body.a11y-simple-mode [class*="animate"],body.a11y-simple-mode [class*="slider"]{animation:none!important;transition:none!important}
body.a11y-simple-mode .container,body.a11y-simple-mode .max-w-7xl{max-width:100%!important;padding-left:20px!important;padding-right:20px!important}

/* Data saver */
body.a11y-data-saver img{content-visibility:auto}
body.a11y-data-saver video{display:none}
body.a11y-data-saver .bg-gradient,body.a11y-data-saver [style*="gradient"]{background:#f1f5f9!important}

/* No animation */
body.a11y-no-animation,body.a11y-no-animation *{animation-duration:0s!important;animation-delay:0s!important;transition-duration:0s!important;transition-delay:0s!important}
body.a11y-no-animation *::before,body.a11y-no-animation *::after{animation-duration:0s!important;transition-duration:0s!important}

/* TTS hover highlight */
.a11y-tts-hover{background:rgba(37,99,235,0.12)!important;outline:2px solid #3b82f6!important;outline-offset:2px!important;border-radius:4px!important;transition:all .15s!important}

/* Readability */
body.a11y-readability-easy p,body.a11y-readability-easy li,body.a11y-readability-easy span:not([class]){font-size:1.1em!important;line-height:2!important;max-width:70ch!important}
body.a11y-readability-easy h1{font-size:2em!important;margin-bottom:0.5em!important}
body.a11y-readability-easy h2{font-size:1.5em!important;margin-bottom:0.5em!important}
body.a11y-readability-easy a{text-decoration:underline!important;text-underline-offset:3px!important}
</style>

<style scoped>
.a11y-wrap{position:fixed;bottom:90px;left:16px;z-index:9998}
.a11y-btn{width:50px;height:50px;border-radius:50%;border:none;background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff;font-size:22px;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(37,99,235,0.35);transition:all .2s}
.a11y-btn:hover{transform:scale(1.1);box-shadow:0 6px 20px rgba(37,99,235,0.45)}
.a11y-btn.active{background:#1d4ed8}
.a11y-panel{position:absolute;bottom:58px;left:0;width:300px;background:#fff;border-radius:16px;border:1px solid #e2e8f0;box-shadow:0 16px 48px rgba(2,6,23,0.2);overflow:hidden}
.a11y-header{display:flex;align-items:center;gap:8px;padding:10px 16px;background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff;font-size:14px;font-weight:700}
.a11y-reset{margin-left:auto;background:rgba(255,255,255,0.2);border:none;color:#fff;width:28px;height:28px;border-radius:8px;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center}
.a11y-reset:hover{background:rgba(255,255,255,0.3)}
.a11y-body{padding:8px 16px 10px;max-height:450px;overflow-y:auto}
.a11y-body::-webkit-scrollbar{width:4px}.a11y-body::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:99px}
.a11y-section{margin-bottom:8px}
.a11y-section:last-child{margin-bottom:0}
.a11y-label{display:flex;align-items:center;gap:6px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-bottom:4px}
.a11y-hint{font-size:11px;color:#94a3b8;margin-bottom:4px;line-height:1.4}
.a11y-btn-group{display:flex;gap:4px}
.a11y-opt{flex:1;padding:6px 4px;border-radius:8px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#334155;font-size:12px;font-weight:600;cursor:pointer;transition:all .15s;text-align:center;font-family:'Quicksand',sans-serif}
.a11y-opt:hover{border-color:#93c5fd;background:#eff6ff}
.a11y-opt.active{background:#2563eb;color:#fff;border-color:#2563eb}
.a11y-toggle{display:flex;align-items:center;justify-content:space-between;padding:5px 0;cursor:pointer;border-bottom:1px solid #f1f5f9}
.a11y-toggle:last-child{border-bottom:none}
.a11y-toggle span{display:flex;align-items:center;gap:8px;font-size:13px;color:#334155}
.a11y-toggle i{color:#64748b;width:16px;text-align:center}
.a11y-switch{width:36px;height:20px;border-radius:999px;background:#e2e8f0;position:relative;transition:all .2s;flex-shrink:0}
.a11y-switch::after{content:'';position:absolute;top:2px;left:2px;width:16px;height:16px;border-radius:50%;background:#fff;transition:all .2s;box-shadow:0 1px 3px rgba(0,0,0,0.15)}
.a11y-switch.on{background:#2563eb}
.a11y-switch.on::after{left:18px}
.a11y-footer{padding:8px 16px;border-top:1px solid #f1f5f9;text-align:center;display:flex;flex-direction:column;gap:1px}
.a11y-footer-text{font-size:10px;color:#94a3b8}
.a11y-footer kbd{background:#f1f5f9;border:1px solid #e2e8f0;border-radius:4px;padding:1px 5px;font-size:10px;font-family:monospace}
.a11y-slide-enter-active,.a11y-slide-leave-active{transition:all .25s ease}
.a11y-slide-enter-from,.a11y-slide-leave-to{opacity:0;transform:translateY(12px) scale(0.95)}
</style>
