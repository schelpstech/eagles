<?php 
header('Content-Type: application/json');

// Database credentials
$host = 'localhost';
$dbname = 'rebicor4_my_e_church_repo';
$username = 'rebicor4_shalom';
$password = 'conference_db';

// Receipt directory
$uploadDir = __DIR__ . '/../receipt/';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = htmlspecialchars(trim($_POST['fullname'] ?? ''));
    $gender = htmlspecialchars(trim($_POST['gender'] ?? ''));
    $title = htmlspecialchars(trim($_POST['title'] ?? ''));
    $position = htmlspecialchars(trim($_POST['position'] ?? ''));
    $department = htmlspecialchars(trim($_POST['department'] ?? ''));
    $participation_mode = htmlspecialchars(trim($_POST['participation_mode'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $church_name = htmlspecialchars(trim($_POST['church_name'] ?? ''));
    $payment_receipt = $_FILES['payment_receipt'] ?? null;
    $transaction_date = htmlspecialchars(trim($_POST['transaction_date'] ?? ''));

    // Generate unique reference
    $ref = "WLC24" . str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);

    // Validate required fields
    if (empty($fullname) || empty($gender) || empty($title) || empty($position) ||
        empty($department) || empty($participation_mode) || empty($phone) || empty($church_name)  || empty($transaction_date)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    // Validate file upload
    if ($payment_receipt && $payment_receipt['error'] === UPLOAD_ERR_OK) {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        $fileMimeType = mime_content_type($payment_receipt['tmp_name']);
        $fileExtension = pathinfo($payment_receipt['name'], PATHINFO_EXTENSION);
        $uniqueFileName = uniqid("receipt_") . '.' . $fileExtension;
        $uploadPath = $uploadDir . $uniqueFileName;

        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and PDF are allowed.']);
            exit;
        }

        // Move uploaded file
        if (!move_uploaded_file($payment_receipt['tmp_name'], $uploadPath)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to upload file.']);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'File upload is required.']);
        exit;
    }

    // Ensure unique ref
    try {
        do {
            $ref = "WLC24" . str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $query = "SELECT COUNT(*) FROM registrations WHERE regid = :ref";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':ref', $ref);
            $stmt->execute();
            $exists = $stmt->fetchColumn();
        } while ($exists > 0);

        $query = "INSERT INTO registrations 
                  (regid, fullname, gender, title, position, department, participation_mode, email, phone, church_name, payment_receipt, transaction_date)
                  VALUES (:ref, :fullname, :gender, :title, :position, :department, :participation_mode, :email, :phone, :church_name, :payment_receipt, :transaction_date)";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':department', $department);
        $stmt->bindParam(':participation_mode', $participation_mode);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':church_name', $church_name);
        $stmt->bindParam(':ref', $ref);
        $stmt->bindParam(':payment_receipt', $uniqueFileName);
        $stmt->bindParam(':transaction_date', $transaction_date);

        $stmt->execute();
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => "Registration successful! Thank you, $fullname."]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error saving data: ' . $e->getMessage()]);
    }
}
exit;
