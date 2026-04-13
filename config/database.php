<?php
class Database {
    private string $host = 'localhost';
    private string $dbname = 'air_trx_db';
    private string $username = 'root';
    private string $password = '';
    public ?PDO $conn = null;

    public function connect(): PDO {
        if ($this->conn instanceof PDO) {
            return $this->conn;
        }
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die('Database Connection Failed: ' . $e->getMessage());
        }
        return $this->conn;
    }
}
?>