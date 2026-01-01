<?php

declare(strict_types=1);

namespace App\Domain\Kerdes;

use App\Core\Database;

/**
 * Kérdés repository
 */
class KerdesRepository
{
    public function __construct(
        private Database $db
    ) {}

    /**
     * Kérdés keresése ID alapján
     */
    public function findById(int $id): ?KerdesEntity
    {
        $data = $this->db->fetchOne(
            "SELECT q.*, u.felhasznalonev, c.nev as kategoria_nev, c.slug as kategoria_slug
             FROM questions q
             JOIN users u ON q.felhasznalo_id = u.id
             JOIN categories c ON q.kategoria_id = c.id
             WHERE q.id = :id",
            ['id' => $id]
        );

        return $data ? KerdesEntity::fromArray($data) : null;
    }

    /**
     * Legújabb kérdések lekérdezése
     * @return KerdesEntity[]
     */
    public function findLegujabbak(int $limit = 20, int $offset = 0): array
    {
        $rows = $this->db->fetchAll(
            "SELECT q.*, u.felhasznalonev, c.nev as kategoria_nev, c.slug as kategoria_slug
             FROM questions q
             JOIN users u ON q.felhasznalo_id = u.id
             JOIN categories c ON q.kategoria_id = c.id
             ORDER BY q.letrehozva DESC
             LIMIT :limit OFFSET :offset",
            ['limit' => $limit, 'offset' => $offset]
        );

        return array_map(fn($row) => KerdesEntity::fromArray($row), $rows);
    }

    /**
     * Kérdések kategória alapján
     * @return KerdesEntity[]
     */
    public function findByKategoria(int $kategoriaId, int $limit = 20, int $offset = 0): array
    {
        $rows = $this->db->fetchAll(
            "SELECT q.*, u.felhasznalonev, c.nev as kategoria_nev, c.slug as kategoria_slug
             FROM questions q
             JOIN users u ON q.felhasznalo_id = u.id
             JOIN categories c ON q.kategoria_id = c.id
             WHERE q.kategoria_id = :kategoria_id
             ORDER BY q.letrehozva DESC
             LIMIT :limit OFFSET :offset",
            ['kategoria_id' => $kategoriaId, 'limit' => $limit, 'offset' => $offset]
        );

        return array_map(fn($row) => KerdesEntity::fromArray($row), $rows);
    }

    /**
     * Kérdések kategória és alkategóriái alapján
     * @param int[] $kategoriaIds
     * @return KerdesEntity[]
     */
    public function findByKategoriak(array $kategoriaIds, int $limit = 20, int $offset = 0): array
    {
        if (empty($kategoriaIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($kategoriaIds), '?'));
        
        $rows = $this->db->fetchAll(
            "SELECT q.*, u.felhasznalonev, c.nev as kategoria_nev, c.slug as kategoria_slug
             FROM questions q
             JOIN users u ON q.felhasznalo_id = u.id
             JOIN categories c ON q.kategoria_id = c.id
             WHERE q.kategoria_id IN ($placeholders)
             ORDER BY q.letrehozva DESC
             LIMIT ? OFFSET ?",
            [...$kategoriaIds, $limit, $offset]
        );

        return array_map(fn($row) => KerdesEntity::fromArray($row), $rows);
    }

    /**
     * Felhasználó kérdései
     * @return KerdesEntity[]
     */
    public function findByFelhasznalo(int $felhasznaloId, int $limit = 20, int $offset = 0): array
    {
        $rows = $this->db->fetchAll(
            "SELECT q.*, u.felhasznalonev, c.nev as kategoria_nev, c.slug as kategoria_slug
             FROM questions q
             JOIN users u ON q.felhasznalo_id = u.id
             JOIN categories c ON q.kategoria_id = c.id
             WHERE q.felhasznalo_id = :felhasznalo_id
             ORDER BY q.letrehozva DESC
             LIMIT :limit OFFSET :offset",
            ['felhasznalo_id' => $felhasznaloId, 'limit' => $limit, 'offset' => $offset]
        );

        return array_map(fn($row) => KerdesEntity::fromArray($row), $rows);
    }

    /**
     * Megválaszolatlan kérdések
     * @return KerdesEntity[]
     */
    public function findMegvalaszolatlan(int $limit = 20, int $offset = 0): array
    {
        $rows = $this->db->fetchAll(
            "SELECT q.*, u.felhasznalonev, c.nev as kategoria_nev, c.slug as kategoria_slug
             FROM questions q
             JOIN users u ON q.felhasznalo_id = u.id
             JOIN categories c ON q.kategoria_id = c.id
             WHERE q.valaszok_szama = 0
             ORDER BY q.letrehozva DESC
             LIMIT :limit OFFSET :offset",
            ['limit' => $limit, 'offset' => $offset]
        );

        return array_map(fn($row) => KerdesEntity::fromArray($row), $rows);
    }

    /**
     * Keresés szöveg alapján (Full-text search)
     * @return KerdesEntity[]
     */
    public function search(string $kulcsszo, int $limit = 50): array
    {
        // Full-text search használata
        $rows = $this->db->fetchAll(
            "SELECT q.*, u.felhasznalonev, c.nev as kategoria_nev, c.slug as kategoria_slug,
                    MATCH(q.cim, q.tartalom) AGAINST(:kulcsszo IN NATURAL LANGUAGE MODE) as relevancia
             FROM questions q
             JOIN users u ON q.felhasznalo_id = u.id
             JOIN categories c ON q.kategoria_id = c.id
             WHERE MATCH(q.cim, q.tartalom) AGAINST(:kulcsszo IN NATURAL LANGUAGE MODE)
             ORDER BY relevancia DESC
             LIMIT :limit",
            ['kulcsszo' => $kulcsszo, 'limit' => $limit]
        );

        return array_map(fn($row) => KerdesEntity::fromArray($row), $rows);
    }

    /**
     * Új kérdés létrehozása
     */
    public function create(KerdesEntity $kerdes): KerdesEntity
    {
        $this->db->execute(
            "INSERT INTO questions (felhasznalo_id, kategoria_id, cim, slug, tartalom)
             VALUES (:felhasznalo_id, :kategoria_id, :cim, :slug, :tartalom)",
            [
                'felhasznalo_id' => $kerdes->felhasznaloId,
                'kategoria_id' => $kerdes->kategoriaId,
                'cim' => $kerdes->cim,
                'slug' => $kerdes->slug,
                'tartalom' => $kerdes->tartalom,
            ]
        );

        $id = $this->db->lastInsertId();
        return $this->findById($id);
    }

    /**
     * Megtekintések növelése
     */
    public function incrementMegtekintes(int $id): void
    {
        $this->db->execute(
            "UPDATE questions SET megtekintesek = megtekintesek + 1 WHERE id = :id",
            ['id' => $id]
        );
    }

    /**
     * Válaszok számának növelése
     */
    public function incrementValaszokSzama(int $id): void
    {
        $this->db->execute(
            "UPDATE questions SET valaszok_szama = valaszok_szama + 1 WHERE id = :id",
            ['id' => $id]
        );
    }

    /**
     * Kérdés törlése
     */
    public function delete(int $id): void
    {
        $this->db->execute("DELETE FROM questions WHERE id = :id", ['id' => $id]);
    }

    /**
     * Kérdések száma kategóriában
     */
    public function countByKategoria(int $kategoriaId): int
    {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as cnt FROM questions WHERE kategoria_id = :id",
            ['id' => $kategoriaId]
        );
        return (int) ($result['cnt'] ?? 0);
    }

    /**
     * Összes kérdés száma
     */
    public function countAll(): int
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM questions");
        return (int) ($result['cnt'] ?? 0);
    }
}
