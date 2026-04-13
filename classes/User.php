<?php
require_once __DIR__ . '/Model.php';
class User extends Model {
    protected string $table = 'users';
    public function create(array $data): bool {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (full_name,email,passport_no,birthday,contact_no,address,password_hash) VALUES (:full_name,:email,:passport_no,:birthday,:contact_no,:address,:password_hash)");
        return $stmt->execute([
            ':full_name' => trim($data['full_name']),
            ':email' => strtolower(trim($data['email'])),
            ':passport_no' => trim($data['passport_no']),
            ':birthday' => $data['birthday'] !== '' ? $data['birthday'] : null,
            ':contact_no' => trim($data['contact_no']),
            ':address' => trim($data['address']),
            ':password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
        ]);
    }
    public function read(): array {
        $stmt = $this->conn->prepare("SELECT id,full_name,email,passport_no,birthday,contact_no,address,created_at FROM {$this->table} ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function update(int $id, array $data): bool {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET full_name=:full_name, email=:email, passport_no=:passport_no, birthday=:birthday, contact_no=:contact_no, address=:address WHERE id=:id");
        return $stmt->execute([
            ':full_name' => trim($data['full_name']),
            ':email' => strtolower(trim($data['email'])),
            ':passport_no' => trim($data['passport_no']),
            ':birthday' => $data['birthday'] !== '' ? $data['birthday'] : null,
            ':contact_no' => trim($data['contact_no']),
            ':address' => trim($data['address']),
            ':id' => $id,
        ]);
    }
    public function delete(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id=:id");
        return $stmt->execute([':id'=>$id]);
    }
    public function findByEmail(string $email): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE email=:email LIMIT 1");
        $stmt->execute([':email' => strtolower(trim($email))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
    public function getById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id=:id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
    public function emailExists(string $email, ?int $ignoreId = null): bool {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table} WHERE email=:email";
        $params = [':email' => strtolower(trim($email))];
        if ($ignoreId !== null) { $sql .= " AND id!=:id"; $params[':id'] = $ignoreId; }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return ((int)($row['total'] ?? 0)) > 0;
    }
    public function authenticate(string $email, string $password): ?array {
        $user = $this->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) return null;
        return $user;
    }
    public function resetPassword(string $email, string $passportNo, string $newPassword): bool {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET password_hash=:password_hash WHERE email=:email AND passport_no=:passport_no");
        return $stmt->execute([
            ':password_hash' => password_hash($newPassword, PASSWORD_BCRYPT),
            ':email' => strtolower(trim($email)),
            ':passport_no' => trim($passportNo),
        ]);
    }
    public function updatePassword(int $id, string $currentPassword, string $newPassword): bool {
        $user = $this->getById($id);
        if (!$user || !password_verify($currentPassword, $user['password_hash'])) return false;
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET password_hash=:password_hash WHERE id=:id");
        return $stmt->execute([':password_hash' => password_hash($newPassword, PASSWORD_BCRYPT), ':id' => $id]);
    }
}
?>