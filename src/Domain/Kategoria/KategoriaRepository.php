<?php

declare(strict_types=1);

namespace App\Domain\Kategoria;

use App\Core\Database;

/**
 * Kategória repository
 */
class KategoriaRepository
{
    public function __construct(
        private Database $db
    ) {}

    /**
     * Kategória keresése ID alapján
     */
    public function findById(int $id): ?KategoriaEntity
    {
        $data = $this->db->fetchOne(
            "SELECT * FROM categories WHERE id = :id",
            ['id' => $id]
        );

        return $data ? KategoriaEntity::fromArray($data) : null;
    }

    /**
     * Kategória keresése slug alapján
     */
    public function findBySlug(string $slug): ?KategoriaEntity
    {
        $data = $this->db->fetchOne(
            "SELECT * FROM categories WHERE slug = :slug",
            ['slug' => $slug]
        );

        return $data ? KategoriaEntity::fromArray($data) : null;
    }

    /**
     * Összes főkategória lekérdezése
     * @return KategoriaEntity[]
     */
    public function findFokategoriak(): array
    {
        $rows = $this->db->fetchAll(
            "SELECT * FROM categories WHERE szulo_id IS NULL AND aktiv = 1 ORDER BY sorrend ASC"
        );

        return array_map(fn($row) => KategoriaEntity::fromArray($row), $rows);
    }

    /**
     * Alkategóriák lekérdezése szülő ID alapján
     * @return KategoriaEntity[]
     */
    public function findAlkategoriak(int $szuloId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT * FROM categories WHERE szulo_id = :szulo_id AND aktiv = 1 ORDER BY sorrend ASC",
            ['szulo_id' => $szuloId]
        );

        return array_map(fn($row) => KategoriaEntity::fromArray($row), $rows);
    }

    /**
     * Összes kategória lekérdezése (fa struktúrával)
     * @return KategoriaEntity[]
     */
    public function findAllWithAlkategoriak(): array
    {
        $fokategoriak = $this->findFokategoriak();

        foreach ($fokategoriak as $fokategoria) {
            $alkategoriak = $this->findAlkategoriak($fokategoria->id);
            $fokategoria->setAlkategoriak($alkategoriak);
        }

        return $fokategoriak;
    }

    /**
     * Kategória szülőjének lekérdezése
     */
    public function findSzulo(KategoriaEntity $kategoria): ?KategoriaEntity
    {
        if ($kategoria->szuloId === null) {
            return null;
        }

        return $this->findById($kategoria->szuloId);
    }

    /**
     * Kategória útvonal (breadcrumb) lekérdezése
     * @return KategoriaEntity[]
     */
    public function getBreadcrumb(KategoriaEntity $kategoria): array
    {
        $path = [$kategoria];
        $current = $kategoria;

        while ($current->szuloId !== null) {
            $szulo = $this->findById($current->szuloId);
            if ($szulo === null) {
                break;
            }
            array_unshift($path, $szulo);
            $current = $szulo;
        }

        return $path;
    }

    /**
     * Összes kategória (flat lista)
     * @return KategoriaEntity[]
     */
    public function findAll(): array
    {
        $rows = $this->db->fetchAll(
            "SELECT * FROM categories WHERE aktiv = 1 ORDER BY szulo_id IS NULL DESC, szulo_id ASC, sorrend ASC"
        );

        return array_map(fn($row) => KategoriaEntity::fromArray($row), $rows);
    }
}
