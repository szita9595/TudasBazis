<?php

declare(strict_types=1);

namespace App\Domain\Valasz;

use App\Domain\Kerdes\KerdesRepository;

/**
 * Válasz szolgáltatás
 */
class ValaszService
{
    public function __construct(
        private ValaszRepository $repository
    ) {}

    /**
     * Új válasz létrehozása
     */
    public function letrehoz(int $kerdesId, int $felhasznaloId, string $tartalom): ValaszEntity
    {
        $tartalom = trim($tartalom);

        if (strlen($tartalom) < 10) {
            throw new \InvalidArgumentException('A válasz legalább 10 karakter legyen.');
        }

        if (strlen($tartalom) > 10000) {
            throw new \InvalidArgumentException('A válasz maximum 10000 karakter lehet.');
        }

        $valasz = new ValaszEntity(
            id: null,
            kerdesId: $kerdesId,
            felhasznaloId: $felhasznaloId,
            tartalom: $tartalom
        );

        return $this->repository->create($valasz);
    }

    /**
     * Válasz lekérdezése ID alapján
     */
    public function getById(int $id): ?ValaszEntity
    {
        return $this->repository->findById($id);
    }

    /**
     * Válaszok lekérdezése kérdéshez
     * @return ValaszEntity[]
     */
    public function getByKerdes(int $kerdesId): array
    {
        return $this->repository->findByKerdes($kerdesId);
    }

    /**
     * Felhasználó válaszai
     * @return ValaszEntity[]
     */
    public function getByFelhasznalo(int $felhasznaloId, int $limit = 20, int $oldal = 1): array
    {
        $offset = ($oldal - 1) * $limit;
        return $this->repository->findByFelhasznalo($felhasznaloId, $limit, $offset);
    }

    /**
     * Válasz törlése
     */
    public function torol(int $id): void
    {
        $this->repository->delete($id);
    }

    /**
     * Felhasználó válaszainak száma
     */
    public function countByFelhasznalo(int $felhasznaloId): int
    {
        return $this->repository->countByFelhasznalo($felhasznaloId);
    }

    /**
     * Összes válasz száma
     */
    public function countAll(): int
    {
        return $this->repository->countAll();
    }
}
