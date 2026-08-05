<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SiaminService
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.siamin.base_url', 'https://api-siamin.bpmpntb.id');
        $this->timeout = config('services.siamin.timeout', 15);
    }

    public function login(string $email, string $password): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['Accept' => 'application/json'])
                ->post("{$this->baseUrl}/api/v1/auth/login-admin", [
                    'email' => $email,
                    'password' => $password,
                ]);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json('data')];
            }

            $errors = $response->json('errors', []);
            $message = $response->json('message', 'Login gagal');
            if (!empty($errors)) {
                $message = collect($errors)->flatten()->first() ?? $message;
            }

            return ['success' => false, 'message' => $message, 'status' => $response->status()];
        } catch (\Exception $e) {
            Log::error('SiaminService::login error', ['message' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Tidak dapat terhubung ke server SIAMIN. Silakan coba lagi.'];
        }
    }

    public function getProfile(string $token): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withToken($token)
                ->withHeaders(['Accept' => 'application/json'])
                ->get("{$this->baseUrl}/api/v1/me");

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json('data')];
            }

            return ['success' => false, 'status' => $response->status()];
        } catch (\Exception $e) {
            Log::error('SiaminService::getProfile error', ['message' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Gagal memverifikasi token.'];
        }
    }

    public function getAllUsers(string $token): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withToken($token)
                ->withHeaders(['Accept' => 'application/json'])
                ->get("{$this->baseUrl}/api/v1/users");

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json('data', [])];
            }

            return ['success' => false, 'status' => $response->status()];
        } catch (\Exception $e) {
            Log::error('SiaminService::getAllUsers error', ['message' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Gagal mengambil data user dari SIAMIN.'];
        }
    }

    public function logout(string $token): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withToken($token)
                ->withHeaders(['Accept' => 'application/json'])
                ->post("{$this->baseUrl}/api/v1/logout");

            return ['success' => $response->successful()];
        } catch (\Exception $e) {
            Log::error('SiaminService::logout error', ['message' => $e->getMessage()]);
            return ['success' => false];
        }
    }
}
