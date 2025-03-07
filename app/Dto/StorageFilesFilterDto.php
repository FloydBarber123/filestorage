<?php

namespace App\Dto;

readonly class StorageFilesFilterDto
{
    public function __construct(
        public ?string $dateFrom,
        public ?string $dateTo,
        public ?int $maxSize
    ) {
    }
}
