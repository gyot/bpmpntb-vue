<template>
<div>
<button @click="toggleChat" class="chat-btn">
    <img src="/intan.png" alt="INTAN" class="chat-btn-img" @error="$event.target.style.display='none'">
    <span class="chat-btn-text">TANYA INTAN<br><small>SEKARANG</small></span>
    <div class="chat-btn-badge"><i class="fas fa-comment-dots mr-1"></i>{{ badgeMessages[badgeIdx] }}</div>
</button>

<div v-if="isOpen" class="chat-overlay" :class="{visible:expanded}" @click="expanded=false"></div>

<div v-if="isOpen" class="chat-window" :class="{expanded}">
    <div class="chat-header">
        <img src="/intan.png" alt="INTAN" class="chat-header-img" @error="$event.target.style.display='none'">
        <div class="flex-1 min-w-0"><div class="text-sm font-bold">SI INTAN v2 - BPMP NTB</div><div class="text-[10px] opacity-80">Sistem Informasi Instan</div></div>
        <div class="flex items-center gap-1">
            <button @click="expanded=!expanded" class="chat-hdr-btn" title="Perluas"><i :class="expanded?'fa-window-restore':'fa-window-maximize'" class="fas"></i></button>
            <button @click="darkMode=!darkMode" class="chat-hdr-btn" title="Mode Gelap">{{ darkMode ? '☀️' : '🌙' }}</button>
            <button @click="isOpen=false" class="chat-hdr-btn" title="Tutup">✕</button>
        </div>
    </div>

    <div class="admin-status-bar">
        <div><span class="status-pill" :class="adminOnline?'status-online':'status-offline'">{{ adminOnline?'Online':'Offline' }}</span></div>
        <div class="flex gap-1">
            <button v-if="!isLiveChat && adminOnline" @click="startLiveChat" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-500 text-white">Live Chat</button>
            <button v-if="isLiveChat" @click="stopLiveChat" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-500 text-white">Kembali ke Bot</button>
        </div>
    </div>

    <div v-if="identitySaved" class="px-3 py-1 text-right bg-gray-50 border-b border-gray-100">
        <button @click="logoutChatbot" class="text-[11px] text-blue-500 hover:underline">Keluar</button>
    </div>

    <div class="chat-box" ref="chatBox" @scroll="onScroll">
        <div v-if="!identitySaved" class="identity-form">
            <div class="text-center mb-3"><div class="text-2xl mb-1">👋</div><div class="font-bold text-sm">Selamat Datang!</div><div class="text-xs text-gray-500">Isi identitas untuk memulai</div></div>
            <input v-model="identity.nama" placeholder="Nama Lengkap" class="chat-input">
            <input v-model="identity.instansi" placeholder="Asal Sekolah/Instansi" class="chat-input">
            <input v-model="identity.kontak" placeholder="No Whatsapp Aktif" class="chat-input">
            <button @click="submitIdentity" class="chat-start-btn">Mulai Chat dengan INTAN</button>
        </div>

        <template v-else>
            <div v-for="(msg,i) in messages" :key="i" class="chat-row" :class="msg.sender==='user'?'chat-row-user':'chat-row-bot'">
                <div v-if="msg.sender==='bot'" class="chat-avatar" style="background:#3b82f6"><img src="/intan.png" class="w-4 h-4 rounded-full" @error="$event.target.style.display='none'"></div>
                <div class="bubble" :class="msg.sender==='user'?'bubble-user':'bubble-bot'">
                    <div v-if="msg.sender==='bot'" class="md-content" v-html="renderMd(msg.text)"></div>
                    <div v-else>{{ msg.text }}</div>
                    <div class="msg-time">{{ msg.time }}</div>
                </div>
                <div v-if="msg.sender==='user'" class="chat-avatar" style="background:#6366f1"><i class="fas fa-user text-[10px] text-white"></i></div>
            </div>

            <div v-if="streaming" class="chat-row chat-row-bot">
                <div class="chat-avatar" style="background:#3b82f6"><img src="/intan.png" class="w-4 h-4 rounded-full" @error="$event.target.style.display='none'"></div>
                <div class="bubble bubble-bot"><div class="md-content" v-html="renderMd(streamText)"></div><span class="typing-cursor"></span></div>
            </div>

            <div v-if="botTyping && !streaming" class="chat-row chat-row-bot">
                <div class="chat-avatar" style="background:#3b82f6"><img src="/intan.png" class="w-4 h-4 rounded-full" @error="$event.target.style.display='none'"></div>
                <div class="bubble bubble-bot"><div class="typing-dots"><span></span><span></span><span></span><span class="typing-label">INTAN mengetik...</span></div></div>
            </div>

            <div v-if="showWelcome" class="welcome-cards">
                <div class="welcome-card" @click="sendQuick('1')"><div class="welcome-card-icon" style="background:#eff6ff;color:#2563eb">📋</div><div class="flex-1"><div class="font-semibold text-xs">Layanan BPMP NTB</div><div class="text-[10px] text-gray-400">Info layanan dan program</div></div><div class="text-gray-300">›</div></div>
                <div class="welcome-card" @click="sendQuick('2')"><div class="welcome-card-icon" style="background:#fef3c7;color:#d97706">🎯</div><div class="flex-1"><div class="font-semibold text-xs">Program Prioritas</div><div class="text-[10px] text-gray-400">Program unggulan BPMP</div></div><div class="text-gray-300">›</div></div>
                <div class="welcome-card" @click="sendQuick('3')"><div class="welcome-card-icon" style="background:#fce7f3;color:#db2777">🎓</div><div class="flex-1"><div class="font-semibold text-xs">Pengaduan SPMB</div><div class="text-[10px] text-gray-400">Lapor kendala PPDB</div></div><div class="text-gray-300">›</div></div>
                <div class="welcome-card" @click="sendQuick('4')"><div class="welcome-card-icon" style="background:#dcfce7;color:#16a34a">📱</div><div class="flex-1"><div class="font-semibold text-xs">Hubungi Admin</div><div class="text-[10px] text-gray-400">Chat langsung via WhatsApp</div></div><div class="text-gray-300">›</div></div>
                <div class="text-center text-[10px] text-gray-400 mt-2">💡 Atau langsung ketik pertanyaan Anda di bawah</div>
            </div>

            <div v-if="suggestions.length" class="suggestions">
                <button v-for="s in suggestions" :key="s" @click="sendText(s)" class="suggestion-btn">{{ s }}</button>
            </div>
        </template>
    </div>

    <button v-if="showScrollBtn" @click="scrollBottom" class="scroll-bottom-btn">↓</button>

    <form v-if="identitySaved" @submit.prevent="sendMessage" class="chat-form">
        <div class="relative"><button type="button" @click="showEmoji=!showEmoji" class="emoji-btn">😊</button>
            <div v-if="showEmoji" class="emoji-popup"><span v-for="e in emojis" :key="e" @click="insertEmoji(e)" class="emoji-item">{{ e }}</span></div>
        </div>
        <input v-model="input" type="text" placeholder="Ketik pesan..." class="chat-msg-input" :disabled="streaming||botTyping">
        <button type="submit" :disabled="!input.trim()||streaming||botTyping" class="chat-send-btn"><i class="fas fa-paper-plane"></i></button>
    </form>
</div>
</div>
</template>

<script setup>
import {ref,reactive,watch,onMounted,onUnmounted,nextTick,computed} from 'vue';

const isOpen=ref(false);const expanded=ref(false);const darkMode=ref(false);
const identitySaved=ref(false);const adminOnline=ref(false);const isLiveChat=ref(false);
const botTyping=ref(false);const streaming=ref(false);const streamText=ref('');
const showWelcome=ref(false);const showEmoji=ref(false);const showScrollBtn=ref(false);
const input=ref('');const chatBox=ref(null);const messages=ref([]);const suggestions=ref([]);
const badgeIdx=ref(0);let badgeTimer=null;let adminTimer=null;let liveSessionId=null;let lastLiveMsgId=0;let livePollTimer=null;

const identity=reactive({nama:'',instansi:'',kontak:''});
const badgeMessages=['💬 Ada pertanyaan?','🚀 500+ terbantu!','💡 Aduan SPMB!','⚡ Jawaban instan','📞 Hubungi admin!'];
const emojis=['😊','👍','❤️','🎉','🙏','😄','😢','😮','🔥','✅','❌','📌','📞','💡','⭐','👋','🤝','📝','🔔','💪','🎯','💯','🤔','👀','📊','📚','🏫','📋'];

const CHATBOT_BASE='/api/chatbot';

function playNotifSound(times=2){
    try{
        const ctx=new(window.AudioContext||window.webkitAudioContext)();
        for(let i=0;i<times;i++){
            setTimeout(()=>{const o=ctx.createOscillator();const g=ctx.createGain();o.connect(g);g.connect(ctx.destination);o.frequency.value=920;o.type='sine';g.gain.value=0.15;o.start();o.stop(ctx.currentTime+0.18);},i*220);
        }
    }catch(e){}
}

function getTime(){return new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});}
function scrollBottom(){nextTick(()=>{if(chatBox.value)chatBox.value.scrollTop=chatBox.value.scrollHeight;});}
function onScroll(){if(!chatBox.value)return;showScrollBtn.value=chatBox.value.scrollHeight-chatBox.value.scrollTop-chatBox.value.clientHeight>60;}

function renderMd(text){
    if(!text)return'';if(/<div|<button|<span class="welcome/.test(text))return text;
    if(text.includes('<b>')||text.includes('<br')||text.includes('<a '))return text;
    return text.replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>').replace(/\*(.*?)\*/g,'<em>$1</em>').replace(/`(.*?)`/g,'<code>$1</code>').replace(/^### (.*$)/gm,'<h3>$1</h3>').replace(/^## (.*$)/gm,'<h2>$1</h2>').replace(/^> (.*$)/gm,'<blockquote>$1</blockquote>').replace(/^- (.*$)/gm,'<li>$1</li>').replace(/\n/g,'<br>');
}

async function apiPost(path,body){
    const resp=await fetch(CHATBOT_BASE+path,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||''},body:new URLSearchParams(body).toString()});
    return resp;
}
async function apiGet(path){return fetch(CHATBOT_BASE+path,{credentials:'same-origin'});}

function toggleChat(){isOpen.value=!isOpen.value;if(isOpen.value)scrollBottom();}

async function submitIdentity(){
    if(!identity.nama.trim()||!identity.instansi.trim()||!identity.kontak.trim()){return;}
    const r=await apiPost('/save_identity',identity);
    const d=await r.json();
    if(d.status==='ok'){identitySaved.value=true;showWelcome.value=true;showGreeting();}
}

function showGreeting(){
    messages.value.push({sender:'bot',text:`Halo, <b>${identity.nama}</b>! 👋<br>Saya <b>INTAN</b>, Asisten Virtual BPMP Provinsi NTB.`,time:getTime()});
    loadSuggestions();scrollBottom();
}

async function loadSuggestions(){
    try{const r=await apiGet('/suggested-questions');const d=await r.json();suggestions.value=d.questions||[];}catch(e){}
}

async function sendMessage(){
    const text=input.value.trim();if(!text)return;
    input.value='';showWelcome.value=false;
    messages.value.push({sender:'user',text,time:getTime()});scrollBottom();

    if(isLiveChat.value&&liveSessionId){
        try{const r=await apiPost('/send_live',{session_id:liveSessionId,message:text});const d=await r.json();if(d.status!=='ok')messages.value.push({sender:'bot',text:d.message||'Gagal mengirim.',time:getTime()});}catch(e){}
        scrollBottom();return;
    }

    botTyping.value=true;streaming.value=true;streamText.value='';

    try{
        const response=await fetch(CHATBOT_BASE+'/respond-stream',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||''},body:'message='+encodeURIComponent(text)});
        if(!response.ok){const t=await response.text();streaming.value=false;botTyping.value=false;messages.value.push({sender:'bot',text:t,time:getTime()});playNotifSound(1);scrollBottom();return;}

        botTyping.value=false;
        const reader=response.body.getReader();const decoder=new TextDecoder();let buffer='';
        while(true){
            const{done,value}=await reader.read();if(done)break;
            buffer+=decoder.decode(value,{stream:true});const lines=buffer.split('\n');buffer=lines.pop();
            for(const line of lines){
                const trimmed=line.trim();if(!trimmed.startsWith('data:'))continue;
                const json=trimmed.substring(5).trim();if(!json)continue;
                try{const data=JSON.parse(json);
                    if(data.type==='token'){if(data.content==='MENU_UTAMA'){streaming.value=false;showWelcome.value=true;showGreeting();return;}streamText.value+=data.content;scrollBottom();}
                    if(data.type==='done'){streaming.value=false;messages.value.push({sender:'bot',text:streamText.value,time:getTime()});streamText.value='';playNotifSound(1);scrollBottom();return;}
                }catch(e){}
            }
        }
        if(streamText.value){streaming.value=false;messages.value.push({sender:'bot',text:streamText.value,time:getTime()});streamText.value='';playNotifSound(1);scrollBottom();}
    }catch(e){streaming.value=false;botTyping.value=false;messages.value.push({sender:'bot',text:'Maaf, terjadi kesalahan koneksi.',time:getTime()});scrollBottom();}
}

function sendQuick(id){
    const labels={'1':'Layanan BPMP NTB','2':'Program Prioritas','3':'Pengaduan SPMB','4':'Hubungi Admin'};
    input.value=labels[id]||id;sendMessage();
}
function sendText(t){input.value=t;sendMessage();}
function insertEmoji(e){input.value+=e;showEmoji.value=false;}

async function startLiveChat(){
    try{const r=await apiPost('/start_live',{});const d=await r.json();
        if(d.status==='ok'){isLiveChat.value=true;liveSessionId=d.session_id;lastLiveMsgId=0;messages.value.push({sender:'bot',text:'Anda terhubung ke <b>Live Chat Admin</b>.',time:getTime()});startLivePolling();}
        else if(d.message)messages.value.push({sender:'bot',text:d.message,time:getTime()});
    }catch(e){}
    scrollBottom();
}
function stopLiveChat(){isLiveChat.value=false;liveSessionId=null;if(livePollTimer){clearInterval(livePollTimer);livePollTimer=null;}}
function startLivePolling(){if(livePollTimer)clearInterval(livePollTimer);pullLive();livePollTimer=setInterval(pullLive,3000);}
async function pullLive(){
    if(!isLiveChat.value||!liveSessionId)return;
    try{const r=await apiGet(`/live_messages?session_id=${liveSessionId}&after_id=${lastLiveMsgId}`);const d=await r.json();
        if(d.status==='closed'){stopLiveChat();messages.value.push({sender:'bot',text:'Sesi live chat ditutup admin.',time:getTime()});return;}
        (d.messages||[]).forEach(m=>{lastLiveMsgId=Math.max(lastLiveMsgId,m.id);if(m.sender_type==='admin'){messages.value.push({sender:'bot',text:m.message,time:formatTime(m.created_at)});playNotifSound(2);}});
    }catch(e){}
    scrollBottom();
}

function formatTime(dt){if(!dt)return getTime();const d=new Date(dt.replace('T',' ').replace('Z',''));return isNaN(d)?getTime():d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});}

async function logoutChatbot(){
    try{await apiGet('/chatbot/logout');}catch(e){}
    identitySaved.value=false;showWelcome.value=false;messages.value=[];suggestions.value=[];
    if(isLiveChat.value)stopLiveChat();
    identity.nama='';identity.instansi='';identity.kontak='';
}

async function refreshAdminStatus(){
    try{const r=await apiGet('/admin_status');const d=await r.json();adminOnline.value=!!d.online;}catch(e){adminOnline.value=false;}
}

onMounted(async()=>{
    try{const r=await apiGet('/check_identity');const d=await r.json();
        if(d.has_identity){
            identitySaved.value=true;
            try{const nr=await apiGet('/get_username');const nd=await nr.json();if(nd.nama)identity.nama=nd.nama;}catch(e){}
            showWelcome.value=true;showGreeting();
        }
    }catch(e){}
    refreshAdminStatus();adminTimer=setInterval(refreshAdminStatus,15000);
    badgeTimer=setInterval(()=>{badgeIdx.value=(badgeIdx.value+1)%badgeMessages.length;},4000);
    document.addEventListener('click',e=>{if(showEmoji.value&&!e.target.closest('.relative'))showEmoji.value=false;});
});
onUnmounted(()=>{if(adminTimer)clearInterval(adminTimer);if(badgeTimer)clearInterval(badgeTimer);if(livePollTimer)clearInterval(livePollTimer);});

watch(darkMode,v=>{document.body.classList.toggle('dark-mode',v);});
</script>

<style scoped>
.chat-btn{position:fixed;bottom:20px;right:20px;z-index:9999;background:linear-gradient(135deg,var(--color-primary),color-mix(in srgb,var(--color-primary) 70%,white));color:white;border:none;border-radius:32px;padding:10px 16px;display:flex;align-items:center;gap:10px;font-size:14px;font-weight:700;cursor:pointer;box-shadow:0 8px 24px rgba(37,99,235,0.35);transition:all .2s;font-family:'Quicksand',sans-serif;animation:pulse-btn 2s infinite}
.chat-btn:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(37,99,235,0.45);animation:none}
@keyframes pulse-btn{0%,100%{box-shadow:0 8px 24px rgba(37,99,235,0.35)}50%{box-shadow:0 8px 24px rgba(37,99,235,0.35),0 0 0 10px rgba(37,99,235,0.1)}}
.chat-btn-img{width:32px;height:32px;border-radius:50%;background:white;padding:2px}
.chat-btn-text{text-align:left;line-height:1.1;font-size:13px}.chat-btn-text small{font-size:10px;opacity:.8;font-weight:500}
.chat-btn-badge{position:absolute;top:-6px;right:-6px;background:#ef4444;color:white;font-size:9px;font-weight:700;padding:3px 8px;border-radius:999px;white-space:nowrap;box-shadow:0 2px 8px rgba(239,68,68,0.4);animation:badge-pop 2.5s infinite}
@keyframes badge-pop{0%,100%{transform:scale(1)}50%{transform:scale(1.08)}}

.chat-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);backdrop-filter:blur(4px);z-index:999}.chat-overlay.visible{display:block}
.chat-window{position:fixed;bottom:90px;right:20px;width:370px;max-height:560px;background:white;border-radius:18px;box-shadow:0 24px 54px rgba(2,6,23,0.25);display:flex;flex-direction:column;overflow:hidden;z-index:10000;border:1px solid #e5e7eb;animation:slideUp .3s ease}
.chat-window.expanded{width:min(900px,calc(100vw - 40px));max-height:calc(100vh - 80px);right:50%;bottom:50%;transform:translate(50%,50%);border-radius:20px;box-shadow:0 32px 80px rgba(2,6,23,0.35)}
@keyframes slideUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}

.chat-header{background:linear-gradient(135deg,#0f3ea2,#1d4ed8,#3b82f6);color:white;padding:10px 14px;display:flex;align-items:center;gap:10px;flex-shrink:0}
.chat-header-img{width:28px;height:28px;border-radius:50%;border:2px solid white}
.chat-hdr-btn{background:transparent;border:none;color:white;font-size:14px;cursor:pointer;padding:4px 8px;border-radius:6px}.chat-hdr-btn:hover{background:rgba(255,255,255,0.15)}

.admin-status-bar{padding:6px 14px;border-bottom:1px solid #e5e7eb;font-size:11px;display:flex;justify-content:space-between;align-items:center;background:#f8fafc;flex-shrink:0}
.status-pill{display:inline-flex;align-items:center;padding:2px 8px;border-radius:999px;font-weight:700;font-size:10px;border:1px solid transparent}
.status-online{color:#166534;background:#dcfce7;border-color:#86efac}.status-offline{color:#991b1b;background:#fee2e2;border-color:#fecaca}

.chat-box{flex:1;overflow-y:auto;padding:12px;background:linear-gradient(180deg,#f8fbff,#f1f5f9);scroll-behavior:smooth;display:flex;flex-direction:column}
.chat-box::-webkit-scrollbar{width:4px}.chat-box::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:999px}

.chat-row{display:flex;margin:6px 0;gap:6px;animation:fadeIn .25s ease}.chat-row-user{justify-content:flex-end}.chat-row-bot{justify-content:flex-start}
@keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
.chat-avatar{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;align-self:flex-end}
.bubble{max-width:78%;padding:9px 13px;border-radius:16px;font-size:13px;line-height:1.5;word-wrap:break-word;position:relative}
.bubble-user{background:#dbeafe;border:1px solid #bfdbfe;color:#0f172a;border-bottom-right-radius:4px}
.bubble-bot{background:white;border:1px solid #e2e8f0;color:#1f2937;border-bottom-left-radius:4px}
.msg-time{display:block;font-size:10px;opacity:.6;margin-top:2px}

.typing-dots{display:flex;align-items:center;gap:4px;padding:4px 0}.typing-dots span:not(.typing-label){width:6px;height:6px;border-radius:50%;background:#3b82f6;animation:dotBounce 1.2s infinite}.typing-dots span:nth-child(2){animation-delay:.2s}.typing-dots span:nth-child(3){animation-delay:.4s}
@keyframes dotBounce{0%,100%{transform:translateY(0);opacity:.3}50%{transform:translateY(-4px);opacity:1}}
.typing-label{color:#94a3b8;font-size:11px;margin-left:6px}
.typing-cursor{display:inline-block;width:6px;height:16px;background:#3b82f6;vertical-align:bottom;margin-left:2px;animation:blink 1s steps(2,start) infinite}
@keyframes blink{0%{opacity:1}49%{opacity:1}50%{opacity:0}100%{opacity:0}}

.identity-form{background:white;border-radius:16px;padding:16px;border:1px solid #e2e8f0;text-align:center}
.chat-input{width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:10px;font-size:13px;margin-bottom:6px;outline:none;font-family:'Quicksand',sans-serif}.chat-input:focus{border-color:#60a5fa;box-shadow:0 0 0 3px rgba(59,130,246,0.1)}
.chat-start-btn{width:100%;padding:9px;background:linear-gradient(135deg,#2563eb,#3b82f6);color:white;border:none;border-radius:12px;font-size:13px;font-weight:600;cursor:pointer;font-family:'Quicksand',sans-serif}.chat-start-btn:hover{opacity:.9}

.welcome-cards{display:flex;flex-direction:column;gap:6px;margin:8px 0}
.welcome-card{display:flex;align-items:center;gap:10px;padding:10px 12px;background:white;border:1px solid #e2e8f0;border-radius:12px;cursor:pointer;transition:all .15s;animation:cardReveal .35s ease forwards}
.welcome-card:nth-child(1){animation-delay:.1s}.welcome-card:nth-child(2){animation-delay:.25s}.welcome-card:nth-child(3){animation-delay:.4s}.welcome-card:nth-child(4){animation-delay:.55s}
@keyframes cardReveal{to{opacity:1;transform:translateY(0)}}.welcome-card{opacity:0;transform:translateY(10px)}
.welcome-card:hover{border-color:#93c5fd;background:#f0f7ff;transform:translateY(-1px);box-shadow:0 3px 10px rgba(37,99,235,0.08)}
.welcome-card-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}

.suggestions{display:flex;flex-wrap:wrap;gap:6px;padding:6px 12px 2px;background:#f8fafc;border-top:1px solid #e2e8f0;flex-shrink:0}
.suggestion-btn{background:#eff6ff;border:1px solid #bfdbfe;border-radius:999px;padding:5px 12px;font-size:11px;color:#1d4ed8;cursor:pointer;font-weight:500;font-family:'Quicksand',sans-serif;transition:all .15s;white-space:nowrap}.suggestion-btn:hover{background:#dbeafe;transform:translateY(-1px)}

.scroll-bottom-btn{position:absolute;bottom:70px;right:16px;width:36px;height:36px;border-radius:50%;background:white;border:1px solid #e2e8f0;box-shadow:0 4px 12px rgba(15,23,42,0.12);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:18px;color:#64748b;z-index:5}

.chat-form{display:flex;gap:8px;padding:8px 12px;border-top:1px solid #e2e8f0;background:white;flex-shrink:0;align-items:center}
.chat-msg-input{flex:1;padding:8px 14px;border:1px solid #e2e8f0;border-radius:999px;outline:none;font-size:13px;font-family:'Quicksand',sans-serif}.chat-msg-input:focus{border-color:#60a5fa;box-shadow:0 0 0 3px rgba(59,130,246,0.1)}
.chat-send-btn{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#3b82f6);color:white;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .15s}.chat-send-btn:hover{opacity:.9;transform:scale(1.05)}.chat-send-btn:disabled{opacity:.4;cursor:not-allowed;transform:none}

.emoji-btn{background:transparent;border:none;font-size:20px;cursor:pointer;padding:4px 6px;border-radius:50%;transition:background .2s}.emoji-btn:hover{background:#f1f5f9}
.emoji-popup{position:absolute;bottom:48px;left:0;background:white;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 8px 24px rgba(15,23,42,0.15);padding:8px;max-height:180px;overflow-y:auto;z-index:10;width:240px;display:flex;flex-wrap:wrap;gap:4px}
.emoji-item{cursor:pointer;font-size:22px;padding:3px;border-radius:6px;transition:background .15s}.emoji-item:hover{background:#f1f5f9}

.md-content{line-height:1.55;font-size:13px}.md-content p{margin:0 0 8px}.md-content p:last-child{margin-bottom:0}.md-content strong{font-weight:700;color:#0f172a}.md-content h2,.md-content h3{font-weight:700;color:#0f172a;margin:10px 0 6px}.md-content ul,.md-content ol{margin:6px 0;padding-left:20px}.md-content li{margin-bottom:4px}.md-content ul{list-style:disc}.md-content ol{list-style:decimal}.md-content code{background:#f1f5f9;border:1px solid #e2e8f0;border-radius:4px;padding:1px 5px;font-size:12px;color:#e11d48}.md-content blockquote{border-left:3px solid #3b82f6;margin:8px 0;padding:4px 12px;background:#eff6ff;color:#334155;border-radius:0 6px 6px 0}.md-content a{color:#2563eb;text-decoration:underline}

body.dark-mode .chat-window{background:#1e293b;border-color:#334155}
body.dark-mode .chat-box{background:linear-gradient(180deg,#1e293b,#0f172a)}
body.dark-mode .bubble-bot{background:#334155;border-color:#475569;color:#e2e8f0}
body.dark-mode .bubble-user{background:#1d4ed8;border-color:#2563eb;color:#fff}
body.dark-mode .chat-form{background:#1e293b;border-color:#334155}
body.dark-mode .chat-msg-input{background:#334155;border-color:#475569;color:#e2e8f0}
body.dark-mode .welcome-card{background:#334155;border-color:#475569}
body.dark-mode .md-content strong{color:#f1f5f9}.dark-mode .md-content h2,.dark-mode .md-content h3{color:#f1f5f9}

@media(max-width:480px){.chat-window{width:calc(100vw - 28px);right:14px;bottom:80px;max-height:70vh}.chat-window.expanded{width:calc(100vw - 14px);right:7px;bottom:7px;transform:none;max-height:calc(100vh - 14px)}.chat-btn{border-radius:50%;width:56px;height:56px;padding:0;justify-content:center;right:16px;bottom:16px}.chat-btn-img{width:28px;height:28px}.chat-btn-text,.chat-btn-badge{display:none}}
</style>
