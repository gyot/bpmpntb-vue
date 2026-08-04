<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LayananController extends Controller
{
    public function index()
    {
        return response()->json(Layanan::orderBy('order')->get());
    }

    public function publicIndex()
    {
        return response()->json(Layanan::where('status', 1)->orderBy('order')->get(['id', 'title', 'image', 'link_type', 'link_url', 'link_post_jenis', 'link_post_id', 'slug', 'tags', 'order']));
    }

    public function show(int $id)
    {
        $item = Layanan::findOrFail($id);
        $lasts = Layanan::where('id', '!=', $id)->where('status', 1)->where('link_type', 'post')->orderBy('tanggal', 'desc')->take(5)->get();
        return response()->json(compact('item', 'lasts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'content' => 'nullable|string',
            'pos_file' => 'nullable|file|mimes:pdf|max:20480',
            'tags' => 'nullable|string|max:500',
            'writer' => 'nullable|string|max:255',
            'tanggal' => 'nullable|date',
            'link_type' => 'required|in:post,external',
            'link_url' => 'nullable|string|max:500',
            'link_post_jenis' => 'nullable|string|max:50',
            'link_post_id' => 'nullable|integer',
            'status' => 'required|in:0,1',
        ]);

        $validated['slug'] = Str::slug($request->title);
        if (!empty($validated['content'])) {
            $validated['content'] = $this->sanitizeContent($validated['content']);
        }

        if ($request->hasFile('image')) {
            $ext = strtolower($request->file('image')->getClientOriginalExtension());
            $filename = Str::random(20) . '.' . $ext;
            $uploadDir = public_path('upload/layanans');
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $request->file('image')->move($uploadDir, $filename);
            $validated['image'] = $filename;
        }

        if ($request->hasFile('pos_file')) {
            $ext = strtolower($request->file('pos_file')->getClientOriginalExtension());
            $filename = Str::random(20) . '.' . $ext;
            $uploadDir = public_path('upload/layanans');
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $request->file('pos_file')->move($uploadDir, $filename);
            $validated['pos_file'] = $filename;
        }

        $validated['order'] = Layanan::max('order') + 1;

        return response()->json(Layanan::create($validated), 201);
    }

    public function update(Request $request, int $id)
    {
        $item = Layanan::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'content' => 'nullable|string',
            'pos_file' => 'nullable|file|mimes:pdf|max:20480',
            'tags' => 'nullable|string|max:500',
            'writer' => 'nullable|string|max:255',
            'tanggal' => 'nullable|date',
            'link_type' => 'required|in:post,external',
            'link_url' => 'nullable|string|max:500',
            'link_post_jenis' => 'nullable|string|max:50',
            'link_post_id' => 'nullable|integer',
            'status' => 'required|in:0,1',
        ]);

        $validated['slug'] = Str::slug($request->title);
        if (!empty($validated['content'])) {
            $validated['content'] = $this->sanitizeContent($validated['content']);
        }

        if ($request->hasFile('image')) {
            $old = public_path('upload/layanans/' . $item->image);
            if ($item->image && file_exists($old)) unlink($old);
            $ext = strtolower($request->file('image')->getClientOriginalExtension());
            $filename = Str::random(20) . '.' . $ext;
            $uploadDir = public_path('upload/layanans');
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $request->file('image')->move($uploadDir, $filename);
            $validated['image'] = $filename;
        }

        if ($request->hasFile('pos_file')) {
            $old = public_path('upload/layanans/' . $item->pos_file);
            if ($item->pos_file && file_exists($old)) unlink($old);
            $ext = strtolower($request->file('pos_file')->getClientOriginalExtension());
            $filename = Str::random(20) . '.' . $ext;
            $uploadDir = public_path('upload/layanans');
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $request->file('pos_file')->move($uploadDir, $filename);
            $validated['pos_file'] = $filename;
        }

        $item->update($validated);
        return response()->json($item);
    }

    public function destroy(int $id)
    {
        $item = Layanan::findOrFail($id);
        $old = public_path('upload/layanans/' . $item->image);
        if ($item->image && file_exists($old)) unlink($old);
        $item->delete();
        return response()->json(['message' => 'Layanan berhasil dihapus']);
    }

    public function reorder(Request $request)
    {
        $order = $request->input('order', []);
        foreach ($order as $index => $id) {
            Layanan::where('id', (int) $id)->update(['order' => $index + 1]);
        }
        return response()->json(['status' => 'success']);
    }

    private function sanitizeContent(?string $content): string
    {
        if (empty($content)) return '';
        $allowed = '<p><b><strong><i><em><u><s><sub><sup><a><br><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><pre><code><img><table><thead><tbody><tr><th><td><div><span><hr>';
        $clean = strip_tags($content, $allowed);
        $clean = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $clean);
        $clean = preg_replace('/javascript:/i', '', $clean);
        return $clean;
    }
}
