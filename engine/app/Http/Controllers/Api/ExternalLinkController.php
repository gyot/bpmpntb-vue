<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExternalLink;
use Illuminate\Http\Request;

class ExternalLinkController extends Controller
{
    public function index()
    {
        return response()->json(ExternalLink::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'link' => 'required|url',
            'images' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        return response()->json(ExternalLink::create($validated), 201);
    }

    public function update(Request $request, int $id)
    {
        $item = ExternalLink::findOrFail($id);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'link' => 'required|url',
            'images' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);
        $item->update($validated);
        return response()->json($item);
    }

    public function destroy(int $id)
    {
        ExternalLink::findOrFail($id)->delete();
        return response()->json(['message' => 'Link berhasil dihapus']);
    }
}
