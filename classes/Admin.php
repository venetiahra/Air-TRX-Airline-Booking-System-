<?php
class Admin {
    private PDO $conn;
    private string $table = 'admins';
    public function __construct(PDO $db) { $this->conn = $db; }
    public function authenticate(string $username, string $password): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => trim($username)]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($password, $row['password_hash'])) return null;
        return $row;
    }
}
?>