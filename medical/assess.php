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
                    height_cm, weight_kg, temperature_c, pulse_rate, rr, o2_sat, bp_systolic, bp_diastolic,
                    past_medical_history, ob_lmp, ob_gtpal, ob_chest_xray, ob_ecg,
                    physical_findings, stress_level, coping_level
                 ) VALUES (
                    ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?,
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
                <input class="form-control" name="contact_number" value="<?= e((string)($patient['contact_number'] ?? '')) ?>" type="tel" inputmode="tel" pattern="[0-9+()\-\s]{0,30}" maxlength="30" oninput="this.value=this.value.replace(/[^0-9+()\-\s]/g,'');">
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
            <div class="row g-3">
              <div class="col-12 col-md-3">
                <label class="form-label">Height</label>
                <div class="input-group">
                  <input class="form-control" type="number" step="0.01" min="0" max="1000" name="height_cm" value="<?= e((string)($defaults['height_cm'] ?? '')) ?>" placeholder="Height">
                  <select class="form-select" name="height_unit" style="max-width: 92px;">
                    <option value="cm" <?= ((string)($defaults['height_unit'] ?? 'cm') === 'cm') ? 'selected' : '' ?>>cm</option>
                    <option value="m" <?= ((string)($defaults['height_unit'] ?? 'cm') === 'm') ? 'selected' : '' ?>>m</option>
                    <option value="ft" <?= ((string)($defaults['height_unit'] ?? 'cm') === 'ft') ? 'selected' : '' ?>>ft</option>
                  </select>
                </div>
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label">Weight</label>
                <input class="form-control" type="number" step="0.01" min="0" max="600" name="weight_kg" value="<?= e((string)($defaults['weight_kg'] ?? '')) ?>" placeholder="kg">
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
