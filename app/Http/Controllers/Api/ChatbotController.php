<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{AiConfiguration, ChatbotUser, ChatbotResponse, ChatbotIntent, ChatbotConversationLog, ChatbotSettings};
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Session, Cache, Log, Http, Schema};
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ChatbotController extends Controller
{
    private const DAILY_TOKEN_LIMIT = 1000000;

    public function checkIdentity(Request $request)
    {
        return response()->json(['has_identity' => $request->session()->has('chatbot_user_id')]);
    }

    public function saveIdentity(Request $request)
    {
        $v = $request->validate(['nama'=>'required|string|max:255','instansi'=>'required|string|max:255','kontak'=>'required|string|max:255']);
        $userId = DB::table('chatbot_user')->insertGetId(['nama'=>$v['nama'],'instansi'=>$v['instansi'],'kontak'=>$v['kontak'],'waktu'=>now()]);
        $request->session()->put(['chatbot_user_id'=>$userId,'nama'=>$v['nama'],'instansi'=>$v['instansi'],'kontak'=>$v['kontak']]);
        return response()->json(['status'=>'ok']);
    }

    public function getUsername(Request $request)
    {
        $nama = $request->session()->get('nama');
        if (!$nama && $request->session()->has('chatbot_user_id')) {
            $user = DB::table('chatbot_user')->where('id', $request->session()->get('chatbot_user_id'))->first();
            if ($user) { $nama = $user->nama; $request->session()->put('nama', $nama); }
        }
        return response()->json(['nama' => $nama]);
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['chatbot_user_id','nama','instansi','kontak','step']);
        return response()->json(['status' => 'ok']);
    }

    public function respond(Request $request)
    {
        $raw = trim(strip_tags((string)$request->input('message')));
        if (empty($raw) || strlen($raw) > 2000) return response('Pesan tidak valid.', 422);
        $msg = strtolower($raw);
        return response($this->getResponse($msg, $raw, $request));
    }

    public function respondStream(Request $request)
    {
        $raw = trim(strip_tags((string)$request->input('message')));
        if (empty($raw) || strlen($raw) > 2000) return response('Pesan tidak valid.', 422);
        $msg = strtolower($raw);

        $intent = $this->tryIntentMatch($msg, $raw, $request);
        if ($intent !== null) return $this->sseResponse(function() use ($intent) {
            echo "data: ".json_encode(['type'=>'token','content'=>$intent])."\n\n";
            echo "data: ".json_encode(['type'=>'done'])."\n\n";
        });

        if ($q = $this->getDailyAiQuotaExceededMessage()) {
            $this->logConversation($request, $raw, $q, 'quota');
            return $this->sseResponse(function() use ($q) {
                echo "data: ".json_encode(['type'=>'token','content'=>$q])."\n\n";
                echo "data: ".json_encode(['type'=>'done'])."\n\n";
            });
        }

        $aiService = new AIService();
        $config = $aiService->getConfig();
        if (!$config) return $this->sseResponse(function() {
            echo "data: ".json_encode(['type'=>'token','content'=>'Maaf, konfigurasi AI belum diatur.'])."\n\n";
            echo "data: ".json_encode(['type'=>'done'])."\n\n";
        });

        $systemPrompt = $this->generateSystemPrompt();
        $history = $this->getConversationHistory($request, 5);
        $messages = [['role'=>'system','content'=>$systemPrompt]];
        foreach ($history as $h) { $messages[]=['role'=>'user','content'=>$h->user_message]; $messages[]=['role'=>'assistant','content'=>$h->bot_response]; }
        $messages[] = ['role'=>'user','content'=>$msg];

        $request->session()->save();
        $fullResponse = '';
        $chatbotUserId = $request->session()->get('chatbot_user_id');

        return $this->sseResponse(function() use ($aiService, $messages, $raw, &$fullResponse, $chatbotUserId) {
            $start = microtime(true);
            try {
                foreach ($aiService->chatStream($messages) as $token) {
                    $fullResponse .= $token;
                    echo "data: ".json_encode(['type'=>'token','content'=>$token])."\n\n";
                    if (ob_get_level()>0) ob_flush(); flush();
                }
                echo "data: ".json_encode(['type'=>'done'])."\n\n";
                if (ob_get_level()>0) ob_flush(); flush();
                $elapsed = (int)round((microtime(true)-$start)*1000);
                $usage = $aiService->getLastUsage();
                $this->logConversationRaw($raw, $fullResponse, 'ai', [
                    'prompt_tokens'=>$usage['prompt']??null,'completion_tokens'=>$usage['completion']??null,
                    'total_tokens'=>$usage['total']??null,'response_time_ms'=>$elapsed,
                ], $chatbotUserId);
            } catch (\Throwable $e) {
                Log::error('AI Stream: '.$e->getMessage());
                echo "data: ".json_encode(['type'=>'token','content'=>'Maaf, terjadi kesalahan.'])."\n\n";
                echo "data: ".json_encode(['type'=>'done'])."\n\n";
                if (ob_get_level()>0) ob_flush(); flush();
            }
        });
    }

    public function suggestedQuestions()
    {
        $q = Cache::remember('chatbot_suggested', 3600, function() {
            $keywords = DB::table('chatbot_responses')->orderBy('id')->limit(6)->pluck('keyword')->toArray();
            if (empty($keywords)) $keywords = ['Apa itu BPMP?','Layanan BPMP','Program prioritas','Kontak BPMP','Alamat BPMP NTB','Pengaduan'];
            return $keywords;
        });
        return response()->json(['questions' => $q]);
    }

    public function adminStatus()
    {
        if (!Schema::hasTable('chat_admin_presence')) return response()->json(['online'=>false]);
        $online = DB::table('chat_admin_presence')->where('is_online',true)->where('last_seen_at','>=',now()->subSeconds(60))->first();
        return response()->json(['online'=>(bool)$online]);
    }

    public function startLiveChat(Request $request)
    {
        $uid = $request->session()->get('chatbot_user_id');
        if (!$uid) return response()->json(['status'=>'error','message'=>'Isi identitas terle dahulu.'], 422);
        if (!Schema::hasTable('chat_sessions')) return response()->json(['status'=>'error','message'=>'Live chat belum siap.'], 503);
        $sessionId = DB::table('chat_sessions')->insertGetId(['chatbot_user_id'=>$uid,'assigned_admin_id'=>1,'status'=>'open','created_at'=>now(),'updated_at'=>now()]);
        return response()->json(['status'=>'ok','session_id'=>$sessionId]);
    }

    public function sendLiveMessage(Request $request)
    {
        $v = $request->validate(['session_id'=>'required|integer','message'=>'required|string|max:2000']);
        $uid = $request->session()->get('chatbot_user_id');
        if (!$uid) return response()->json(['status'=>'error','message'=>'Sesi tidak ditemukan.'], 422);
        $msgId = DB::table('chat_messages')->insertGetId(['session_id'=>$v['session_id'],'sender_type'=>'user','sender_id'=>$uid,'message'=>$v['message'],'is_read'=>false,'created_at'=>now(),'updated_at'=>now()]);
        return response()->json(['status'=>'ok','message_id'=>$msgId]);
    }

    public function getLiveMessages(Request $request)
    {
        $uid = $request->session()->get('chatbot_user_id');
        if (!$uid) return response()->json(['status'=>'error'], 422);
        $sid = (int)$request->query('session_id');
        $afterId = (int)$request->query('after_id', 0);
        $session = DB::table('chat_sessions')->where('id',$sid)->where('chatbot_user_id',$uid)->first();
        if (!$session) return response()->json(['status'=>'error','message'=>'Sesi tidak ditemukan.'], 404);
        if (($session->status??'open')!=='open') return response()->json(['status'=>'closed','message'=>'Sesi ditutup admin.','messages'=>[]]);
        $messages = DB::table('chat_messages')->where('session_id',$sid)->where('id','>',$afterId)->orderBy('id')->get();
        DB::table('chat_messages')->where('session_id',$sid)->where('sender_type','admin')->where('is_read',false)->where('id','>',$afterId)->update(['is_read'=>true,'updated_at'=>now()]);
        return response()->json(['status'=>'ok','admin_online'=>true,'admin_typing'=>(bool)($session->admin_is_typing??false),'messages'=>$messages,'unread_count'=>0]);
    }

    public function sendTyping(Request $request)
    {
        if (Schema::hasTable('chat_sessions')) {
            DB::table('chat_sessions')->where('id',$request->input('session_id'))->update(['admin_is_typing'=>(bool)$request->input('is_typing'),'updated_at'=>now()]);
        }
        return response()->json(['status'=>'ok']);
    }

    // ===== AI CONFIG ADMIN =====
    public function aiConfigIndex() { return response()->json(AiConfiguration::orderBy('id')->get()); }
    public function aiConfigStore(Request $request)
    {
        $v = $request->validate(['name'=>'required|string|max:255','provider_type'=>'required|in:openai,openai_compatible,ollama','base_url'=>'required|string|max:500','api_key'=>'nullable|string','chat_model'=>'required|string|max:255','max_tokens'=>'required|integer|min:1','temperature'=>'required|numeric|min:0|max:2','is_active'=>'boolean']);
        if ($request->input('is_active')) { AiConfiguration::where('is_active',true)->update(['is_active'=>false]); AIService::clearConfigCache(); }
        return response()->json(AiConfiguration::create($v), 201);
    }
    public function aiConfigUpdate(Request $request, int $id)
    {
        $item = AiConfiguration::findOrFail($id);
        $v = $request->validate(['name'=>'required|string|max:255','provider_type'=>'required|in:openai,openai_compatible,ollama','base_url'=>'required|string|max:500','api_key'=>'nullable|string','chat_model'=>'required|string|max:255','max_tokens'=>'required|integer|min:1','temperature'=>'required|numeric|min:0|max:2','is_active'=>'boolean']);
        if ($request->input('is_active') && !$item->is_active) { AiConfiguration::where('is_active',true)->where('id','!=',$id)->update(['is_active'=>false]); AIService::clearConfigCache(); }
        $item->update($v);
        AIService::clearConfigCache();
        return response()->json($item);
    }
    public function aiConfigDestroy(int $id) { AiConfiguration::findOrFail($id)->delete(); AIService::clearConfigCache(); return response()->json(['message'=>'Dihapus']); }
    public function aiConfigTest(int $id) { $config = AiConfiguration::findOrFail($id); $result = AIService::testConfig($config); return response()->json($result); }

    // ===== KEYWORD RESPONSES ADMIN =====
    public function keywordIndex() { return response()->json(DB::table('chatbot_responses')->orderBy('id')->get()); }
    public function keywordStore(Request $request)
    {
        $v = $request->validate(['keyword'=>'required|string|max:255','response'=>'required|string']);
        $id = DB::table('chatbot_responses')->insertGetId(array_merge($v, ['created_at'=>now(),'updated_at'=>now()]));
        return response()->json(['id'=>$id]+$v, 201);
    }
    public function keywordUpdate(Request $request, int $id)
    {
        $v = $request->validate(['keyword'=>'required|string|max:255','response'=>'required|string']);
        DB::table('chatbot_responses')->where('id',$id)->update(array_merge($v, ['updated_at'=>now()]));
        return response()->json(DB::table('chatbot_responses')->where('id',$id)->first());
    }
    public function keywordDestroy(int $id) { DB::table('chatbot_responses')->where('id',$id)->delete(); return response()->json(['message'=>'Dihapus']); }

    // ===== INTENT ADMIN =====
    public function intentIndex() { return response()->json(DB::table('chatbot_intent')->orderBy('id')->get()); }
    public function intentStore(Request $request)
    {
        $v = $request->validate(['keyword'=>'required|string|max:128','response'=>'required|string']);
        $id = DB::table('chatbot_intent')->insertGetId(array_merge($v, ['created_at'=>now(),'updated_at'=>now()]));
        return response()->json(['id'=>$id]+$v, 201);
    }
    public function intentUpdate(Request $request, int $id)
    {
        $v = $request->validate(['keyword'=>'required|string|max:128','response'=>'required|string']);
        DB::table('chatbot_intent')->where('id',$id)->update(array_merge($v, ['updated_at'=>now()]));
        return response()->json(DB::table('chatbot_intent')->where('id',$id)->first());
    }
    public function intentDestroy(int $id) { DB::table('chatbot_intent')->where('id',$id)->delete(); return response()->json(['message'=>'Dihapus']); }

    // ===== ANALYTICS =====
    public function analytics()
    {
        $total = DB::table('chatbot_conversation_logs')->count();
        $today = DB::table('chatbot_conversation_logs')->whereDate('created_at', Carbon::today())->count();
        $sources = DB::table('chatbot_conversation_logs')->select('source', DB::raw('COUNT(*) as total'))->groupBy('source')->pluck('total','source');
        $topQueries = DB::table('chatbot_conversation_logs')->select('user_message', DB::raw('COUNT(*) as count'))->whereNotNull('user_message')->groupBy('user_message')->orderByDesc('count')->limit(10)->get();
        $users = DB::table('chatbot_user')->count();
        return response()->json(compact('total','today','sources','topQueries','users'));
    }

    // ===== HELPER: Get WA setting =====
    private function getWASetting(string $key, string $default = ''): string
    {
        return Cache::remember('wa_setting_'.$key, 600, function() use ($key, $default) {
            $row = DB::table('chatbot_settings')->where('key', $key)->first();
            return $row ? $row->value : $default;
        });
    }

    // ===== WHATSAPP GATEWAY SETTINGS =====
    public function whatsappSettings()
    {
        $settings = [
            'wa_domain' => $this->getWASetting('wa_domain', 'https://wapi1.gdoank.my.id'),
            'wa_spmb_number' => $this->getWASetting('wa_spmb_number', '6281805297478@c.us'),
            'wa_group_name' => $this->getWASetting('wa_group_name', 'GROUP ULT BPMP NTB 2024'),
            'wa_spmb_enabled' => $this->getWASetting('wa_spmb_enabled', '1'),
        ];
        return response()->json($settings);
    }

    public function whatsappSettingsUpdate(Request $request)
    {
        $v = $request->validate([
            'wa_domain' => 'required|string|max:500',
            'wa_spmb_number' => 'required|string|max:50',
            'wa_group_name' => 'required|string|max:255',
            'wa_spmb_enabled' => 'required|in:0,1',
        ]);
        foreach ($v as $key => $value) {
            DB::table('chatbot_settings')->updateOrInsert(['key'=>$key],['value'=>$value,'updated_at'=>now()]);
        }
        Cache::flush();
        return response()->json(['status'=>'ok','message'=>'Pengaturan WhatsApp disimpan']);
    }

    // ===== PRIVATE HELPERS =====
    private function getResponse($msg, $raw, $request)
    {
        if ($msg === 'menu') { $request->session()->forget('step'); return 'MENU_UTAMA'; }
        if (in_array($msg, ['hubungi admin','4','admin','pengaduan'])) { $request->session()->put('step','hubungi_admin'); return 'Silakan tulis pengaduan atau pertanyaan Anda. Kami akan meneruskannya ke Admin BPMP Provinsi NTB.'; }
        if (in_array($msg, ['lapor spmb','3','pengaduan spmb'])) { $request->session()->put('step','lapor_spmb'); return 'Silakan tulis pengaduan Anda terkait SPMB. Data Anda dijamin rahasia.'; }
        $step = $request->session()->get('step');
        if (in_array($step, ['hubungi_admin','lapor_spmb'])) return $this->handleComplaint($step, $raw, $request);

        $result = Cache::remember('kw:'.md5($msg), 3600, fn()=>DB::table('chatbot_responses')->where('keyword','like','%'.$msg.'%')->orWhere('id',$msg)->first());
        if ($result) { $this->logConversation($request, $raw, $result->response, 'keyword'); return $result->response; }

        if ($q = $this->getDailyAiQuotaExceededMessage()) { $this->logConversation($request, $raw, $q, 'quota'); return $q; }

        $ragMeta = null; $metrics = null;
        $resp = $this->askAI($raw, $request, $ragMeta, $metrics);
        $this->logConversation($request, $raw, $resp, 'ai', $ragMeta, $metrics);
        return $resp;
    }

    private function tryIntentMatch($msg, $raw, $request)
    {
        if ($msg === 'menu') { $request->session()->forget('step'); $this->logConversation($request, $msg, 'MENU_UTAMA', 'menu'); return 'MENU_UTAMA'; }
        if (in_array($msg, ['hubungi admin','4','admin','pengaduan'])) { $request->session()->put('step','hubungi_admin'); $r = 'Silakan tulis pengaduan atau pertanyaan Anda.'; $this->logConversation($request,$msg,$r,'menu'); return $r; }
        if (in_array($msg, ['lapor spmb','3','pengaduan spmb'])) { $request->session()->put('step','lapor_spmb'); $r = 'Silakan tulis pengaduan Anda terkait SPMB.'; $this->logConversation($request,$msg,$r,'menu'); return $r; }
        $step = $request->session()->get('step');
        if (in_array($step, ['hubungi_admin','lapor_spmb'])) return $this->handleComplaint($step, $raw, $request);
        $result = Cache::remember('kw:'.md5($msg), 3600, fn()=>DB::table('chatbot_responses')->where('keyword','like','%'.$msg.'%')->orWhere('id',$msg)->first());
        if ($result) { $this->logConversation($request,$msg,$result->response,'keyword'); return $result->response; }
        return null;
    }

    private function handleComplaint($step, $raw, $request)
    {
        $request->session()->forget('step');
        $nama = $request->session()->get('nama','-');
        $instansi = $request->session()->get('instansi','-');
        $kontak = $request->session()->get('kontak','-');
        try { DB::table('user_messages')->insert(['user_message'=>$raw,'nama'=>$nama,'instansi'=>$instansi,'kontak'=>$kontak]); } catch (\Throwable $e) {}
        $formatted = "Pertanyaan dari chatbot Intan:\nNama: $nama\nInstansi: $instansi\nKontak: $kontak\nPesan: $raw";

        if ($step === 'lapor_spmb') {
            $sent = $this->sendToLocalSPMB($formatted);
            $resp = $sent ? 'Pengaduan SPMB telah diterima. Tim kami akan segera menghubungi Anda. Ketik <b>menu</b> untuk kembali.' : 'Pengaduan Anda sudah kami catat. Ketik <b>menu</b> untuk kembali.';
        } else {
            $sent = $this->sendToGroup($formatted);
            $resp = $sent ? 'Pengaduan telah diterima. Admin akan segera menghubungi Anda. Ketik <b>menu</b> untuk kembali.' : 'Pengaduan Anda sudah kami catat. Ketik <b>menu</b> untuk kembali.';
        }

        $this->logConversation($request, $raw, $resp, 'intent');
        return $resp;
    }

    private function sendToLocalSPMB(string $message): bool
    {
        $domain = $this->getWASetting('wa_domain', 'https://wapi1.gdoank.my.id');
        $number = $this->getWASetting('wa_spmb_number', '6281805297478@c.us');
        if (!$this->getWASetting('wa_spmb_enabled', '1')) return false;
        try {
            $response = Http::timeout(8)->post($domain . '/api/whatsapp/send-message', ['number'=>$number,'message'=>$message]);
            if ($response->failed()) { Log::warning('WA gateway (SPMB) gagal: HTTP '.$response->status()); return false; }
            return true;
        } catch (\Throwable $e) { Log::warning('WA gateway (SPMB) exception: '.$e->getMessage()); return false; }
    }

    private function sendToGroup(string $message): bool
    {
        $domain = $this->getWASetting('wa_domain', 'https://wapi1.gdoank.my.id');
        $group = $this->getWASetting('wa_group_name', 'GROUP ULT BPMP NTB 2024');
        try {
            $response = Http::timeout(8)->post($domain . '/send-group', ['groupName'=>$group,'message'=>$message]);
            return $response->successful();
        } catch (\Throwable $e) { Log::warning('WA gateway (group) exception: '.$e->getMessage()); return false; }
    }

    private function askAI($prompt, $request, ?array &$ragMeta = null, ?array &$metrics = null)
    {
        try {
            $systemPrompt = $this->generateSystemPrompt();
            $history = $this->getConversationHistory($request, 5);
            $messages = [['role'=>'system','content'=>$systemPrompt]];
            foreach ($history as $h) { $messages[]=['role'=>'user','content'=>$h->user_message]; $messages[]=['role'=>'assistant','content'=>$h->bot_response]; }
            $messages[] = ['role'=>'user','content'=>$prompt];

            $aiService = new AIService();
            $start = microtime(true);
            $response = $aiService->chat($messages);
            $usage = $aiService->getLastUsage();
            $metrics = ['prompt'=>$usage['prompt']??null,'completion'=>$usage['completion']??null,'total'=>$usage['total']??null,'response_time_ms'=>(int)round((microtime(true)-$start)*1000)];
            return $response;
        } catch (\Throwable $e) { Log::error('AI Chat: '.$e->getMessage()); return 'Maaf, terjadi kesalahan saat menghubungi server AI.'; }
    }

    private function generateSystemPrompt()
    {
        $intents = Cache::remember('chatbot_intents', 3600, fn()=>DB::table('chatbot_intent')->get());
        $list = '';
        foreach ($intents as $i) $list .= "- Jika user mengetik '{$i->keyword}', balas dengan: {$i->response}\n";
        return "Anda adalah SI INTAN, asisten ramah BPMP Provinsi NTB. Gunakan bahasa Indonesia yang santun dan profesional.\n\nIntent khusus:\n{$list}\n\nFORMAT: Gunakan markdown (**bold**, list, ### heading, > kutipan, tabel). Jangan gunakan heading # level 1.";
    }

    private function getConversationHistory($request, $limit = 5)
    {
        $uid = $request->session()->get('chatbot_user_id');
        if (!$uid) return [];
        return DB::table('chatbot_conversation_logs')->where('chatbot_user_id',$uid)->orderByDesc('id')->limit($limit)->get(['user_message','bot_response'])->reverse()->values();
    }

    private function logConversation($request, $userMsg, $botResp, $source, $ragMeta = null, $metrics = null)
    {
        try {
            $row = ['chatbot_user_id'=>$request->session()->get('chatbot_user_id'),'user_message'=>$userMsg,'bot_response'=>$botResp,'source'=>$source,'ip_address'=>$request->ip(),'user_agent'=>$request->userAgent(),'created_at'=>now()];
            if ($metrics) { $row['prompt_tokens']=$metrics['prompt']??null; $row['completion_tokens']=$metrics['completion']??null; $row['total_tokens']=$metrics['total']??null; $row['response_time_ms']=$metrics['response_time_ms']??null; }
            DB::table('chatbot_conversation_logs')->insert($row);
        } catch (\Throwable $e) { Log::warning('Log conversation gagal: '.$e->getMessage()); }
    }

    private function logConversationRaw($userMsg, $botResp, $source, $metrics = null, $chatbotUserId = null)
    {
        try {
            $row = ['chatbot_user_id'=>$chatbotUserId,'user_message'=>$userMsg,'bot_response'=>$botResp,'source'=>$source,'ip_address'=>request()->ip(),'user_agent'=>request()->userAgent(),'created_at'=>now()];
            if ($metrics) { $row['prompt_tokens']=$metrics['prompt_tokens']??null; $row['completion_tokens']=$metrics['completion_tokens']??null; $row['total_tokens']=$metrics['total_tokens']??null; $row['response_time_ms']=$metrics['response_time_ms']??null; }
            DB::table('chatbot_conversation_logs')->insert($row);
        } catch (\Throwable $e) { Log::warning('Log conversation raw gagal: '.$e->getMessage()); }
    }

    private function getDailyAiQuotaExceededMessage($extra = 0)
    {
        $today = Carbon::today();
        $used = (int)DB::table('chatbot_conversation_logs')->whereDate('created_at',$today)->sum('total_tokens');
        if ($used + $extra >= self::DAILY_TOKEN_LIMIT) return 'Maaf, kuota AI harian sudah habis. Silakan coba lagi besok atau hubungi admin.';
        return null;
    }

    private function sseResponse(callable $cb)
    {
        return response()->stream(function() use ($cb) {
            @ini_set('output_buffering','off'); @ini_set('zlib.output_compression',false);
            while (ob_get_level()>0) ob_end_flush();
            if (function_exists('apache_setenv')) @apache_setenv('no-gzip','1');
            ob_implicit_flush(true); $cb();
        }, 200, ['Content-Type'=>'text/event-stream','Cache-Control'=>'no-cache','X-Accel-Buffering'=>'no']);
    }

    // ===== ADMIN LIVE CHAT =====
    public function adminLiveDashboard()
    {
        if (!Schema::hasTable('chat_admin_presence')) {
            Schema::create('chat_admin_presence', function($t) { $t->id(); $t->boolean('is_online')->default(false); $t->timestamp('last_seen_at')->nullable(); $t->timestamps(); });
        }
        return response()->json(['status'=>'ok','message'=>'Live dashboard ready']);
    }

    public function adminPing(Request $request)
    {
        if (Schema::hasTable('chat_admin_presence')) {
            $adminId = $request->user()?->id ?? 1;
            $row = DB::table('chat_admin_presence')->where('admin_id',$adminId)->first();
            if ($row) {
                DB::table('chat_admin_presence')->where('admin_id',$adminId)->update(['last_seen_at'=>now(),'updated_at'=>now()]);
            } else {
                DB::table('chat_admin_presence')->insert(['admin_id'=>$adminId,'is_online'=>true,'last_seen_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
            }
        }
        return response()->json(['status'=>'ok']);
    }

    public function adminToggleOnline(Request $request)
    {
        if (!Schema::hasTable('chat_admin_presence')) return response()->json(['status'=>'error','message'=>'Tabel tidak ada'], 500);
        $adminId = $request->user()?->id ?? 1;
        $row = DB::table('chat_admin_presence')->where('admin_id',$adminId)->first();
        if ($request->has('online')) {
            $newState = (bool) $request->input('online');
        } else {
            $newState = $row ? !$row->is_online : true;
        }
        if ($row) {
            DB::table('chat_admin_presence')->where('admin_id',$adminId)->update(['is_online'=>$newState,'last_seen_at'=>now(),'updated_at'=>now()]);
        } else {
            DB::table('chat_admin_presence')->insert(['admin_id'=>$adminId,'is_online'=>$newState,'last_seen_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
        }
        return response()->json(['status'=>'ok','online'=>$newState]);
    }

    public function adminSessions(Request $request)
    {
        if (!Schema::hasTable('chat_sessions')) return response()->json(['sessions'=>[]]);
        $status = $request->query('status');
        $query = DB::table('chat_sessions')->leftJoin('chatbot_user','chat_sessions.chatbot_user_id','=','chatbot_user.id')
            ->select('chat_sessions.*','chatbot_user.nama','chatbot_user.instansi','chatbot_user.kontak');
        if ($status) $query->where('chat_sessions.status',$status);
        $sessions = $query->orderByDesc('chat_sessions.updated_at')->paginate(20);

        foreach ($sessions as $s) {
            $s->unread_count = DB::table('chat_messages')->where('session_id',$s->id)->where('sender_type','user')->where('is_read',false)->count();
            $s->last_message = DB::table('chat_messages')->where('session_id',$s->id)->orderByDesc('id')->first()?->message;
        }
        return response()->json($sessions);
    }

    public function adminMessages(int $sessionId, Request $request)
    {
        $afterId = (int)$request->query('after_id', 0);
        $messages = DB::table('chat_messages')->where('session_id',$sessionId)->where('id','>',$afterId)->orderBy('id')->get();
        $session = DB::table('chat_sessions')->where('id',$sessionId)->first();
        DB::table('chat_messages')->where('session_id',$sessionId)->where('sender_type','user')->where('is_read',false)->update(['is_read'=>true,'updated_at'=>now()]);
        return response()->json(['status'=>'ok','messages'=>$messages,'session'=>$session]);
    }

    public function adminSendMessage(Request $request, int $sessionId)
    {
        $v = $request->validate(['message'=>'required|string|max:5000']);
        $adminId = $request->user()?->id ?? 1;
        $msgId = DB::table('chat_messages')->insertGetId([
            'session_id'=>$sessionId,'sender_type'=>'admin','sender_id'=>$adminId,
            'message'=>$v['message'],'is_read'=>false,'created_at'=>now(),'updated_at'=>now(),
        ]);
        DB::table('chat_sessions')->where('id',$sessionId)->update(['updated_at'=>now()]);
        return response()->json(['status'=>'ok','message_id'=>$msgId]);
    }

    public function adminCloseSession(int $sessionId)
    {
        DB::table('chat_sessions')->where('id',$sessionId)->update(['status'=>'closed','updated_at'=>now()]);
        return response()->json(['status'=>'ok','message'=>'Sesi ditutup']);
    }

    public function adminReopenSession(int $sessionId)
    {
        DB::table('chat_sessions')->where('id',$sessionId)->update(['status'=>'open','updated_at'=>now()]);
        return response()->json(['status'=>'ok','message'=>'Sesi dibuka kembali']);
    }

    public function adminExportSession(int $sessionId, Request $request)
    {
        $session = DB::table('chat_sessions')->where('id',$sessionId)->first();
        if (!$session) return response()->json(['status'=>'error','message'=>'Sesi tidak ditemukan'], 404);
        $user = DB::table('chatbot_user')->where('id',$session->chatbot_user_id)->first();
        $messages = DB::table('chat_messages')->where('session_id',$sessionId)->orderBy('id')->get();
        $format = $request->query('format', 'csv');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.transcript-pdf', compact('session', 'sessionId', 'user', 'messages'))
                ->setPaper('a4', 'portrait')
                ->setOptions(['isHtml5ParserEnabled'=>true, 'isRemoteEnabled'=>true, 'defaultFont'=>'serif', 'dpi'=>150, 'margin_top'=>15, 'margin_bottom'=>15, 'margin_left'=>20, 'margin_right'=>20]);
            return $pdf->stream("chat-session-{$sessionId}.pdf");
        }

        $lines = ["=== CHAT LOG ===","Sesi: #$sessionId","User: ".($user->nama??'-')." (".($user->instansi??'-').")","Tanggal: ".($session->created_at??''),"",""];
        foreach ($messages as $m) { $sender = $m->sender_type==='admin'?'Admin':($user->nama??'User'); $lines[] = "[{$m->created_at}] $sender: {$m->message}"; }
        $lines[] = ""; $lines[] = "=== END ===";

        if ($format === 'csv') {
            $csv = "Sender,Message,Timestamp\n";
            foreach ($messages as $m) {
                $sender = $m->sender_type==='admin'?'Admin':($user->nama??'User');
                $csv .= '"'.str_replace('"','""',$sender).'","'.str_replace('"','""',$m->message).'","'.str_replace('"','""',$m->created_at)."\"\n";
            }
            return response($csv)->header('Content-Type','text/csv')->header('Content-Disposition','attachment; filename="chat-session-'.$sessionId.'.csv"');
        }

        $content = implode("\n",$lines);
        return response($content)->header('Content-Type','text/plain')->header('Content-Disposition','attachment; filename="chat-session-'.$sessionId.'.txt"');
    }

    public function adminUserDetail(int $chatbotUserId)
    {
        $user = DB::table('chatbot_user')->where('id',$chatbotUserId)->first();
        if (!$user) return response()->json(['status'=>'error','message'=>'User tidak ditemukan'], 404);
        $sessions = DB::table('chat_sessions')->where('chatbot_user_id',$chatbotUserId)->orderByDesc('created_at')->get();
        $totalMessages = DB::table('chatbot_conversation_logs')->where('chatbot_user_id',$chatbotUserId)->count();
        $openSessions = DB::table('chat_sessions')->where('chatbot_user_id',$chatbotUserId)->where('status','open')->count();
        $lastActive = DB::table('chatbot_conversation_logs')->where('chatbot_user_id',$chatbotUserId)->orderByDesc('created_at')->first();
        $lastIp = DB::table('chatbot_conversation_logs')->where('chatbot_user_id',$chatbotUserId)->whereNotNull('ip_address')->orderByDesc('created_at')->first();
        return response()->json([
            'status' => 'ok',
            'user' => $user,
            'sessions' => $sessions,
            'totalMessages' => $totalMessages,
            'total_sessions' => $sessions->count(),
            'open_sessions' => $openSessions,
            'last_active_at' => $lastActive?->created_at,
            'ip_address' => $lastIp?->ip_address,
        ]);
    }

    public function adminGetSettings()
    {
        if (!Schema::hasTable('chatbot_settings')) return response()->json(['settings'=>[]]);
        $settings = DB::table('chatbot_settings')->pluck('value','key');
        return response()->json(['settings'=>$settings]);
    }

    public function adminUpdateSettings(Request $request)
    {
        if (!Schema::hasTable('chatbot_settings')) return response()->json(['status'=>'error'], 500);
        foreach ($request->all() as $key => $value) {
            if ($key === '_token') continue;
            $existing = DB::table('chatbot_settings')->where('key',$key)->first();
            if ($existing) DB::table('chatbot_settings')->where('key',$key)->update(['value'=>$value,'updated_at'=>now()]);
            else DB::table('chatbot_settings')->insert(['key'=>$key,'value'=>$value,'created_at'=>now(),'updated_at'=>now()]);
        }
        return response()->json(['status'=>'ok','message'=>'Pengaturan disimpan']);
    }

    public function adminTyping(Request $request)
    {
        $sessionId = $request->input('session_id');
        $isTyping = (bool)$request->input('is_typing');
        if (Schema::hasTable('chat_sessions')) {
            DB::table('chat_sessions')->where('id',$sessionId)->update(['admin_is_typing'=>$isTyping,'updated_at'=>now()]);
        }
        return response()->json(['status'=>'ok']);
    }

    public function adminAnalytics()
    {
        $now = Carbon::now();
        $today = $now->copy()->startOfDay();
        $weekStart = $now->copy()->subDays(7);
        $monthStart = $now->copy()->startOfMonth();

        // Today
        $todayTotal = DB::table('chatbot_conversation_logs')->whereDate('created_at', $today)->count();
        $todayUniqueUsers = DB::table('chatbot_conversation_logs')->whereDate('created_at', $today)->distinct('chatbot_user_id')->count('chatbot_user_id');
        $todayAiCalls = DB::table('chatbot_conversation_logs')->whereDate('created_at', $today)->where('source', 'ai')->count();
        $todayKeyword = DB::table('chatbot_conversation_logs')->whereDate('created_at', $today)->where('source', 'keyword')->count();

        // This week
        $weekTotal = DB::table('chatbot_conversation_logs')->where('created_at', '>=', $weekStart)->count();
        $weekActiveUsers = DB::table('chatbot_conversation_logs')->where('created_at', '>=', $weekStart)->distinct('chatbot_user_id')->count('chatbot_user_id');

        // This month
        $monthNewUsers = DB::table('chatbot_user')->where('waktu', '>=', $monthStart)->count();
        $monthLiveChat = Schema::hasTable('chat_sessions') ? DB::table('chat_sessions')->where('created_at', '>=', $monthStart)->count() : 0;
        $monthOpenSessions = Schema::hasTable('chat_sessions') ? DB::table('chat_sessions')->where('status', 'open')->count() : 0;
        $monthComplaints = DB::table('chatbot_conversation_logs')->where('created_at', '>=', $monthStart)->whereIn('source', ['hubungi_admin', 'lapor_spmb'])->count();

        // Totals
        $total = DB::table('chatbot_conversation_logs')->count();
        $sources = DB::table('chatbot_conversation_logs')->select('source', DB::raw('COUNT(*) as total'))->groupBy('source')->pluck('total', 'source');
        $topQueries = DB::table('chatbot_conversation_logs')->select('user_message', DB::raw('COUNT(*) as count'))
            ->whereNotNull('user_message')->where('created_at', '>=', $monthStart)
            ->groupBy('user_message')->orderByDesc('count')->limit(10)->get();
        $users = DB::table('chatbot_user')->count();
        $sessions = Schema::hasTable('chat_sessions') ? DB::table('chat_sessions')->count() : 0;
        $totalTokens = DB::table('chatbot_conversation_logs')->sum('total_tokens');
        $avgResponseTime = DB::table('chatbot_conversation_logs')->whereNotNull('response_time_ms')->avg('response_time_ms');

        // Today sources
        $todaySources = DB::table('chatbot_conversation_logs')
            ->select('source', DB::raw('COUNT(*) as total'))
            ->whereDate('created_at', $today)
            ->groupBy('source')
            ->pluck('total', 'source');

        // Hourly activity today
        $hourlyActivity = DB::table('chatbot_conversation_logs')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00') as hour, COUNT(*) as count")
            ->whereDate('created_at', $today)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Token usage this month
        $tokensMonth = (int) DB::table('chatbot_conversation_logs')->where('created_at', '>=', $monthStart)->sum('total_tokens');
        $tokensToday = (int) DB::table('chatbot_conversation_logs')->whereDate('created_at', $today)->sum('total_tokens');
        $promptMonth = (int) DB::table('chatbot_conversation_logs')->where('created_at', '>=', $monthStart)->sum('prompt_tokens');
        $completionMonth = (int) DB::table('chatbot_conversation_logs')->where('created_at', '>=', $monthStart)->sum('completion_tokens');

        return response()->json([
            'today' => $todayTotal,
            'today_unique_users' => $todayUniqueUsers,
            'today_ai_calls' => $todayAiCalls,
            'today_keyword' => $todayKeyword,
            'week_total' => $weekTotal,
            'week_active_users' => $weekActiveUsers,
            'month_new_users' => $monthNewUsers,
            'month_livechat' => $monthLiveChat,
            'month_open_sessions' => $monthOpenSessions,
            'month_complaints' => $monthComplaints,
            'total' => $total,
            'sources' => $sources,
            'today_sources' => $todaySources,
            'topQueries' => $topQueries,
            'users' => $users,
            'sessions' => $sessions,
            'totalTokens' => $totalTokens,
            'avgResponseTime' => $avgResponseTime,
            'hourly_activity' => $hourlyActivity,
            'tokens_month' => $tokensMonth,
            'tokens_today' => $tokensToday,
            'prompt_tokens_month' => $promptMonth,
            'completion_tokens_month' => $completionMonth,

            // Per-user token usage (top 50)
            'user_usage' => (function() use ($monthStart, $today) {
                $dailyLimit = 1000000;
                $pricing = DB::table('ai_configurations')->where('is_active', 1)->first();
                $inputPrice = $pricing && $pricing->input_price_per_1m ? (float) $pricing->input_price_per_1m : null;
                $outputPrice = $pricing && $pricing->output_price_per_1m ? (float) $pricing->output_price_per_1m : null;
                $hasPricing = $inputPrice !== null && $outputPrice !== null;

                $users = DB::table('chatbot_conversation_logs as cl')
                    ->leftJoin('chatbot_user as u', 'cl.chatbot_user_id', '=', 'u.id')
                    ->select(
                        'cl.chatbot_user_id',
                        'u.nama',
                        'u.instansi',
                        'u.kontak',
                        DB::raw('COUNT(*) as ai_calls'),
                        DB::raw('COALESCE(SUM(cl.total_tokens), 0) as total_tokens'),
                        DB::raw('COALESCE(SUM(cl.prompt_tokens), 0) as prompt_tokens'),
                        DB::raw('COALESCE(SUM(cl.completion_tokens), 0) as completion_tokens'),
                        DB::raw('ROUND(AVG(cl.response_time_ms)) as avg_response_ms'),
                        DB::raw('MAX(cl.created_at) as last_active_at'),
                        DB::raw('MAX(cl.ip_address) as last_ip')
                    )
                    ->whereNotNull('cl.chatbot_user_id')
                    ->where('cl.created_at', '>=', $monthStart)
                    ->groupBy('cl.chatbot_user_id', 'u.nama', 'u.instansi', 'u.kontak')
                    ->orderByDesc('total_tokens')
                    ->limit(50)
                    ->get();

                $todayQuotas = DB::table('chatbot_conversation_logs')
                    ->select('chatbot_user_id', DB::raw('SUM(total_tokens) as used_today'))
                    ->whereDate('created_at', $today)
                    ->whereNotNull('chatbot_user_id')
                    ->groupBy('chatbot_user_id')
                    ->pluck('used_today', 'chatbot_user_id');

                return $users->map(function($u) use ($todayQuotas, $dailyLimit, $inputPrice, $outputPrice, $hasPricing) {
                    $usedToday = (int) ($todayQuotas[$u->chatbot_user_id] ?? 0);
                    $remaining = max(0, $dailyLimit - $usedToday);
                    $prompt = (int) $u->prompt_tokens;
                    $completion = (int) $u->completion_tokens;
                    $cost = $hasPricing ? (($prompt / 1000000 * $inputPrice) + ($completion / 1000000 * $outputPrice)) : null;

                    return [
                        'chatbot_user_id' => $u->chatbot_user_id,
                        'nama' => $u->nama ?? '-',
                        'instansi' => $u->instansi ?? '-',
                        'kontak_masked' => $u->kontak ? substr($u->kontak, 0, 4) . '****' : '-',
                        'ai_calls' => (int) $u->ai_calls,
                        'total_tokens' => (int) $u->total_tokens,
                        'prompt_tokens' => $prompt,
                        'completion_tokens' => $completion,
                        'avg_response_ms' => (int) ($u->avg_response_ms ?? 0),
                        'last_active_at' => $u->last_active_at,
                        'last_ip' => $u->last_ip ?? '-',
                        'estimated_cost' => $cost,
                        'has_pricing' => $hasPricing,
                        'daily_quota' => [
                            'used' => $usedToday,
                            'limit' => $dailyLimit,
                            'remaining' => $remaining,
                            'percent' => $dailyLimit > 0 ? min(100, round($usedToday / $dailyLimit * 100)) : 0,
                            'exceeded' => $usedToday >= $dailyLimit,
                        ],
                    ];
                });
            })(),

            // Knowledge base health
            'kb_health' => [
                'total_documents' => Schema::hasTable('knowledge_documents') ? DB::table('knowledge_documents')->count() : 0,
                'active_documents' => Schema::hasTable('knowledge_documents') ? DB::table('knowledge_documents')->where('status', 'active')->count() : 0,
                'total_chunks' => Schema::hasTable('knowledge_chunks') ? DB::table('knowledge_chunks')->count() : 0,
                'null_embeddings' => Schema::hasTable('knowledge_chunks') ? DB::table('knowledge_chunks')->whereNull('embedding')->count() : 0,
                'last_generated_at' => Schema::hasTable('knowledge_chunks') ? DB::table('knowledge_chunks')->max('updated_at') : null,
            ],
        ]);
    }

    public function adminAnalyticsPage()
    {
        return response()->json(['status'=>'ok','message'=>'Use Vue admin analytics page']);
    }

    public function adminAnalyticsReport(Request $request)
    {
        $from = $request->query('from', Carbon::now()->subDays(30)->toDateString());
        $to = $request->query('to', Carbon::now()->toDateString());
        $daily = DB::table('chatbot_conversation_logs')
            ->select(DB::raw('DATE(created_at) as date'),DB::raw('COUNT(*) as total'),DB::raw('SUM(total_tokens) as tokens'))
            ->whereBetween(DB::raw('DATE(created_at)'),[$from,$to])->groupBy('date')->orderBy('date')->get();
        $sources = DB::table('chatbot_conversation_logs')->select('source',DB::raw('COUNT(*) as total'))
            ->whereBetween(DB::raw('DATE(created_at)'),[$from,$to])->groupBy('source')->pluck('total','source');
        return response()->json(compact('daily','sources','from','to'));
    }

    public function resetUserTokenQuota(Request $request, int $userId)
    {
        DB::table('chatbot_conversation_logs')->where('chatbot_user_id',$userId)->whereDate('created_at',Carbon::today())->update(['total_tokens'=>0]);
        return response()->json(['status'=>'ok','message'=>'Quota direset']);
    }

    public function adminAnalyticsPdf(Request $request)
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);
        $from = Carbon::create($year, $month, 1)->startOfDay();
        $to = Carbon::create($year, $month, 1)->endOfMonth();

        $total = DB::table('chatbot_conversation_logs')->count();
        $today = DB::table('chatbot_conversation_logs')->whereDate('created_at', Carbon::today())->count();
        $thisWeek = DB::table('chatbot_conversation_logs')->where('created_at', '>=', Carbon::now()->subDays(7))->count();
        $sources = DB::table('chatbot_conversation_logs')->select('source', DB::raw('COUNT(*) as total'))->whereBetween('created_at', [$from, $to])->groupBy('source')->pluck('total', 'source');
        $topQueries = DB::table('chatbot_conversation_logs')->select('user_message', DB::raw('COUNT(*) as count'))->whereNotNull('user_message')->whereBetween('created_at', [$from, $to])->groupBy('user_message')->orderByDesc('count')->limit(10)->get();
        $users = DB::table('chatbot_user')->count();
        $sessions = Schema::hasTable('chat_sessions') ? DB::table('chat_sessions')->count() : 0;
        $openSessions = Schema::hasTable('chat_sessions') ? DB::table('chat_sessions')->where('status', 'open')->count() : 0;
        $totalTokens = DB::table('chatbot_conversation_logs')->sum('total_tokens');
        $avgResponseTime = DB::table('chatbot_conversation_logs')->whereNotNull('response_time_ms')->avg('response_time_ms');

        $daily = DB::table('chatbot_conversation_logs')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'), DB::raw('COALESCE(SUM(total_tokens),0) as tokens'))
            ->whereBetween('created_at', [$from, $to])->groupBy(DB::raw('DATE(created_at)'))->orderBy('date')->get();

        $analytics = compact('total', 'today', 'thisWeek', 'users', 'sessions', 'openSessions', 'totalTokens', 'avgResponseTime');
        $bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $periodLabel = $bulan[$month - 1] . ' ' . $year;
        $fromStr = $from->translatedFormat('d F Y');
        $toStr = $to->translatedFormat('d F Y');
        $fmt = fn($v) => number_format((float)($v ?? 0), 0, ',', '.');

        $pdf = Pdf::loadView('admin.analytics-pdf', compact('analytics', 'sources', 'topQueries', 'daily', 'periodLabel', 'fromStr', 'toStr', 'fmt'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'serif',
                'dpi' => 150,
                'margin_top' => 15,
                'margin_bottom' => 15,
                'margin_left' => 20,
                'margin_right' => 20,
            ]);

        $filename = 'Analytics-Chatbot-SI-INTAN-' . $bulan[$month - 1] . '-' . $year . '.pdf';
        return $pdf->stream($filename);
    }

    public function adminUnreadSessions()
    {
        if (!Schema::hasTable('chat_sessions')) return response()->json(['sessions'=>[]]);
        $sessions = DB::table('chat_sessions')
            ->leftJoin('chatbot_user','chat_sessions.chatbot_user_id','=','chatbot_user.id')
            ->leftJoin(DB::raw('(SELECT session_id, COUNT(*) as unread FROM chat_messages WHERE sender_type=\'user\' AND is_read=0 GROUP BY session_id) as um'),'chat_sessions.id','=','um.session_id')
            ->select('chat_sessions.*','chatbot_user.nama','chatbot_user.instansi',DB::raw('COALESCE(um.unread,0) as unread_count'))
            ->where('chat_sessions.status','open')
            ->having('unread_count','>',0)
            ->orderByDesc('chat_sessions.updated_at')->get();
        return response()->json(['sessions'=>$sessions]);
    }

    public function adminMarkRead(Request $request)
    {
        $sessionId = $request->input('session_id');
        DB::table('chat_messages')->where('session_id',$sessionId)->where('sender_type','user')->where('is_read',false)->update(['is_read'=>true,'updated_at'=>now()]);
        return response()->json(['status'=>'ok']);
    }
}
