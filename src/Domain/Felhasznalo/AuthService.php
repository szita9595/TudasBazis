<?php

declare(strict_types=1);

namespace App\Domain\Felhasznalo;

use App\Core\Session;

/**
 * Autentikációs szolgáltatás - felhasználói műveletek
 */
class AuthService
{
    public function __construct(
        private FelhasznaloRepository $repository,
        private Session $session
    ) {}

    /**
     * Új felhasználó regisztrálása
     * 
     * @throws \InvalidArgumentException ha az adatok hibásak
     * @throws \RuntimeException ha az email vagy felhasználónév foglalt
     */
    public function regisztral(
        string $felhasznalonev,
        string $email,
        string $jelszo,
        string $jelszoMegerosites,
        bool $nevLatszik = false
    ): FelhasznaloEntity {
        // Validálás
        $this->validateFelhasznalonev($felhasznalonev);
        $this->validateEmail($email);
        $this->validateJelszo($jelszo, $jelszoMegerosites);

        // Egyediség ellenőrzése
        if ($this->repository->emailLetezik($email)) {
            throw new \RuntimeException('Ez az email cím már foglalt.');
        }

        if ($this->repository->felhasznalonevLetezik($felhasznalonev)) {
            throw new \RuntimeException('Ez a felhasználónév már foglalt.');
        }

        // Jelszó hashelése
        $jelszoHash = password_hash($jelszo, PASSWORD_ARGON2ID);

        // Entitás létrehozása
        $felhasznalo = new FelhasznaloEntity(
            id: null,
            felhasznalonev: $felhasznalonev,
            email: $email,
            jelszoHash: $jelszoHash,
            nevLatszik: $nevLatszik
        );

        return $this->repository->create($felhasznalo);
    }

    /**
     * Bejelentkezés
     * 
     * @throws \RuntimeException ha a bejelentkezés sikertelen
     */
    public function bejelentkezik(string $email, string $jelszo): FelhasznaloEntity
    {
        $felhasznalo = $this->repository->findByEmail($email);

        if ($felhasznalo === null) {
            throw new \RuntimeException('Hibás email cím vagy jelszó.');
        }

        if ($felhasznalo->tiltva) {
            throw new \RuntimeException('A fiókod le van tiltva. Ok: ' . ($felhasznalo->tiltasOka ?? 'Nincs megadva'));
        }

        if (!password_verify($jelszo, $felhasznalo->jelszoHash)) {
            throw new \RuntimeException('Hibás email cím vagy jelszó.');
        }

        // Session beállítása
        $this->session->setFelhasznaloId($felhasznalo->id);
        
        // Utolsó belépés frissítése
        $this->repository->updateUtolsoBelepes($felhasznalo->id);

        return $felhasznalo;
    }

    /**
     * Kijelentkezés
     */
    public function kijelentkezik(): void
    {
        $this->session->destroy();
    }

    /**
     * Aktuális bejelentkezett felhasználó lekérdezése
     */
    public function aktualisFelhasznalo(): ?FelhasznaloEntity
    {
        $felhasznaloId = $this->session->getFelhasznaloId();
        
        if ($felhasznaloId === null) {
            return null;
        }

        return $this->repository->findById($felhasznaloId);
    }

    /**
     * Be van-e jelentkezve valaki
     */
    public function bejelentkezveVan(): bool
    {
        return $this->session->isLoggedIn();
    }

    /**
     * Felhasználónév validálása
     */
    private function validateFelhasznalonev(string $felhasznalonev): void
    {
        if (strlen($felhasznalonev) < 3) {
            throw new \InvalidArgumentException('A felhasználónév legalább 3 karakter legyen.');
        }

        if (strlen($felhasznalonev) > 50) {
            throw new \InvalidArgumentException('A felhasználónév maximum 50 karakter lehet.');
        }

        if (!preg_match('/^[a-zA-Z0-9_éáűőúöüóíÉÁŰŐÚÖÜÓÍ]+$/', $felhasznalonev)) {
            throw new \InvalidArgumentException('A felhasználónév csak betűket, számokat és alulvonást tartalmazhat.');
        }
    }

    /**
     * Email validálása
     */
    private function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Érvénytelen email cím.');
        }
    }

    /**
     * Jelszó validálása
     */
    private function validateJelszo(string $jelszo, string $megerosites): void
    {
        if (strlen($jelszo) < 6) {
            throw new \InvalidArgumentException('A jelszó legalább 6 karakter legyen.');
        }

        if ($jelszo !== $megerosites) {
            throw new \InvalidArgumentException('A két jelszó nem egyezik.');
        }
    }

    /**
     * Felhasználó keresése ID alapján
     */
    public function findById(int $id): ?FelhasznaloEntity
    {
        return $this->repository->findById($id);
    }
}
