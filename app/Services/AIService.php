<?php
namespace App\Services;
use App\Models\AiConfiguration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AIService
{
    private $config;
    private $timeout = 120;
    private $lastUsage = ['prompt'=>null,'completion'=>null,'total'=>null];

    public function __construct(?AiConfiguration $config = null)
    {
        $this->config = $config ?? self::getActiveConfig();
    }

    public function getLastUsage(): array { return $this->lastUsage; }
    private function resetUsage(): void { $this->lastUsage = ['prompt'=>null,'completion'=>null,'total'=>null]; }

    private function setUsageFromArray(?array $u): void
    {
        if (!$u) return;
        $p = $u['prompt_tokens'] ?? $u['prompt_eval_count'] ?? null;
        $c = $u['completion_tokens'] ?? $u['eval_count'] ?? null;
        $t = $u['total_tokens'] ?? (($p !== null || $c !== null) ? (int)$p + (int)$c : null);
        $this->lastUsage = ['prompt'=>$p?(int)$p:null,'completion'=>$c?(int)$c:null,'total'=>$t?(int)$t:null];
    }

    public static function getActiveConfig()
    {
        return Cache::remember('ai_active_config', 300, fn() => AiConfiguration::where('is_active', true)->first());
    }

    public static function clearConfigCache() { Cache::forget('ai_active_config'); }
    public function getConfig() { return $this->config; }

    public function testConnection()
    {
        if (!$this->config) return ['status'=>'error','message'=>'Tidak ada konfigurasi AI aktif.'];
        try {
            $response = $this->chat([['role'=>'user','content'=>'Hello, respond with exactly: "Connection OK"']], 50, 0);
            return ['status'=>'ok','message'=>$response,'provider'=>$this->config->provider_type,'model'=>$this->config->chat_model];
        } catch (\Throwable $e) {
            return ['status'=>'error','message'=>$e->getMessage(),'provider'=>$this->config->provider_type];
        }
    }

    public static function testConfig(AiConfiguration $config)
    {
        $service = new self($config);
        return $service->testConnection();
    }

    public function chat($messages, $maxTokens = null, $temperature = null)
    {
        $this->resetUsage();
        if (!$this->config) return 'Maaf, konfigurasi AI belum diatur.';
        $maxTokens = $maxTokens ?? $this->config->max_tokens;
        $temperature = $temperature ?? $this->config->temperature;
        return match($this->config->provider_type) {
            'ollama' => $this->callOllama($messages, $maxTokens, $temperature),
            default => $this->callOpenAICompatible($messages, $maxTokens, $temperature),
        };
    }

    public function chatStream($messages, $maxTokens = null, $temperature = null): \Generator
    {
        $this->resetUsage();
        if (!$this->config) { yield 'Maaf, konfigurasi AI belum diatur.'; return; }
        $maxTokens = $maxTokens ?? $this->config->max_tokens;
        $temperature = $temperature ?? $this->config->temperature;
        yield from match($this->config->provider_type) {
            'ollama' => $this->streamOllama($messages, $maxTokens, $temperature),
            default => $this->streamOpenAICompatible($messages, $maxTokens, $temperature),
        };
    }

    private function callOpenAICompatible($messages, $maxTokens, $temperature)
    {
        $url = rtrim($this->config->base_url, '/') . '/chat/completions';
        $http = Http::timeout($this->timeout);
        if ($this->config->api_key) $http = $http->withToken($this->config->api_key);

        $response = $http->post($url, [
            'model' => $this->config->chat_model,
            'messages' => $messages,
            'max_completion_tokens' => $maxTokens,
            'temperature' => $temperature,
        ]);

        if ($response->failed()) throw new \Exception('API error: ' . $response->body());
        $this->setUsageFromArray($response->json('usage'));
        return $response->json('choices.0.message.content') ?? 'Maaf, terjadi kesalahan.';
    }

    private function callOllama($messages, $maxTokens, $temperature)
    {
        $url = rtrim($this->config->base_url, '/') . '/api/chat';
        $response = Http::timeout($this->timeout)->post($url, [
            'model' => $this->config->chat_model,
            'messages' => array_map(fn($m) => ['role'=>$m['role'],'content'=>$m['content']], $messages),
            'stream' => false,
            'options' => ['num_predict'=>$maxTokens,'temperature'=>$temperature],
        ]);
        if ($response->failed()) throw new \Exception('Ollama error: ' . $response->body());
        $this->setUsageFromArray(['prompt_eval_count'=>$response->json('prompt_eval_count'),'eval_count'=>$response->json('eval_count')]);
        return $response->json('message.content') ?? 'Maaf, terjadi kesalahan.';
    }

    private function streamOpenAICompatible($messages, $maxTokens, $temperature): \Generator
    {
        $url = rtrim($this->config->base_url, '/') . '/chat/completions';
        $headers = ['Accept'=>'text/event-stream'];
        if ($this->config->api_key) $headers['Authorization'] = 'Bearer ' . $this->config->api_key;

        try {
            $client = new \GuzzleHttp\Client(['timeout'=>$this->timeout]);
            $response = $client->post($url, ['headers'=>$headers, 'json'=>[
                'model'=>$this->config->chat_model,'messages'=>$messages,
                'max_completion_tokens'=>$maxTokens,'temperature'=>$temperature,'stream'=>true,
            ], 'stream'=>true]);

            $body = $response->getBody(); $buffer = '';
            while (!$body->eof()) {
                $buffer .= $body->read(1024);
                $lines = explode("\n", $buffer); $buffer = array_pop($lines);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!str_starts_with($line, 'data: ')) continue;
                    $data = substr($line, 6);
                    if ($data === '[DONE]') return;
                    $json = json_decode($data, true);
                    if (!is_array($json)) continue;
                    if (isset($json['usage'])) $this->setUsageFromArray($json['usage']);
                    $token = $json['choices'][0]['delta']['content'] ?? null;
                    if ($token !== null && $token !== '') yield $token;
                }
            }
        } catch (\Throwable $e) { Log::error('AI Stream: '.$e->getMessage()); yield 'Maaf, terjadi kesalahan.'; }
    }

    private function streamOllama($messages, $maxTokens, $temperature): \Generator
    {
        $url = rtrim($this->config->base_url, '/') . '/api/chat';
        try {
            $client = new \GuzzleHttp\Client(['timeout'=>$this->timeout]);
            $response = $client->post($url, ['headers'=>['Accept'=>'application/x-ndjson'], 'json'=>[
                'model'=>$this->config->chat_model,
                'messages'=>array_map(fn($m)=>['role'=>$m['role'],'content'=>$m['content']], $messages),
                'stream'=>true,'options'=>['num_predict'=>$maxTokens,'temperature'=>$temperature],
            ], 'stream'=>true]);

            $body = $response->getBody(); $buffer = '';
            while (!$body->eof()) {
                $buffer .= $body->read(1024);
                $lines = explode("\n", $buffer); $buffer = array_pop($lines);
                foreach ($lines as $line) {
                    $line = trim($line); if (empty($line)) continue;
                    $json = json_decode($line, true); if (!$json) continue;
                    if (!empty($json['done'])) { $this->setUsageFromArray(['prompt_eval_count'=>$json['prompt_eval_count']??null,'eval_count'=>$json['eval_count']??null]); return; }
                    $token = $json['message']['content'] ?? null;
                    if ($token !== null && $token !== '') yield $token;
                }
            }
        } catch (\Throwable $e) { Log::error('Ollama Stream: '.$e->getMessage()); yield 'Maaf, terjadi kesalahan.'; }
    }
}
