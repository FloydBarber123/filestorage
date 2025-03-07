<?php

namespace App\Http\Controllers;

use App\Exceptions\StorageFilesException;
use App\Service\StorageFilesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class FileController extends Controller
{
    protected StorageFilesService $storageService;

    public function __construct()
    {
        $this->storageService = new StorageFilesService();
    }

    public function openFolder(Request $request): JsonResponse
    {
        try {
            $files = $this->storageService->openFolder($request->input('path'));

            return response()->json([
                'files' => $files,
            ]);
        } catch (StorageFilesException $e) {
            Log::error("Files error: " . $e->getMessage());

            return response()->json(
                [
                    'error' => $e->getMessage(),
                ],
                422
            );
        } catch (Throwable $t) {
            Log::error("Unexpected error: " . $t->getMessage());

            return response()->json(
                [
                    'error' => 'Unexpected error',
                ],
                500
            );
        }
    }

    public function createFolder(Request $request)
    {
        try {
            $this->storageService->createFolder($request->input('path'));
        } catch (StorageFilesException $e) {
            Log::error("Create folder error: " . $e->getMessage());

            return response()->json(
                [
                    'error' => $e->getMessage(),
                ],
                422
            );
        } catch (Throwable $t) {
            Log::error("Unexpected error: " . $t->getMessage());

            return response()->json(
                [
                    'error' => 'Unexpected error',
                ],
                500
            );
        }
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|array',
            'file.*' => 'file|max:512000',
            'path' => 'required|string',
        ]);

        try {
            $this->storageService->uploadFiles($request->file('file'), $request->input('path'));
        } catch (Throwable $t) {
            Log::error("Unexpected error: " . $t->getMessage());

            return response()->json(
                [
                    'error' => 'Unexpected error',
                ],
                500
            );
        }
    }

    public function delete(Request $request)
    {
        try {
            $this->storageService->deleteFile($request->input('path'));
        } catch (StorageFilesException $e) {
            Log::error("files error: " . $e->getMessage());

            return response()->json(
                [
                    'error' => $e->getMessage(),
                ],
                422
            );
        } catch (Throwable $t) {
            Log::error("Unexpected error: " . $t->getMessage());

            return response()->json(
                [
                    'error' => 'Unexpected error',
                ],
                500
            );
        }
    }

    public function searchFiles(Request $request): JsonResponse
    {
        $request->validate([
            'dateFrom' => 'nullable|date',
            'dateTo' => 'nullable|date|after_or_equal:dateFrom',
            'maxSize' => 'nullable|numeric|min:1',
        ]);

        try {
            return response()->json(
                [
                    'files' => $this->storageService->searchFiles($request),
                ]
            );
        } catch (Throwable $t) {
            Log::error("Unexpected error: " . $t->getMessage());

            return response()->json(
                [
                    'error' => 'Unexpected error',
                ],
                500
            );
        }
    }
}
