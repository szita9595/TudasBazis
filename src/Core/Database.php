<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Adatbázis kapcsolat wrapper - PDO Prepared Statements
 */
class Database
{
    private PDO $pdo;
    private static ?Database $instance = null;

    public function __construct(
        string $host,
        string $database,
        string $username,
        string $password,
        string $charset = 'utf8mb4',
        int $port = 3306
    ) {
        $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=$charset";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $charset"
        ];

        try {
            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new PDOException("Adatbázis kapcsolódási hiba: " . $e->getMessage());
        }
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * SELECT lekérdezés prepared statement-tel
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Egy sor lekérdezése
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result === false ? null : $result;
    }

    /**
     * Összes sor lekérdezése
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * INSERT/UPDATE/DELETE végrehajtása
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Utolsó beszúrt ID lekérdezése
     */
    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Tranzakció indítása
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Tranzakció véglegesítése
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Tranzakció visszagörgetése
     */
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Tranzakcióban futtatás
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Sor zárolása FOR UPDATE-tel
     */
    public function fetchOneForUpdate(string $sql, array $params = []): ?array
    {
        if (stripos($sql, 'FOR UPDATE') === false) {
            $sql .= ' FOR UPDATE';
        }
        return $this->fetchOne($sql, $params);
    }
}
