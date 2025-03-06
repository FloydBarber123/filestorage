<?php

namespace App\Entity;

class StorageFileEntity
{
    /**
     * @param int|null $id
     * @param int $userId
     * @param string $name
     * @param int $size
     * @param string $path
     */
    public function __construct(
        protected ?int $id = null,
        protected int $userId,
        protected string $name,
        protected int $size,
        protected string $path,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function fromEntity(): array
    {
        return [
            'id' => $this->getId(),
            'user_id' => $this->getUserId(),
            'name' => $this->getName(),
            'size' => $this->getSize(),
            'path' => $this->getPath(),
        ];
    }
}
