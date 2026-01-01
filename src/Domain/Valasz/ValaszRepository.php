<?php

declare(strict_types=1);

namespace App\Domain\Valasz;

use App\Core\Database;

/**
 * Válasz repository
 */
class ValaszRepository
{
    public function __construct(
        private Database $db
    ) {}

    /**
     * Válasz keresése ID alapján
     */
    public function findById(int $id): ?ValaszEntity
    {
        $data = $this->db->fetchOne(
            "SELECT a.*, u.felhasznalonev, u.reputacio_szazalek
             FROM answers a
             JOIN users u ON a.felhasznalo_id = u.id
             WHERE a.id = :id",
            ['id' => $id]
        );

        return $data ? ValaszEntity::fromArray($data) : null;
    }

    /**
     * Válaszok lekérdezése kérdés alapján
     * @return ValaszEntity[]
     */
    public function findByKerdes(int $kerdesId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT a.*, u.felhasznalonev, u.reputacio_szazalek
             FROM answers a
             JOIN users u ON a.felhasznalo_id = u.id
             WHERE a.kerdes_id = :kerdes_id
             ORDER BY a.hasznos_szavazat DESC, a.letrehozva ASC",
            ['kerdes_id' => $kerdesId]
        );

        return array_map(fn($row) => ValaszEntity::fromArray($row), $rows);
    }

    /**
     * Felhasználó válaszai
     * @return ValaszEntity[]
     */
    public function findByFelhasznalo(int $felhasznaloId, int $limit = 20, int $offset = 0): array
    {
        $rows = $this->db->fetchAll(
            "SELECT a.*, u.felhasznalonev, u.reputacio_szazalek
             FROM answers a
             JOIN users u ON a.felhasznalo_id = u.id
             WHERE a.felhasznalo_id = :felhasznalo_id
             ORDER BY a.letrehozva DESC
             LIMIT :limit OFFSET :offset",
            ['felhasznalo_id' => $felhasznaloId, 'limit' => $limit, 'offset' => $offset]
        );

        return array_map(fn($row) => ValaszEntity::fromArray($row), $rows);
    }

    /**
     * Új válasz létrehozása
     */
    public function create(ValaszEntity $valasz): ValaszEntity
    {
        $this->db->execute(
            "INSERT INTO answers (kerdes_id, felhasznalo_id, tartalom)
             VALUES (:kerdes_id, :felhasznalo_id, :tartalom)",
            [
                'kerdes_id' => $valasz->kerdesId,
                'felhasznalo_id' => $valasz->felhasznaloId,
                'tartalom' => $valasz->tartalom,
            ]
        );

        $id = $this->db->lastInsertId();
        return $this->findById($id);
    }

    /**
     * Hasznos szavazat növelése
     */
    public function incrementHasznosSzavazat(int $id): void
    {
        $this->db->execute(
            "UPDATE answers SET hasznos_szavazat = hasznos_szavazat + 1 WHERE id = :id",
            ['id' => $id]
        );
    }

    /**
     * Nem hasznos szavazat növelése
     */
    public function incrementNemHasznosSzavazat(int $id): void
    {
        $this->db->execute(
            "UPDATE answers SET nem_hasznos_szavazat = nem_hasznos_szavazat + 1 WHERE id = :id",
            ['id' => $id]
        );
    }

    /**
     * Válasz törlése
     */
    public function delete(int $id): void
    {
        $this->db->execute("DELETE FROM answers WHERE id = :id", ['id' => $id]);
    }

    /**
     * Felhasználó válaszainak száma
     */
    public function countByFelhasznalo(int $felhasznaloId): int
    {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as cnt FROM answers WHERE felhasznalo_id = :id",
            ['id' => $felhasznaloId]
        );
        return (int) ($result['cnt'] ?? 0);
    }

    /**
     * Összes válasz száma
     */
    public function countAll(): int
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM answers");
        return (int) ($result['cnt'] ?? 0);
    }

    /**
     * Válasz zárolása UPDATE-tel (szavazáshoz)
     */
    public function findByIdForUpdate(int $id): ?ValaszEntity
    {
        $data = $this->db->fetchOneForUpdate(
            "SELECT a.*, u.felhasznalonev, u.reputacio_szazalek
             FROM answers a
             JOIN users u ON a.felhasznalo_id = u.id
             WHERE a.id = :id",
            ['id' => $id]
        );

        return $data ? ValaszEntity::fromArray($data) : null;
    }
}
