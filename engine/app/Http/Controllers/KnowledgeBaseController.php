<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;
use App\Models\{KnowledgeCategory, KnowledgeDocument, KnowledgeChunk};
use App\Services\AIService;

class KnowledgeBaseController extends Controller
{
    public function getCategories()
    {
        return response()->json(['categories'=>KnowledgeCategory::withCount('documents')->orderBy('name')->get()]);
    }

    public function indexCategories(Request $request)
    {
        $categories = KnowledgeCategory::withCount('documents')->orderBy('name')->get();
        $documents = KnowledgeDocument::with('category')->withCount('chunks')->orderByDesc('updated_at')->paginate(10);
        return response()->json(compact('categories','documents'));
    }

    public function storeCategory(Request $request)
    {
        $v = $request->validate(['name'=>'required|string|max:255','description'=>'nullable|string|max:1000']);
        $v['slug'] = Str::slug($v['name']);
        return response()->json(['status'=>'ok','category'=>KnowledgeCategory::create($v)], 201);
    }

    public function updateCategory(Request $request, int $id)
    {
        $cat = KnowledgeCategory::findOrFail($id);
        $v = $request->validate(['name'=>'required|string|max:255','description'=>'nullable|string|max:1000']);
        $v['slug'] = Str::slug($v['name']);
        $cat->update($v);
        return response()->json(['status'=>'ok']);
    }

    public function destroyCategory(int $id)
    {
        $cat = KnowledgeCategory::findOrFail($id);
        $docIds = KnowledgeDocument::where('category_id',$id)->pluck('id');
        KnowledgeChunk::whereIn('document_id',$docIds)->delete();
        KnowledgeDocument::where('category_id',$id)->delete();
        $cat->delete();
        return response()->json(['status'=>'ok','message'=>'Kategori dan dokumen terkait dihapus']);
    }

    public function indexDocuments(Request $request)
    {
        $query = KnowledgeDocument::with('category')->withCount('chunks');
        if ($request->filled('category_id')) $query->where('category_id',$request->category_id);
        if ($request->filled('search')) $query->where(fn($q)=>$q->where('title','like','%'.$request->search.'%')->orWhere('content','like','%'.$request->search.'%'));
        return response()->json(['documents'=>$query->orderByDesc('updated_at')->paginate(10)]);
    }

    public function createDocument() { return response()->json(['categories'=>KnowledgeCategory::orderBy('name')->get()]); }
    public function editDocument(int $id) { return response()->json(KnowledgeDocument::with('category')->findOrFail($id)); }

    public function storeDocument(Request $request)
    {
        $v = $request->validate([
            'title'=>'required|string|max:500','content'=>'nullable|string',
            'category_id'=>'nullable|exists:knowledge_categories,id','status'=>'required|in:active,draft',
            'pdf_file'=>'nullable|file|mimes:pdf|max:10240',
        ]);
        $sourceType = 'manual';
        $content = $v['content'] ?? '';
        $filePath = null;

        if ($request->hasFile('pdf_file')) {
            $file = $request->file('pdf_file');
            $path = $file->store('knowledge-base','public');
            $filePath = $path;
            $sourceType = 'file';
            if (empty($content)) {
                $content = $this->extractPdfText($file);
            }
        }

        $doc = KnowledgeDocument::create([
            'title'=>$v['title'],'content'=>$content,'category_id'=>$v['category_id']??null,
            'status'=>$v['status'],'source_type'=>$sourceType,'file_path'=>$filePath,
        ]);
        $this->chunkDocument($doc);
        return response()->json(['status'=>'ok','document'=>$doc], 201);
    }

    public function parsePdf(Request $request)
    {
        $request->validate(['pdf_file'=>'required|file|mimes:pdf|max:10240']);
        $file = $request->file('pdf_file');
        $content = $this->extractPdfText($file);
        if (empty(trim($content))) {
            $size = $file->getSize();
            $classExists = class_exists('Smalot\PdfParser\Parser');
            $msg = $classExists
                ? "Tidak ada teks yang bisa dibaca dari PDF ini. ({$file->getClientOriginalName()}, ".number_format($size/1024)."KB). PDF mungkin berbasis gambar/scan."
                : "Library PDF parser belum terinstall dengan benar. Silakan jalankan: composer require smalot/pdfparser";
            Log::warning("PDF kosong: {$file->getClientOriginalName()}, size={$size}, classExists={$classExists}");
            return response()->json(['status'=>'error','message'=>$msg]);
        }
        $chunks = $this->splitChunks($content);
        $tokenCount = (int) (str_word_count($content) * 1.3);
        return response()->json([
            'status'=>'ok','content'=>$content,'content_preview'=>mb_substr($content,0,200).'...',
            'chunk_count'=>count($chunks),'token_count'=>$tokenCount,
        ]);
    }

    private function extractPdfText($file): string
    {
        if (!class_exists('Smalot\PdfParser\Parser')) {
            Log::error('Smalot PdfParser class not found. Check autoload.');
            return '';
        }
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($file->getRealPath());
            $text = trim($pdf->getText());
            if (!empty($text)) return $text;
        } catch (\Throwable $e) {
            Log::warning('PDF parseFile gagal: '.$e->getMessage());
        }

        try {
            $content = file_get_contents($file->getRealPath());
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseContent($content);
            $text = trim($pdf->getText());
            if (!empty($text)) return $text;
        } catch (\Throwable $e) {
            Log::warning('PDF parseContent gagal: '.$e->getMessage());
        }

        return '';
    }

    public function updateDocument(Request $request, int $id)
    {
        $doc = KnowledgeDocument::findOrFail($id);
        $v = $request->validate([
            'title'=>'required|string|max:500','content'=>'nullable|string',
            'category_id'=>'nullable|exists:knowledge_categories,id','status'=>'required|in:active,draft',
        ]);
        $doc->update($v);
        KnowledgeChunk::where('document_id',$id)->delete();
        $this->chunkDocument($doc);
        return response()->json(['status'=>'ok']);
    }

    public function destroyDocument(int $id)
    {
        KnowledgeChunk::where('document_id',$id)->delete();
        KnowledgeDocument::findOrFail($id)->delete();
        return response()->json(['status'=>'ok','message'=>'Dokumen dihapus']);
    }

    public function previewChunks(Request $request)
    {
        $content = $request->input('content','');
        $chunks = $this->splitChunks($content);
        return response()->json(['chunks'=>$chunks,'count'=>count($chunks)]);
    }

    public function stats()
    {
        $categories = KnowledgeCategory::count();
        $documents = KnowledgeDocument::count();
        $chunks = KnowledgeChunk::count();
        $withEmbedding = KnowledgeChunk::whereNotNull('embedding')->count();
        return response()->json(compact('categories','documents','chunks','withEmbedding'));
    }

    public function regenerateEmbeddings()
    {
        $chunks = KnowledgeChunk::whereNull('embedding')->limit(50)->get();
        $aiService = new AIService();
        $count = 0;
        foreach ($chunks as $chunk) {
            try {
                $embedding = $aiService->embedding($chunk->content);
                if ($embedding) { $chunk->update(['embedding'=>json_encode($embedding)]); $count++; }
            } catch (\Throwable $e) { Log::error('Embedding gagal chunk '.$chunk->id.': '.$e->getMessage()); }
        }
        return response()->json(['status'=>'ok','processed'=>$count,'remaining'=>KnowledgeChunk::whereNull('embedding')->count()]);
    }

    public function regenerateDocument(int $id)
    {
        $doc = KnowledgeDocument::findOrFail($id);
        KnowledgeChunk::where('document_id',$id)->delete();
        $this->chunkDocument($doc);
        return response()->json(['status'=>'ok','chunks'=>KnowledgeChunk::where('document_id',$id)->count()]);
    }

    private function chunkDocument(KnowledgeDocument $doc)
    {
        $content = $doc->content ?? '';
        if (empty(trim($content))) return;
        $chunks = $this->splitChunks($content);
        $rows = [];
        $now = now();
        foreach ($chunks as $i => $chunk) {
            $rows[] = ['document_id'=>$doc->id,'chunk_index'=>$i,'content'=>$chunk,'created_at'=>$now,'updated_at'=>$now];
        }
        foreach (array_chunk($rows, 50) as $batch) {
            KnowledgeChunk::insert($batch);
        }
    }

    private function splitChunks(string $text, int $size = 800, int $overlap = 100): array
    {
        $text = mb_substr(trim($text), 0, 500000);
        $text = preg_replace('/\s+/', ' ', $text);
        if (!$text || mb_strlen($text) <= $size) return [$text ?: ''];
        $chunks = []; $start = 0; $len = mb_strlen($text); $max = 500;
        while ($start < $len && count($chunks) < $max) {
            $end = min($start + $size, $len);
            $chunks[] = mb_substr($text, $start, $end - $start);
            $start = $end - $overlap;
            if ($start >= $len) break;
        }
        return $chunks;
    }
}
