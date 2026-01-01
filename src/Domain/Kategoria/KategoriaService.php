<?php

declare(strict_types=1);

namespace App\Domain\Kategoria;

/**
 * Kategória szolgáltatás
 */
class KategoriaService
{
    public function __construct(
        private KategoriaRepository $repository
    ) {}

    /**
     * Összes főkategória lekérdezése alkategóriákkal
     * @return KategoriaEntity[]
     */
    public function getFokategoriakAlkategoriakkal(): array
    {
        return $this->repository->findAllWithAlkategoriak();
    }

    /**
     * Kategória keresése slug alapján
     */
    public function getBySlug(string $slug): ?KategoriaEntity
    {
        return $this->repository->findBySlug($slug);
    }

    /**
     * Kategória keresése ID alapján
     */
    public function getById(int $id): ?KategoriaEntity
    {
        return $this->repository->findById($id);
    }

    /**
     * Breadcrumb (kategória útvonal) lekérdezése
     * @return KategoriaEntity[]
     */
    public function getBreadcrumb(int $kategoriaId): array
    {
        $kategoria = $this->repository->findById($kategoriaId);
        
        if ($kategoria === null) {
            return [];
        }

        return $this->repository->getBreadcrumb($kategoria);
    }

    /**
     * Összes kategória lekérdezése (select/dropdown számára)
     * @return KategoriaEntity[]
     */
    public function getAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Alkategóriák lekérdezése
     * @return KategoriaEntity[]
     */
    public function getAlkategoriak(int $szuloId): array
    {
        return $this->repository->findAlkategoriak($szuloId);
    }
}
