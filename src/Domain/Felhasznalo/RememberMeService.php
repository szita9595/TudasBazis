<?php

declare(strict_types=1);

namespace App\Domain\Felhasznalo;

use App\Core\Database;

/**
 * Belépve Maradok (Remember Me) Szolgáltatás
 * 
 * Biztonságos "emlékezz rám" funkció implementációja.
 * A token-t hash-elve tároljuk az adatbázisban.
 */
class RememberMeService
{
    private const COOKIE_NAME = 'remember_me';
    private const TOKEN_EXPIRY_DAYS = 30;

    public function __construct(
        private Database $db
    ) {}

    /**
     * Remember me token létrehozása és cookie beállítása
     */
    public function empitenEmlekezz(int $felhasznaloId): void
    {
        // Véletlenszerű token generálása
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        
        // Lejárati idő
        $lejar = date('Y-m-d H:i:s', strtotime('+' . self::TOKEN_EXPIRY_DAYS . ' days'));

        // Token mentése az adatbázisba
        $this->db->query(
            "INSERT INTO remember_tokens (felhasznalo_id, token_hash, lejar) VALUES (:felhasznalo_id, :token_hash, :lejar)",
            [
                'felhasznalo_id' => $felhasznaloId,
                'token_hash' => $tokenHash,
                'lejar' => $lejar
            ]
        );

        // Cookie beállítása (felhasznalo_id:token formátumban)
        $cookieErtek = $felhasznaloId . ':' . $token;
        $this->setCookie($cookieErtek, self::TOKEN_EXPIRY_DAYS);
    }

    /**
     * Felhasználó azonosítása a remember me cookie alapján
     * 
     * @return int|null Felhasználó ID vagy null ha nincs érvényes cookie
     */
    public function azonositCookieAlapjan(): ?int
    {
        $cookieErtek = $_COOKIE[self::COOKIE_NAME] ?? null;
        
        if ($cookieErtek === null) {
            return null;
        }

        // Cookie feldolgozása
        $parts = explode(':', $cookieErtek, 2);
        if (count($parts) !== 2) {
            $this->torolCookie();
            return null;
        }

        [$felhasznaloId, $token] = $parts;
        $felhasznaloId = (int) $felhasznaloId;
        $tokenHash = hash('sha256', $token);

        // Token keresése az adatbázisban
        $sor = $this->db->fetchOne(
            "SELECT id, felhasznalo_id FROM remember_tokens 
             WHERE felhasznalo_id = :felhasznalo_id 
             AND token_hash = :token_hash 
             AND lejar > NOW()",
            [
                'felhasznalo_id' => $felhasznaloId,
                'token_hash' => $tokenHash
            ]
        );

        if ($sor === null) {
            $this->torolCookie();
            return null;
        }

        // Token rotáció biztonság miatt - régi token törlése, új generálása
        $this->db->query(
            "DELETE FROM remember_tokens WHERE id = :id",
            ['id' => $sor['id']]
        );

        // Új token generálása (token rotation)
        $this->empitenEmlekezz($felhasznaloId);

        return $felhasznaloId;
    }

    /**
     * Remember me cookie és tokenek törlése (kijelentkezéskor)
     */
    public function elfelejtFelhasznalo(int $felhasznaloId): void
    {
        // Összes token törlése az adatbázisból
        $this->db->query(
            "DELETE FROM remember_tokens WHERE felhasznalo_id = :felhasznalo_id",
            ['felhasznalo_id' => $felhasznaloId]
        );

        $this->torolCookie();
    }

    /**
     * Lejárt tokenek törlése (takarítás)
     */
    public function takaritLejartTokenek(): int
    {
        $stmt = $this->db->query(
            "DELETE FROM remember_tokens WHERE lejar < NOW()"
        );
        
        return $stmt->rowCount();
    }

    /**
     * Cookie beállítása
     */
    private function setCookie(string $ertek, int $napok): void
    {
        $lejar = time() + ($napok * 24 * 60 * 60);
        
        setcookie(
            self::COOKIE_NAME,
            $ertek,
            [
                'expires' => $lejar,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }

    /**
     * Cookie törlése
     */
    private function torolCookie(): void
    {
        if (isset($_COOKIE[self::COOKIE_NAME])) {
            setcookie(
                self::COOKIE_NAME,
                '',
                [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );
            unset($_COOKIE[self::COOKIE_NAME]);
        }
    }
}
