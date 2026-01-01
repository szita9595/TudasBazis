<?php

declare(strict_types=1);

namespace App\Domain\Kerdes;

/**
 * Kérdés szolgáltatás
 */
class KerdesService
{
    public function __construct(
        private KerdesRepository $repository
    ) {}

    /**
     * Új kérdés létrehozása
     */
    public function letrehoz(
        int $felhasznaloId,
        int $kategoriaId,
        string $cim,
        string $tartalom
    ): KerdesEntity {
        // Validálás
        $cim = trim($cim);
        $tartalom = trim($tartalom);

        if (strlen($cim) < 10) {
            throw new \InvalidArgumentException('A kérdés címe legalább 10 karakter legyen.');
        }

        if (strlen($cim) > 255) {
            throw new \InvalidArgumentException('A kérdés címe maximum 255 karakter lehet.');
        }

        if (strlen($tartalom) < 20) {
            throw new \InvalidArgumentException('A kérdés kifejtése legalább 20 karakter legyen.');
        }

        // Slug generálása
        $slug = KerdesEntity::generateSlug($cim);

        $kerdes = new KerdesEntity(
            id: null,
            felhasznaloId: $felhasznaloId,
            kategoriaId: $kategoriaId,
            cim: $cim,
            slug: $slug,
            tartalom: $tartalom
        );

        return $this->repository->create($kerdes);
    }

    /**
     * Kérdés lekérdezése ID alapján (megtekintés növeléssel)
     */
    public function getById(int $id, bool $incrementView = true): ?KerdesEntity
    {
        $kerdes = $this->repository->findById($id);

        if ($kerdes !== null && $incrementView) {
            $this->repository->incrementMegtekintes($id);
        }

        return $kerdes;
    }

    /**
     * Legújabb kérdések
     * @return KerdesEntity[]
     */
    public function getLegujabbak(int $limit = 20, int $oldal = 1): array
    {
        $offset = ($oldal - 1) * $limit;
        return $this->repository->findLegujabbak($limit, $offset);
    }

    /**
     * Kérdések kategória alapján
     * @return KerdesEntity[]
     */
    public function getByKategoria(int $kategoriaId, int $limit = 20, int $oldal = 1): array
    {
        $offset = ($oldal - 1) * $limit;
        return $this->repository->findByKategoria($kategoriaId, $limit, $offset);
    }

    /**
     * Kérdések több kategóriában (főkategória + alkategóriái)
     * @param int[] $kategoriaIds
     * @return KerdesEntity[]
     */
    public function getByKategoriak(array $kategoriaIds, int $limit = 20, int $oldal = 1): array
    {
        $offset = ($oldal - 1) * $limit;
        return $this->repository->findByKategoriak($kategoriaIds, $limit, $offset);
    }

    /**
     * Felhasználó kérdései
     * @return KerdesEntity[]
     */
    public function getByFelhasznalo(int $felhasznaloId, int $limit = 20, int $oldal = 1): array
    {
        $offset = ($oldal - 1) * $limit;
        return $this->repository->findByFelhasznalo($felhasznaloId, $limit, $offset);
    }

    /**
     * Megválaszolatlan kérdések
     * @return KerdesEntity[]
     */
    public function getMegvalaszolatlan(int $limit = 20, int $oldal = 1): array
    {
        $offset = ($oldal - 1) * $limit;
        return $this->repository->findMegvalaszolatlan($limit, $offset);
    }

    /**
     * Keresés
     * @return KerdesEntity[]
     */
    public function keres(string $kulcsszo): array
    {
        $kulcsszo = trim($kulcsszo);

        if (strlen($kulcsszo) < 3) {
            return [];
        }

        return $this->repository->search($kulcsszo);
    }

    /**
     * Kérdés törlése
     */
    public function torol(int $id): void
    {
        $this->repository->delete($id);
    }

    /**
     * Kérdések száma kategóriában
     */
    public function countByKategoria(int $kategoriaId): int
    {
        return $this->repository->countByKategoria($kategoriaId);
    }

    /**
     * Összes kérdés száma
     */
    public function countAll(): int
    {
        return $this->repository->countAll();
    }
}
