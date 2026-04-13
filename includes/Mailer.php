<?php
class AppMailer
{
    private string $fromEmail = 'trxmcln@gmail.com';
    private string $fromName = 'Air-TRX';
    private string $smtpHost = 'smtp.gmail.com';
    private int $smtpPort = 587;
    private string $smtpUsername = 'trxmcln@gmail.com';
    private string $smtpPassword = 'chyj pljg agax leuw';
    private string $smtpSecure = 'tls';
    private string $lastError = '';

    private function loadPhpMailerIfAvailable(): bool
    {
        $paths = [
            __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php',
            __DIR__ . '/../phpmailer/src/PHPMailer.php',
        ];

        foreach ($paths as $phpmailerPath) {
            if (file_exists($phpmailerPath)) {
                require_once dirname($phpmailerPath) . '/PHPMailer.php';
                require_once dirname($phpmailerPath) . '/SMTP.php';
                require_once dirname($phpmailerPath) . '/Exception.php';
                return class_exists('PHPMailer\\PHPMailer\\PHPMailer');
            }
        }

        $this->lastError = 'PHPMailer files not found in vendor/PHPMailer/src or phpmailer/src.';
        return false;
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }

    private function formatText($value, bool $titleCase = true): string
    {
        $value = trim((string)$value);
        $value = preg_replace('/\s+/', ' ', $value);

        if ($value === '') {
            return '';
        }

        if ($titleCase) {
            if (function_exists('mb_convert_case')) {
                return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
            }
            return ucwords(strtolower($value));
        }

        return $value;
    }

    public function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        string $plainBody = ''
    ): bool {
        $this->lastError = '';

        if ($this->loadPhpMailerIfAvailable()) {
            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = $this->smtpHost;
                $mail->SMTPAuth = true;
                $mail->Username = $this->smtpUsername;
                $mail->Password = $this->smtpPassword;
                $mail->SMTPSecure = $this->smtpSecure;
                $mail->Port = $this->smtpPort;
                $mail->setFrom($this->fromEmail, $this->fromName);
                $mail->addAddress($toEmail, $toName);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $htmlBody;
                $mail->AltBody = $plainBody !== '' ? $plainBody : strip_tags($htmlBody);
                return $mail->send();
            } catch (Throwable $e) {
                $this->lastError = $e->getMessage();
                return false;
            }
        }

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $result = @mail($toEmail, $subject, $htmlBody, $headers);

        if (!$result && $this->lastError === '') {
            $this->lastError = 'mail() fallback failed.';
        }

        return $result;
    }

    public function sendBookingConfirmation(array $user, array $flight, array $booking): bool
    {
        $subject = 'Air-TRX Booking Confirmation - ' . ($booking['booking_reference'] ?? '');

        $html =
            '<h2 style="color:#213A6B;font-family:Arial, Helvetica, sans-serif;">Air-TRX Booking Confirmation</h2>'
            . '<p>Dear <strong>' . htmlspecialchars($this->formatText($user['full_name'] ?? 'Passenger')) . '</strong>,</p>'
            . '<p>Your booking has been confirmed. Please review the following travel details:</p>'
            . '<ul>'
            . '<li><strong>Booking Reference:</strong> ' . htmlspecialchars((string)($booking['booking_reference'] ?? '')) . '</li>'
            . '<li><strong>Flight Number:</strong> ' . htmlspecialchars(strtoupper((string)($flight['flight_code'] ?? ''))) . '</li>'
            . '<li><strong>Route:</strong> ' . htmlspecialchars($this->formatText($flight['origin'] ?? '')) . ' to ' . htmlspecialchars($this->formatText($flight['destination'] ?? '')) . '</li>'
            . '<li><strong>Departure Date:</strong> ' . htmlspecialchars((string)($flight['departure_date'] ?? '')) . '</li>'
            . '<li><strong>Departure Time:</strong> ' . htmlspecialchars(substr((string)($flight['departure_time'] ?? ''), 0, 5)) . '</li>'
            . '<li><strong>Seat Assignment:</strong> ' . htmlspecialchars(strtoupper((string)($booking['seat_no'] ?? ''))) . ' (' . htmlspecialchars($this->formatText($booking['seat_class'] ?? '')) . ')</li>'
            . '<li><strong>Fare:</strong> PHP ' . number_format((float)($booking['fare_amount'] ?? 0), 2) . '</li>'
            . '</ul>'
            . '<p>Thank you for choosing Air-TRX.</p>';

        $plain =
            "Air-TRX Booking Confirmation\n"
            . "Booking Reference: " . ($booking['booking_reference'] ?? '') . "\n"
            . "Flight Number: " . strtoupper((string)($flight['flight_code'] ?? '')) . "\n"
            . "Route: " . $this->formatText($flight['origin'] ?? '') . " to " . $this->formatText($flight['destination'] ?? '') . "\n"
            . "Departure Date: " . ($flight['departure_date'] ?? '') . "\n"
            . "Departure Time: " . substr((string)($flight['departure_time'] ?? ''), 0, 5) . "\n"
            . "Seat Assignment: " . strtoupper((string)($booking['seat_no'] ?? '')) . " (" . $this->formatText($booking['seat_class'] ?? '') . ")\n"
            . "Fare: PHP " . number_format((float)($booking['fare_amount'] ?? 0), 2);

        return $this->send(
            (string)($user['email'] ?? ''),
            $this->formatText($user['full_name'] ?? 'Passenger'),
            $subject,
            $html,
            $plain
        );
    }

    private function escapePdfText(string $text): string
    {
        $text = preg_replace('/[^\x20-\x7E]/', '', $text);
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\\(', $text);
        $text = str_replace(')', '\\)', $text);
        return $text;
    }

    private function buildBrandedBoardingPassPdf(array $user, array $flight, array $booking): string
    {
        $passenger = $this->escapePdfText($this->formatText($user['full_name'] ?? 'Passenger'));
        $reference = $this->escapePdfText((string)($booking['booking_reference'] ?? ''));
        $flightNo = $this->escapePdfText(strtoupper((string)($flight['flight_code'] ?? '')));
        $origin = $this->escapePdfText($this->formatText($flight['origin'] ?? ''));
        $destination = $this->escapePdfText($this->formatText($flight['destination'] ?? ''));
        $date = $this->escapePdfText((string)($flight['departure_date'] ?? ''));
        $time = $this->escapePdfText(substr((string)($flight['departure_time'] ?? ''), 0, 5));
        $seat = $this->escapePdfText(strtoupper((string)($booking['seat_no'] ?? '')) . ' (' . $this->formatText($booking['seat_class'] ?? '') . ')');
        $status = $this->escapePdfText($this->formatText($booking['booking_status'] ?? 'Confirmed'));
        $fare = $this->escapePdfText('PHP ' . number_format((float)($booking['fare_amount'] ?? 0), 2));

        $gold = '0.831 0.686 0.216';
        $pink = '1.000 0.820 0.863';
        $black = '0.067 0.067 0.067';
        $gray = '0.420 0.420 0.420';

        $stream = '';
        $stream .= "0.99 0.97 0.98 rg 0 0 595 842 re f\n";
        $stream .= "1 1 1 rg 30 40 535 760 re f\n";
        $stream .= "0.95 0.84 0.88 RG 2 w 30 40 535 760 re S\n";
        $stream .= "0 0 0 rg 30 700 535 100 re f\n";
        $stream .= $gold . " rg 30 695 535 5 re f\n";

        // emblem
        $stream .= $gold . " rg 64 760 m 76 772 l 88 760 l 76 748 l h f\n";
        $stream .= "1 1 1 rg 70 761 22 3 re f\n";
        $stream .= "1 1 1 rg 67 755 18 3 re f\n";
        $stream .= "1 1 1 rg 64 749 14 3 re f\n";

        // header text colors same idea as ticket.php
        $stream .= "BT\n/F2 24 Tf " . $gold . " rg 1 0 0 1 108 758 Tm (AIR-TRX) Tj\nET\n";
        $stream .= "BT\n/F1 12 Tf " . $pink . " rg 1 0 0 1 108 738 Tm (Electronic Boarding Pass) Tj\nET\n";
        $stream .= "BT\n/F2 18 Tf " . $gold . " rg 1 0 0 1 370 755 Tm (BOARDING PASS) Tj\nET\n";

        $stream .= "0.98 0.96 0.98 rg 50 625 495 55 re f\n";
        $stream .= "0.95 0.84 0.88 RG 1 w 50 625 495 55 re S\n";
        $stream .= "BT\n/F1 10 Tf " . $gray . " rg 1 0 0 1 65 658 Tm (Passenger) Tj\n/F2 18 Tf " . $black . " rg 1 0 0 1 65 636 Tm (" . $passenger . ") Tj\nET\n";
        $stream .= "BT\n/F1 10 Tf " . $gray . " rg 1 0 0 1 400 658 Tm (Booking Ref) Tj\n/F2 16 Tf " . $black . " rg 1 0 0 1 400 636 Tm (" . $reference . ") Tj\nET\n";

        $stream .= "0.94 0.97 1.00 rg 50 545 495 60 re f\n";
        $stream .= "0.78 0.87 0.97 RG 1 w 50 545 495 60 re S\n";
        $stream .= "BT\n/F1 10 Tf " . $gray . " rg 1 0 0 1 65 585 Tm (Origin) Tj\n/F2 20 Tf " . $gold . " rg 1 0 0 1 65 560 Tm (" . $origin . ") Tj\nET\n";
        $stream .= $gold . " rg 268 572 60 4 re f\n";
        $stream .= "BT\n/F2 18 Tf " . $gold . " rg 1 0 0 1 284 558 Tm (TO) Tj\nET\n";
        $stream .= "BT\n/F1 10 Tf " . $gray . " rg 1 0 0 1 390 585 Tm (Destination) Tj\n/F2 20 Tf " . $gold . " rg 1 0 0 1 390 560 Tm (" . $destination . ") Tj\nET\n";

        $boxes = [
            [50, 470, 235, 55, 'Flight Number', $flightNo],
            [310, 470, 235, 55, 'Departure Date', $date],
            [50, 395, 235, 55, 'Departure Time', $time],
            [310, 395, 235, 55, 'Seat Assignment', $seat],
            [50, 320, 235, 55, 'Booking Status', $status],
            [310, 320, 235, 55, 'Fare', $fare],
        ];

        foreach ($boxes as $box) {
            $x = $box[0];
            $y = $box[1];
            $w = $box[2];
            $h = $box[3];
            $label = $this->escapePdfText($box[4]);
            $value = $this->escapePdfText($box[5]);

            $stream .= "0.99 0.99 1.00 rg {$x} {$y} {$w} {$h} re f\n";
            $stream .= "0.86 0.89 0.94 RG 1 w {$x} {$y} {$w} {$h} re S\n";
            $stream .= "BT\n/F1 10 Tf " . $gray . " rg 1 0 0 1 " . ($x + 15) . ' ' . ($y + 35) . " Tm (" . $label . ") Tj\n";
            $stream .= "/F2 14 Tf " . $black . " rg 1 0 0 1 " . ($x + 15) . ' ' . ($y + 15) . " Tm (" . $value . ") Tj\nET\n";
        }

        $note = $this->escapePdfText('Requierd to be presented during check-in and boarding. Thank you for choosing Air-TRX.');
        $stream .= $gold . " rg 50 255 495 4 re f\n";
        $stream .= "BT\n/F1 10 Tf " . $black . " rg 1 0 0 1 50 235 Tm (" . $note . ") Tj\nET\n";

        $objects = [];
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj";
        $objects[] = "2 0 obj\n<< /Type /Pages /Count 1 /Kids [3 0 R] >>\nendobj";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >>\nendobj";
        $objects[] = "4 0 obj\n<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream\nendobj";
        $objects[] = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj";
        $objects[] = "6 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>\nendobj";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string)$offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xref . "\n%%EOF";

        return $pdf;
    }

    public function sendAdminTicketEmail(array $user, array $flight, array $booking, string $ticketUrl): bool
    {
        $subject = 'Air-TRX E-Ticket - ' . ($booking['booking_reference'] ?? '');

        $safeName = htmlspecialchars($this->formatText($user['full_name'] ?? 'Passenger'));
        $safeRef = htmlspecialchars((string)($booking['booking_reference'] ?? ''));
        $safeFlight = htmlspecialchars(strtoupper((string)($flight['flight_code'] ?? '')));
        $safeOrigin = htmlspecialchars($this->formatText($flight['origin'] ?? ''));
        $safeDestination = htmlspecialchars($this->formatText($flight['destination'] ?? ''));
        $safeDate = htmlspecialchars((string)($flight['departure_date'] ?? ''));
        $safeTime = htmlspecialchars(substr((string)($flight['departure_time'] ?? ''), 0, 5));
        $safeSeat = htmlspecialchars(strtoupper((string)($booking['seat_no'] ?? '')));
        $safeClass = htmlspecialchars($this->formatText($booking['seat_class'] ?? ''));
        $safeStatus = htmlspecialchars($this->formatText($booking['booking_status'] ?? 'Confirmed'));
        $safeUrl = htmlspecialchars($ticketUrl);
        $fare = number_format((float)($booking['fare_amount'] ?? 0), 2);

        $html =
            '<div style="font-family:Arial, Helvetica, sans-serif;background:#fff8fb;padding:24px;color:#1f2937;">'
            . '<div style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #f3d6de;border-radius:18px;overflow:hidden;box-shadow:0 16px 34px rgba(0,0,0,0.08);">'
            . '<div style="background:linear-gradient(90deg,#ffd1dc,#f7b8c9);padding:18px 22px;">'
            . '<h2 style="margin:0;color:#b38b16;font-size:24px;font-weight:800;">Air-TRX Electronic Ticket</h2>'
            . '<p style="margin:8px 0 0;color:#4b5563;font-size:14px;">Please find your confirmed travel details below.</p>'
            . '</div>'
            . '<div style="padding:22px;line-height:1.65;">'
            . '<p style="margin-top:0;">Dear <strong>' . $safeName . '</strong>,</p>'
            . '<p>This email serves as your official Air-TRX electronic ticket. A PDF version of your boarding pass, featuring the Air-TRX branding, is attached for your convenience. Kindly review the travel information below and keep this message for reference.</p>'
            . '<table style="width:100%;border-collapse:collapse;margin:18px 0;">'
            . '<tr><td style="padding:8px 0;color:#6b7280;width:38%;">Booking Reference</td><td style="padding:8px 0;font-weight:700;color:#111827;">' . $safeRef . '</td></tr>'
            . '<tr><td style="padding:8px 0;color:#6b7280;">Flight Number</td><td style="padding:8px 0;font-weight:700;color:#111827;">' . $safeFlight . '</td></tr>'
            . '<tr><td style="padding:8px 0;color:#6b7280;">Route</td><td style="padding:8px 0;font-weight:700;color:#111827;">' . $safeOrigin . ' to ' . $safeDestination . '</td></tr>'
            . '<tr><td style="padding:8px 0;color:#6b7280;">Departure Date</td><td style="padding:8px 0;font-weight:700;color:#111827;">' . $safeDate . '</td></tr>'
            . '<tr><td style="padding:8px 0;color:#6b7280;">Departure Time</td><td style="padding:8px 0;font-weight:700;color:#111827;">' . $safeTime . '</td></tr>'
            . '<tr><td style="padding:8px 0;color:#6b7280;">Seat Assignment</td><td style="padding:8px 0;font-weight:700;color:#111827;">' . $safeSeat . ' (' . $safeClass . ')</td></tr>'
            . '<tr><td style="padding:8px 0;color:#6b7280;">Booking Status</td><td style="padding:8px 0;font-weight:700;color:#111827;">' . $safeStatus . '</td></tr>'
            . '<tr><td style="padding:8px 0;color:#6b7280;">Fare</td><td style="padding:8px 0;font-weight:700;color:#111827;">PHP ' . $fare . '</td></tr>'
            . '</table>'
            . '<p style="margin:22px 0;">'
            . '<a href="' . $safeUrl . '" style="display:inline-block;background:#ffd1dc;border:1px solid #f3a9bf;color:#b38b16;text-decoration:none;font-weight:800;padding:12px 18px;border-radius:12px;">View or Print Boarding Pass Online</a>'
            . '</p>'
            . '<p style="margin-bottom:0;color:#4b5563;font-size:14px;">Should the button above not open, you may copy and paste the following link into your browser:</p>'
            . '<p style="color:#6b7280;font-size:13px;word-break:break-all;">' . $safeUrl . '</p>'
            . '<p style="margin-top:20px;">Thank you for choosing Air-TRX.</p>'
            . '</div></div></div>';

        $plain =
            "Air-TRX Electronic Ticket\n"
            . "Passenger: " . $this->formatText($user['full_name'] ?? 'Passenger') . "\n"
            . "Booking Reference: " . ($booking['booking_reference'] ?? '') . "\n"
            . "Flight Number: " . strtoupper((string)($flight['flight_code'] ?? '')) . "\n"
            . "Route: " . $this->formatText($flight['origin'] ?? '') . " to " . $this->formatText($flight['destination'] ?? '') . "\n"
            . "Departure Date: " . ($flight['departure_date'] ?? '') . "\n"
            . "Departure Time: " . substr((string)($flight['departure_time'] ?? ''), 0, 5) . "\n"
            . "Seat Assignment: " . strtoupper((string)($booking['seat_no'] ?? '')) . " (" . $this->formatText($booking['seat_class'] ?? '') . ")\n"
            . "Booking Status: " . $this->formatText($booking['booking_status'] ?? 'Confirmed') . "\n"
            . "Fare: PHP " . $fare . "\n"
            . "Boarding Pass Link: " . $ticketUrl;

        if ($this->loadPhpMailerIfAvailable()) {
            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = $this->smtpHost;
                $mail->SMTPAuth = true;
                $mail->Username = $this->smtpUsername;
                $mail->Password = $this->smtpPassword;
                $mail->SMTPSecure = $this->smtpSecure;
                $mail->Port = $this->smtpPort;
                $mail->setFrom($this->fromEmail, $this->fromName);
                $mail->addAddress((string)($user['email'] ?? ''), $this->formatText($user['full_name'] ?? 'Passenger'));
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $html;
                $mail->AltBody = $plain;

                $pdfContent = $this->buildBrandedBoardingPassPdf($user, $flight, $booking);
                $fileName = 'Air-TRX-Boarding-Pass-' . preg_replace('/[^A-Za-z0-9\-_]/', '-', (string)($booking['booking_reference'] ?? 'ticket')) . '.pdf';
                $mail->addStringAttachment($pdfContent, $fileName, 'base64', 'application/pdf');
                return $mail->send();
            } catch (Throwable $e) {
                $this->lastError = $e->getMessage();
                return false;
            }
        }

        $this->lastError = 'PHPMailer is required to send the boarding pass PDF attachment.';
        return $this->send(
            (string)($user['email'] ?? ''),
            $this->formatText($user['full_name'] ?? 'Passenger'),
            $subject,
            $html,
            $plain
        );
    }
}
?>