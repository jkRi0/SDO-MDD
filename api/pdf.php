<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/auth.php';

header('Content-Type: application/json; charset=utf-8');

$user = current_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!in_array($user['role'] ?? '', ['admin', 'medical', 'dental'], true)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
}

$type = (string)($_GET['type'] ?? 'medical');
$patientId = (int)($_GET['patient_id'] ?? 0);

if (!in_array($type, ['medical', 'dental'], true) || $patientId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid request']);
    exit;
}

// Role restriction: staff can only request their module
if (($user['role'] ?? '') === 'medical' && $type !== 'medical') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
}
if (($user['role'] ?? '') === 'dental' && $type !== 'dental') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
}

try {
    $stmt = db()->prepare('SELECT id, school, level, entry_date, fullname, age, sex, address, contact_number, date_of_birth, civil_status, designation, region, division, district, hmo_provider, medical_checked, dental_checked, created_at FROM patients WHERE id = ? LIMIT 1');
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Patient not found']);
        exit;
    }

    $assessment = null;
    if ($type === 'medical') {
        $stmt = db()->prepare('SELECT * FROM medical_assessments WHERE patient_id = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$patientId]);
        $assessment = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } else {
        $stmt = db()->prepare('SELECT * FROM dental_assessments WHERE patient_id = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$patientId]);
        $assessment = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    echo json_encode([
        'ok' => true,
        'type' => $type,
        'patient' => $patient,
        'assessment' => $assessment,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}
