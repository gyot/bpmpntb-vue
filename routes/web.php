<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{PdfController, IntentController, KnowledgeBaseController, AiSettingsController};
use App\Http\Controllers\Api\ChatbotController;

Route::get('/post/{jenis}/{id}/pdf', [PdfController::class, 'cetak'])->name('post.pdf');
Route::get('/post/{jenis}/{id}/cetak', [PdfController::class, 'cetak'])->name('post.cetak');

// Chatbot public (session-based)
Route::prefix('api/chatbot')->group(function () {
    Route::get('/check_identity', [ChatbotController::class, 'checkIdentity']);
    Route::post('/save_identity', [ChatbotController::class, 'saveIdentity']);
    Route::get('/get_username', [ChatbotController::class, 'getUsername']);
    Route::get('/logout', [ChatbotController::class, 'logout']);
    Route::post('/respond', [ChatbotController::class, 'respond']);
    Route::post('/respond-stream', [ChatbotController::class, 'respondStream']);
    Route::get('/suggested-questions', [ChatbotController::class, 'suggestedQuestions']);
    Route::get('/admin_status', [ChatbotController::class, 'adminStatus']);
    Route::post('/start_live', [ChatbotController::class, 'startLiveChat']);
    Route::post('/send_live', [ChatbotController::class, 'sendLiveMessage']);
    Route::get('/live_messages', [ChatbotController::class, 'getLiveMessages']);
    Route::post('/typing', [ChatbotController::class, 'sendTyping']);
});

// Chatbot admin (auth required, session-based for livechat)
Route::middleware(['auth:sanctum', 'admin'])->prefix('api/chatbot/admin')->group(function () {
    Route::get('/dashboard', [ChatbotController::class, 'adminLiveDashboard']);
    Route::post('/ping', [ChatbotController::class, 'adminPing']);
    Route::post('/toggle-online', [ChatbotController::class, 'adminToggleOnline']);
    Route::get('/sessions', [ChatbotController::class, 'adminSessions']);
    Route::get('/sessions/{sessionId}/messages', [ChatbotController::class, 'adminMessages']);
    Route::post('/sessions/{sessionId}/messages', [ChatbotController::class, 'adminSendMessage']);
    Route::post('/sessions/{sessionId}/close', [ChatbotController::class, 'adminCloseSession']);
    Route::post('/sessions/{sessionId}/reopen', [ChatbotController::class, 'adminReopenSession']);
    Route::get('/sessions/{sessionId}/export', [ChatbotController::class, 'adminExportSession']);
    Route::get('/user-detail/{chatbotUserId}', [ChatbotController::class, 'adminUserDetail']);
    Route::get('/settings', [ChatbotController::class, 'adminGetSettings']);
    Route::post('/settings', [ChatbotController::class, 'adminUpdateSettings']);
    Route::post('/typing', [ChatbotController::class, 'adminTyping']);
    Route::get('/analytics', [ChatbotController::class, 'adminAnalytics']);
    Route::get('/analytics-page', [ChatbotController::class, 'adminAnalyticsPage']);
    Route::get('/analytics/report', [ChatbotController::class, 'adminAnalyticsReport']);
    Route::get('/analytics/pdf', [ChatbotController::class, 'adminAnalyticsPdf']);
    Route::post('/analytics/user-usage/{userId}/reset-quota', [ChatbotController::class, 'resetUserTokenQuota']);
    Route::get('/unread', [ChatbotController::class, 'adminUnreadSessions']);
    Route::post('/mark-read', [ChatbotController::class, 'adminMarkRead']);

    // Intent
    Route::get('/intent', [IntentController::class, 'index']);
    Route::post('/intent', [IntentController::class, 'store']);
    Route::get('/intent/{id}/edit', [IntentController::class, 'edit']);
    Route::put('/intent/{id}', [IntentController::class, 'update']);
    Route::delete('/intent/{id}', [IntentController::class, 'destroy']);

    // Knowledge Base RAG
    Route::get('/knowledge-base', [KnowledgeBaseController::class, 'indexCategories']);
    Route::get('/knowledge-base/categories', [KnowledgeBaseController::class, 'getCategories']);
    Route::post('/knowledge-base/categories', [KnowledgeBaseController::class, 'storeCategory']);
    Route::put('/knowledge-base/categories/{id}', [KnowledgeBaseController::class, 'updateCategory']);
    Route::delete('/knowledge-base/categories/{id}', [KnowledgeBaseController::class, 'destroyCategory']);
    Route::get('/knowledge-base/documents', [KnowledgeBaseController::class, 'indexDocuments']);
    Route::get('/knowledge-base/documents/create', [KnowledgeBaseController::class, 'createDocument']);
    Route::post('/knowledge-base/documents', [KnowledgeBaseController::class, 'storeDocument']);
    Route::get('/knowledge-base/documents/{id}/edit', [KnowledgeBaseController::class, 'editDocument']);
    Route::put('/knowledge-base/documents/{id}', [KnowledgeBaseController::class, 'updateDocument']);
    Route::delete('/knowledge-base/documents/{id}', [KnowledgeBaseController::class, 'destroyDocument']);
    Route::post('/knowledge-base/preview-chunks', [KnowledgeBaseController::class, 'previewChunks']);
    Route::post('/knowledge-base/parse-pdf', [KnowledgeBaseController::class, 'parsePdf']);
    Route::get('/knowledge-base/stats', [KnowledgeBaseController::class, 'stats']);
    Route::post('/knowledge-base/regenerate-embeddings', [KnowledgeBaseController::class, 'regenerateEmbeddings']);
    Route::post('/knowledge-base/regenerate-document/{id}', [KnowledgeBaseController::class, 'regenerateDocument']);

    // AI Settings
    Route::get('/ai-settings', [AiSettingsController::class, 'index']);
    Route::post('/ai-settings', [AiSettingsController::class, 'store']);
    Route::get('/ai-settings/current', [AiSettingsController::class, 'current']);
    Route::get('/ai-settings/{id}/edit', [AiSettingsController::class, 'edit']);
    Route::put('/ai-settings/{id}', [AiSettingsController::class, 'update']);
    Route::delete('/ai-settings/{id}', [AiSettingsController::class, 'destroy']);
    Route::post('/ai-settings/{id}/activate', [AiSettingsController::class, 'activate']);
    Route::post('/ai-settings/test', [AiSettingsController::class, 'test']);
    Route::post('/ai-settings/test-embedding', [AiSettingsController::class, 'testEmbedding']);

    // WhatsApp Gateway Settings
    Route::get('/whatsapp-settings', [ChatbotController::class, 'whatsappSettings']);
    Route::post('/whatsapp-settings', [ChatbotController::class, 'whatsappSettingsUpdate']);
});

Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
