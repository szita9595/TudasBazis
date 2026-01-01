<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Session kezelés
 */
class Session
{
    private bool $started = false;

    public function __construct()
    {
        $this->start();
    }

    public function start(): void
    {
        if ($this->started) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->started = true;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function clear(): void
    {
        $_SESSION = [];
    }

    public function destroy(): void
    {
        $this->clear();
        
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        $this->started = false;
    }

    public function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public function getId(): string
    {
        return session_id();
    }

    /**
     * Flash üzenet beállítása (egyszer jelenik meg)
     */
    public function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Flash üzenet lekérdezése és törlése
     */
    public function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /**
     * Van-e flash üzenet
     */
    public function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    /**
     * CSRF token generálása
     */
    public function getCsrfToken(): string
    {
        if (!$this->has('_csrf_token')) {
            $this->set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return $this->get('_csrf_token');
    }

    /**
     * CSRF token ellenőrzése
     */
    public function validateCsrfToken(string $token): bool
    {
        return hash_equals($this->getCsrfToken(), $token);
    }

    /**
     * Felhasználó ID tárolása bejelentkezéskor
     */
    public function setFelhasznaloId(int $id): void
    {
        $this->regenerate();
        $this->set('felhasznalo_id', $id);
    }

    /**
     * Felhasználó ID lekérdezése
     */
    public function getFelhasznaloId(): ?int
    {
        $id = $this->get('felhasznalo_id');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Be van-e jelentkezve a felhasználó
     */
    public function isLoggedIn(): bool
    {
        return $this->getFelhasznaloId() !== null;
    }
}
