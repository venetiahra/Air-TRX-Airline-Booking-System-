<?php
require_once __DIR__ . '/Model.php';
class Booking extends Model {
    protected string $table = 'bookings';
    public function create(array $data): bool {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (user_id,flight_id,booking_reference,seat_no,seat_class,booking_status,fare_amount,payment_method,payment_status) VALUES (:user_id,:flight_id,:booking_reference,:seat_no,:seat_class,:booking_status,:fare_amount,:payment_method,:payment_status)");
        return $stmt->execute([
            ':user_id' => (int)$data['user_id'],
            ':flight_id' => (int)$data['flight_id'],
            ':booking_reference' => $data['booking_reference'],
            ':seat_no' => strtoupper(trim($data['seat_no'])),
            ':seat_class' => trim($data['seat_class']),
            ':booking_status' => trim($data['booking_status']),
            ':fare_amount' => $data['fare_amount'],
            ':payment_method' => trim($data['payment_method']),
            ':payment_status' => trim($data['payment_status']),
        ]);
    }
    public function read(): array {
        $sql = "SELECT b.*,u.full_name,u.email,u.passport_no,f.flight_code,f.origin,f.destination,f.departure_date,f.departure_time FROM {$this->table} b INNER JOIN users u ON b.user_id=u.id INNER JOIN flights f ON b.flight_id=f.id ORDER BY b.id DESC";
        $stmt=$this->conn->prepare($sql); $stmt->execute(); return $stmt->fetchAll();
    }
    public function update(int $id, array $data): bool {
        $stmt=$this->conn->prepare("UPDATE {$this->table} SET seat_no=:seat_no, seat_class=:seat_class, booking_status=:booking_status, fare_amount=:fare_amount, payment_method=:payment_method, payment_status=:payment_status WHERE id=:id");
        return $stmt->execute([':seat_no'=>strtoupper(trim($data['seat_no'])), ':seat_class'=>trim($data['seat_class']), ':booking_status'=>trim($data['booking_status']), ':fare_amount'=>$data['fare_amount'], ':payment_method'=>trim($data['payment_method']), ':payment_status'=>trim($data['payment_status']), ':id'=>$id]);
    }
    public function delete(int $id): bool { $stmt=$this->conn->prepare("DELETE FROM {$this->table} WHERE id=:id"); return $stmt->execute([':id'=>$id]); }
    public function getById(int $id): ?array { $stmt=$this->conn->prepare("SELECT * FROM {$this->table} WHERE id=:id LIMIT 1"); $stmt->execute([':id'=>$id]); $row=$stmt->fetch(); return $row ?: null; }
    public function getDetailedById(int $id): ?array {
        $sql = "SELECT b.*,u.full_name,u.email,u.passport_no,u.contact_no,u.address,u.birthday,f.flight_code,f.origin,f.destination,f.departure_date,f.departure_time FROM {$this->table} b INNER JOIN users u ON b.user_id=u.id INNER JOIN flights f ON b.flight_id=f.id WHERE b.id=:id LIMIT 1";
        $stmt=$this->conn->prepare($sql); $stmt->execute([':id'=>$id]); $row=$stmt->fetch(); return $row ?: null;
    }
    public function getByUserId(int $uid): array {
        $sql = "SELECT b.*,f.flight_code,f.origin,f.destination,f.departure_date,f.departure_time FROM {$this->table} b INNER JOIN flights f ON b.flight_id=f.id WHERE b.user_id=:user_id ORDER BY b.id DESC";
        $stmt=$this->conn->prepare($sql); $stmt->execute([':user_id'=>$uid]); return $stmt->fetchAll();
    }
    public function getBookedSeatsByFlight(int $flightId): array {
        $stmt=$this->conn->prepare("SELECT seat_no FROM {$this->table} WHERE flight_id=:flight_id AND booking_status!='Cancelled'"); $stmt->execute([':flight_id'=>$flightId]); return array_map(fn($r)=>strtoupper($r['seat_no']), $stmt->fetchAll());
    }
    public function isSeatAvailable(int $flightId, string $seatNo): bool {
        $stmt=$this->conn->prepare("SELECT COUNT(*) AS total FROM {$this->table} WHERE flight_id=:flight_id AND seat_no=:seat_no AND booking_status!='Cancelled'"); $stmt->execute([':flight_id'=>$flightId, ':seat_no'=>strtoupper(trim($seatNo))]); $row=$stmt->fetch(); return ((int)($row['total'] ?? 0))===0;
    }
    public function generateReference(): string { return 'ATX-' . strtoupper(bin2hex(random_bytes(3))); }
}
?>