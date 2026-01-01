<?php

declare(strict_types=1);

namespace App\Domain\Szavazas;

use App\Core\Database;
use App\Domain\Valasz\ValaszRepository;
use App\Domain\Felhasznalo\FelhasznaloRepository;

/**
 * Szavazás szolgáltatás - tranzakció-biztos szavazás kezelés
 */
class SzavazasService
{
    public function __construct(
        private Database $db,
        private ValaszRepository $valaszRepository,
        private FelhasznaloRepository $felhasznaloRepository
    ) {}

    /**
     * Szavazás egy válaszra
     * 
     * @param int $felhasznaloId A szavazó felhasználó
     * @param int $valaszId A válasz ID-ja
     * @param int $irany +1 = hasznos, -1 = nem hasznos
     * @throws \RuntimeException Ha már szavazott vagy hibás a művelet
     */
    public function szavaz(int $felhasznaloId, int $valaszId, int $irany): void
    {
        if (!in_array($irany, [1, -1], true)) {
            throw new \InvalidArgumentException('Érvénytelen szavazat irány.');
        }

        $this->db->transaction(function (Database $db) use ($felhasznaloId, $valaszId, $irany) {
            // Ellenőrzés: már szavazott-e
            $letezik = $db->fetchOne(
                "SELECT id FROM votes WHERE felhasznalo_id = :felhasznalo_id AND valasz_id = :valasz_id FOR UPDATE",
                ['felhasznalo_id' => $felhasznaloId, 'valasz_id' => $valaszId]
            );

            if ($letezik !== null) {
                throw new \RuntimeException('Már szavaztál erre a válaszra.');
            }

            // Válasz zárolása és lekérdezése
            $valasz = $db->fetchOneForUpdate(
                "SELECT * FROM answers WHERE id = :id",
                ['id' => $valaszId]
            );

            if ($valasz === null) {
                throw new \RuntimeException('A válasz nem található.');
            }

            // Saját válaszra nem lehet szavazni
            if ((int) $valasz['felhasznalo_id'] === $felhasznaloId) {
                throw new \RuntimeException('Saját válaszra nem szavazhatsz.');
            }

            // Szavazat rögzítése
            $db->execute(
                "INSERT INTO votes (felhasznalo_id, valasz_id, irany) VALUES (:felhasznalo_id, :valasz_id, :irany)",
                ['felhasznalo_id' => $felhasznaloId, 'valasz_id' => $valaszId, 'irany' => $irany]
            );

            // Válasz szavazat frissítése
            if ($irany === 1) {
                $db->execute(
                    "UPDATE answers SET hasznos_szavazat = hasznos_szavazat + 1 WHERE id = :id",
                    ['id' => $valaszId]
                );
            } else {
                $db->execute(
                    "UPDATE answers SET nem_hasznos_szavazat = nem_hasznos_szavazat + 1 WHERE id = :id",
                    ['id' => $valaszId]
                );
            }

            // Válaszoló reputációjának újraszámítása
            $valaszoloId = (int) $valasz['felhasznalo_id'];
            $reputacio = $this->szamoljReputaciot($db, $valaszoloId);
            
            $db->execute(
                "UPDATE users SET reputacio_szazalek = :reputacio WHERE id = :id",
                ['reputacio' => $reputacio, 'id' => $valaszoloId]
            );
        });
    }

    /**
     * Felhasználó reputációjának számítása
     */
    private function szamoljReputaciot(Database $db, int $felhasznaloId): float
    {
        $result = $db->fetchOne(
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

    /**
     * Ellenőrzés: szavazott-e már a felhasználó
     */
    public function marSzavazott(int $felhasznaloId, int $valaszId): bool
    {
        $result = $this->db->fetchOne(
            "SELECT id FROM votes WHERE felhasznalo_id = :felhasznalo_id AND valasz_id = :valasz_id",
            ['felhasznalo_id' => $felhasznaloId, 'valasz_id' => $valaszId]
        );

        return $result !== null;
    }

    /**
     * Felhasználó szavazata lekérdezése
     */
    public function getSzavazat(int $felhasznaloId, int $valaszId): ?int
    {
        $result = $this->db->fetchOne(
            "SELECT irany FROM votes WHERE felhasznalo_id = :felhasznalo_id AND valasz_id = :valasz_id",
            ['felhasznalo_id' => $felhasznaloId, 'valasz_id' => $valaszId]
        );

        return $result !== null ? (int) $result['irany'] : null;
    }
}
