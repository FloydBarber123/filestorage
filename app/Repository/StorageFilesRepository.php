<?php

namespace App\Repository;

use App\Entity\StorageFileEntity;
use App\Models\StorageUserFilesModel;

class StorageFilesRepository
{
    public function save(StorageFileEntity $entity): StorageFileEntity
    {
        $model = StorageUserFilesModel::create($entity->fromEntity());
        return $model->toEntity();
    }
}
