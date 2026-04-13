public function create($data) {
    $sql = "INSERT INTO bookings (user_id, flight_id, booking_reference, seat_no, seat_class, booking_status, fare_amount, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $this->db->prepare($sql);
    $payment_method = $data['payment_method'] ?? 'Cash (24 hours)';
    $stmt->bind_param("iissssds", 
        $data['user_id'], $data['flight_id'], $data['booking_reference'], 
        $data['seat_no'], $data['seat_class'], $data['booking_status'], 
        $data['fare_amount'], $payment_method
    );
    return $stmt->execute();
}