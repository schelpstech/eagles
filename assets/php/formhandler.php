<?php
header('Content-Type: application/json');

// Database credentials
$host = 'localhost';
$dbname = 'conference_db';
$username = 'root';
$password = '';

try {
    // Secure connection using PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input data
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $gender = htmlspecialchars(trim($_POST['gender']));
    $title = htmlspecialchars(trim($_POST['title']));
    $position = htmlspecialchars(trim($_POST['position']));
    $department = htmlspecialchars(trim($_POST['department']));
    $participation_mode = htmlspecialchars(trim($_POST['participation_mode']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $church_name = htmlspecialchars(trim($_POST['church_name']));

    $ref = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
    $ref = "WLC24".$ref;

    // Validate required fields
    if (empty($fullname) || empty($gender) || empty($title) || empty($position) ||
        empty($department) || empty($participation_mode) || empty($ref) || empty($phone) || empty($church_name)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    // Insert into the database
    try {
        $query = "INSERT INTO registrations 
                  (regid, fullname, gender, title, position, department, participation_mode, email, phone, church_name)
                  VALUES (:ref, :fullname, :gender, :title, :position, :department, :participation_mode, :email, :phone, :church_name)";
        
        $stmt = $pdo->prepare($query);

        // Bind parameters to prevent SQL injection
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

        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Registration successful!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error saving data: ' . $e->getMessage()]);
    }
    exit;
}
