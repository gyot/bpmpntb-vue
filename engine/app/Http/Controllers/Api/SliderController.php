<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SliderController extends Controller
{
    public function index()
    {
        return response()->json(Slider::orderBy('order')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'link' => 'nullable|url',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $file = $request->file('image');
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = Str::random(20) . '.' . $ext;
        $uploadDir = public_path('upload/sliders');
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $file->move($uploadDir, $filename);

        $order = Slider::max('order') + 1;

        $slider = Slider::create([
            'title' => $request->title,
            'image' => $filename,
            'link' => $request->link,
            'description' => $request->description,
            'order' => $order,
            'status' => $request->status,
        ]);

        return response()->json($slider, 201);
    }

    public function update(Request $request, int $id)
    {
        $slider = Slider::findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'link' => 'nullable|url',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('image')) {
            $oldPath = public_path('upload/sliders/' . $slider->image);
            if (file_exists($oldPath)) unlink($oldPath);

            $file = $request->file('image');
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = Str::random(20) . '.' . $ext;
            $file->move(public_path('upload/sliders'), $filename);
            $slider->image = $filename;
        }

        $slider->title = $request->title;
        $slider->link = $request->link;
        $slider->description = $request->description;
        $slider->status = $request->status;
        $slider->save();

        return response()->json($slider);
    }

    public function destroy(int $id)
    {
        $slider = Slider::findOrFail($id);
        $path = public_path('upload/sliders/' . $slider->image);
        if (file_exists($path)) unlink($path);
        $slider->delete();
        return response()->json(['message' => 'Slider berhasil dihapus']);
    }

    public function reorder(Request $request)
    {
        $order = $request->input('order', []);
        if (!is_array($order)) {
            return response()->json(['message' => 'Invalid order data'], 422);
        }
        foreach ($order as $index => $id) {
            Slider::where('id', (int) $id)->update(['order' => $index + 1]);
        }
        return response()->json(['status' => 'success']);
    }
}
