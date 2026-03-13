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

if (!in_array($user['role'] ?? '', ['admin', 'medical'], true)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
}

$limit = (int)($_GET['limit'] ?? 500);
if ($limit < 1) $limit = 1;
if ($limit > 5000) $limit = 5000;

$dateFrom = (string)($_GET['date_from'] ?? '');
$dateTo = (string)($_GET['date_to'] ?? '');
$month = (string)($_GET['month'] ?? '');
$level = trim((string)($_GET['level'] ?? ''));
$school = trim((string)($_GET['school'] ?? ''));
$district = trim((string)($_GET['district'] ?? ''));
$designation = trim((string)($_GET['designation'] ?? ''));
$sex = trim((string)($_GET['sex'] ?? ''));
$ageRange = trim((string)($_GET['age_range'] ?? ''));

try {
    $where = [];
    $params = [];

    if ($month !== '' && preg_match('/^\d{4}-\d{2}$/', $month)) {
        try {
            $from = new DateTimeImmutable($month . '-01');
            $to = $from->modify('last day of this month');
            $where[] = 'p.entry_date >= ?';
            $params[] = $from->format('Y-m-d');
            $where[] = 'p.entry_date <= ?';
            $params[] = $to->format('Y-m-d');
        } catch (Throwable $e) {
        }
    }

    if (!$month && $dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
        $where[] = 'p.entry_date >= ?';
        $params[] = $dateFrom;
    }
    if (!$month && $dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
        $where[] = 'p.entry_date <= ?';
        $params[] = $dateTo;
    }

    if ($level !== '') {
        $where[] = 'p.level = ?';
        $params[] = $level;
    }
    if ($school !== '') {
        $where[] = 'p.school = ?';
        $params[] = $school;
    }
    if ($district !== '') {
        $where[] = 'p.district = ?';
        $params[] = $district;
    }
    if ($designation !== '') {
        $where[] = 'p.designation LIKE ?';
        $params[] = '%' . $designation . '%';
    }
    if ($sex !== '') {
        $where[] = 'p.sex = ?';
        $params[] = $sex;
    }

    if ($ageRange !== '') {
        if ($ageRange === '0-5') { $where[] = 'p.age BETWEEN 0 AND 5'; }
        elseif ($ageRange === '6-12') { $where[] = 'p.age BETWEEN 6 AND 12'; }
        elseif ($ageRange === '13-17') { $where[] = 'p.age BETWEEN 13 AND 17'; }
        elseif ($ageRange === '18-24') { $where[] = 'p.age BETWEEN 18 AND 24'; }
        elseif ($ageRange === '25-59') { $where[] = 'p.age BETWEEN 25 AND 59'; }
        elseif ($ageRange === '60+') { $where[] = 'p.age >= 60'; }
    }

    $sql = "SELECT
        p.id,
        p.school,
        p.level,
        p.district,
        p.designation,
        p.entry_date,
        p.age,
        p.sex,
        p.medical_checked,
        p.medical_checked_at,
        m.id AS medical_assessment_id,
        m.created_at AS medical_assessed_at,
        m.bmi_category,
        m.stress_level,
        m.coping_level,
        m.bp_systolic,
        m.bp_diastolic
    FROM patients p
    LEFT JOIN (
        SELECT ma.*
        FROM medical_assessments ma
        INNER JOIN (
            SELECT patient_id, MAX(id) AS max_id
            FROM medical_assessments
            GROUP BY patient_id
        ) latest ON latest.patient_id = ma.patient_id AND latest.max_id = ma.id
    ) m ON m.patient_id = p.id";

    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY p.id DESC LIMIT ?';

    $stmt = db()->prepare($sql);
    $i = 1;
    foreach ($params as $p) {
        $stmt->bindValue($i, $p);
        $i++;
    }
    $stmt->bindValue($i, $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totals = [
        'patients' => 0,
        'medical_checked' => 0,
        'medical_pending' => 0,
        'with_assessment' => 0,
        'without_assessment' => 0,
    ];

    $sexCounts = [];
    $levelCounts = [];
    $schoolCounts = [];

    $bmiCategoryCounts = [];
    $stressCounts = ['1' => 0, '2' => 0, '3' => 0, '4' => 0];
    $copingCounts = ['1' => 0, '2' => 0, '3' => 0, '4' => 0];

    $bpCounts = [
        'normal' => 0,
        'elevated' => 0,
        'stage1' => 0,
        'stage2' => 0,
        'crisis' => 0,
        'unknown' => 0,
    ];

    $totals['patients'] = count($rows);

    foreach ($rows as $r) {
        $medicalChecked = (int)($r['medical_checked'] ?? 0) === 1;
        if ($medicalChecked) $totals['medical_checked']++; else $totals['medical_pending']++;

        $sexKey = (string)($r['sex'] ?? '');
        if ($sexKey === '') $sexKey = 'Unknown';
        $sexCounts[$sexKey] = ($sexCounts[$sexKey] ?? 0) + 1;

        $levelKey = (string)($r['level'] ?? '');
        if ($levelKey === '') $levelKey = 'Unknown';
        $levelCounts[$levelKey] = ($levelCounts[$levelKey] ?? 0) + 1;

        $schoolKey = (string)($r['school'] ?? '');
        if ($schoolKey === '') $schoolKey = 'Unknown';
        $schoolCounts[$schoolKey] = ($schoolCounts[$schoolKey] ?? 0) + 1;

        $hasAssessment = $r['medical_assessment_id'] !== null;
        if ($hasAssessment) {
            $totals['with_assessment']++;

            $bmi = (string)($r['bmi_category'] ?? '');
            if ($bmi !== '') {
                $bmiCategoryCounts[$bmi] = ($bmiCategoryCounts[$bmi] ?? 0) + 1;
            }

            $stress = (string)($r['stress_level'] ?? '');
            if (isset($stressCounts[$stress])) $stressCounts[$stress]++;

            $coping = (string)($r['coping_level'] ?? '');
            if (isset($copingCounts[$coping])) $copingCounts[$coping]++;

            $sys = $r['bp_systolic'] === null ? null : (int)$r['bp_systolic'];
            $dia = $r['bp_diastolic'] === null ? null : (int)$r['bp_diastolic'];

            if ($sys === null || $dia === null) {
                $bpCounts['unknown']++;
            } else {
                if ($sys >= 180 || $dia >= 120) $bpCounts['crisis']++;
                elseif ($sys >= 140 || $dia >= 90) $bpCounts['stage2']++;
                elseif ($sys >= 130 || $dia >= 80) $bpCounts['stage1']++;
                elseif ($sys >= 120 && $dia < 80) $bpCounts['elevated']++;
                else $bpCounts['normal']++;
            }
        } else {
            $totals['without_assessment']++;
        }
    }

    arsort($schoolCounts);
    $topSchools = [];
    foreach ($schoolCounts as $k => $v) {
        $topSchools[] = ['school' => $k, 'count' => $v];
        if (count($topSchools) >= 8) break;
    }

    echo json_encode([
        'ok' => true,
        'filters' => [
            'month' => $month,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'level' => $level,
            'school' => $school,
            'district' => $district,
            'designation' => $designation,
            'sex' => $sex,
            'age_range' => $ageRange,
            'limit' => $limit,
        ],
        'totals' => $totals,
        'breakdowns' => [
            'sex' => $sexCounts,
            'level' => $levelCounts,
            'top_schools' => $topSchools,
            'bmi_category' => $bmiCategoryCounts,
            'stress_level' => $stressCounts,
            'coping_level' => $copingCounts,
            'bp_category' => $bpCounts,
        ],
        'server_time' => date('c'),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}
