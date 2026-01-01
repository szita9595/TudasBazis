<?php

declare(strict_types=1);

namespace App\Domain\Felhasznalo;

/**
 * Felhasználó entitás
 */
class FelhasznaloEntity
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $felhasznalonev,
        public readonly string $email,
        public readonly string $jelszoHash,
        public readonly string $szerep = 'user',
        public readonly float $reputacioSzazalek = 0.0,
        public readonly bool $nevLatszik = false,
        public readonly ?string $letrehozva = null,
        public readonly ?string $utolsoBelepes = null,
        public readonly bool $tiltva = false,
        public readonly ?string $tiltasOka = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            felhasznalonev: $data['felhasznalonev'],
            email: $data['email'],
            jelszoHash: $data['jelszo_hash'] ?? '',
            szerep: $data['szerep'] ?? 'user',
            reputacioSzazalek: isset($data['reputacio_szazalek']) ? (float) $data['reputacio_szazalek'] : 0.0,
            nevLatszik: (bool) ($data['nev_latszik'] ?? false),
            letrehozva: $data['letrehozva'] ?? null,
            utolsoBelepes: $data['utolso_belepes'] ?? null,
            tiltva: (bool) ($data['tiltva'] ?? false),
            tiltasOka: $data['tiltas_oka'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'felhasznalonev' => $this->felhasznalonev,
            'email' => $this->email,
            'jelszo_hash' => $this->jelszoHash,
            'szerep' => $this->szerep,
            'reputacio_szazalek' => $this->reputacioSzazalek,
            'nev_latszik' => $this->nevLatszik ? 1 : 0,
            'letrehozva' => $this->letrehozva,
            'utolso_belepes' => $this->utolsoBelepes,
            'tiltva' => $this->tiltva ? 1 : 0,
            'tiltas_oka' => $this->tiltasOka,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->szerep === 'admin';
    }

    public function isModerator(): bool
    {
        return in_array($this->szerep, ['admin', 'moderator'], true);
    }
}
