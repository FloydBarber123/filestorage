<?php

namespace App\Models;

use App\Entity\StorageFileEntity;
use Illuminate\Database\Eloquent\Model;

class StorageUserFilesModel extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'size',
        'path',
        'ext',
    ];

    /**
     * @return \App\Entity\StorageFileEntity
     */
    public function toEntity(): StorageFileEntity
    {
        return new StorageFileEntity(
            id: $this->id,
            userId: $this->user_id,
            name: $this->name,
            size: $this->size,
            path: $this->path,
            ext: $this->ext
        );
    }
}
