-- Belépve maradok funkció - Remember Me tokens tábla
-- Futtatás: mysql -u root tudasbazis < database/remember_tokens.sql

CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    felhasznalo_id INT UNSIGNED NOT NULL,
    token_hash VARCHAR(64) NOT NULL COMMENT 'SHA-256 hash of the token',
    lejar DATETIME NOT NULL COMMENT 'Token expiration date',
    letrehozva DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (felhasznalo_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token_hash (token_hash),
    INDEX idx_lejar (lejar)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;
