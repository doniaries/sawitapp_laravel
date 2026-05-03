<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait HandlesOfflineSync
{
    protected function offlineSyncValidationRules(): array
    {
        return [
            'client_uuid' => ['nullable', 'uuid'],
            'client_created_at' => ['nullable', 'date'],
            'client_updated_at' => ['nullable', 'date'],
        ];
    }

    protected function findExistingOfflineRecord(string $modelClass, Request $request): ?Model
    {
        if (! $request->filled('client_uuid')) {
            return null;
        }

        return $modelClass::query()
            ->where('perusahaan_id', $request->user()->perusahaan_id)
            ->where('client_uuid', $request->input('client_uuid'))
            ->first();
    }

    protected function offlineSyncAttributes(Request $request): array
    {
        return [
            'client_uuid' => $request->input('client_uuid'),
            'client_created_at' => $request->input('client_created_at'),
            'client_updated_at' => $request->input('client_updated_at'),
            'synced_at' => now(),
        ];
    }

    protected function idempotentResponse(Model $record, array $relations = []): JsonResponse
    {
        if ($relations !== []) {
            $record->load($relations);
        }

        return response()->json([
            'data' => $record,
            'meta' => [
                'idempotent' => true,
                'message' => 'Data sudah pernah disinkronkan.',
            ],
        ]);
    }

    protected function isDuplicateOfflineSyncKey(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;
        $driverCode = $exception->errorInfo[1] ?? null;

        return $sqlState === '23000' || (int) $driverCode === 1062;
    }
}
