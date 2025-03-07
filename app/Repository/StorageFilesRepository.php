<?php

namespace App\Repository;

use App\Dto\StorageFilesFilterDto;
use App\Entity\StorageFileCollection;
use App\Entity\StorageFileEntity;
use App\Models\StorageUserFilesModel;
use Illuminate\Support\Facades\Auth;

class StorageFilesRepository
{
    public function save(StorageFileEntity $entity): StorageFileEntity
    {
        $model = StorageUserFilesModel::create($entity->fromEntity());

        return $model->toEntity();
    }

    public function searchByFilter(StorageFilesFilterDto $filter): StorageFileCollection
    {
        $query = StorageUserFilesModel::query()
            ->where('user_id', Auth::id());

        if ($filter->dateFrom) {
            $query->where('created_at', '>=', $filter->dateFrom);
        }

        if ($filter->dateTo) {
            $query->where('created_at', '<=', $filter->dateTo);
        }

        if ($filter->maxSize) {
            $maxSizeInBytes = $filter->maxSize * 1024 * 1024;
            $query->where('size', '<=', $maxSizeInBytes);
        }

        $files = $query->get();

        $filesCollection = new StorageFileCollection();
        foreach ($files as $fileModel) {
            $filesCollection->addItem($fileModel->toEntity());
        }

        return $filesCollection;
    }
}
