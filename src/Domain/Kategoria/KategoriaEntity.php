<?php

declare(strict_types=1);

namespace App\Domain\Kategoria;

/**
 * Kategória entitás
 */
class KategoriaEntity
{
    /** @var KategoriaEntity[] */
    private array $alkategoriak = [];

    public function __construct(
        public readonly ?int $id,
        public readonly string $nev,
        public readonly string $slug,
        public readonly ?int $szuloId = null,
        public readonly int $sorrend = 0,
        public readonly bool $aktiv = true
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            nev: $data['nev'],
            slug: $data['slug'],
            szuloId: isset($data['szulo_id']) ? (int) $data['szulo_id'] : null,
            sorrend: (int) ($data['sorrend'] ?? 0),
            aktiv: (bool) ($data['aktiv'] ?? true)
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nev' => $this->nev,
            'slug' => $this->slug,
            'szulo_id' => $this->szuloId,
            'sorrend' => $this->sorrend,
            'aktiv' => $this->aktiv ? 1 : 0,
        ];
    }

    public function isFokategoria(): bool
    {
        return $this->szuloId === null;
    }

    /**
     * Alkategóriák beállítása
     * @param KategoriaEntity[] $alkategoriak
     */
    public function setAlkategoriak(array $alkategoriak): void
    {
        $this->alkategoriak = $alkategoriak;
    }

    /**
     * @return KategoriaEntity[]
     */
    public function getAlkategoriak(): array
    {
        return $this->alkategoriak;
    }
}
