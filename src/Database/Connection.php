<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOStatement;

class Connection
{
    private static ?self $instance = null;
    private PDO $connection;

    private function __construct(string $dsn, string $username = "", string $password = "")
    {
        $this->connection = match (true) {
            str_starts_with($dsn, "sqlite") => new PDO($dsn),
            default => new PDO($dsn, $username, $password),
        };
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public static function getInstance(string $dsn, string $username = "", string $password = ""): self
    {
        if (self::$instance == null) {
            self::$instance = new self($dsn, $username, $password);
        }

        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * @return array<string>
     */
    public function getTables(): array
    {
        $driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
        $sql = match($driver) {
            "sqlite" => "SELECT name FROM sqlite_master WHERE type='table'",
            "mysql" => "SHOW TABLES",
            "pgsql" => "SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema'",
            default => "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'",
        };
        $stmt = $this->connection->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * @param array<int,mixed> $params
     */
    public function query(string $sql, array $params = []): ?PDOStatement
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt ?: null;
    }
}
