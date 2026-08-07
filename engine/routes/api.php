<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AuthController, BerandaController, PostController, SliderController, SettingController, UserController, ExternalLinkController, LayananController, ChatbotController, PpidController, ExportImportController};

Route::get('/beranda', [BerandaController::class, 'index']);
Route::get('/settings', [BerandaController::class, 'settings']);
Route::get('/theme', [BerandaController::class, 'theme']);
Route::get('/visitor-stats', [BerandaController::class, 'visitorStats']);
Route::get('/sliders-public', fn()=>response()->json(\App\Models\Slider::where('status','active')->orderBy('order')->get()));
Route::get('/external-links', fn()=>response()->json(\App\Models\ExternalLink::where('status',1)->get()));
Route::get('/posts-front/{jenis}', [PostController::class, 'listFront']);
Route::get('/posts-front/{jenis}/{id}', [PostController::class, 'show']);
Route::get('/layanans-public', [LayananController::class, 'publicIndex']);
Route::get('/layanans/{id}', [LayananController::class, 'show']);
Route::get('/ppid', [PpidController::class, 'publicIndex']);

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('siamin.auth');

Route::middleware('siamin.auth')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/my-menu-access', [UserController::class, 'myMenuAccess']);
    Route::get('/posts/{jenis}', [PostController::class, 'index']);
    Route::post('/quil-upload-image', [PostController::class, 'uploadImage']);
    Route::get('/kategori/{jenis}', [PostController::class, 'kategoriIndex']);
});

Route::middleware(['siamin.auth', 'siamin.admin'])->group(function () {
    Route::get('/dashboard-stats', [PostController::class, 'dashboardStats']);
    Route::apiResource('sliders', SliderController::class)->except(['show']);
    Route::post('/sliders/reorder', [SliderController::class, 'reorder']);
    Route::get('/settings-admin', [SettingController::class, 'index']);
    Route::post('/settings-admin', [SettingController::class, 'update']);
    Route::apiResource('users', UserController::class)->except(['show']);
    Route::get('/users/{id}/menu-access', [UserController::class, 'getMenuAccess']);
    Route::put('/users/{id}/menu-access', [UserController::class, 'updateMenuAccess']);
    Route::get('/users/menus/list', [UserController::class, 'menus']);
    Route::apiResource('external-links', ExternalLinkController::class)->except(['show']);
    Route::get('/layanans', [LayananController::class, 'index']);
    Route::post('/layanans', [LayananController::class, 'store']);
    Route::put('/layanans/{id}', [LayananController::class, 'update']);
    Route::delete('/layanans/{id}', [LayananController::class, 'destroy']);
    Route::post('/layanans/reorder', [LayananController::class, 'reorder']);
    Route::post('/posts/{jenis}', [PostController::class, 'store']);
    Route::put('/posts/{jenis}/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{jenis}/{id}', [PostController::class, 'destroy']);
    Route::post('/kategori/{jenis}', [PostController::class, 'kategoriStore']);
    Route::put('/kategori/{jenis}/{id}', [PostController::class, 'kategoriUpdate']);
    Route::delete('/kategori/{jenis}/{id}', [PostController::class, 'kategoriDestroy']);
    // PPID Admin
    Route::get('/ppid/profile', [PpidController::class, 'profileIndex']);
    Route::post('/ppid/profile', [PpidController::class, 'profileUpdate']);
    Route::get('/ppid/informations', [PpidController::class, 'informationIndex']);
    Route::post('/ppid/informations', [PpidController::class, 'informationStore']);
    Route::put('/ppid/informations/{id}', [PpidController::class, 'informationUpdate']);
    Route::delete('/ppid/informations/{id}', [PpidController::class, 'informationDestroy']);
    Route::get('/ppid/standards', [PpidController::class, 'standardIndex']);
    Route::post('/ppid/standards', [PpidController::class, 'standardStore']);
    Route::put('/ppid/standards/{id}', [PpidController::class, 'standardUpdate']);
    Route::delete('/ppid/standards/{id}', [PpidController::class, 'standardDestroy']);
    Route::get('/ppid/regulations', [PpidController::class, 'regulationIndex']);
    Route::post('/ppid/regulations', [PpidController::class, 'regulationStore']);
    Route::put('/ppid/regulations/{id}', [PpidController::class, 'regulationUpdate']);
    Route::delete('/ppid/regulations/{id}', [PpidController::class, 'regulationDestroy']);
    Route::get('/ppid/external-links', [PpidController::class, 'externalLinksIndex']);
    Route::post('/ppid/external-links', [PpidController::class, 'externalLinksStore']);
    Route::put('/ppid/external-links/{id}', [PpidController::class, 'externalLinksUpdate']);
    Route::delete('/ppid/external-links/{id}', [PpidController::class, 'externalLinksDestroy']);
    Route::post('/ppid/external-links/reorder', [PpidController::class, 'externalLinksReorder']);
    Route::get('/ppid/annual-reports', [PpidController::class, 'annualReportIndex']);
    Route::post('/ppid/annual-reports', [PpidController::class, 'annualReportStore']);
    Route::put('/ppid/annual-reports/{id}', [PpidController::class, 'annualReportUpdate']);
    Route::delete('/ppid/annual-reports/{id}', [PpidController::class, 'annualReportDestroy']);

    Route::get('/chatbot-responses', [ChatbotController::class, 'keywordIndex']);
    Route::post('/chatbot-responses', [ChatbotController::class, 'keywordStore']);
    Route::put('/chatbot-responses/{id}', [ChatbotController::class, 'keywordUpdate']);
    Route::delete('/chatbot-responses/{id}', [ChatbotController::class, 'keywordDestroy']);
    Route::get('/chatbot-intents', [ChatbotController::class, 'intentIndex']);
    Route::post('/chatbot-intents', [ChatbotController::class, 'intentStore']);
    Route::put('/chatbot-intents/{id}', [ChatbotController::class, 'intentUpdate']);
    Route::delete('/chatbot-intents/{id}', [ChatbotController::class, 'intentDestroy']);
    Route::get('/ai-configs', [ChatbotController::class, 'aiConfigIndex']);
    Route::post('/ai-configs', [ChatbotController::class, 'aiConfigStore']);
    Route::put('/ai-configs/{id}', [ChatbotController::class, 'aiConfigUpdate']);
    Route::delete('/ai-configs/{id}', [ChatbotController::class, 'aiConfigDestroy']);
    Route::post('/ai-configs/{id}/test', [ChatbotController::class, 'aiConfigTest']);
    Route::get('/chatbot-analytics', [ChatbotController::class, 'analytics']);

    // WhatsApp Broadcast
    Route::post('/wa-broadcast/send', [ChatbotController::class, 'broadcastSend']);
    Route::post('/wa-broadcast/send-stream', [ChatbotController::class, 'broadcastSendStream']);
    Route::get('/wa-broadcast/users', [ChatbotController::class, 'broadcastUsers']);
    Route::get('/wa-broadcast/history', [ChatbotController::class, 'broadcastHistory']);

    // Export/Import
    Route::get('/export-import/types', [ExportImportController::class, 'types']);
    Route::get('/export-import/{type}/template', [ExportImportController::class, 'downloadTemplate']);
    Route::get('/export-import/{type}', [ExportImportController::class, 'export']);
    Route::post('/export-import/{type}', [ExportImportController::class, 'import']);

    // SIAMIN User Management
    Route::get('/siamin/users', [AuthController::class, 'siaminUsers']);
    Route::post('/siamin/set-role', [AuthController::class, 'setRole']);
    Route::delete('/siamin/revoke-role/{id_user}', [AuthController::class, 'revokeRole']);
    Route::get('/siamin/local-roles', [AuthController::class, 'localRoles']);
});
