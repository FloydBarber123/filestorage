<?php

namespace App\Service;

use App\Dto\StorageFilesFilterDto;
use App\Entity\StorageFileEntity;
use App\Exceptions\StorageFilesException;
use App\Repository\StorageFilesRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * @todo: отрефакторить рут патч + патч
 */
class StorageFilesService
{
    protected string $rootUserFolder;

    protected StorageFilesRepository $repository;

    public function __construct()
    {
        $this->rootUserFolder = $this->getUserRootFolderPath();
        $this->repository = new StorageFilesRepository();
    }

    public function searchFiles(Request $request): array
    {
        return $this->repository
            ->searchByFilter(
                new StorageFilesFilterDto(
                    dateFrom: $request->input('dateFrom'),
                    dateTo: $request->input('dateTo'),
                    maxSize: $request->input('maxSize')
                )
            )
            ->toArray();
    }

    public function uploadFiles(array $files, string $path)
    {
        $targetPath = $this->rootUserFolder . $path;

        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $filePath = $targetPath . '/' . $originalName;

            if (Storage::exists($filePath)) {
                $originalName = pathinfo($originalName, PATHINFO_FILENAME)
                    . '_' . time()
                    . '.' . $file->getClientOriginalExtension();
            }

            $file->storeAs($targetPath, $originalName);

            if ($this->fileIsExist($targetPath)) {
                $this->repository->save(
                    new StorageFileEntity(
                        id: null,
                        userId: Auth::id(),
                        name: $originalName,
                        size: $file->getSize(),
                        path: $path,
                        ext: $file->getClientOriginalExtension()
                    )
                );
            }
        }
    }

    public function createFolder(string $path): void
    {
        $pathToNewFolder = $this->rootUserFolder . $path;
        if ($this->fileIsExist($pathToNewFolder)) {
            throw new StorageFilesException('Folder is exist');
        }

        Storage::makeDirectory($pathToNewFolder);
        if (!$this->fileIsExist($pathToNewFolder)) {
            throw new StorageFilesException('Cant create a new folder');
        }
    }

    public function openFolder(string $path, int $page = 1, int $perPage = 10): array
    {
        $pathToOpen = $this->rootUserFolder . $path;
        if (!$this->fileIsExist($pathToOpen)) {
            throw new StorageFilesException('Target path does not exist');
        }

        $directories = $this->getDirectoriesByPath($pathToOpen);
        $files = $this->getFilesByPath($pathToOpen);

        return array_merge($directories, $files);
    }

    public function deleteFile(string $path): void
    {
        $pathToDelete = $this->rootUserFolder . $path;
        if (!$this->fileIsExist($pathToDelete)) {
            throw new StorageFilesException('Target path does not exist');
        }

        if (Storage::directoryExists($pathToDelete)) {
            Storage::deleteDirectory($pathToDelete);
            return;
        }

        if (Storage::exists($pathToDelete)) {
            Storage::delete($pathToDelete);
            return;
        }
    }

    private function getUserRootFolderPath(): string
    {
        $rootPath = sprintf(
            'user_%s/',
            Auth::id()
        );

        if (!$this->fileIsExist($rootPath)) {
            throw new StorageFilesException('root folder doesnt exist');
        }

        return $rootPath;
    }

    private function fileIsExist(string $path): bool
    {
        if (Storage::exists($path)) {
            return true;
        }

        return false;
    }

    private function getDirectoriesByPath(string $pathToOpen): array
    {
        $directoriesWithRoot = Storage::directories($pathToOpen);

        return array_map(function ($dir) {
            $relativePath = str_replace($this->rootUserFolder, '', $dir);

            return (new StorageFileEntity(
                id: null,
                userId: Auth::id(),
                name: $relativePath,
                size: $this->getDirectorySize($dir),
                path: $relativePath,
                ext: 'directory'
            ))
                ->fromEntity();
        }, $directoriesWithRoot);
    }

    private function getFilesByPath(string $pathToOpen): array
    {
        $filesWithRoot = Storage::files($pathToOpen);

        return array_map(function ($file) use ($pathToOpen) {
            $relativePath = str_replace($this->rootUserFolder, '', $pathToOpen);
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $sizeBytes = Storage::size($file);
            $sizeMb = $this->formatSize($sizeBytes);

            return (new StorageFileEntity(
                id: null,
                userId: Auth::id(),
                name: basename($file),
                size: $sizeMb,
                path: $relativePath,
                ext: $extension
            ))
                ->fromEntity();
        }, $filesWithRoot);
    }

    private function getDirectorySize($directory): string
    {
        $files = Storage::allFiles($directory);
        $sizeBytes = array_reduce($files, fn($carry, $file) => $carry + Storage::size($file), 0);

        return $this->formatSize($sizeBytes);
    }

    private function formatSize(int $sizeBytes): string
    {
        if ($sizeBytes < 1024 * 1024) {
            return round($sizeBytes / 1024, 2) . ' KB';
        }

        return round($sizeBytes / (1024 * 1024), 2) . ' MB';
    }
}
