<?php

declare(strict_types=1);

namespace App\Domain\Felhasznalo;

use App\Core\Database;

/**
 * Felhasználó repository - adatbázis műveletek
 */
class FelhasznaloRepository
{
    public function __construct(
        private Database $db
    ) {}

    /**
     * Felhasználó keresése ID alapján
     */
    public function findById(int $id): ?FelhasznaloEntity
    {
        $data = $this->db->fetchOne(
            "SELECT * FROM users WHERE id = :id",
            ['id' => $id]
        );

        return $data ? FelhasznaloEntity::fromArray($data) : null;
    }

    /**
     * Felhasználó keresése email alapján
     */
    public function findByEmail(string $email): ?FelhasznaloEntity
    {
        $data = $this->db->fetchOne(
            "SELECT * FROM users WHERE email = :email",
            ['email' => $email]
        );

        return $data ? FelhasznaloEntity::fromArray($data) : null;
    }

    /**
     * Felhasználó keresése felhasználónév alapján
     */
    public function findByFelhasznalonev(string $felhasznalonev): ?FelhasznaloEntity
    {
        $data = $this->db->fetchOne(
            "SELECT * FROM users WHERE felhasznalonev = :felhasznalonev",
            ['felhasznalonev' => $felhasznalonev]
        );

        return $data ? FelhasznaloEntity::fromArray($data) : null;
    }

    /**
     * Új felhasználó létrehozása
     */
    public function create(FelhasznaloEntity $felhasznalo): FelhasznaloEntity
    {
        $this->db->execute(
            "INSERT INTO users (felhasznalonev, email, jelszo_hash, szerep, nev_latszik)
             VALUES (:felhasznalonev, :email, :jelszo_hash, :szerep, :nev_latszik)",
            [
                'felhasznalonev' => $felhasznalo->felhasznalonev,
                'email' => $felhasznalo->email,
                'jelszo_hash' => $felhasznalo->jelszoHash,
                'szerep' => $felhasznalo->szerep,
                'nev_latszik' => $felhasznalo->nevLatszik ? 1 : 0,
            ]
        );

        $id = $this->db->lastInsertId();
        return $this->findById($id);
    }

    /**
     * Utolsó belépés frissítése
     */
    public function updateUtolsoBelepes(int $id): void
    {
        $this->db->execute(
            "UPDATE users SET utolso_belepes = NOW() WHERE id = :id",
            ['id' => $id]
        );
    }

    /**
     * Reputáció frissítése
     */
    public function updateReputacio(int $id, float $szazalek): void
    {
        $this->db->execute(
            "UPDATE users SET reputacio_szazalek = :szazalek WHERE id = :id",
            ['id' => $id, 'szazalek' => $szazalek]
        );
    }

    /**
     * Felhasználó tiltása
     */
    public function tilt(int $id, string $ok): void
    {
        $this->db->execute(
            "UPDATE users SET tiltva = 1, tiltas_oka = :ok WHERE id = :id",
            ['id' => $id, 'ok' => $ok]
        );
    }

    /**
     * Felhasználó tiltás feloldása
     */
    public function tiltasFeloldas(int $id): void
    {
        $this->db->execute(
            "UPDATE users SET tiltva = 0, tiltas_oka = NULL WHERE id = :id",
            ['id' => $id]
        );
    }

    /**
     * Email létezik-e már
     */
    public function emailLetezik(string $email): bool
    {
        $result = $this->db->fetchOne(
            "SELECT 1 FROM users WHERE email = :email LIMIT 1",
            ['email' => $email]
        );
        return $result !== null;
    }

    /**
     * Felhasználónév létezik-e már
     */
    public function felhasznalonevLetezik(string $felhasznalonev): bool
    {
        $result = $this->db->fetchOne(
            "SELECT 1 FROM users WHERE felhasznalonev = :felhasznalonev LIMIT 1",
            ['felhasznalonev' => $felhasznalonev]
        );
        return $result !== null;
    }

    /**
     * Felhasználó reputációjának újraszámítása a válaszok szavazatai alapján
     */
    public function szamoljReputaciot(int $felhasznaloId): float
    {
        $result = $this->db->fetchOne(
            "SELECT 
                COALESCE(SUM(hasznos_szavazat), 0) as hasznos,
                COALESCE(SUM(nem_hasznos_szavazat), 0) as nem_hasznos
             FROM answers 
             WHERE felhasznalo_id = :id",
            ['id' => $felhasznaloId]
        );

        $hasznos = (int) ($result['hasznos'] ?? 0);
        $nemHasznos = (int) ($result['nem_hasznos'] ?? 0);
        $osszes = $hasznos + $nemHasznos;

        if ($osszes === 0) {
            return 0.0;
        }

        return round(($hasznos / $osszes) * 100, 2);
    }
}
