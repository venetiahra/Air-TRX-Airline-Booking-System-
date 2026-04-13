<?php
require_once __DIR__ . '/Model.php';
class Flight extends Model {
    protected string $table = 'flights';

    public function create(array $data): bool {
        $sql = "INSERT INTO {$this->table} (flight_code, origin, destination, departure_date, departure_time, economy_fare, premium_fare, business_fare, first_class_fare) VALUES (:flight_code, :origin, :destination, :departure_date, :departure_time, :economy_fare, :premium_fare, :business_fare, :first_class_fare)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':flight_code' => strtoupper(trim($data['flight_code'])),
            ':origin' => trim($data['origin']),
            ':destination' => trim($data['destination']),
            ':departure_date' => $data['departure_date'],
            ':departure_time' => $data['departure_time'],
            ':economy_fare' => $data['economy_fare'],
            ':premium_fare' => $data['premium_fare'],
            ':business_fare' => $data['business_fare'],
            ':first_class_fare' => $data['first_class_fare'],
        ]);
    }

    public function read(): array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} ORDER BY departure_date ASC, departure_time ASC, id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE {$this->table} SET flight_code = :flight_code, origin = :origin, destination = :destination, departure_date = :departure_date, departure_time = :departure_time, economy_fare = :economy_fare, premium_fare = :premium_fare, business_fare = :business_fare, first_class_fare = :first_class_fare WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':flight_code' => strtoupper(trim($data['flight_code'])),
            ':origin' => trim($data['origin']),
            ':destination' => trim($data['destination']),
            ':departure_date' => $data['departure_date'],
            ':departure_time' => $data['departure_time'],
            ':economy_fare' => $data['economy_fare'],
            ':premium_fare' => $data['premium_fare'],
            ':business_fare' => $data['business_fare'],
            ':first_class_fare' => $data['first_class_fare'],
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function search(string $from = '', string $to = '', string $date = ''): array {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        if ($from !== '') { $sql .= " AND origin LIKE :origin"; $params[':origin'] = '%' . $from . '%'; }
        if ($to !== '') { $sql .= " AND destination LIKE :destination"; $params[':destination'] = '%' . $to . '%'; }
        if ($date !== '') { $sql .= " AND departure_date = :departure_date"; $params[':departure_date'] = $date; }
        $sql .= " ORDER BY departure_date ASC, departure_time ASC, id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
?>