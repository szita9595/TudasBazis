<?php

declare(strict_types=1);

namespace App\Domain\Kerdes;

/**
 * Kérdés entitás
 */
class KerdesEntity
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $felhasznaloId,
        public readonly int $kategoriaId,
        public readonly string $cim,
        public readonly string $slug,
        public readonly string $tartalom,
        public readonly int $valaszokSzama = 0,
        public readonly int $megtekintesek = 0,
        public readonly ?string $letrehozva = null,
        public readonly ?string $frissitve = null,
        public readonly bool $lezarva = false,
        // Kapcsolódó adatok (JOIN-ból)
        public readonly ?string $felhasznalonev = null,
        public readonly ?string $kategoriaNev = null,
        public readonly ?string $kategoriaSlug = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            felhasznaloId: (int) $data['felhasznalo_id'],
            kategoriaId: (int) $data['kategoria_id'],
            cim: $data['cim'],
            slug: $data['slug'],
            tartalom: $data['tartalom'],
            valaszokSzama: (int) ($data['valaszok_szama'] ?? 0),
            megtekintesek: (int) ($data['megtekintesek'] ?? 0),
            letrehozva: $data['letrehozva'] ?? null,
            frissitve: $data['frissitve'] ?? null,
            lezarva: (bool) ($data['lezarva'] ?? false),
            felhasznalonev: $data['felhasznalonev'] ?? null,
            kategoriaNev: $data['kategoria_nev'] ?? null,
            kategoriaSlug: $data['kategoria_slug'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'felhasznalo_id' => $this->felhasznaloId,
            'kategoria_id' => $this->kategoriaId,
            'cim' => $this->cim,
            'slug' => $this->slug,
            'tartalom' => $this->tartalom,
            'valaszok_szama' => $this->valaszokSzama,
            'megtekintesek' => $this->megtekintesek,
            'letrehozva' => $this->letrehozva,
            'frissitve' => $this->frissitve,
            'lezarva' => $this->lezarva ? 1 : 0,
        ];
    }

    /**
     * URL-barát slug generálása címből
     */
    public static function generateSlug(string $cim): string
    {
        // Ékezetek cseréje
        $slug = str_replace(
            ['á', 'é', 'í', 'ó', 'ö', 'ő', 'ú', 'ü', 'ű', 'Á', 'É', 'Í', 'Ó', 'Ö', 'Ő', 'Ú', 'Ü', 'Ű'],
            ['a', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'u', 'a', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'u'],
            $cim
        );
        
        // Kisbetűsítés
        $slug = mb_strtolower($slug);
        
        // Nem alfanumerikus karakterek cseréje kötőjelre
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        
        // Szélső kötőjelek eltávolítása
        $slug = trim($slug, '-');
        
        // Maximum 100 karakter
        return substr($slug, 0, 100);
    }
}
