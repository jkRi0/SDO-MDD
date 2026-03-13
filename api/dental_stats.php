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

if (!in_array($user['role'] ?? '', ['admin', 'dental'], true)) {
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
        p.dental_checked,
        p.dental_checked_at,
        d.id AS dental_assessment_id,
        d.created_at AS dental_assessed_at,
        d.dmft_total,
        d.d_count,
        d.m_count,
        d.f_count,
        d.debris,
        d.gingiva_inflammation,
        d.calculus,
        d.orthodontic_treatment,
        d.occlusion,
        d.tmj_exam,
        d.soft_tissue_exam,
        d.periodontal_diagnosis,
        d.periodontitis,
        d.home_care_effectiveness,
        d.periodontal_condition,
        d.recommendations_json,
        d.mh_allergy,
        d.mh_asthma,
        d.mh_bleeding_problem,
        d.mh_heart_ailment,
        d.mh_diabetes,
        d.mh_epilepsy,
        d.mh_kidney_disease,
        d.mh_convulsion,
        d.mh_fainting
    FROM patients p
    LEFT JOIN (
        SELECT da.*
        FROM dental_assessments da
        INNER JOIN (
            SELECT patient_id, MAX(id) AS max_id
            FROM dental_assessments
            GROUP BY patient_id
        ) latest ON latest.patient_id = da.patient_id AND latest.max_id = da.id
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
        'dental_checked' => 0,
        'dental_pending' => 0,
        'with_assessment' => 0,
        'without_assessment' => 0,
        'dmft_avg' => 0,
    ];

    $sexCounts = [];
    $levelCounts = [];
    $schoolCounts = [];

    $dmftBuckets = ['0' => 0, '1-3' => 0, '4-6' => 0, '7+' => 0, 'unknown' => 0];
    $occlusionCounts = [];
    $periodontalDxCounts = [];
    $periodontitisCounts = [];

    $oralFindings = [
        'debris' => 0,
        'gingiva_inflammation' => 0,
        'calculus' => 0,
        'orthodontic_treatment' => 0,
    ];

    $recommendationCounts = [];

    $mhCounts = [
        'allergy' => 0,
        'asthma' => 0,
        'bleeding_problem' => 0,
        'heart_ailment' => 0,
        'diabetes' => 0,
        'epilepsy' => 0,
        'kidney_disease' => 0,
        'convulsion' => 0,
        'fainting' => 0,
    ];

    $totals['patients'] = count($rows);

    $dmftSum = 0;
    $dmftN = 0;

    foreach ($rows as $r) {
        $dentalChecked = (int)($r['dental_checked'] ?? 0) === 1;
        if ($dentalChecked) $totals['dental_checked']++; else $totals['dental_pending']++;

        $sexKey = (string)($r['sex'] ?? '');
        if ($sexKey === '') $sexKey = 'Unknown';
        $sexCounts[$sexKey] = ($sexCounts[$sexKey] ?? 0) + 1;

        $levelKey = (string)($r['level'] ?? '');
        if ($levelKey === '') $levelKey = 'Unknown';
        $levelCounts[$levelKey] = ($levelCounts[$levelKey] ?? 0) + 1;

        $schoolKey = (string)($r['school'] ?? '');
        if ($schoolKey === '') $schoolKey = 'Unknown';
        $schoolCounts[$schoolKey] = ($schoolCounts[$schoolKey] ?? 0) + 1;

        $hasAssessment = $r['dental_assessment_id'] !== null;
        if (!$hasAssessment) {
            $totals['without_assessment']++;
            continue;
        }

        $totals['with_assessment']++;

        foreach (['debris', 'gingiva_inflammation', 'calculus', 'orthodontic_treatment'] as $k) {
            if ((int)($r[$k] ?? 0) === 1) $oralFindings[$k]++;
        }

        $oc = (string)($r['occlusion'] ?? '');
        if ($oc !== '') $occlusionCounts[$oc] = ($occlusionCounts[$oc] ?? 0) + 1;

        $dx = (string)($r['periodontal_diagnosis'] ?? '');
        if ($dx !== '') $periodontalDxCounts[$dx] = ($periodontalDxCounts[$dx] ?? 0) + 1;

        $perio = (string)($r['periodontitis'] ?? '');
        if ($perio !== '') $periodontitisCounts[$perio] = ($periodontitisCounts[$perio] ?? 0) + 1;

        $dmft = $r['dmft_total'] === null ? null : (int)$r['dmft_total'];
        if ($dmft === null) {
            $dmftBuckets['unknown']++;
        } else {
            $dmftSum += $dmft;
            $dmftN++;
            if ($dmft <= 0) $dmftBuckets['0']++;
            elseif ($dmft <= 3) $dmftBuckets['1-3']++;
            elseif ($dmft <= 6) $dmftBuckets['4-6']++;
            else $dmftBuckets['7+']++;
        }

        $recsRaw = (string)($r['recommendations_json'] ?? '');
        if ($recsRaw !== '') {
            $decoded = json_decode($recsRaw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $v) {
                    if (!is_string($v) || trim($v) === '') continue;
                    $k = trim($v);
                    $recommendationCounts[$k] = ($recommendationCounts[$k] ?? 0) + 1;
                }
            }
        }

        foreach ($mhCounts as $k => $_) {
            $col = 'mh_' . $k;
            if ((int)($r[$col] ?? 0) === 1) $mhCounts[$k]++;
        }
    }

    if ($dmftN > 0) {
        $totals['dmft_avg'] = round($dmftSum / $dmftN, 2);
    }

    arsort($schoolCounts);
    $topSchools = [];
    foreach ($schoolCounts as $k => $v) {
        $topSchools[] = ['school' => $k, 'count' => $v];
        if (count($topSchools) >= 8) break;
    }

    arsort($recommendationCounts);
    $topRecs = [];
    foreach ($recommendationCounts as $k => $v) {
        $topRecs[] = ['recommendation' => $k, 'count' => $v];
        if (count($topRecs) >= 10) break;
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
            'dmft_bucket' => $dmftBuckets,
            'occlusion' => $occlusionCounts,
            'periodontal_diagnosis' => $periodontalDxCounts,
            'periodontitis' => $periodontitisCounts,
            'oral_findings' => $oralFindings,
            'top_recommendations' => $topRecs,
            'medical_history_flags' => $mhCounts,
        ],
        'server_time' => date('c'),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}
