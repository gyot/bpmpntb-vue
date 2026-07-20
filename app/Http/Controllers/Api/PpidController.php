<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{PpidProfile, PpidInformation, PpidStandard, PpidRegulation, PpidExternalLink};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PpidController extends Controller
{
    // Public API
    public function publicIndex()
    {
        $profile = PpidProfile::first();
        $informations = [
            "berkala" => PpidInformation::where("category","berkala")->where("status",1)->orderBy("order")->get(),
            "setiap_saat" => PpidInformation::where("category","setiap_saat")->where("status",1)->orderBy("order")->get(),
            "serta_merta" => PpidInformation::where("category","serta_merta")->where("status",1)->orderBy("order")->get(),
            "informasi" => PpidInformation::where("category","informasi")->where("status",1)->orderBy("order")->get(),
        ];
        $standards = PpidStandard::where("status",1)->orderBy("order")->get();
        $regulations = PpidRegulation::where("status",1)->orderBy("order")->get();
        $externalLinks = PpidExternalLink::where("status",1)->orderBy("order")->get();
        return response()->json(compact("profile","informations","standards","regulations","externalLinks"));
    }

    // Admin CRUD - Profile
    public function profileIndex() { return response()->json(PpidProfile::first() ?? new PpidProfile()); }
    public function profileUpdate(Request $request)
    {
        $v = $request->validate([
            "title" => "nullable|string|max:255",
            "about" => "nullable|string",
            "visi" => "nullable|string",
            "misi" => "nullable|string",
            "tupoksi" => "nullable|string",
            "kontak" => "nullable|string",
            "profil_pejabat" => "nullable|string",
            "profil_sdm" => "nullable|string",
            "beranda_image" => "nullable|image|mimes:jpg,jpeg,png|max:5120",
            "beranda_title" => "nullable|string|max:255",
            "beranda_description" => "nullable|string",
            "navigations" => "nullable|string",
            "permohonan_link" => "nullable|string|max:500",
            "permohonan_email" => "nullable|string|max:255",
            "permohonan_phone" => "nullable|string|max:50",
            "struktur_image" => "nullable|image|mimes:jpg,jpeg,png|max:5120",
        ]);
        $profile = PpidProfile::first() ?? new PpidProfile();
        if ($request->hasFile("struktur_image")) {
            $ext = strtolower($request->file("struktur_image")->getClientOriginalExtension());
            $filename = Str::random(20) . "." . $ext;
            $dir = public_path("upload/ppid");
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $request->file("struktur_image")->move($dir, $filename);
            if ($profile->struktur_image && file_exists(public_path("upload/ppid/".$profile->struktur_image))) {
                unlink(public_path("upload/ppid/".$profile->struktur_image));
            }
            $v["struktur_image"] = $filename;
        }
        if ($request->hasFile("beranda_image")) {
            $ext = strtolower($request->file("beranda_image")->getClientOriginalExtension());
            $filename = Str::random(20) . "." . $ext;
            $dir = public_path("upload/ppid");
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $request->file("beranda_image")->move($dir, $filename);
            if ($profile->beranda_image && file_exists(public_path("upload/ppid/".$profile->beranda_image))) {
                unlink(public_path("upload/ppid/".$profile->beranda_image));
            }
            $v["beranda_image"] = $filename;
        }
        $profile->fill($v);
        $profile->save();
        return response()->json($profile);
    }

    // Admin CRUD - Informations
    public function informationIndex(Request $request)
    {
        $query = PpidInformation::query();
        if ($request->filled("category")) $query->where("category", $request->category);
        return response()->json($query->orderBy("order")->get());
    }
    public function informationStore(Request $request)
    {
        $v = $request->validate([
            "category" => "required|in:berkala,setiap_saat,serta_merta,informasi",
            "title" => "required|string|max:500",
            "description" => "nullable|string",
            "file" => "nullable|file|max:10240",
            "link" => "nullable|string|max:500",
            "status" => "required|in:0,1",
        ]);
        if ($request->hasFile("file")) {
            $ext = strtolower($request->file("file")->getClientOriginalExtension());
            $filename = Str::random(20) . "." . $ext;
            $dir = public_path("upload/ppid");
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $request->file("file")->move($dir, $filename);
            $v["file"] = $filename;
        }
        $v["order"] = PpidInformation::max("order") + 1;
        return response()->json(PpidInformation::create($v), 201);
    }
    public function informationUpdate(Request $request, int $id)
    {
        $item = PpidInformation::findOrFail($id);
        $v = $request->validate([
            "category" => "required|in:berkala,setiap_saat,serta_merta,informasi",
            "title" => "required|string|max:500",
            "description" => "nullable|string",
            "file" => "nullable|file|max:10240",
            "link" => "nullable|string|max:500",
            "status" => "required|in:0,1",
        ]);
        if ($request->hasFile("file")) {
            $ext = strtolower($request->file("file")->getClientOriginalExtension());
            $filename = Str::random(20) . "." . $ext;
            $dir = public_path("upload/ppid");
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $request->file("file")->move($dir, $filename);
            if ($item->file && file_exists(public_path("upload/ppid/".$item->file))) unlink(public_path("upload/ppid/".$item->file));
            $v["file"] = $filename;
        }
        $item->update($v);
        return response()->json($item);
    }
    public function informationDestroy(int $id) { PpidInformation::findOrFail($id)->delete(); return response()->json(["message"=>"Dihapus"]); }

    // Admin CRUD - Standards
    public function standardIndex() { return response()->json(PpidStandard::orderBy("order")->get()); }
    public function standardStore(Request $request)
    {
        $v = $request->validate([
            "title" => "required|string|max:500",
            "content" => "nullable|string",
            "file" => "nullable|file|mimes:pdf|max:20480",
            "status" => "required|in:0,1",
        ]);
        if ($request->hasFile("file")) {
            $ext = strtolower($request->file("file")->getClientOriginalExtension());
            $filename = Str::random(20) . "." . $ext;
            $dir = public_path("upload/ppid");
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $request->file("file")->move($dir, $filename);
            $v["file"] = $filename;
        }
        $v["order"] = PpidStandard::max("order") + 1;
        return response()->json(PpidStandard::create($v), 201);
    }
    public function standardUpdate(Request $request, int $id)
    {
        $item = PpidStandard::findOrFail($id);
        $v = $request->validate([
            "title" => "required|string|max:500",
            "content" => "nullable|string",
            "file" => "nullable|file|mimes:pdf|max:20480",
            "status" => "required|in:0,1",
        ]);
        if ($request->hasFile("file")) {
            $old = public_path("upload/ppid/" . $item->file);
            if ($item->file && file_exists($old)) unlink($old);
            $ext = strtolower($request->file("file")->getClientOriginalExtension());
            $filename = Str::random(20) . "." . $ext;
            $dir = public_path("upload/ppid");
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $request->file("file")->move($dir, $filename);
            $v["file"] = $filename;
        }
        $item->update($v);
        return response()->json($item);
    }
    public function standardDestroy(int $id) { PpidStandard::findOrFail($id)->delete(); return response()->json(["message"=>"Dihapus"]); }

    // Admin CRUD - Regulations
    public function regulationIndex() { return response()->json(PpidRegulation::orderBy("order")->get()); }
    public function regulationStore(Request $request)
    {
        $v = $request->validate([
            "title" => "required|string|max:500",
            "nomor" => "nullable|string|max:255",
            "description" => "nullable|string",
            "file" => "nullable|file|max:10240",
            "link" => "nullable|string|max:500",
            "tanggal" => "nullable|date",
            "status" => "required|in:0,1",
        ]);
        if ($request->hasFile("file")) {
            $ext = strtolower($request->file("file")->getClientOriginalExtension());
            $filename = Str::random(20) . "." . $ext;
            $dir = public_path("upload/ppid");
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $request->file("file")->move($dir, $filename);
            $v["file"] = $filename;
        }
        $v["order"] = PpidRegulation::max("order") + 1;
        return response()->json(PpidRegulation::create($v), 201);
    }
    public function regulationUpdate(Request $request, int $id)
    {
        $item = PpidRegulation::findOrFail($id);
        $v = $request->validate([
            "title" => "required|string|max:500",
            "nomor" => "nullable|string|max:255",
            "description" => "nullable|string",
            "file" => "nullable|file|max:10240",
            "link" => "nullable|string|max:500",
            "tanggal" => "nullable|date",
            "status" => "required|in:0,1",
        ]);
        if ($request->hasFile("file")) {
            $ext = strtolower($request->file("file")->getClientOriginalExtension());
            $filename = Str::random(20) . "." . $ext;
            $dir = public_path("upload/ppid");
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $request->file("file")->move($dir, $filename);
            if ($item->file && file_exists(public_path("upload/ppid/".$item->file))) unlink(public_path("upload/ppid/".$item->file));
            $v["file"] = $filename;
        }
        $item->update($v);
        return response()->json($item);
    }
    public function regulationDestroy(int $id) { PpidRegulation::findOrFail($id)->delete(); return response()->json(["message"=>"Dihapus"]); }

    // Admin CRUD - External Links
    public function externalLinksIndex() { return response()->json(PpidExternalLink::orderBy("order")->get()); }
    public function externalLinksStore(Request $request)
    {
        $v = $request->validate([
            "title" => "required|string|max:255",
            "link" => "nullable|string|max:500",
            "image" => "nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048",
        ]);
        if ($request->hasFile("image")) {
            $ext = strtolower($request->file("image")->getClientOriginalExtension());
            $filename = Str::random(20) . "." . $ext;
            $dir = public_path("upload/ppid");
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $request->file("image")->move($dir, $filename);
            $v["image"] = $filename;
        }
        $v["order"] = PpidExternalLink::max("order") + 1;
        return response()->json(PpidExternalLink::create($v), 201);
    }
    public function externalLinksUpdate(Request $request, int $id)
    {
        $item = PpidExternalLink::findOrFail($id);
        $v = $request->validate([
            "title" => "required|string|max:255",
            "link" => "nullable|string|max:500",
            "image" => "nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048",
            "status" => "required|in:0,1",
        ]);
        if ($request->hasFile("image")) {
            $old = public_path("upload/ppid/" . $item->image);
            if ($item->image && file_exists($old)) unlink($old);
            $ext = strtolower($request->file("image")->getClientOriginalExtension());
            $filename = Str::random(20) . "." . $ext;
            $dir = public_path("upload/ppid");
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $request->file("image")->move($dir, $filename);
            $v["image"] = $filename;
        }
        $item->update($v);
        return response()->json($item);
    }
    public function externalLinksDestroy(int $id)
    {
        $item = PpidExternalLink::findOrFail($id);
        $old = public_path("upload/ppid/" . $item->image);
        if ($item->image && file_exists($old)) unlink($old);
        $item->delete();
        return response()->json(["message"=>"Dihapus"]);
    }
    public function externalLinksReorder(Request $request)
    {
        foreach ($request->input("order", []) as $i => $id) {
            PpidExternalLink::where("id", (int)$id)->update(["order" => $i + 1]);
        }
        return response()->json(["status"=>"success"]);
    }
}
