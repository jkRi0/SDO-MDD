<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/auth.php';

require_login('medical', 'medical/assess.php');

$cfg = base_config();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$errors = [];

if ($id <= 0) {
    redirect('/medical/index.php');
}

try {
    $stmt = db()->prepare('SELECT id, school, level, entry_date, fullname, age, sex, address, contact_number, date_of_birth, civil_status, designation, region, division, district, hmo_provider FROM patients WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $patient = null;
}

if (!$patient) {
    redirect('/medical/index.php');
}

$defaults = [
    'height_cm' => '',
    'height_unit' => 'cm',
    'weight_kg' => '',
    'bmi_value' => '',
    'bmi_category' => '',
    'bmi_percentile' => '',
    'temperature_c' => '',
    'pulse_rate' => '',
    'rr' => '',
    'o2_sat' => '',
    'bp_systolic' => '',
    'bp_diastolic' => '',
    'pmh' => [],
    'pmh_cancer_type' => '',
    'pmh_operation' => '',
    'pmh_confinement' => '',
    'pmh_others' => '',
    'ob_lmp' => '',
    'ob_gtpal' => '',
    'ob_chest_xray' => '',
    'ob_ecg' => '',
    'physical_findings' => '',
    'stress_level' => '',
    'coping_level' => '',
    'assessed_by_name' => current_user()['fullname'] ?? '',
    'license_no' => '',
];

try {
    $stmt = db()->prepare(
        'SELECT
            assessed_by_name, license_no,
            height_cm, weight_kg, bmi_value, bmi_category, bmi_percentile, temperature_c, pulse_rate, rr, o2_sat, bp_systolic, bp_diastolic,
            past_medical_history,
            ob_lmp, ob_gtpal, ob_chest_xray, ob_ecg,
            physical_findings, stress_level, coping_level
         FROM medical_assessments
         WHERE patient_id = ?
         ORDER BY id DESC
         LIMIT 1'
    );
    $stmt->execute([$id]);
    $last = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($last) {
        $defaults['assessed_by_name'] = (string)($last['assessed_by_name'] ?? $defaults['assessed_by_name']);
        $defaults['license_no'] = (string)($last['license_no'] ?? $defaults['license_no']);

        $defaults['height_cm'] = $last['height_cm'] === null ? '' : (string)$last['height_cm'];
        $defaults['weight_kg'] = $last['weight_kg'] === null ? '' : (string)$last['weight_kg'];
        $defaults['bmi_value'] = $last['bmi_value'] === null ? '' : (string)$last['bmi_value'];
        $defaults['bmi_category'] = $last['bmi_category'] === null ? '' : (string)$last['bmi_category'];
        $defaults['bmi_percentile'] = $last['bmi_percentile'] === null ? '' : (string)$last['bmi_percentile'];
        $defaults['temperature_c'] = $last['temperature_c'] === null ? '' : (string)$last['temperature_c'];
        $defaults['pulse_rate'] = $last['pulse_rate'] === null ? '' : (string)$last['pulse_rate'];
        $defaults['rr'] = $last['rr'] === null ? '' : (string)$last['rr'];
        $defaults['o2_sat'] = $last['o2_sat'] === null ? '' : (string)$last['o2_sat'];
        $defaults['bp_systolic'] = $last['bp_systolic'] === null ? '' : (string)$last['bp_systolic'];
        $defaults['bp_diastolic'] = $last['bp_diastolic'] === null ? '' : (string)$last['bp_diastolic'];

        $defaults['ob_lmp'] = (string)($last['ob_lmp'] ?? '');
        $defaults['ob_gtpal'] = (string)($last['ob_gtpal'] ?? '');
        $defaults['ob_chest_xray'] = (string)($last['ob_chest_xray'] ?? '');
        $defaults['ob_ecg'] = (string)($last['ob_ecg'] ?? '');
        $defaults['physical_findings'] = (string)($last['physical_findings'] ?? '');
        $defaults['stress_level'] = $last['stress_level'] === null ? '' : (string)$last['stress_level'];
        $defaults['coping_level'] = $last['coping_level'] === null ? '' : (string)$last['coping_level'];

        $pmh = [];
        $pmhCancer = '';
        $pmhOp = '';
        $pmhCon = '';
        $pmhOthers = '';
        $rawPmh = (string)($last['past_medical_history'] ?? '');
        if ($rawPmh !== '') {
            $decoded = json_decode($rawPmh, true);
            if (is_array($decoded)) {
                $pmh = isset($decoded['checked']) && is_array($decoded['checked']) ? $decoded['checked'] : [];
                $pmhCancer = is_string($decoded['cancer_type'] ?? null) ? $decoded['cancer_type'] : '';
                $pmhOp = is_string($decoded['operation'] ?? null) ? $decoded['operation'] : '';
                $pmhCon = is_string($decoded['confinement'] ?? null) ? $decoded['confinement'] : '';
                $pmhOthers = is_string($decoded['others'] ?? null) ? $decoded['others'] : '';
            }
        }

        $defaults['pmh'] = array_values(array_filter($pmh, fn($v) => is_string($v) && $v !== ''));
        $defaults['pmh_cancer_type'] = $pmhCancer;
        $defaults['pmh_operation'] = $pmhOp;
        $defaults['pmh_confinement'] = $pmhCon;
        $defaults['pmh_others'] = $pmhOthers;
    }
} catch (Throwable $e) {
}

$pmhOptions = [
    'DM' => 'DM',
    'HPN' => 'HPN',
    'Asthma' => 'Asthma',
    'Allergies' => 'Allergies',
    'Heart Dse.' => 'Heart Dse.',
    'Lung Dse.' => 'Lung Dse.',
    'Kidney Dse.' => 'Kidney Dse.',
    'Brain Dse.' => 'Brain Dse.',
    'PTB' => 'PTB',
];

$pmhSpecialOptions = [
    'Cancer' => 'Cancer/Type',
    'Operation' => 'Operation',
    'Confinement' => 'Confinement',
];

$designationOptions = [
    'School Principal I',
    'School Principal II',
    'School Principal III',
    'School Principal IV',
    'Teacher I (Elementary)',
    'Teacher II (Elementary)',
    'Teacher III (Elementary)',
    'Teacher IV (Elementary)',
    'Teacher V (Elementary)',
    'Teacher VI (Elementary)',
    'Teacher VII (Elementary)',
    'Teacher I (Secondary)',
    'Teacher II (Secondary)',
    'Teacher III (Secondary)',
    'Teacher IV (Secondary)',
    'Teacher V (Secondary)',
    'Teacher VI (Secondary)',
    'Teacher VII (Secondary)',
    'Master Teacher I (Elementary)',
    'Master Teacher II (Elementary)',
    'Master Teacher III (Elementary)',
    'Master Teacher IV (Elementary)',
    'Master Teacher V (Elementary)',
    'Master Teacher I (Secondary)',
    'Master Teacher II (Secondary)',
    'Master Teacher III (Secondary)',
    'Master Teacher IV (Secondary)',
    'Master Teacher V (Secondary)',
    'Teacher I (Senior High School Teacher I - Academic Track and Core Subjects)',
    'Teacher II (Senior High School Teacher II - Academic and Core Subjects)',
    'Teacher III (Senior High School Teacher III - Academic and Core Subjects)',
    'Teacher IV (Senior High School Teacher IV - Academic and Core Subjects)',
    'Teacher V (Senior High School Teacher V - Academic and Core Subjects)',
    'Teacher VI (Senior High School Teacher VI - Academic and Core Subjects)',
    'Teacher VII (Senior High School Teacher VII - Academic and Core Subjects)',
    'Master Teacher I (Senior High School Master Teacher I - Academic Track and Core Subjects)',
    'Master Teacher II (Senior High School Master Teacher II - Academic Track and Core Subjects)',
    'Master Teacher III (Senior High School Master Teacher III - Academic and Core Subjects)',
    'Master Teacher IV (Senior High School Master Teacher IV - Academic and Core Subjects)',
    'Master Teacher V (Senior High School Master Teacher V - Academic Track and Core Subjects)',
    'Teacher I (Senior High School Teacher I - Arts and Design Track)',
    'Teacher II (Senior High School Teacher II - Arts and Design Track)',
    'Teacher III (Senior High School Teacher III - Arts and Design Track)',
    'Teacher IV (Senior High School Teacher IV - Arts and Design Track)',
    'Teacher V (Senior High School Teacher V - Arts and Design Track)',
    'Teacher VI (Senior High School Teacher VI - Arts and Design Track)',
    'Teacher VII (Senior High School Teacher VII - Arts and Design Track)',
    'Master Teacher I (Senior High School Master Teacher I - Arts and Design Track)',
    'Master Teacher II (Senior High School Master Teacher II - Arts and Design Track)',
    'Master Teacher III (Senior High School Master Teacher III - Arts and Design Track)',
    'Master Teacher IV (Senior High School Master Teacher IV - Arts and Design Track)',
    'Master Teacher V (Senior High School Master Teacher V - Arts and Design Track)',
    'Teacher I (Senior High School Teacher I - Sports Track)',
    'Teacher II (Senior High School Teacher II - Sports Track)',
    'Teacher III (Senior High School Teacher III - Sports Track)',
    'Teacher IV (Senior High School Teacher IV - Sports Track)',
    'Teacher V (Senior High School Teacher V - Sports Track)',
    'Teacher VI (Senior High School Teacher VI - Sports Track)',
    'Teacher VII (Senior High School Teacher VII - Sports Track)',
    'Master Teacher I (Senior High School Master Teacher I - Sports Track)',
    'Master Teacher II (Senior High School Master Teacher II - Sports Track)',
    'Master Teacher III (Senior High School Master Teacher III - Sports Track)',
    'Master Teacher IV (Senior High School Master Teacher IV - Sports Track)',
    'Master Teacher V (Senior High School Master Teacher V - Sports Track)',
    'Teacher I (Senior High School Teacher I - Technical Vocational Track (TVL))',
    'Teacher II (Senior High School Teacher II - Technical Vocational Track (TVL))',
    'Teacher III (Senior High School Teacher III - Technical Vocational Track (TVL))',
    'Teacher IV (Senior High School Teacher IV - Technical Vocational Track (TVL))',
    'Teacher V (Senior High School Teacher V - Technical Vocational Track (TVL))',
    'Teacher VI (Senior High School Teacher VI - Technical Vocational Track (TVL))',
    'Teacher VII (Senior High School Teacher VII - Technical Vocational Track (TVL))',
    'Master Teacher I (Senior High School Master Teacher I - Technical Vocational Track (TVL))',
    'Master Teacher II (Senior High School Master Teacher II - Technical Vocational Track (TVL))',
    'Master Teacher III (Senior High School Master Teacher III - Technical Vocational Track (TVL))',
    'Master Teacher IV (Senior High School Master Teacher IV - Technical Vocational Track (TVL))',
    'Master Teacher V (Senior High School Master Teacher V - Technical Vocational Track (TVL))',
    'Guidance Coordinator I',
    'Guidance Coordinator II',
    'Guidance Coordinator III',
    'Guidance Counselor I',
    'Guidance Counselor II',
    'Guidance Counselor III',
    'Guidance Services Associate I',
    'Guidance Services Associate II',
    'Guidance Services Specialist I',
    'Guidance Services Specialist II',
    'Guidance Services Specialist III',
    'Guidance Services Specialist IV',
    'Guidance Services Specialist V',
    'Special Science Teacher I',
    'SPED Teacher I',
    'SPED Teacher II',
    'SPED Teacher III',
    'Attorney III',
    'Accountant III',
    'Information Technology Officer I',
    'Administrative Officer V',
    'Administrative Officer IV',
    'Administrative Officer II',
    'Project Development Officer I',
    'Administrative Assistant III',
    'Administrative Assistant II',
    'Administrative Assistant I',
    'Administrative Aide VI',
    'Administrative Aide IV',
    'Administrative Aide I',
    'Chief Education Supervisor *for CID/SGOD Chief',
    'Education Program Supervisor *for LRMDS Manager, QA Coord.',
    'Education Program Supervisor',
    'Public Schools District Supervisor',
    'Education Program Specialist II *for ALS',
    'Librarian II',
    'Project Development Officer II',
    'Medical Officer III',
    'Engineer III',
    'Senior Education Program Specialist',
    'Planning Officer III',
    'Dentist II',
    'Education Program Specialist II *for Human Resource Division',
    'Education Program Specialist II *for School Management Monitoring and Evaluation',
    'Education Program Specialist II *for School Mobilization and Networking',
    'Project Development Officer II *for DRRM',
    'Nurse II',
    'Project Development Officer I (Youth Formation Coordinator)',
];

$likertStress = [
    1 => '1 = Not Stressed - I feel no stress related to my work responsibilities or environment in DepED.',
    2 => '2 = Mildly Stressed - I experience some stress, but it is manageable and does not interfere with my performance or well-being.',
    3 => '3 = Moderately Stressed - I feel stress frequently, which sometimes affects my performance or well-being.',
    4 => '4 = Highly Stressed - I feel significant stress that often impacts my performance, well-being, or ability to cope effectively.',
];

$likertCoping = [
    1 => '1 = Poor Coping - Struggles significantly to manage stress, often feeling overwhelmed.',
    2 => '2 = Fair Coping - Occasionally manages stress but struggles with consistent strategies; sometimes feels overwhelmed.',
    3 => '3 = Good Coping - Generally effective in managing stress; uses strategies and resources to maintain a balanced response to stressors.',
    4 => '4 = Excellent Coping - Highly effective in handling stress; demonstrates resilience and consistently employs positive coping mechanisms.',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p = [
        'school' => trim((string)($_POST['school'] ?? '')),
        'level' => trim((string)($_POST['level'] ?? '')),
        'entry_date' => (string)($_POST['entry_date'] ?? ''),
        'fullname' => trim((string)($_POST['fullname'] ?? '')),
        'age' => trim((string)($_POST['age'] ?? '')),
        'sex' => (string)($_POST['sex'] ?? ''),
        'address' => trim((string)($_POST['address'] ?? '')),
        'contact_number' => trim((string)($_POST['contact_number'] ?? '')),
        'date_of_birth' => (string)($_POST['date_of_birth'] ?? ''),
        'civil_status' => trim((string)($_POST['civil_status'] ?? '')),
        'designation' => trim((string)($_POST['designation'] ?? '')),
        'region' => trim((string)($_POST['region'] ?? '')),
        'division' => trim((string)($_POST['division'] ?? '')),
        'district' => trim((string)($_POST['district'] ?? '')),
        'hmo_provider' => trim((string)($_POST['hmo_provider'] ?? '')),
    ];

    $a = [
        'height_cm' => trim((string)($_POST['height_cm'] ?? '')),
        'height_unit' => trim((string)($_POST['height_unit'] ?? 'cm')),
        'weight_kg' => trim((string)($_POST['weight_kg'] ?? '')),
        'bmi_value' => trim((string)($_POST['bmi_value'] ?? '')),
        'bmi_category' => trim((string)($_POST['bmi_category'] ?? '')),
        'bmi_percentile' => trim((string)($_POST['bmi_percentile'] ?? '')),
        'temperature_c' => trim((string)($_POST['temperature_c'] ?? '')),
        'pulse_rate' => trim((string)($_POST['pulse_rate'] ?? '')),
        'rr' => trim((string)($_POST['rr'] ?? '')),
        'o2_sat' => trim((string)($_POST['o2_sat'] ?? '')),
        'bp_systolic' => trim((string)($_POST['bp_systolic'] ?? '')),
        'bp_diastolic' => trim((string)($_POST['bp_diastolic'] ?? '')),
        'pmh' => (array)($_POST['pmh'] ?? []),
        'pmh_cancer_type' => trim((string)($_POST['pmh_cancer_type'] ?? '')),
        'pmh_operation' => trim((string)($_POST['pmh_operation'] ?? '')),
        'pmh_confinement' => trim((string)($_POST['pmh_confinement'] ?? '')),
        'pmh_others' => trim((string)($_POST['pmh_others'] ?? '')),
        'ob_lmp' => trim((string)($_POST['ob_lmp'] ?? '')),
        'ob_gtpal' => trim((string)($_POST['ob_gtpal'] ?? '')),
        'ob_chest_xray' => trim((string)($_POST['ob_chest_xray'] ?? '')),
        'ob_ecg' => trim((string)($_POST['ob_ecg'] ?? '')),
        'physical_findings' => trim((string)($_POST['physical_findings'] ?? '')),
        'stress_level' => trim((string)($_POST['stress_level'] ?? '')),
        'coping_level' => trim((string)($_POST['coping_level'] ?? '')),
        'assessed_by_name' => trim((string)($_POST['assessed_by_name'] ?? '')),
        'license_no' => trim((string)($_POST['license_no'] ?? '')),
    ];

    if ($p['fullname'] === '') $errors[] = 'Fullname is required.';
    if ($p['age'] === '') $errors[] = 'Age is required.';
    if ($p['sex'] === '') $errors[] = 'Sex is required.';
    if ($p['address'] === '') $errors[] = 'Address is required.';
    if ($p['contact_number'] !== '' && strlen($p['contact_number']) > 30) {
        $errors[] = 'Contact number is too long.';
    }
    if ($p['contact_number'] !== '' && !preg_match('/^[0-9+()\-\s]*$/', $p['contact_number'])) {
        $errors[] = 'Contact number must not contain letters.';
    }
    if ($p['date_of_birth'] === '') $errors[] = 'Date of Birth is required.';
    if ($p['civil_status'] === '') $errors[] = 'Civil status is required.';

    if ($p['age'] !== '' && (!ctype_digit($p['age']) || (int)$p['age'] > 150)) {
        $errors[] = 'Age must be a valid number.';
    }

    $validSex = ['Male', 'Female', 'Others'];
    if ($p['sex'] !== '' && !in_array($p['sex'], $validSex, true)) {
        $errors[] = 'Invalid sex.';
    }

    $validCivilStatus = [
        'Single',
        'Married',
        'Widowed',
        'Divorced',
        'Separated',
        'Registered Partnership/Civil Union',
        'Common-Law/Cohabitating',
    ];
    if ($p['civil_status'] !== '' && !in_array($p['civil_status'], $validCivilStatus, true)) {
        $errors[] = 'Invalid civil status.';
    }

    if ($p['designation'] !== '' && !in_array($p['designation'], $designationOptions, true)) {
        $errors[] = 'Invalid designation.';
    }

    $validHeightUnits = ['cm', 'm', 'ft'];
    if ($a['height_unit'] === '') $a['height_unit'] = 'cm';
    if (!in_array($a['height_unit'], $validHeightUnits, true)) {
        $errors[] = 'Invalid height unit.';
    }

    if ($a['height_cm'] !== '' && is_numeric($a['height_cm']) && in_array($a['height_unit'], $validHeightUnits, true)) {
        $raw = (float)$a['height_cm'];
        $cm = $raw;
        if ($a['height_unit'] === 'm') $cm = $raw * 100;
        if ($a['height_unit'] === 'ft') $cm = $raw * 30.48;
        $a['height_cm'] = rtrim(rtrim(number_format($cm, 2, '.', ''), '0'), '.');
    }

    $numericFields = [
        'height_cm' => ['decimal', 0, 300],
        'weight_kg' => ['decimal', 0, 600],
        'temperature_c' => ['decimal', 0, 60],
        'pulse_rate' => ['int', 0, 300],
        'rr' => ['int', 0, 120],
        'o2_sat' => ['int', 0, 100],
        'bp_systolic' => ['int', 0, 350],
        'bp_diastolic' => ['int', 0, 250],
    ];
    foreach ($numericFields as $key => $rule) {
        if ($a[$key] === '') continue;
        [$type, $min, $max] = $rule;
        if ($type === 'int') {
            if (!ctype_digit($a[$key]) || (int)$a[$key] < $min || (int)$a[$key] > $max) {
                $errors[] = ucfirst(str_replace('_', ' ', $key)) . ' must be a valid number.';
            }
        } else {
            if (!is_numeric($a[$key]) || (float)$a[$key] < $min || (float)$a[$key] > $max) {
                $errors[] = ucfirst(str_replace('_', ' ', $key)) . ' must be a valid number.';
            }
        }
    }

    $a['pmh'] = array_values(array_filter($a['pmh'], fn($v) => is_string($v) && $v !== ''));
    foreach ($a['pmh'] as $v) {
        if (!array_key_exists($v, $pmhOptions) && !array_key_exists($v, $pmhSpecialOptions)) {
            $errors[] = 'Invalid past medical history value.';
            break;
        }
    }
    if (in_array('Cancer', $a['pmh'], true) && $a['pmh_cancer_type'] === '') {
        $errors[] = 'Cancer type is required.';
    }
    if (in_array('Operation', $a['pmh'], true) && $a['pmh_operation'] === '') {
        $errors[] = 'Operation is required.';
    }
    if (in_array('Confinement', $a['pmh'], true) && $a['pmh_confinement'] === '') {
        $errors[] = 'Confinement is required.';
    }

    if ($a['stress_level'] === '' || !in_array((int)$a['stress_level'], [1, 2, 3, 4], true)) {
        $errors[] = 'Stress level is required.';
    }
    if ($a['coping_level'] === '' || !in_array((int)$a['coping_level'], [1, 2, 3, 4], true)) {
        $errors[] = 'Coping level is required.';
    }

    if ($a['assessed_by_name'] === '') $errors[] = 'Assessed by (Name) is required.';
    if ($a['license_no'] === '') $errors[] = 'License No. is required.';

    if (!$errors) {
        try {
            db()->beginTransaction();

            $stmt = db()->prepare(
                'UPDATE patients
                 SET school = ?, level = ?, entry_date = ?, fullname = ?, age = ?, sex = ?, address = ?, contact_number = ?, date_of_birth = ?, civil_status = ?, designation = ?, region = ?, division = ?, district = ?, hmo_provider = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $p['school'],
                $p['level'],
                $p['entry_date'],
                $p['fullname'],
                (int)$p['age'],
                $p['sex'],
                $p['address'],
                $p['contact_number'] === '' ? null : $p['contact_number'],
                $p['date_of_birth'],
                $p['civil_status'],
                $p['designation'] === '' ? null : $p['designation'],
                $p['region'],
                $p['division'],
                $p['district'] === '' ? null : $p['district'],
                $p['hmo_provider'] === '' ? null : $p['hmo_provider'],
                $id,
            ]);

            $pmhPayload = [
                'checked' => $a['pmh'],
                'cancer_type' => $a['pmh_cancer_type'],
                'operation' => $a['pmh_operation'],
                'confinement' => $a['pmh_confinement'],
                'others' => $a['pmh_others'],
            ];

            $ins = db()->prepare(
                'INSERT INTO medical_assessments (
                    patient_id, assessed_by_name, license_no,
                    height_cm, weight_kg, bmi_value, bmi_category, bmi_percentile, temperature_c, pulse_rate, rr, o2_sat, bp_systolic, bp_diastolic,
                    past_medical_history, ob_lmp, ob_gtpal, ob_chest_xray, ob_ecg,
                    physical_findings, stress_level, coping_level
                 ) VALUES (
                    ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?
                 )'
            );

            $ins->execute([
                $id,
                $a['assessed_by_name'],
                $a['license_no'],
                $a['height_cm'] === '' ? null : (float)$a['height_cm'],
                $a['weight_kg'] === '' ? null : (float)$a['weight_kg'],
                $a['bmi_value'] === '' ? null : (float)$a['bmi_value'],
                $a['bmi_category'] === '' ? null : $a['bmi_category'],
                $a['bmi_percentile'] === '' ? null : (float)$a['bmi_percentile'],
                $a['temperature_c'] === '' ? null : (float)$a['temperature_c'],
                $a['pulse_rate'] === '' ? null : (int)$a['pulse_rate'],
                $a['rr'] === '' ? null : (int)$a['rr'],
                $a['o2_sat'] === '' ? null : (int)$a['o2_sat'],
                $a['bp_systolic'] === '' ? null : (int)$a['bp_systolic'],
                $a['bp_diastolic'] === '' ? null : (int)$a['bp_diastolic'],
                json_encode($pmhPayload, JSON_UNESCAPED_UNICODE),
                $a['ob_lmp'] === '' ? null : $a['ob_lmp'],
                $a['ob_gtpal'] === '' ? null : $a['ob_gtpal'],
                $a['ob_chest_xray'] === '' ? null : $a['ob_chest_xray'],
                $a['ob_ecg'] === '' ? null : $a['ob_ecg'],
                $a['physical_findings'] === '' ? null : $a['physical_findings'],
                (int)$a['stress_level'],
                (int)$a['coping_level'],
            ]);

            $mark = db()->prepare('UPDATE patients SET medical_checked = 1, medical_checked_at = NOW() WHERE id = ?');
            $mark->execute([$id]);

            db()->commit();
            set_flash('success', 'Medical assessment saved.');
            redirect('/medical/index.php');
        } catch (Throwable $e) {
            try { db()->rollBack(); } catch (Throwable $e2) {}
            $errors[] = 'Failed to save assessment. Please try again.';
        }
    }

    $patient = array_merge($patient, $p);
    $defaults = array_merge($defaults, $a);
}

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Medical Assessment - <?= e($cfg['app_name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= e(asset('public/assets/css/styles.css')) ?>" rel="stylesheet">
  <style>
    @media (min-width: 768px) {
      .medical-top-row .form-label { min-height: 2.5rem; }
    }
  </style>
</head>
<body class="bg-light">
  <header class="appbar">
    <div class="container py-3">
      <div class="row align-items-center">
        <div class="col-12 col-md">
          <div class="d-flex align-items-center gap-3">
            <img src="<?= e(asset('public/assets/sdo-logo.png')) ?>" alt="Logo" style="width: 48px; height: 48px; flex-shrink: 0;">
            <div class="overflow-hidden">
              <div class="brand h5 mb-0 text-white fw-bold lh-1">Medical Assessment</div>
              <div class="small text-white-50 lh-1 mt-1">School Health Section</div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-auto mt-2 mt-md-0">
          <div class="d-flex align-items-center gap-2 justify-content-md-end">
            <a href="<?= url('/medical/index.php') ?>" class="btn btn-light btn-sm fw-bold px-3" style="border-radius: 8px;">Back</a>
            <a href="<?= url('/auth/logout.php') ?>" class="btn btn-outline-light btn-sm px-3" style="border-radius: 8px;">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="container py-4 py-md-5" style="max-width: 980px;">
    <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
      <div class="card-body p-4">
        <?php if ($errors): ?>
          <div class="alert alert-danger" style="border-radius: 12px;">
            <div class="fw-semibold mb-1">Please fix the following:</div>
            <ul class="mb-0">
              <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" class="vstack gap-4">
          <input type="hidden" name="id" value="<?= (int)$patient['id'] ?>">
          <input type="hidden" name="bmi_value" id="bmiValueHidden" value="<?= e((string)($defaults['bmi_value'] ?? '')) ?>">
          <input type="hidden" name="bmi_category" id="bmiCategoryHidden" value="<?= e((string)($defaults['bmi_category'] ?? '')) ?>">
          <input type="hidden" name="bmi_percentile" id="bmiPercentileHidden" value="<?= e((string)($defaults['bmi_percentile'] ?? '')) ?>">

          <div>
            <div class="h5 mb-2 text-primary">PART I. Personal Details</div>
            <div class="row g-3">
              <div class="col-12 col-md-4">
                <label class="form-label">Level</label>
                <input class="form-control" name="level" value="<?= e((string)$patient['level']) ?>" required>
              </div>
              <div class="col-12 col-md-8">
                <label class="form-label">School</label>
                <input class="form-control" name="school" value="<?= e((string)$patient['school']) ?>" required>
              </div>

              <div class="col-12 col-md-4">
                <label class="form-label">Date</label>
                <input class="form-control" type="date" name="entry_date" value="<?= e((string)$patient['entry_date']) ?>" required>
              </div>
              <div class="col-12 col-md-8">
                <label class="form-label">Fullname</label>
                <input class="form-control" name="fullname" value="<?= e((string)$patient['fullname']) ?>" required>
              </div>

              <div class="col-12 col-md-3">
                <label class="form-label">Age</label>
                <input class="form-control" type="number" min="0" max="150" step="1" name="age" value="<?= e((string)$patient['age']) ?>" required>
              </div>

              <div class="col-12 col-md-3">
                <label class="form-label">Sex</label>
                <select class="form-select" name="sex" required>
                  <option value="" <?= ((string)$patient['sex'] === '') ? 'selected' : '' ?>>Select</option>
                  <option value="Male" <?= ((string)$patient['sex'] === 'Male') ? 'selected' : '' ?>>Male</option>
                  <option value="Female" <?= ((string)$patient['sex'] === 'Female') ? 'selected' : '' ?>>Female</option>
                  <option value="Others" <?= ((string)$patient['sex'] === 'Others') ? 'selected' : '' ?>>Others</option>
                </select>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label">Address</label>
                <input class="form-control" name="address" value="<?= e((string)$patient['address']) ?>" required>
              </div>

              <div class="col-12 col-md-4">
                <label class="form-label">Contact Number</label>
                <input class="form-control" name="contact_number" value="<?= e((string)($patient['contact_number'] ?? '')) ?>" type="tel" inputmode="tel" maxlength="30" oninput="this.value=this.value.replace(/[^0-9+()\-\s]/g,'');">
              </div>

              <div class="col-12 col-md-4">
                <label class="form-label">Date of Birth</label>
                <input class="form-control" type="date" name="date_of_birth" value="<?= e((string)$patient['date_of_birth']) ?>" required>
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label">Civil Status</label>
                <select class="form-select" name="civil_status" required>
                  <option value="" <?= ((string)$patient['civil_status'] === '') ? 'selected' : '' ?>>Select</option>
                  <option value="Single" <?= ((string)$patient['civil_status'] === 'Single') ? 'selected' : '' ?>>Single</option>
                  <option value="Married" <?= ((string)$patient['civil_status'] === 'Married') ? 'selected' : '' ?>>Married</option>
                  <option value="Widowed" <?= ((string)$patient['civil_status'] === 'Widowed') ? 'selected' : '' ?>>Widowed</option>
                  <option value="Divorced" <?= ((string)$patient['civil_status'] === 'Divorced') ? 'selected' : '' ?>>Divorced</option>
                  <option value="Separated" <?= ((string)$patient['civil_status'] === 'Separated') ? 'selected' : '' ?>>Separated</option>
                  <option value="Registered Partnership/Civil Union" <?= ((string)$patient['civil_status'] === 'Registered Partnership/Civil Union') ? 'selected' : '' ?>>Registered Partnership/Civil Union</option>
                  <option value="Common-Law/Cohabitating" <?= ((string)$patient['civil_status'] === 'Common-Law/Cohabitating') ? 'selected' : '' ?>>Common-Law/Cohabitating</option>
                </select>
              </div>

              <div class="col-12 col-md-12">
                <label class="form-label">Designation</label>
                <div class="ac-wrap">
                  <input class="form-control" id="designationInput" name="designation" value="<?= e((string)($patient['designation'] ?? '')) ?>" placeholder="Type to search..." autocomplete="off">
                  <div class="ac-menu" id="designationMenu" role="listbox" aria-label="Designation options"></div>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <label class="form-label">Region</label>
                <input class="form-control" name="region" value="<?= e((string)$patient['region']) ?>" required>
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label">Division</label>
                <input class="form-control" name="division" value="<?= e((string)$patient['division']) ?>" required>
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label">District</label>
                <input class="form-control" name="district" value="<?= e((string)$patient['district']) ?>">
              </div>

              <div class="col-12">
                <label class="form-label">HMO Provider</label>
                <input class="form-control" name="hmo_provider" value="<?= e((string)$patient['hmo_provider']) ?>">
              </div>
            </div>
          </div>
          
          <br><br>

          <div>
            <div class="h5 mb-2 text-primary">PART II. Medical Details</div>
            <div class="row g-3 medical-top-row">
              <div class="col-12 col-md-3">
                <label class="form-label">Height</label>
                <div class="input-group">
                  <input class="form-control" type="number" step="0.01" min="0" max="1000" name="height_cm" id="heightInput" value="<?= e((string)($defaults['height_cm'] ?? '')) ?>" placeholder="Height">
                  <select class="form-select" name="height_unit" id="heightUnit" style="max-width: 76px;">
                    <option value="cm" <?= ((string)($defaults['height_unit'] ?? 'cm') === 'cm') ? 'selected' : '' ?>>cm</option>
                    <option value="m" <?= ((string)($defaults['height_unit'] ?? 'cm') === 'm') ? 'selected' : '' ?>>m</option>
                    <option value="ft" <?= ((string)($defaults['height_unit'] ?? 'cm') === 'ft') ? 'selected' : '' ?>>ft</option>
                  </select>
                </div>
              </div>
              <div class="col-12 col-md-2">
                <label class="form-label">Weight</label>
                <input class="form-control" type="number" step="0.01" min="0" max="600" name="weight_kg" id="weightInput" value="<?= e((string)($defaults['weight_kg'] ?? '')) ?>" placeholder="kg">
              </div>

              <div class="col-12 col-md-2">
                <label class="form-label">BMI</label>
                <input class="form-control" id="bmiValue" value="<?= e((string)($defaults['bmi_value'] ?? '')) ?>" readonly>
              </div>

              <div class="col-12 col-md-3">
                <label class="form-label">Classification / Category</label>
                <input class="form-control" id="bmiCategory" value="<?= e((string)($defaults['bmi_category'] ?? '')) ?>" readonly>
              </div>

              <div class="col-12 col-md-2" id="bmiPercentileWrap" style="display:none;">
                <label class="form-label">BMI Percentile (Age ≤ 20)</label>
                <input class="form-control" type="text" id="bmiPercentile" value="<?= e((string)($defaults['bmi_percentile'] ?? '')) ?>" readonly>
              </div>

              <div class="col-12">
                <div class="row g-3">
                  <div class="d-none d-md-block col-md-3"></div>
                  <div class="d-none d-md-block col-md-2"></div>
                  <div class="col-12 col-md-2">
                    <div class="form-text" id="bmiFormula">BMI = weight (kg) / [height (m)]²</div>
                  </div>
                  <div class="col-12 col-md-3">
                    <div class="form-text" id="bmiBasis"></div>
                  </div>
                  <div class="col-12 col-md-2" id="bmiPercentileNoteWrap" style="display:none;">
                    <div class="form-text">Computed automatically using BMI-for-age reference (sex + age).</div>
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-3">
                <label class="form-label">Temperature</label>
                <input class="form-control" type="number" step="0.1" min="0" max="60" name="temperature_c" value="<?= e((string)($defaults['temperature_c'] ?? '')) ?>" placeholder="°C">
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label">Pulse Rate</label>
                <input class="form-control" type="number" step="1" min="0" max="300" name="pulse_rate" value="<?= e((string)($defaults['pulse_rate'] ?? '')) ?>">
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label">RR</label>
                <input class="form-control" type="number" step="1" min="0" max="120" name="rr" value="<?= e((string)($defaults['rr'] ?? '')) ?>">
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label">O2 Sat</label>
                <input class="form-control" type="number" step="1" min="0" max="100" name="o2_sat" value="<?= e((string)($defaults['o2_sat'] ?? '')) ?>" placeholder="%">
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label">BP Systolic</label>
                <input class="form-control" type="number" step="1" min="0" max="350" name="bp_systolic" value="<?= e((string)($defaults['bp_systolic'] ?? '')) ?>">
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label">BP Diastolic</label>
                <input class="form-control" type="number" step="1" min="0" max="250" name="bp_diastolic" value="<?= e((string)($defaults['bp_diastolic'] ?? '')) ?>">
              </div>
            </div>
          </div>
          
          <br><br>

          <div>
            <div class="h5 mb-2 text-primary">PART III. Past Medical History</div>
            <div class="row g-3">
              <div class="col-12">
                <div class="row g-2">
                  <?php foreach ($pmhOptions as $key => $label): ?>
                    <div class="col-6 col-md-3">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="pmh[]" id="pmh_<?= e($key) ?>" value="<?= e($key) ?>" <?= in_array($key, (array)($defaults['pmh'] ?? []), true) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="pmh_<?= e($key) ?>"><?= e($label) ?></label>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label d-flex align-items-center gap-2" for="pmh_special_cancer">
                  <input class="form-check-input m-0" type="checkbox" name="pmh[]" id="pmh_special_cancer" value="Cancer" <?= in_array('Cancer', (array)($defaults['pmh'] ?? []), true) ? 'checked' : '' ?>>
                  <span>Cancer/Type</span>
                </label>
                <input class="form-control" name="pmh_cancer_type" id="pmh_cancer_type" value="<?= e((string)($defaults['pmh_cancer_type'] ?? '')) ?>" <?= in_array('Cancer', (array)($defaults['pmh'] ?? []), true) ? '' : 'disabled' ?>>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label d-flex align-items-center gap-2" for="pmh_special_operation">
                  <input class="form-check-input m-0" type="checkbox" name="pmh[]" id="pmh_special_operation" value="Operation" <?= in_array('Operation', (array)($defaults['pmh'] ?? []), true) ? 'checked' : '' ?>>
                  <span>Operation</span>
                </label>
                <input class="form-control" name="pmh_operation" id="pmh_operation" value="<?= e((string)($defaults['pmh_operation'] ?? '')) ?>" <?= in_array('Operation', (array)($defaults['pmh'] ?? []), true) ? '' : 'disabled' ?>>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label d-flex align-items-center gap-2" for="pmh_special_confinement">
                  <input class="form-check-input m-0" type="checkbox" name="pmh[]" id="pmh_special_confinement" value="Confinement" <?= in_array('Confinement', (array)($defaults['pmh'] ?? []), true) ? 'checked' : '' ?>>
                  <span>Confinement</span>
                </label>
                <input class="form-control" name="pmh_confinement" id="pmh_confinement" value="<?= e((string)($defaults['pmh_confinement'] ?? '')) ?>" <?= in_array('Confinement', (array)($defaults['pmh'] ?? []), true) ? '' : 'disabled' ?>>
              </div>
              <div class="col-12">
                <label class="form-label">Others</label>
                <textarea class="form-control" name="pmh_others" rows="3"><?= e((string)($defaults['pmh_others'] ?? '')) ?></textarea>
              </div>
            </div>
          </div>
          
          <br><br>

          <div>
            <div class="h5 mb-2 text-primary">PART IV. OB History</div>
            <div class="row g-3">
              <div class="col-12 col-md-4">
                <label class="form-label">LMP</label>
                <input class="form-control" name="ob_lmp" value="<?= e((string)($defaults['ob_lmp'] ?? '')) ?>">
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label">OB SCORING (GTPAL)</label>
                <input class="form-control" name="ob_gtpal" value="<?= e((string)($defaults['ob_gtpal'] ?? '')) ?>">
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label">CHEST XRAY</label>
                <input class="form-control" name="ob_chest_xray" value="<?= e((string)($defaults['ob_chest_xray'] ?? '')) ?>">
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label">ECG</label>
                <input class="form-control" name="ob_ecg" value="<?= e((string)($defaults['ob_ecg'] ?? '')) ?>">
              </div>
            </div>
          </div>
          
          <br><br>

          <div>
            <div class="h5 mb-2 text-primary">PART IV. Physical &amp; Laboratory Assessment/Findings</div>
            <textarea class="form-control" name="physical_findings" rows="4"><?= e((string)($defaults['physical_findings'] ?? '')) ?></textarea>
          </div>
          
          <br><br>
          
          <div>
            <div class="h5 mb-2 text-primary">PART V. Mental Health</div>

            <div class="mb-3">
              <div class="fw-semibold mb-2">A. Stress Level at Work</div>
              <div class="mb-2">
                <img src="<?= e(asset('public/assets/likertScale.png')) ?>" alt="Likert Scale" style="max-width: 520px; width: 100%; height: auto; display: block; margin: 0 auto;">
              </div>
              <div class="vstack gap-2">
                <?php foreach ($likertStress as $k => $label): ?>
                  <label class="form-check">
                    <input class="form-check-input" type="radio" name="stress_level" value="<?= (int)$k ?>" <?= ((string)($defaults['stress_level'] ?? '') === (string)$k) ? 'checked' : '' ?> required>
                    <span class="form-check-label"><?= e($label) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <div>
              <div class="fw-semibold mb-2">B. Coping Level at Work</div>
              <div class="mb-2">
                <img src="<?= e(asset('public/assets/likertScale.png')) ?>" alt="Likert Scale" style="max-width: 520px; width: 100%; height: auto; display: block; margin: 0 auto;">
              </div>
              <div class="vstack gap-2">
                <?php foreach ($likertCoping as $k => $label): ?>
                  <label class="form-check">
                    <input class="form-check-input" type="radio" name="coping_level" value="<?= (int)$k ?>" <?= ((string)($defaults['coping_level'] ?? '') === (string)$k) ? 'checked' : '' ?> required>
                    <span class="form-check-label"><?= e($label) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          
          <br><br>

          <div>
            <div class="h5 mb-2">Assessed by</div>
            <div class="row g-3">
              <div class="col-12 col-md-8">
                <label class="form-label">Name</label>
                <input class="form-control" name="assessed_by_name" value="<?= e((string)($defaults['assessed_by_name'] ?? '')) ?>" required>
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label">License No.</label>
                <input class="form-control" name="license_no" value="<?= e((string)($defaults['license_no'] ?? '')) ?>" required>
              </div>
            </div>
          </div>

          <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-primary" type="submit">Save Assessment</button>
            <a class="btn btn-outline-secondary" href="<?= url('/medical/index.php') ?>">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </main>
  <script>
    (function () {
      function bindToggle(checkboxId, inputId) {
        var cb = document.getElementById(checkboxId);
        var input = document.getElementById(inputId);
        if (!cb || !input) return;
        function sync() {
          input.disabled = !cb.checked;
          if (input.disabled) input.value = '';
        }
        cb.addEventListener('change', sync);
        sync();
      }

      bindToggle('pmh_special_cancer', 'pmh_cancer_type');
      bindToggle('pmh_special_operation', 'pmh_operation');
      bindToggle('pmh_special_confinement', 'pmh_confinement');
    })();
  </script>

  <script>
    (function(){
      var ageInput = document.querySelector('input[name="age"]');
      var sexInput = document.querySelector('select[name="sex"]');
      var heightInput = document.getElementById('heightInput');
      var heightUnit = document.getElementById('heightUnit');
      var weightInput = document.getElementById('weightInput');
      var bmiValue = document.getElementById('bmiValue');
      var bmiCategory = document.getElementById('bmiCategory');
      var bmiBasis = document.getElementById('bmiBasis');
      var pctWrap = document.getElementById('bmiPercentileWrap');
      var pctInput = document.getElementById('bmiPercentile');
      var pctNoteWrap = document.getElementById('bmiPercentileNoteWrap');

      var bmiValueHidden = document.getElementById('bmiValueHidden');
      var bmiCategoryHidden = document.getElementById('bmiCategoryHidden');
      var bmiPercentileHidden = document.getElementById('bmiPercentileHidden');

      if (!ageInput || !sexInput || !heightInput || !heightUnit || !weightInput || !bmiValue || !bmiCategory || !bmiBasis || !pctWrap || !pctInput || !pctNoteWrap || !bmiValueHidden || !bmiCategoryHidden || !bmiPercentileHidden) return;

      var lmsBySex = null; // { 1: [{agemos,L,M,S}], 2: [...] }
      var lmsReady = false;
      var lmsUrl = <?php echo json_encode(asset('public/files/bmi-age-2022.csv'), JSON_UNESCAPED_SLASHES); ?>;

      function toNumber(v){
        var n = Number(String(v || '').trim());
        return Number.isFinite(n) ? n : NaN;
      }

      // Pediatric percentile uses Age field only: ageMos = ageYears * 12

      function erf(x){
        // Abramowitz and Stegun approximation
        var sign = x >= 0 ? 1 : -1;
        x = Math.abs(x);
        var a1 = 0.254829592;
        var a2 = -0.284496736;
        var a3 = 1.421413741;
        var a4 = -1.453152027;
        var a5 = 1.061405429;
        var p = 0.3275911;
        var t = 1 / (1 + p * x);
        var y = 1 - (((((a5 * t + a4) * t) + a3) * t + a2) * t + a1) * t * Math.exp(-x * x);
        return sign * y;
      }

      function normCdf(z){
        return 0.5 * (1 + erf(z / Math.SQRT2));
      }

      function csvToLms(text){
        var lines = String(text || '').split(/\r?\n/).filter(function(l){ return l.trim() !== ''; });
        if (lines.length < 2) return null;
        var header = lines[0].split(',').map(function(s){ return s.trim(); });
        var idxSex = header.indexOf('sex');
        var idxAge = header.indexOf('agemos');
        var idxL = header.indexOf('L');
        var idxM = header.indexOf('M');
        var idxS = header.indexOf('S');
        if (idxSex < 0 || idxAge < 0 || idxL < 0 || idxM < 0 || idxS < 0) return null;

        var out = { 1: [], 2: [] };
        for (var i = 1; i < lines.length; i++) {
          var parts = lines[i].split(',');
          if (parts.length < header.length) continue;
          var sex = Number(parts[idxSex]);
          var agemos = Number(parts[idxAge]);
          var L = Number(parts[idxL]);
          var M = Number(parts[idxM]);
          var S = Number(parts[idxS]);
          if (!Number.isFinite(sex) || !Number.isFinite(agemos) || !Number.isFinite(L) || !Number.isFinite(M) || !Number.isFinite(S)) continue;
          if (!out[sex]) out[sex] = [];
          out[sex].push({ agemos: agemos, L: L, M: M, S: S });
        }
        Object.keys(out).forEach(function(k){
          out[k].sort(function(a,b){ return a.agemos - b.agemos; });
        });
        return out;
      }

      function getLms(sexCode, ageMos){
        if (!lmsBySex || !lmsBySex[sexCode] || !lmsBySex[sexCode].length) return null;
        var arr = lmsBySex[sexCode];
        if (!Number.isFinite(ageMos)) return null;
        if (ageMos < arr[0].agemos || ageMos > arr[arr.length - 1].agemos) return null;

        // find right bracket
        var lo = 0;
        var hi = arr.length - 1;
        while (lo <= hi) {
          var mid = (lo + hi) >> 1;
          if (arr[mid].agemos === ageMos) return arr[mid];
          if (arr[mid].agemos < ageMos) lo = mid + 1;
          else hi = mid - 1;
        }
        var i1 = Math.max(1, lo);
        var a = arr[i1 - 1];
        var b = arr[i1];
        var t = (ageMos - a.agemos) / (b.agemos - a.agemos);
        return {
          agemos: ageMos,
          L: a.L + (b.L - a.L) * t,
          M: a.M + (b.M - a.M) * t,
          S: a.S + (b.S - a.S) * t
        };
      }

      function lmsPercentile(bmi, lms){
        if (!Number.isFinite(bmi) || bmi <= 0 || !lms) return NaN;
        var L = lms.L;
        var M = lms.M;
        var S = lms.S;
        if (!Number.isFinite(L) || !Number.isFinite(M) || !Number.isFinite(S) || M <= 0 || S <= 0) return NaN;
        var z;
        if (Math.abs(L) < 1e-8) {
          z = Math.log(bmi / M) / S;
        } else {
          z = (Math.pow(bmi / M, L) - 1) / (L * S);
        }
        var p = normCdf(z) * 100;
        if (!Number.isFinite(p)) return NaN;
        return Math.max(0, Math.min(100, p));
      }

      function ensureLmsLoaded(){
        if (lmsReady) return Promise.resolve(true);
        if (lmsBySex) return Promise.resolve(true);
        return fetch(lmsUrl, { cache: 'force-cache' })
          .then(function(r){ return r.ok ? r.text() : Promise.reject(new Error('fetch')); })
          .then(function(t){
            lmsBySex = csvToLms(t);
            lmsReady = !!lmsBySex;
            return lmsReady;
          })
          .catch(function(){
            lmsBySex = null;
            lmsReady = false;
            return false;
          });
      }

      function heightToMeters(h, unit){
        if (!Number.isFinite(h) || h <= 0) return NaN;
        if (unit === 'm') return h;
        if (unit === 'ft') return h * 0.3048;
        return h / 100;
      }

      function calcBmi(){
        var age = toNumber(ageInput.value);
        var hRaw = toNumber(heightInput.value);
        var w = toNumber(weightInput.value);
        var unit = String(heightUnit.value || 'cm');
        var hM = heightToMeters(hRaw, unit);

        var bmi = (Number.isFinite(w) && w > 0 && Number.isFinite(hM) && hM > 0) ? (w / (hM * hM)) : NaN;
        if (Number.isFinite(bmi)) {
          bmiValue.value = bmi.toFixed(2);
          bmiValueHidden.value = bmi.toFixed(2);
        } else {
          bmiValue.value = '';
          bmiValueHidden.value = '';
        }

        var isAdult = Number.isFinite(age) ? (age >= 21) : true;
        pctWrap.style.display = isAdult ? 'none' : '';
        pctNoteWrap.style.display = isAdult ? 'none' : '';

        if (!isAdult) {
          pctInput.value = '';
        }

        if (!Number.isFinite(bmi)) {
          bmiCategory.value = '';
          bmiCategoryHidden.value = '';
          bmiPercentileHidden.value = '';
          bmiBasis.textContent = '';
          return;
        }

        if (isAdult) {
          var c = '';
          if (bmi < 16) c = 'Severe Thinness';
          else if (bmi < 17) c = 'Moderate Thinness';
          else if (bmi < 18.5) c = 'Mild Thinness';
          else if (bmi < 25) c = 'Normal';
          else if (bmi < 30) c = 'Overweight';
          else if (bmi < 35) c = 'Obese Class I';
          else if (bmi < 40) c = 'Obese Class II';
          else c = 'Obese Class III';
          bmiCategory.value = c;
          bmiCategoryHidden.value = c;
          bmiPercentileHidden.value = '';
          bmiBasis.textContent = 'Adult BMI classification (Age ≥ 21)';
        } else {
          // Pediatric: compute percentile using LMS reference (2–20 years)
          var sexVal = String(sexInput.value || '');
          var sexCode = (sexVal === 'Male') ? 1 : (sexVal === 'Female' ? 2 : 0);
          if (!sexCode) {
            pctInput.value = '';
            bmiCategory.value = '';
            bmiBasis.textContent = 'Pediatric BMI percentile requires Sex = Male/Female.';
            return;
          }

          if (!Number.isFinite(age) || age < 2 || age > 20) {
            pctInput.value = '';
            bmiCategory.value = 'Requires BMI percentile';
            bmiCategoryHidden.value = 'Requires BMI percentile';
            bmiPercentileHidden.value = '';
            bmiBasis.textContent = 'Pediatric BMI percentile available for ages 2–20 only.';
            return;
          }

          var ageMos = age * 12;
          var usingAgeFallback = false;

          ensureLmsLoaded().then(function(ok){
            if (!ok) {
              pctInput.value = '';
              bmiCategory.value = '';
              bmiBasis.textContent = 'Failed to load BMI-for-age reference table.';
              return;
            }

            var lms = getLms(sexCode, ageMos);
            var p = lmsPercentile(bmi, lms);
            if (!Number.isFinite(p)) {
              pctInput.value = '';
              bmiCategory.value = 'Requires BMI percentile';
              bmiCategoryHidden.value = 'Requires BMI percentile';
              bmiPercentileHidden.value = '';
              bmiBasis.textContent = 'Pediatric BMI percentile available for ages 2–20 only.' + (Number.isFinite(ageMos) ? (' (computed age: ' + ageMos.toFixed(1) + ' months)') : '');
              return;
            }

            pctInput.value = p.toFixed(1) + '%';
            bmiPercentileHidden.value = p.toFixed(1);

            var cat = '';
            if (p < 5) cat = 'Underweight';
            else if (p < 85) cat = 'Healthy weight';
            else if (p <= 95) cat = 'At risk of overweight';
            else cat = 'Overweight';
            bmiCategory.value = cat;
            bmiCategoryHidden.value = cat;
            bmiBasis.textContent = 'Pediatric category based on BMI-for-age percentile (Age ≤ 20)';
          });
        }
      }

      ['input','change','blur'].forEach(function(evt){
        ageInput.addEventListener(evt, calcBmi);
        sexInput.addEventListener(evt, calcBmi);
        heightInput.addEventListener(evt, calcBmi);
        heightUnit.addEventListener(evt, calcBmi);
        weightInput.addEventListener(evt, calcBmi);
      });

      calcBmi();
    })();
  </script>

  <script>
    (function(){
      var input = document.getElementById('designationInput');
      var menu = document.getElementById('designationMenu');
      if (!input || !menu) return;

      var options = <?php echo json_encode(array_values($designationOptions), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
      var activeIndex = -1;
      var visible = [];

      function placeMenu(){
        var r = input.getBoundingClientRect();
        var vh = window.innerHeight || document.documentElement.clientHeight || 0;
        var margin = 8;
        var below = vh - r.bottom - margin;
        var above = r.top - margin;
        var openUp = (below < 220 && above > below);
        var space = openUp ? above : below;
        var maxH = Math.max(120, Math.min(280, space - 12));

        menu.style.position = 'fixed';
        menu.style.left = r.left + 'px';
        menu.style.width = r.width + 'px';
        menu.style.maxHeight = maxH + 'px';

        if (openUp) {
          menu.style.top = 'auto';
          menu.style.bottom = (vh - r.top + 6) + 'px';
        } else {
          menu.style.bottom = 'auto';
          menu.style.top = (r.bottom + 6) + 'px';
        }
      }

      function closeMenu(){
        menu.style.display = 'none';
        menu.innerHTML = '';
        activeIndex = -1;
      }

      function openMenu(){
        if (menu.innerHTML.trim() === '') return;
        placeMenu();
        menu.style.display = 'block';
      }

      function setActive(idx){
        activeIndex = idx;
        var items = menu.querySelectorAll('.ac-item');
        items.forEach(function(btn, i){
          if (i === idx) btn.classList.add('is-active');
          else btn.classList.remove('is-active');
        });
        if (idx >= 0) {
          try { items[idx].scrollIntoView({ block: 'nearest' }); } catch (e) {}
        }
      }

      function render(){
        var q = (input.value || '').toLowerCase();
        visible = options.filter(function(o){
          return q === '' ? true : String(o).toLowerCase().includes(q);
        }).slice(0, 200);

        menu.innerHTML = visible.map(function(o, i){
          var safe = String(o)
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;')
            .replace(/'/g,'&#39;');
          return '<button type="button" class="ac-item" role="option" data-idx="' + i + '">' + safe + '</button>';
        }).join('');

        if (visible.length) {
          openMenu();
          setActive(-1);
        } else {
          closeMenu();
        }
      }

      input.addEventListener('input', render);
      input.addEventListener('focus', render);
      input.addEventListener('keydown', function(e){
        if (menu.style.display !== 'block') return;
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          setActive(Math.min(activeIndex + 1, visible.length - 1));
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          setActive(Math.max(activeIndex - 1, 0));
        } else if (e.key === 'Enter') {
          if (activeIndex >= 0 && visible[activeIndex]) {
            e.preventDefault();
            input.value = visible[activeIndex];
            closeMenu();
          }
        } else if (e.key === 'Escape') {
          closeMenu();
        }
      });

      menu.addEventListener('mousedown', function(e){
        var btn = e.target && e.target.closest ? e.target.closest('.ac-item') : null;
        if (!btn) return;
        e.preventDefault();
        var idx = Number(btn.getAttribute('data-idx') || -1);
        if (idx >= 0 && visible[idx]) {
          input.value = visible[idx];
          closeMenu();
          input.focus();
        }
      });

      document.addEventListener('click', function(e){
        if (!menu.contains(e.target) && e.target !== input) closeMenu();
      });

      window.addEventListener('resize', function(){
        if (menu.style.display === 'block') placeMenu();
      });
      window.addEventListener('scroll', function(){
        if (menu.style.display === 'block') placeMenu();
      }, true);
    })();
  </script>
</body>
</html>
