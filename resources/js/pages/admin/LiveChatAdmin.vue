<template>
<div class="livechat-container">
    <div class="toast-container">
        <div v-for="t in toasts" :key="t.id" class="toast-item">
            <span class="toast-icon">{{ t.icon }}</span>
            <span>{{ t.message }}</span>
        </div>
    </div>

    <div class="livechat-card p-4 sm:p-5 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold flex items-center gap-2" style="color:#0f172a">
                    <span>💬</span> Live Chat Admin
                </h2>
                <p class="text-sm mt-1" style="color:#64748b">Kelola percakapan dengan pengguna secara real-time</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <div class="flex items-center gap-2 text-sm" style="color:#64748b">
                    <span class="font-semibold" style="color:#334155">{{ activeSessionCount }}</span>
                    <span>sesi aktif</span>
                </div>
                <button class="mute-btn" :class="{muted:isMuted}" @click="toggleMute" title="Notifikasi suara">
                    <span>{{ isMuted ? '🔕' : '🔔' }}</span>
                </button>
                <div class="flex items-center gap-1 text-xs" style="color:#64748b">
                    <label class="font-medium" title="Tutup sesi idle secara otomatis">Auto-close:</label>
                    <select v-model="autoCloseMin" @change="saveAutoCloseSetting" class="auto-close-select">
                        <option value="0">Tidak pernah</option>
                        <option value="30">30 menit</option>
                        <option value="60">60 menit</option>
                        <option value="120">2 jam</option>
                        <option value="240">4 jam</option>
                    </select>
                </div>
                <label class="toggle-label">
                    <input type="checkbox" :checked="isOnline" @change="toggleOnline" class="sr-only peer" />
                    <div class="toggle-track peer-checked:bg-green-500"><div class="toggle-thumb peer-checked:translate-x-full"></div></div>
                    <span class="ms-2 text-sm font-semibold" :class="isOnline?'text-green-600':'text-red-600'">{{ isOnline ? 'Online' : 'Offline' }}</span>
                </label>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="livechat-card p-3 lg:col-span-1">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-sm" style="color:#0f172a">Daftar Sesi</h3>
                <span v-if="unreadTotal>0" class="unread-badge">{{ unreadTotal }}</span>
            </div>
            <div class="tab-bar mb-3">
                <button class="tab-btn" :class="{active:currentTab==='open'}" @click="switchTab('open')">Aktif</button>
                <button class="tab-btn" :class="{active:currentTab==='closed'}" @click="switchTab('closed')">Arsip</button>
            </div>
            <div class="search-wrapper mb-3">
                <span class="search-icon">🔍</span>
                <input type="text" v-model="searchQuery" placeholder="Cari nama atau instansi..." />
            </div>
            <div class="space-y-2 session-list custom-scrollbar">
                <template v-if="filteredSessions.length">
                    <template v-for="(group, gi) in groupedSessions" :key="gi">
                        <div class="group-label">{{ group.label }}</div>
                        <div v-for="s in group.sessions" :key="s.id" @click="selectSession(s)"
                            class="session-item" :class="{active:selectedSessionId===s.id, 'has-unread':s.unread_count>0, 'session-closed':s.status==='closed'}">
                            <div class="avatar-circle" :style="{background:getAvatarColor(s.nama||s.id)}">
                                {{ getInitials(s.nama||('U'+s.id)) }}
                                <span v-if="s.status==='open'" class="online-dot"></span>
                                <span v-else class="offline-dot"></span>
                            </div>
                            <div class="session-info">
                                <div class="name-row">
                                    <span class="name">{{ s.nama || 'User #'+s.id }}</span>
                                    <span class="instansi">{{ s.instansi }}</span>
                                </div>
                                <div class="last-msg">{{ s.last_message || (s.status==='closed'?'(sesi ditutup)':'Belum ada pesan') }}</div>
                            </div>
                            <div class="session-meta">
                                <span class="time">{{ formatShortTime(s.updated_at) }}</span>
                                <span v-if="s.unread_count>0" class="unread-badge">{{ s.unread_count }}</span>
                                <span v-else-if="s.status==='closed'" class="status-badge closed">Ditutup</span>
                                <span v-else class="status-badge open">Aktif</span>
                            </div>
                        </div>
                    </template>
                </template>
                <div v-else class="empty-state">
                    <div class="empty-icon">{{ searchQuery ? '🔍' : (currentTab==='closed' ? '📁' : '💬') }}</div>
                    <div class="empty-text">{{ searchQuery ? 'Tidak ditemukan' : (currentTab==='closed' ? 'Belum ada sesi arsip' : 'Belum ada sesi aktif') }}</div>
                    <div class="empty-sub">{{ searchQuery ? 'Coba kata kunci lain' : (currentTab==='closed' ? 'Sesi yang ditutup akan muncul di sini' : 'Tunggu hingga ada user yang terhubung') }}</div>
                </div>
            </div>
        </div>

        <div class="livechat-card p-3 lg:col-span-2 flex flex-col" style="min-height:600px">
            <div class="flex items-center justify-between border-b pb-3 mb-3" style="border-color:#f1f5f9">
                <div class="flex items-center gap-3 min-w-0">
                    <div v-if="selectedSession" class="avatar-circle" :style="{background:getAvatarColor(selectedSession.nama||selectedSession.id)}">
                        {{ getInitials(selectedSession.nama||('U'+selectedSession.id)) }}
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-semibold text-sm truncate" style="color:#0f172a">{{ selectedSession ? ('Chat dengan '+(selectedSession.nama||'User')) : 'Pilih sesi chat' }}</h3>
                        <p class="text-xs" style="color:#94a3b8">{{ selectedSession ? ('Sesi #'+selectedSession.id+(selectedSession.instansi?' • '+selectedSession.instansi:'')) : 'Klik sesi di sebelah kiri untuk mulai' }}</p>
                    </div>
                </div>
                <div v-if="selectedSession" class="flex items-center gap-2 flex-wrap">
                    <span class="status-badge" :class="selectedSession.status">{{ selectedSession.status==='open'?'Aktif':'Ditutup' }}</span>
                    <button v-if="selectedSession.status==='open'" @click="closeSession" class="action-btn red">✕ Tutup Sesi</button>
                    <button v-else @click="reopenSession" class="reopen-btn">Buka Kembali</button>
                    <div class="relative" ref="exportRef">
                        <button @click="showExportMenu=!showExportMenu" class="action-btn slate">📥 Export</button>
                        <div v-if="showExportMenu" class="export-menu">
                            <button @click="exportSession('pdf')">📄 Download PDF</button>
                            <button @click="exportSession('csv')">📊 Download CSV</button>
                        </div>
                    </div>
                    <button @click="toggleUserDetail" class="action-btn blue">👤 Info User</button>
                </div>
            </div>

            <div v-if="showUserDetail && selectedSession" class="user-detail-panel mb-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider" style="color:#475569">Informasi User</span>
                    <button @click="showUserDetail=false" class="text-xs" style="color:#94a3b8">✕</button>
                </div>
                <div v-if="userDetail">
                    <div class="detail-row"><span class="detail-label">Nama</span><span class="detail-value">{{ userDetail.user?.nama||'-' }}</span></div>
                    <div class="detail-row"><span class="detail-label">Instansi</span><span class="detail-value">{{ userDetail.user?.instansi||'-' }}</span></div>
                    <div class="detail-row"><span class="detail-label">Kontak</span><span class="detail-value"><a v-if="isWhatsApp(userDetail.user?.kontak)" :href="waLink(userDetail.user?.kontak)" target="_blank" class="wa-link">{{ userDetail.user?.kontak }}</a><span v-else>{{ userDetail.user?.kontak||'-' }}</span></span></div>
                    <div class="detail-row"><span class="detail-label">Terdaftar</span><span class="detail-value">{{ formatDateTime(userDetail.user?.created_at) }}</span></div>
                    <div class="detail-row"><span class="detail-label">Total Sesi</span><span class="detail-value">{{ userDetail.total_sessions||userDetail.sessions?.length||0 }}</span></div>
                    <div class="detail-row"><span class="detail-label">Sesi Aktif</span><span class="detail-value">{{ userDetail.open_sessions||0 }}</span></div>
                    <div class="detail-row"><span class="detail-label">Total Pesan</span><span class="detail-value">{{ userDetail.totalMessages||0 }}</span></div>
                    <div class="detail-row"><span class="detail-label">Terakhir Aktif</span><span class="detail-value">{{ formatDateTime(userDetail.last_active_at) }}</span></div>
                    <div class="detail-row"><span class="detail-label">IP Address</span><span class="detail-value">{{ userDetail.ip_address||'-' }}</span></div>
                </div>
                <div v-else class="text-center text-xs py-2" style="color:#94a3b8">Memuat...</div>
            </div>

            <div ref="msgBox" class="message-box custom-scrollbar">
                <template v-if="selectedSession">
                    <template v-for="(group, gi) in groupedMessages" :key="gi">
                        <div class="timestamp-divider"><span>{{ group.label }}</span></div>
                        <div v-for="m in group.messages" :key="m.id" class="chat-bubble-wrap" :class="m.sender_type==='admin'?'right':'left'">
                            <div class="chat-bubble" :class="'bubble-'+m.sender_type">
                                <span v-if="m.sender_type!=='system'" class="sender-label">{{ m.sender_type==='admin'?'Admin':(selectedSession.nama||'User') }}</span>
                                <div>{{ m.message }}</div>
                                <span class="chat-time" :class="m.sender_type==='admin'?'chat-time-right':'chat-time-left'">{{ formatChatTime(m.created_at) }}</span>
                            </div>
                        </div>
                    </template>
                    <div v-if="userTyping" class="typing-bubble">
                        <div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>
                        <span style="color:#0369a1;font-size:11px;margin-left:4px;">User sedang mengetik...</span>
                    </div>
                </template>
                <div v-else class="empty-state">
                    <div class="empty-icon">💬</div>
                    <div class="empty-text">Belum ada sesi dipilih</div>
                    <div class="empty-sub">Pilih sesi dari daftar di samping</div>
                </div>
            </div>

            <form v-if="selectedSession?.status==='open'" @submit.prevent="sendMsg" class="mt-3 flex gap-2 items-end">
                <div class="flex-1 relative">
                    <input v-model="adminInput" type="text" class="chat-input" placeholder="Ketik balasan admin..." autocomplete="off" @input="onTypingInput" />
                    <button type="button" class="emoji-btn" @click="showEmoji=!showEmoji" title="Emoji">😊</button>
                </div>
                <button type="submit" :disabled="!adminInput.trim()" class="send-btn">Kirim</button>
            </form>
            <div v-else-if="selectedSession?.status==='closed'" class="mt-3 text-center text-sm py-2" style="color:#94a3b8">Sesi ditutup (read-only)</div>
            <div v-if="showEmoji" class="emoji-picker">
                <span v-for="e in emojis" :key="e" class="emoji-item" @click="insertEmoji(e)">{{ e }}</span>
            </div>
        </div>
    </div>
</div>
</template>

<script setup>
import {ref,computed,onMounted,onUnmounted,nextTick,watch} from 'vue';
import api from '@/bootstrap.js';
import { swalConfirm } from '@/swal.js';

const sessions=ref([]);
const selectedSessionId=ref(null);
const selectedSession=ref(null);
const chatMessages=ref([]);
const adminInput=ref('');
const isOnline=ref(false);
const isMuted=ref(localStorage.getItem('livechat_muted')==='true');
const currentTab=ref('open');
const searchQuery=ref('');
const showExportMenu=ref(false);
const showUserDetail=ref(false);
const userDetail=ref(null);
const userTyping=ref(false);
const showEmoji=ref(false);
const autoCloseMin=ref(localStorage.getItem('livechat_autoclose')||'60');
const toasts=ref([]);
const msgBox=ref(null);
const exportRef=ref(null);
const lastMessageId=ref(0);

let sessionsTimer=null;
let messagesTimer=null;
let typingTimeout=null;
let lastAdminSendTime=0;
let titleBlinkTimer=null;
let faviconBlinkTimer=null;
let originalTitle=document.title;
let previousSessionMessages={};

const emojis=['😊','👍','❤️','🎉','🙏','😄','😢','😮','🔥','✅','❌','📌','📞','💡','⭐','👋','🤝','📝','🔔','💪'];

const activeSessionCount=computed(()=>sessions.value.filter(s=>s.status==='open').length);
const unreadTotal=computed(()=>sessions.value.reduce((a,s)=>a+(s.unread_count||0),0));

const filteredSessions=computed(()=>{
    let list=sessions.value;
    if(searchQuery.value){
        const q=searchQuery.value.toLowerCase();
        list=list.filter(s=>(s.nama||'').toLowerCase().includes(q)||(s.instansi||'').toLowerCase().includes(q));
    }
    return list;
});

const groupedSessions=computed(()=>{
    const groups={};
    for(const s of filteredSessions.value){
        const label=getTimeGroupLabel(s.updated_at||s.created_at)||'Lainnya';
        if(!groups[label])groups[label]=[];
        groups[label].push(s);
    }
    const order=['Hari Ini','Kemarin'];
    const labels=Object.keys(groups).sort((a,b)=>{
        const ai=order.indexOf(a),bi=order.indexOf(b);
        if(ai!==-1&&bi!==-1)return ai-bi;
        if(ai!==-1)return-1;if(bi!==-1)return 1;return 0;
    });
    return labels.map(l=>({label:l,sessions:groups[l]}));
});

const groupedMessages=computed(()=>{
    const groups=[];
    let lastLabel='';
    for(const m of chatMessages.value){
        const label=getTimeGroupLabel(m.created_at);
        if(label!==lastLabel){groups.push({label,messages:[]});lastLabel=label;}
        groups[groups.length-1].messages.push(m);
    }
    return groups;
});

function getInitials(name){return(String(name).split(/\s+/).map(w=>w[0]).join('').substring(0,2).toUpperCase())||'?';}
function getAvatarColor(name){const colors=['#3b82f6','#ef4444','#10b981','#f59e0b','#8b5cf6','#ec4899','#14b8a6','#f97316','#6366f1','#84cc16','#06b6d4','#d946ef','#0ea5e9','#eab308','#22c55e'];let h=0;for(const c of String(name))h=c.charCodeAt(0)+((h<<5)-h);return colors[Math.abs(h)%colors.length];}
function isWhatsApp(kontak){return kontak&&/^\d/.test(kontak);}
function waLink(kontak){return'https://wa.me/'+(kontak||'').replace(/^0/,'62');}

function formatShortTime(dt){
    if(!dt)return'';const d=new Date(String(dt).replace(' ','T'));
    if(isNaN(d))return'';const now=new Date();
    if(d.toDateString()===now.toDateString())return d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
    const yesterday=new Date(now);yesterday.setDate(yesterday.getDate()-1);
    if(d.toDateString()===yesterday.toDateString())return'Kemarin';
    return d.toLocaleDateString('id-ID',{day:'numeric',month:'short'});
}
function formatChatTime(dt){if(!dt)return'';const d=new Date(String(dt).replace(' ','T'));return isNaN(d)?'':d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});}
function formatDateTime(dt){if(!dt)return'-';const d=new Date(String(dt).replace(' ','T'));return isNaN(d)?dt:d.toLocaleString('id-ID',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});}
function getTimeGroupLabel(dt){
    if(!dt)return'';const d=new Date(String(dt).replace(' ','T'));
    if(isNaN(d))return'';const now=new Date();
    const today=new Date(now.getFullYear(),now.getMonth(),now.getDate());
    const dateOnly=new Date(d.getFullYear(),d.getMonth(),d.getDate());
    if(dateOnly.getTime()===today.getTime())return'Hari Ini';
    const yesterday=new Date(today);yesterday.setDate(yesterday.getDate()-1);
    if(dateOnly.getTime()===yesterday.getTime())return'Kemarin';
    const diff=Math.floor((today-dateOnly)/(1000*60*60*24));
    if(diff<=7)return diff+' Hari Lalu';
    return d.toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'});
}

function playAlertSound(times=2){
    if(isMuted.value)return;
    try{
        const ctx=new(window.AudioContext||window.webkitAudioContext)();
        for(let i=0;i<times;i++){
            setTimeout(()=>{const o=ctx.createOscillator();const g=ctx.createGain();o.connect(g);g.connect(ctx.destination);o.frequency.value=920;o.type='sine';g.gain.value=0.15;o.start();o.stop(ctx.currentTime+0.18);},i*220);
        }
    }catch(e){}
}

function showToast(message,icon='🔔'){
    const id=Date.now();
    toasts.value.push({id,message,icon});
    setTimeout(()=>{toasts.value=toasts.value.filter(t=>t.id!==id);},4000);
}

function updateFaviconBadge(count){
    try{
        const canvas=document.createElement('canvas');canvas.width=32;canvas.height=32;
        const ctx=canvas.getContext('2d');
        const img=new Image();img.crossOrigin='anonymous';
        img.onload=function(){
            ctx.drawImage(img,0,0,32,32);
            if(count>0){
                ctx.fillStyle='#ef4444';ctx.beginPath();ctx.arc(26,8,10,0,Math.PI*2);ctx.fill();
                ctx.fillStyle='#fff';ctx.font='bold 11px sans-serif';ctx.textAlign='center';ctx.textBaseline='middle';
                ctx.fillText(count>99?'99+':String(count),26,9);
            }
            let link=document.querySelector("link[rel*='icon']")||document.createElement('link');
            link.type='image/x-icon';link.rel='shortcut icon';link.href=canvas.toDataURL();
            document.getElementsByTagName('head')[0].appendChild(link);
        };
        img.src='/favicon.ico';
    }catch(e){}
}

function startTitleBlink(count){
    if(titleBlinkTimer)return;
    let on=true;
    titleBlinkTimer=setInterval(()=>{document.title=on?`🔴 (${count}) Live Chat - Admin`:'💬 Admin';on=!on;},1000);
}
function stopTitleBlink(){if(titleBlinkTimer){clearInterval(titleBlinkTimer);titleBlinkTimer=null;}document.title=originalTitle;}

function updateUnreadIndicator(count){
    updateFaviconBadge(count);
    if(count>0&&document.hidden)startTitleBlink(count);else stopTitleBlink();
}

function toggleMute(){isMuted.value=!isMuted.value;localStorage.setItem('livechat_muted',isMuted.value);}

async function loadSessions(){
    try{
        const{data}=await api.get('/chatbot/admin/sessions',{params:{status:currentTab.value}});
        const list=data.data||data.sessions||data||[];
        const prevMsgMap={};sessions.value.forEach(s=>{prevMsgMap[s.id]=s.last_message;});
        let newMsgCount=0;
        for(const s of list){
            if(prevMsgMap[s.id]!==undefined&&s.last_message&&s.last_message!==prevMsgMap[s.id]&&Date.now()-lastAdminSendTime>3000){
                newMsgCount++;
                showToast(`Pesan baru dari ${s.nama||'User'}`,'💬');
            }
        }
        if(newMsgCount>0)playAlertSound(3);
        sessions.value=list;
        if(selectedSessionId.value){
            const updated=list.find(s=>s.id===selectedSessionId.value);
            if(updated)selectedSession.value=updated;
        }
        const totalUnread=list.reduce((a,s)=>a+(s.unread_count||0),0);
        updateUnreadIndicator(totalUnread);
    }catch(e){}
}

async function ping(){try{await api.post('/chatbot/admin/ping');}catch(e){}}

async function toggleOnline(){
    try{const{data}=await api.post('/chatbot/admin/toggle-online',{online:!isOnline.value?1:0});isOnline.value=data.online;}catch(e){}
}

async function setOnline(val){
    try{const{data}=await api.post('/chatbot/admin/toggle-online',{online:val?1:0});isOnline.value=data.online;}catch(e){}
}

function switchTab(tab){currentTab.value=tab;selectedSessionId.value=null;selectedSession.value=null;chatMessages.value=[];loadSessions();}

async function selectSession(s){
    selectedSessionId.value=s.id;
    selectedSession.value=s;
    showUserDetail.value=false;
    userDetail.value=null;
    showExportMenu.value=false;
    lastMessageId.value=0;
    userTyping.value=false;
    try{
        const{data}=await api.get(`/chatbot/admin/sessions/${s.id}/messages`);
        chatMessages.value=data.messages||[];
        if(chatMessages.value.length)lastMessageId.value=chatMessages.value[chatMessages.value.length-1].id;
        scrollMsg();
        await api.post('/chatbot/admin/mark-read',{session_id:s.id});
        s.unread_count=0;
        if(s.status==='open')startMessagePolling();else stopMessagePolling();
    }catch(e){}
}

function startMessagePolling(){stopMessagePolling();messagesTimer=setInterval(pullMessages,2000);}
function stopMessagePolling(){if(messagesTimer){clearInterval(messagesTimer);messagesTimer=null;}}

async function pullMessages(){
    if(!selectedSession.value||selectedSession.value.status!=='open')return;
    try{
        const{data}=await api.get(`/chatbot/admin/sessions/${selectedSession.value.id}/messages`,{params:{after_id:lastMessageId.value}});
        userTyping.value=!!data.session?.user_is_typing;
        if(data.messages?.length){
            chatMessages.value.push(...data.messages);
            lastMessageId.value=data.messages[data.messages.length-1].id;
            const hasUserMsg=data.messages.some(m=>m.sender_type==='user');
            if(hasUserMsg)playAlertSound(1);
            scrollMsg();
        }
    }catch(e){}
}

async function sendMsg(){
    const msg=adminInput.value.trim();if(!msg||!selectedSession.value)return;
    adminInput.value='';showEmoji.value=false;lastAdminSendTime=Date.now();
    stopTitleBlink();
    try{
        const{data}=await api.post(`/chatbot/admin/sessions/${selectedSession.value.id}/messages`,{message:msg});
        chatMessages.value.push({id:data.message_id||Date.now(),sender_type:'admin',message:msg,created_at:new Date().toISOString()});
        if(selectedSession.value)previousSessionMessages[selectedSession.value.id]=msg;
        scrollMsg();
    }catch(e){}
}

function onTypingInput(){
    if(!selectedSession.value||selectedSession.value.status!=='open')return;
    clearTimeout(typingTimeout);
    api.post('/chatbot/admin/typing',{session_id:selectedSession.value.id,is_typing:true}).catch(()=>{});
    typingTimeout=setTimeout(()=>{api.post('/chatbot/admin/typing',{session_id:selectedSession.value.id,is_typing:false}).catch(()=>{});},3000);
}

async function closeSession(){
    if(!selectedSession.value)return;
    if(!(await swalConfirm('Tutup sesi live chat ini?','Ya, Tutup')))return;
    try{await api.post(`/chatbot/admin/sessions/${selectedSession.value.id}/close`);selectedSession.value.status='closed';stopMessagePolling();loadSessions();showToast('Sesi berhasil ditutup','✅');}catch(e){}
}
async function reopenSession(){
    if(!selectedSession.value)return;
    if(!(await swalConfirm('Buka kembali sesi ini?','Ya, Buka')))return;
    try{await api.post(`/chatbot/admin/sessions/${selectedSession.value.id}/reopen`);selectedSession.value.status='open';startMessagePolling();loadSessions();showToast('Sesi berhasil dibuka kembali','🔄');}catch(e){}
}

async function toggleUserDetail(){
    showUserDetail.value=!showUserDetail.value;
    if(showUserDetail.value&&selectedSession.value){
        try{const{data}=await api.get(`/chatbot/admin/user-detail/${selectedSession.value.chatbot_user_id}`);userDetail.value=data;}catch(e){}
    }
}

async function exportSession(format){
    showExportMenu.value=false;
    if(!selectedSession.value)return;
    try{
        const res=await api.get(`/chatbot/admin/sessions/${selectedSession.value.id}/export`,{params:{format},responseType:'blob'});
        const blob=new Blob([res.data]);
        const url=URL.createObjectURL(blob);
        const a=document.createElement('a');a.href=url;a.download=`chat-session-${selectedSession.value.id}.${format}`;a.click();URL.revokeObjectURL(url);
    }catch(e){showToast('Gagal export sesi','❌');}
}

function insertEmoji(e){adminInput.value+=e;showEmoji.value=false;}

function scrollMsg(){nextTick(()=>{if(msgBox.value)msgBox.value.scrollTop=msgBox.value.scrollHeight;});}

async function saveAutoCloseSetting(){
    localStorage.setItem('livechat_autoclose',autoCloseMin.value);
    try{await api.post('/chatbot/admin/settings',{livechat_auto_close_minutes:autoCloseMin.value});showToast('Auto-close disimpan: '+(autoCloseMin.value==0?'Tidak pernah':autoCloseMin.value+' menit'),'⚡');}catch(e){}
}

function handleClickOutside(e){if(exportRef.value&&!exportRef.value.contains(e.target))showExportMenu.value=false;}

onMounted(async()=>{
    try{Notification.requestPermission();}catch(e){}
    originalTitle=document.title;
    await loadSessions();
    ping();
    await setOnline(true);
    sessionsTimer=setInterval(()=>{ping();loadSessions();},5000);
    document.addEventListener('click',handleClickOutside);
    document.addEventListener('visibilitychange',()=>{
        if(!document.hidden)stopTitleBlink();
        else if(unreadTotal.value>0)startTitleBlink(unreadTotal.value);
    });
});
onUnmounted(()=>{
    if(sessionsTimer)clearInterval(sessionsTimer);
    stopMessagePolling();
    stopTitleBlink();
    document.removeEventListener('click',handleClickOutside);
});
</script>

<style scoped>
.livechat-container{font-family:'Quicksand',system-ui,sans-serif;}
.livechat-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;box-shadow:0 4px 20px rgba(15,23,42,.06);transition:box-shadow .2s;}
.livechat-card:hover{box-shadow:0 8px 30px rgba(15,23,42,.1);}
.session-item{display:flex;align-items:center;gap:10px;width:100%;text-align:left;border:1px solid #e2e8f0;border-radius:12px;padding:10px 12px;background:#fff;transition:all .15s;cursor:pointer;position:relative;}
.session-item:hover{border-color:#93c5fd;box-shadow:0 4px 12px rgba(59,130,246,.12);transform:translateY(-1px);}
.session-item.active{border-color:#3b82f6;background:#eff6ff;box-shadow:inset 3px 0 0 #2563eb,0 4px 12px rgba(59,130,246,.15);}
.session-item.has-unread{border-left:4px solid #ef4444;background:#fef2f2;}
.session-item.has-unread.active{background:#fef2f2;border-left-color:#ef4444;}
.session-item.session-closed{opacity:.7;border-left:4px solid #94a3b8;}
.session-item.session-closed.active{opacity:1;background:#f8fafc;}
.avatar-circle{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;color:#fff;flex-shrink:0;position:relative;}
.online-dot{position:absolute;bottom:0;right:0;width:10px;height:10px;border-radius:50%;border:2px solid #fff;background:#22c55e;}
.offline-dot{position:absolute;bottom:0;right:0;width:10px;height:10px;border-radius:50%;border:2px solid #fff;background:#94a3b8;}
.session-info{flex:1;min-width:0;}
.name-row{display:flex;flex-direction:column;gap:1px;}
.name-row .name{font-weight:600;font-size:13px;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.name-row .instansi{font-size:11px;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.last-msg{font-size:12px;color:#94a3b8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;}
.session-meta{display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0;}
.session-meta .time{font-size:10px;color:#94a3b8;white-space:nowrap;}
.unread-badge{display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;border-radius:999px;background:#ef4444;color:#fff;font-size:10px;font-weight:700;padding:0 5px;}
.status-badge{display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:600;padding:2px 6px;border-radius:6px;}
.status-badge.open{background:#dcfce7;color:#16a34a;}
.status-badge.closed{background:#f1f5f9;color:#64748b;}
.chat-bubble-wrap{display:flex;margin-bottom:4px;}
.chat-bubble-wrap.right{justify-content:flex-end;}
.chat-bubble-wrap.left{justify-content:flex-start;}
.chat-bubble{max-width:88%;padding:10px 14px;border-radius:16px;font-size:13px;line-height:1.5;position:relative;word-wrap:break-word;animation:bubbleIn .25s ease-out;}
@keyframes bubbleIn{from{opacity:0;transform:translateY(8px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
.bubble-user{background:#f1f5f9;border:1px solid #e2e8f0;border-bottom-left-radius:4px;}
.bubble-user::before{content:'';position:absolute;left:-6px;bottom:6px;width:10px;height:10px;background:#f1f5f9;border-left:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;transform:rotate(45deg);}
.bubble-admin{background:#dbeafe;border:1px solid #bfdbfe;border-bottom-right-radius:4px;}
.bubble-admin::after{content:'';position:absolute;right:-6px;bottom:6px;width:10px;height:10px;background:#dbeafe;border-right:1px solid #bfdbfe;border-bottom:1px solid #bfdbfe;transform:rotate(-45deg);}
.bubble-system{background:#fef3c7;border:1px solid #fde68a;border-radius:12px;max-width:92%;text-align:center;font-size:12px;color:#92400e;margin:8px auto;}
.sender-label{font-weight:600;font-size:11px;display:block;margin-bottom:2px;}
.bubble-admin .sender-label{color:#1d4ed8;}
.bubble-user .sender-label{color:#475569;}
.chat-time{display:block;font-size:10px;opacity:.6;margin-top:4px;}
.chat-time-right{text-align:right;}
.chat-time-left{text-align:left;}
.timestamp-divider{display:flex;align-items:center;gap:12px;margin:16px 0;}
.timestamp-divider::before,.timestamp-divider::after{content:'';flex:1;height:1px;background:#e2e8f0;}
.timestamp-divider span{font-size:11px;color:#94a3b8;font-weight:500;white-space:nowrap;}
.group-label{font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;padding:0 4px;margin-top:12px;margin-bottom:4px;}
.group-label:first-child{margin-top:0;}
.search-wrapper{position:relative;}
.search-wrapper input{width:100%;padding:8px 12px 8px 34px;border:1px solid #e2e8f0;border-radius:10px;font-size:13px;outline:none;transition:border-color .2s;font-family:'Quicksand',sans-serif;}
.search-wrapper input:focus{border-color:#60a5fa;box-shadow:0 0 0 3px rgba(59,130,246,.1);}
.search-icon{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:14px;}
.empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 20px;color:#94a3b8;}
.empty-icon{font-size:48px;margin-bottom:12px;opacity:.5;}
.empty-text{font-size:14px;font-weight:500;}
.empty-sub{font-size:12px;margin-top:4px;}
.custom-scrollbar::-webkit-scrollbar{width:5px;}
.custom-scrollbar::-webkit-scrollbar-track{background:transparent;}
.custom-scrollbar::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:999px;}
.custom-scrollbar::-webkit-scrollbar-thumb:hover{background:#94a3b8;}
.typing-bubble{display:flex;align-items:center;gap:4px;padding:8px 14px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:16px;max-width:fit-content;margin-right:auto;border-bottom-left-radius:4px;position:relative;margin-top:8px;}
.typing-bubble::before{content:'';position:absolute;left:-6px;bottom:6px;width:10px;height:10px;background:#f0f9ff;border-left:1px solid #bae6fd;border-bottom:1px solid #bae6fd;transform:rotate(45deg);}
.typing-dot{width:6px;height:6px;border-radius:50%;background:#0284c7;display:inline-block;animation:typingBounce 1.2s ease-in-out infinite;}
.typing-dot:nth-child(2){animation-delay:.2s;}
.typing-dot:nth-child(3){animation-delay:.4s;}
@keyframes typingBounce{0%,100%{transform:translateY(0);opacity:.3}50%{transform:translateY(-3px);opacity:1}}
.toast-container{position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;}
.toast-item{background:#1e293b;color:#fff;padding:12px 18px;border-radius:12px;font-size:13px;box-shadow:0 8px 24px rgba(15,23,42,.2);animation:toastIn .3s ease-out;max-width:320px;display:flex;align-items:center;gap:10px;}
.toast-icon{font-size:18px;}
@keyframes toastIn{from{opacity:0;transform:translateX(40px)}to{opacity:1;transform:translateX(0)}}
.tab-bar{display:flex;gap:0;margin-bottom:10px;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;}
.tab-btn{flex:1;padding:7px 10px;font-size:12px;font-weight:600;border:none;cursor:pointer;background:#f8fafc;color:#64748b;transition:all .15s;text-align:center;font-family:'Quicksand',sans-serif;}
.tab-btn+.tab-btn{border-left:1px solid #e2e8f0;}
.tab-btn.active{background:#3b82f6;color:#fff;}
.tab-btn:hover:not(.active){background:#e2e8f0;}
.user-detail-panel{border:1px solid #e2e8f0;border-radius:12px;padding:14px;background:#f8fafc;}
.detail-row{display:flex;justify-content:space-between;padding:5px 0;font-size:12px;border-bottom:1px solid #f1f5f9;}
.detail-row:last-child{border-bottom:none;}
.detail-label{color:#64748b;font-weight:500;}
.detail-value{color:#0f172a;font-weight:600;text-align:right;max-width:60%;word-break:break-all;}
.wa-link{color:#2563eb;text-decoration:underline;}
.mute-btn{background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;padding:5px 10px;font-size:12px;cursor:pointer;transition:all .15s;}
.mute-btn:hover{background:#e2e8f0;}
.mute-btn.muted{background:#fef2f2;border-color:#fecaca;color:#dc2626;}
.reopen-btn{padding:4px 12px;background:#10b981;color:#fff;border:none;border-radius:8px;font-size:11px;font-weight:600;cursor:pointer;transition:background .15s;font-family:'Quicksand',sans-serif;}
.reopen-btn:hover{background:#059669;}
.action-btn{padding:4px 12px;border-radius:8px;font-size:11px;font-weight:600;cursor:pointer;transition:all .15s;border:1px solid;font-family:'Quicksand',sans-serif;}
.action-btn.red{background:#fef2f2;color:#dc2626;border-color:#fecaca;}
.action-btn.red:hover{background:#fee2e2;}
.action-btn.slate{background:#f8fafc;color:#475569;border-color:#e2e8f0;}
.action-btn.slate:hover{background:#f1f5f9;}
.action-btn.blue{background:#eff6ff;color:#2563eb;border-color:#bfdbfe;}
.action-btn.blue:hover{background:#dbeafe;}
.export-menu{position:absolute;right:0;top:100%;margin-top:4px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 8px 24px rgba(15,23,42,.12);z-index:50;overflow:hidden;min-width:150px;}
.export-menu button{width:100%;padding:8px 14px;text-align:left;font-size:12px;font-weight:500;background:none;border:none;cursor:pointer;font-family:'Quicksand',sans-serif;color:#334155;}
.export-menu button:hover{background:#eff6ff;}
.message-box{flex:1;min-height:380px;max-height:480px;overflow-y:auto;background:linear-gradient(to bottom,rgba(248,250,252,.5),#fff);border-radius:12px;padding:12px;}
.chat-input{width:100%;border:1px solid #e2e8f0;border-radius:12px;padding:10px 40px 10px 14px;font-size:13px;outline:none;transition:border-color .2s;font-family:'Quicksand',sans-serif;}
.chat-input:focus{border-color:#60a5fa;box-shadow:0 0 0 3px rgba(59,130,246,.1);}
.emoji-btn{position:absolute;right:8px;top:50%;transform:translateY(-50%);font-size:18px;cursor:pointer;background:none;border:none;padding:4px;}
.send-btn{padding:10px 20px;background:#2563eb;color:#fff;border:none;border-radius:12px;font-size:13px;font-weight:600;cursor:pointer;transition:all .15s;font-family:'Quicksand',sans-serif;}
.send-btn:hover{background:#1d4ed8;}
.send-btn:disabled{opacity:.5;cursor:not-allowed;}
.emoji-picker{margin-top:8px;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 8px 24px rgba(15,23,42,.12);display:flex;flex-wrap:wrap;gap:4px;max-height:120px;overflow-y:auto;}
.emoji-item{cursor:pointer;padding:4px 6px;border-radius:6px;font-size:18px;transition:background .1s;}
.emoji-item:hover{background:#f1f5f9;}
.auto-close-select{border:1px solid #e2e8f0;border-radius:8px;padding:2px 8px;font-size:12px;background:#fff;outline:none;font-family:'Quicksand',sans-serif;}
.auto-close-select:focus{border-color:#60a5fa;box-shadow:0 0 0 2px rgba(59,130,246,.1);}
.toggle-label{display:inline-flex;align-items:center;cursor:pointer;}
.toggle-track{width:44px;height:24px;background:#cbd5e1;border-radius:999px;position:relative;transition:background .2s;}
.toggle-thumb{position:absolute;top:2px;left:2px;width:20px;height:20px;background:#fff;border-radius:50%;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.15);}
.session-list{max-height:520px;overflow-y:auto;padding-right:4px;}
@media(max-width:1023px){.session-list{max-height:200px;}}
</style>