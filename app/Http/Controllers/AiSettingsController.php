<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\AiConfiguration;
use App\Services\AIService;

class AiSettingsController extends Controller
{
    public function index() { return response()->json(AiConfiguration::orderBy('id')->get()); }
    public function current() { return response()->json(AiConfiguration::where('is_active',true)->first()); }
    public function edit(int $id) { return response()->json(AiConfiguration::findOrFail($id)); }

    public function store(Request $request)
    {
        $v = $request->validate([
            'name'=>'required|string|max:255','provider_type'=>'required|in:openai,openai_compatible,ollama',
            'base_url'=>'required|string|max:500','api_key'=>'nullable|string',
            'chat_model'=>'required|string|max:255','embedding_model'=>'nullable|string|max:255',
            'embedding_base_url'=>'nullable|string|max:500','embedding_api_key'=>'nullable|string',
            'max_tokens'=>'required|integer|min:1','temperature'=>'required|numeric|min:0|max:2',
            'input_price_per_1m'=>'nullable|numeric','output_price_per_1m'=>'nullable|numeric',
            'is_active'=>'boolean','headers'=>'nullable|array',
        ]);
        if ($request->input('is_active')) { AiConfiguration::where('is_active',true)->update(['is_active'=>false]); AIService::clearConfigCache(); }
        return response()->json(AiConfiguration::create($v), 201);
    }

    public function update(Request $request, int $id)
    {
        $item = AiConfiguration::findOrFail($id);
        $v = $request->validate([
            'name'=>'required|string|max:255','provider_type'=>'required|in:openai,openai_compatible,ollama',
            'base_url'=>'required|string|max:500','api_key'=>'nullable|string',
            'chat_model'=>'required|string|max:255','embedding_model'=>'nullable|string|max:255',
            'embedding_base_url'=>'nullable|string|max:500','embedding_api_key'=>'nullable|string',
            'max_tokens'=>'required|integer|min:1','temperature'=>'required|numeric|min:0|max:2',
            'input_price_per_1m'=>'nullable|numeric','output_price_per_1m'=>'nullable|numeric',
            'is_active'=>'boolean','headers'=>'nullable|array',
        ]);
        if ($request->input('is_active') && !$item->is_active) { AiConfiguration::where('is_active',true)->where('id','!=',$id)->update(['is_active'=>false]); }
        $item->update($v);
        AIService::clearConfigCache();
        return response()->json($item);
    }

    public function destroy(int $id) { AiConfiguration::findOrFail($id)->delete(); AIService::clearConfigCache(); return response()->json(['message'=>'Dihapus']); }

    public function activate(int $id)
    {
        AiConfiguration::where('is_active',true)->update(['is_active'=>false]);
        AiConfiguration::where('id',$id)->update(['is_active'=>true]);
        AIService::clearConfigCache();
        return response()->json(['message'=>'Diaktifkan']);
    }

    public function test(Request $request)
    {
        $config = $request->input('config_id') ? AiConfiguration::find($request->input('config_id')) : AiConfiguration::where('is_active',true)->first();
        if (!$config) return response()->json(['status'=>'error','message'=>'Tidak ada konfigurasi AI.']);
        return response()->json(AIService::testConfig($config));
    }

    public function testEmbedding(Request $request)
    {
        $config = AiConfiguration::where('is_active',true)->first();
        if (!$config) return response()->json(['status'=>'error','message'=>'Tidak ada konfigurasi AI.']);
        $service = new AIService($config);
        $url = $request->input('embedding_base_url') ?? $config->embedding_base_url ?? $config->base_url;
        $key = $request->input('embedding_api_key') ?? $config->embedding_api_key ?? $config->api_key;
        $model = $request->input('embedding_model') ?? $config->embedding_model ?? 'text-embedding-3-small';
        return response()->json($service->testEmbeddingConnection($url, $key, $model, $config->provider_type));
    }
}
