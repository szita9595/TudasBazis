<?php

declare(strict_types=1);

namespace App\Domain\Valasz;

/**
 * Válasz entitás
 */
class ValaszEntity
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $kerdesId,
        public readonly int $felhasznaloId,
        public readonly string $tartalom,
        public readonly int $hasznosSzavazat = 0,
        public readonly int $nemHasznosSzavazat = 0,
        public readonly ?string $letrehozva = null,
        public readonly ?string $frissitve = null,
        // Kapcsolódó adatok (JOIN-ból)
        public readonly ?string $felhasznalonev = null,
        public readonly ?float $felhasznaloReputacio = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            kerdesId: (int) $data['kerdes_id'],
            felhasznaloId: (int) $data['felhasznalo_id'],
            tartalom: $data['tartalom'],
            hasznosSzavazat: (int) ($data['hasznos_szavazat'] ?? 0),
            nemHasznosSzavazat: (int) ($data['nem_hasznos_szavazat'] ?? 0),
            letrehozva: $data['letrehozva'] ?? null,
            frissitve: $data['frissitve'] ?? null,
            felhasznalonev: $data['felhasznalonev'] ?? null,
            felhasznaloReputacio: isset($data['reputacio_szazalek']) ? (float) $data['reputacio_szazalek'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'kerdes_id' => $this->kerdesId,
            'felhasznalo_id' => $this->felhasznaloId,
            'tartalom' => $this->tartalom,
            'hasznos_szavazat' => $this->hasznosSzavazat,
            'nem_hasznos_szavazat' => $this->nemHasznosSzavazat,
            'letrehozva' => $this->letrehozva,
            'frissitve' => $this->frissitve,
        ];
    }

    /**
     * Összes szavazat
     */
    public function getOsszesSzavazat(): int
    {
        return $this->hasznosSzavazat + $this->nemHasznosSzavazat;
    }

    /**
     * Hasznossági százalék
     */
    public function getHasznossagSzazalek(): float
    {
        $osszes = $this->getOsszesSzavazat();
        
        if ($osszes === 0) {
            return 0.0;
        }

        return round(($this->hasznosSzavazat / $osszes) * 100, 1);
    }
}
