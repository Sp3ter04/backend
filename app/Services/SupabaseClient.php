<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseClient
{
    protected string $url;
    protected string $anonKey;

    public function __construct()
    {
        $this->url = config('services.supabase.url');
        $this->anonKey = config('services.supabase.public_key');
    }

    /**
     * Query builder for Supabase REST API
     */
    public function from(string $table)
    {
        return new SupabaseQueryBuilder($table, $this->url, $this->anonKey);
    }
}

class SupabaseQueryBuilder
{
    protected string $table;
    protected string $url;
    protected string $anonKey;
    protected array $select = ['*'];
    protected array $filters = [];
    protected ?array $order = null;
    protected ?int $limit = null;
    protected ?int $offset = null;

    public function __construct(string $table, string $url, string $anonKey)
    {
        $this->table = $table;
        $this->url = $url;
        $this->anonKey = $anonKey;
    }

    /**
     * Select columns
     */
    public function select(string $columns = '*')
    {
        $this->select = [$columns];
        return $this;
    }

    /**
     * Add filter (eq, neq, gt, gte, lt, lte, like, ilike, is, in)
     */
    public function eq(string $column, $value)
    {
        $this->filters[] = "{$column}=eq.{$value}";
        return $this;
    }

    public function neq(string $column, $value)
    {
        $this->filters[] = "{$column}=neq.{$value}";
        return $this;
    }

    public function gt(string $column, $value)
    {
        $this->filters[] = "{$column}=gt.{$value}";
        return $this;
    }

    public function gte(string $column, $value)
    {
        $this->filters[] = "{$column}=gte.{$value}";
        return $this;
    }

    public function lt(string $column, $value)
    {
        $this->filters[] = "{$column}=lt.{$value}";
        return $this;
    }

    public function lte(string $column, $value)
    {
        $this->filters[] = "{$column}=lte.{$value}";
        return $this;
    }

    public function like(string $column, string $pattern)
    {
        $this->filters[] = "{$column}=like.{$pattern}";
        return $this;
    }

    public function ilike(string $column, string $pattern)
    {
        $this->filters[] = "{$column}=ilike.{$pattern}";
        return $this;
    }

    /**
     * Order by
     */
    public function order(string $column, bool $ascending = true)
    {
        $this->order = [$column, $ascending ? 'asc' : 'desc'];
        return $this;
    }

    /**
     * Limit results
     */
    public function limit(int $limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Offset results
     */
    public function offset(int $offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Get single record
     */
    public function single()
    {
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Execute GET query
     */
    public function get(): array
    {
        $url = "{$this->url}/rest/v1/{$this->table}";
        
        $params = [
            'select' => implode(',', $this->select),
        ];

        foreach ($this->filters as $filter) {
            $parts = explode('=', $filter, 2);
            $params[$parts[0]] = $parts[1] ?? '';
        }

        if ($this->order) {
            $params['order'] = "{$this->order[0]}.{$this->order[1]}";
        }

        if ($this->limit) {
            $params['limit'] = $this->limit;
        }

        if ($this->offset) {
            $params['offset'] = $this->offset;
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $this->anonKey,
            ])->get($url, $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Supabase query failed', [
                'url' => $url,
                'params' => $params,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Supabase query exception', [
                'message' => $e->getMessage(),
                'url' => $url,
                'params' => $params,
            ]);
            return [];
        }
    }

    /**
     * Execute INSERT
     */
    public function insert(array $data)
    {
        $url = "{$this->url}/rest/v1/{$this->table}";
        
        try {
            $response = Http::withHeaders([
                'apikey' => $this->anonKey,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation',
            ])->post($url, $data);

            if ($response->successful()) {
                $result = $response->json();
                return $result[0] ?? $result;
            }

            Log::error('Supabase insert failed', [
                'url' => $url,
                'data' => $data,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Supabase insert exception', [
                'message' => $e->getMessage(),
                'url' => $url,
                'data' => $data,
            ]);
            return null;
        }
    }

    /**
     * Execute UPDATE
     */
    public function update(array $data)
    {
        $url = "{$this->url}/rest/v1/{$this->table}";
        
        $params = [];
        foreach ($this->filters as $filter) {
            $parts = explode('=', $filter, 2);
            $params[$parts[0]] = $parts[1] ?? '';
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $this->anonKey,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation',
            ])->patch($url . '?' . http_build_query($params), $data);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Supabase update exception', [
                'message' => $e->getMessage(),
                'url' => $url,
                'data' => $data,
            ]);
            return false;
        }
    }

    /**
     * Execute DELETE
     */
    public function delete()
    {
        $url = "{$this->url}/rest/v1/{$this->table}";
        
        $params = [];
        foreach ($this->filters as $filter) {
            $parts = explode('=', $filter, 2);
            $params[$parts[0]] = $parts[1] ?? '';
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $this->anonKey,
            ])->delete($url, $params);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Supabase delete exception', [
                'message' => $e->getMessage(),
                'url' => $url,
            ]);
            return false;
        }
    }
}
