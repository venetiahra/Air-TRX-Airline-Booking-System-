<?php
require_once __DIR__ . '/CrudInterface.php';
abstract class Model implements CrudInterface {
    protected PDO $conn;
    protected string $table;
    public function __construct(PDO $db) { $this->conn = $db; }
    abstract public function create(array $data): bool;
    abstract public function read(): array;
    abstract public function update(int $id, array $data): bool;
    abstract public function delete(int $id): bool;
    public function countAll(): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM {$this->table}");
        $stmt->execute();
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }
}
?>