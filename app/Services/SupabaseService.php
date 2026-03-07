<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SupabaseService
{
    protected string $url;
    protected string $anonKey;
    protected string $serviceRoleKey;

    public function __construct()
    {
        $this->url = config('services.supabase.url');
        $this->anonKey = config('services.supabase.anon_key');
        $this->serviceRoleKey = config('services.supabase.service_role_key');
    }

    /**
     * Get all exercises from Supabase
     */
    public function getExercises(?int $limit = null): array
    {
        $url = "{$this->url}/rest/v1/exercises";
        
        $query = [
            'select' => '*',
            'order' => 'number.desc',
        ];
        
        if ($limit) {
            $query['limit'] = $limit;
        }

        $response = Http::withHeaders([
            'apikey' => $this->serviceRoleKey,
            'Authorization' => "Bearer {$this->serviceRoleKey}",
        ])->get($url, $query);

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    /**
     * Get a single exercise by ID
     */
    public function getExercise(string $id): ?array
    {
        $url = "{$this->url}/rest/v1/exercises";
        
        $response = Http::withHeaders([
            'apikey' => $this->serviceRoleKey,
            'Authorization' => "Bearer {$this->serviceRoleKey}",
        ])->get($url, [
            'id' => "eq.{$id}",
            'select' => '*',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data[0] ?? null;
        }

        return null;
    }

    /**
     * Create a new exercise
     */
    public function createExercise(array $data): ?array
    {
        $url = "{$this->url}/rest/v1/exercises";
        
        $response = Http::withHeaders([
            'apikey' => $this->serviceRoleKey,
            'Authorization' => "Bearer {$this->serviceRoleKey}",
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ])->post($url, $data);

        if ($response->successful()) {
            return $response->json()[0] ?? null;
        }

        return null;
    }

    /**
     * Update an exercise
     */
    public function updateExercise(string $id, array $data): bool
    {
        $url = "{$this->url}/rest/v1/exercises";
        
        $response = Http::withHeaders([
            'apikey' => $this->serviceRoleKey,
            'Authorization' => "Bearer {$this->serviceRoleKey}",
            'Content-Type' => 'application/json',
        ])->patch($url, $data, [
            'id' => "eq.{$id}",
        ]);

        return $response->successful();
    }

    /**
     * Delete an exercise
     */
    public function deleteExercise(string $id): bool
    {
        $url = "{$this->url}/rest/v1/exercises";
        
        $response = Http::withHeaders([
            'apikey' => $this->serviceRoleKey,
            'Authorization' => "Bearer {$this->serviceRoleKey}",
        ])->delete($url, [
            'id' => "eq.{$id}",
        ]);

        return $response->successful();
    }
}
