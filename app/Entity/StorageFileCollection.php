<?php

namespace App\Entity;

class StorageFileCollection
{
    protected $items = [];

    public function addItem(StorageFileEntity $entity): void
    {
        $this->items[] = $entity;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function toArray(): array
    {
        $array = [];
        foreach ($this->getItems() as $item) {
            $array[] = $item->fromEntity();
        }

        return $array;
    }
}
