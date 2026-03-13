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

if (($user['role'] ?? '') !== 'admin') {
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
        p.dental_checked,
        m.id AS medical_assessment_id,
        m.created_at AS medical_assessed_at,
        m.bmi_category,
        m.stress_level,
        m.coping_level,
        m.bp_systolic,
        m.bp_diastolic,
        d.id AS dental_assessment_id,
        d.created_at AS dental_assessed_at,
        d.dmft_total,
        d.periodontal_diagnosis
    FROM patients p
    LEFT JOIN (
        SELECT ma.*
        FROM medical_assessments ma
        INNER JOIN (
            SELECT patient_id, MAX(id) AS max_id
            FROM medical_assessments
            GROUP BY patient_id
        ) latest ON latest.patient_id = ma.patient_id AND latest.max_id = ma.id
    ) m ON m.patient_id = p.id
    LEFT JOIN (
        SELECT da.*
        FROM dental_assessments da
        INNER JOIN (
            SELECT patient_id, MAX(id) AS max_id
            FROM dental_assessments
            GROUP BY patient_id
        ) latest2 ON latest2.patient_id = da.patient_id AND latest2.max_id = da.id
    ) d ON d.patient_id = p.id";

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
        'dental_checked' => 0,
        'dental_pending' => 0,
        'both_completed' => 0,
        'with_medical_assessment' => 0,
        'with_dental_assessment' => 0,
    ];

    $completion = [
        'both' => 0,
        'medical_only' => 0,
        'dental_only' => 0,
        'neither' => 0,
    ];

    $levelCounts = [];
    $sexCounts = [];
    $ageBuckets = ['0-5' => 0, '6-12' => 0, '13-17' => 0, '18-24' => 0, '25-59' => 0, '60+' => 0, 'unknown' => 0];
    $topSchools = [];

    $bmiCounts = [];
    $bpCounts = [];
    $stressCounts = [];
    $copingCounts = [];

    $dmftBuckets = ['0' => 0, '1-3' => 0, '4-6' => 0, '7+' => 0, 'unknown' => 0];
    $periodontalDxCounts = [];

    $schoolCounts = [];

    $totals['patients'] = count($rows);

    foreach ($rows as $r) {
        $medChecked = (int)($r['medical_checked'] ?? 0) === 1;
        $denChecked = (int)($r['dental_checked'] ?? 0) === 1;

        if ($medChecked) $totals['medical_checked']++; else $totals['medical_pending']++;
        if ($denChecked) $totals['dental_checked']++; else $totals['dental_pending']++;
        if ($medChecked && $denChecked) $totals['both_completed']++;

        if ($r['medical_assessment_id'] !== null) $totals['with_medical_assessment']++;
        if ($r['dental_assessment_id'] !== null) $totals['with_dental_assessment']++;

        if ($medChecked && $denChecked) $completion['both']++;
        elseif ($medChecked) $completion['medical_only']++;
        elseif ($denChecked) $completion['dental_only']++;
        else $completion['neither']++;

        $levelKey = (string)($r['level'] ?? '');
        if ($levelKey === '') $levelKey = 'Unknown';
        $levelCounts[$levelKey] = ($levelCounts[$levelKey] ?? 0) + 1;

        $sexKey = (string)($r['sex'] ?? '');
        if ($sexKey === '') $sexKey = 'Unknown';
        $sexCounts[$sexKey] = ($sexCounts[$sexKey] ?? 0) + 1;

        $age = $r['age'] === null ? null : (int)$r['age'];
        if ($age === null) $ageBuckets['unknown']++;
        elseif ($age <= 5) $ageBuckets['0-5']++;
        elseif ($age <= 12) $ageBuckets['6-12']++;
        elseif ($age <= 17) $ageBuckets['13-17']++;
        elseif ($age <= 24) $ageBuckets['18-24']++;
        elseif ($age <= 59) $ageBuckets['25-59']++;
        else $ageBuckets['60+']++;

        $schoolKey = (string)($r['school'] ?? '');
        if ($schoolKey === '') $schoolKey = 'Unknown';
        $schoolCounts[$schoolKey] = ($schoolCounts[$schoolKey] ?? 0) + 1;

        $bmi = (string)($r['bmi_category'] ?? '');
        if ($bmi !== '') $bmiCounts[$bmi] = ($bmiCounts[$bmi] ?? 0) + 1;

        $stress = (string)($r['stress_level'] ?? '');
        if ($stress !== '') $stressCounts[$stress] = ($stressCounts[$stress] ?? 0) + 1;

        $coping = (string)($r['coping_level'] ?? '');
        if ($coping !== '') $copingCounts[$coping] = ($copingCounts[$coping] ?? 0) + 1;

        $sys = $r['bp_systolic'] === null ? null : (int)$r['bp_systolic'];
        $dia = $r['bp_diastolic'] === null ? null : (int)$r['bp_diastolic'];
        if ($sys !== null && $dia !== null) {
            $cat = 'Normal';
            if ($sys >= 140 || $dia >= 90) $cat = 'High';
            elseif ($sys < 90 || $dia < 60) $cat = 'Low';
            $bpCounts[$cat] = ($bpCounts[$cat] ?? 0) + 1;
        }

        $dmft = $r['dmft_total'] === null ? null : (int)$r['dmft_total'];
        if ($dmft === null) $dmftBuckets['unknown']++;
        else {
            if ($dmft <= 0) $dmftBuckets['0']++;
            elseif ($dmft <= 3) $dmftBuckets['1-3']++;
            elseif ($dmft <= 6) $dmftBuckets['4-6']++;
            else $dmftBuckets['7+']++;
        }

        $pdx = (string)($r['periodontal_diagnosis'] ?? '');
        if ($pdx !== '') $periodontalDxCounts[$pdx] = ($periodontalDxCounts[$pdx] ?? 0) + 1;
    }

    arsort($schoolCounts);
    foreach ($schoolCounts as $k => $v) {
        $topSchools[] = ['school' => $k, 'count' => $v];
        if (count($topSchools) >= 10) break;
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
            'completion' => $completion,
            'level' => $levelCounts,
            'sex' => $sexCounts,
            'age_bucket' => $ageBuckets,
            'top_schools' => $topSchools,
            'bmi_category' => $bmiCounts,
            'bp_category' => $bpCounts,
            'stress_level' => $stressCounts,
            'coping_level' => $copingCounts,
            'dmft_bucket' => $dmftBuckets,
            'periodontal_diagnosis' => $periodontalDxCounts,
        ],
        'server_time' => date('c'),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}
