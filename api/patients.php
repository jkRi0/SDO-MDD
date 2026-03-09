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

$limit = (int)($_GET['limit'] ?? 200);
if ($limit < 1) $limit = 1;
if ($limit > 500) $limit = 500;

$dateFrom = (string)($_GET['date_from'] ?? '');
$dateTo = (string)($_GET['date_to'] ?? '');
$month = (string)($_GET['month'] ?? '');
$level = trim((string)($_GET['level'] ?? ''));
$school = trim((string)($_GET['school'] ?? ''));
$district = trim((string)($_GET['district'] ?? ''));
$designation = trim((string)($_GET['designation'] ?? ''));
$sex = trim((string)($_GET['sex'] ?? ''));
$ageRange = trim((string)($_GET['age_range'] ?? ''));
$statusField = trim((string)($_GET['status_field'] ?? ''));
$status = trim((string)($_GET['status'] ?? ''));

try {
    $where = [];
    $params = [];

    if ($month !== '' && preg_match('/^\d{4}-\d{2}$/', $month)) {
        try {
            $from = new DateTimeImmutable($month . '-01');
            $to = $from->modify('last day of this month');
            $where[] = 'entry_date >= ?';
            $params[] = $from->format('Y-m-d');
            $where[] = 'entry_date <= ?';
            $params[] = $to->format('Y-m-d');
        } catch (Throwable $e) {
        }
    }

    if (!$month && $dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
        $where[] = 'entry_date >= ?';
        $params[] = $dateFrom;
    }
    if (!$month && $dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
        $where[] = 'entry_date <= ?';
        $params[] = $dateTo;
    }

    if ($level !== '') {
        $where[] = 'level = ?';
        $params[] = $level;
    }
    if ($school !== '') {
        $where[] = 'school = ?';
        $params[] = $school;
    }
    if ($district !== '') {
        $where[] = 'district = ?';
        $params[] = $district;
    }
    if ($designation !== '') {
        $where[] = 'designation LIKE ?';
        $params[] = '%' . $designation . '%';
    }
    if ($sex !== '') {
        $where[] = 'sex = ?';
        $params[] = $sex;
    }

    if ($ageRange !== '') {
        if ($ageRange === '0-5') { $where[] = 'age BETWEEN 0 AND 5'; }
        elseif ($ageRange === '6-12') { $where[] = 'age BETWEEN 6 AND 12'; }
        elseif ($ageRange === '13-17') { $where[] = 'age BETWEEN 13 AND 17'; }
        elseif ($ageRange === '18-24') { $where[] = 'age BETWEEN 18 AND 24'; }
        elseif ($ageRange === '25-59') { $where[] = 'age BETWEEN 25 AND 59'; }
        elseif ($ageRange === '60+') { $where[] = 'age >= 60'; }
    }

    if ($statusField !== '' && $status !== '') {
        if ($status === 'completed') {
            $where[] = 'medical_checked = 1';
            $where[] = 'dental_checked = 1';
        } else {
            $field = null;
            if ($statusField === 'medical') $field = 'medical_checked';
            if ($statusField === 'dental') $field = 'dental_checked';
            if ($field) {
                if ($status === 'checked') {
                    $where[] = $field . ' = 1';
                } elseif ($status === 'pending') {
                    $where[] = '(' . $field . ' IS NULL OR ' . $field . ' = 0)';
                }
            }
        }
    }

    $sql = 'SELECT id, school, level, entry_date, fullname, age, sex, medical_checked, dental_checked, created_at FROM patients';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY id DESC LIMIT ?';

    $stmt = db()->prepare($sql);
    $i = 1;
    foreach ($params as $p) {
        $stmt->bindValue($i, $p);
        $i++;
    }
    $stmt->bindValue($i, $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'ok' => true,
        'data' => $rows,
        'server_time' => date('c'),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}
