<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema, Cache};
use App\Models\{ChatbotIntent, AiConfiguration};
use App\Services\AIService;

class IntentController extends Controller
{
    public function index() { return response()->json(ChatbotIntent::orderBy('id')->get()); }
    public function store(Request $request)
    {
        $v = $request->validate(['keyword'=>'required|string|max:128','response'=>'required|string']);
        $item = ChatbotIntent::create($v);
        return response()->json($item, 201);
    }
    public function edit(int $id) { return response()->json(ChatbotIntent::findOrFail($id)); }
    public function update(Request $request, int $id)
    {
        $item = ChatbotIntent::findOrFail($id);
        $v = $request->validate(['keyword'=>'required|string|max:128','response'=>'required|string']);
        $item->update($v);
        return response()->json($item);
    }
    public function destroy(int $id) { ChatbotIntent::findOrFail($id)->delete(); return response()->json(['message'=>'Dihapus']); }
}
