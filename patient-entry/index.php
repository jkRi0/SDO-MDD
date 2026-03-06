<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/auth.php';

$cfg = base_config();
$user = current_user();

$elementarySchools = [
    'Baclaran Elementary School',
    'Banay-Banay Elementary School',
    'Banlic Elementary School',
    'Bigaa Elementary School',
    'Butong Elementary School',
    'Cabuyao Central School',
    'Casile Elementary School',
    'Diezmo Integrated School (Elem & JHS)',
    'Guinting Elementary School',
    'Gulod Elementary School',
    'Mamatid Elementary School',
    'Marinig South Elementary School',
    'Niugan Elementary School',
    'North Marinig Elementary School',
    'Pittland Integrated School',
    'Pulo Elementary School',
    'Sala Elementary School',
    'San Isidro Elementary School',
    'Southville Elementary School',
    '',
];

$secondarySchools = [
    'Bigaa Integrated National High School',
    'Cabuyao Integrated National High School',
    'Casile Integrated National High School',
    'Gulod National High School',
    'Mamatid National High School',
    'Mamatid Senior High School Stand Alone',
    'Marinig National High School',
    'Pulo National High School',
    'Pulo Senior High School',
    'Southville 1 Integrated National High School',
];

$defaults = [
    'entry_date' => date('Y-m-d'),
    'region' => 'IV-A CALABARZON',
    'division' => 'Cabuyao City',
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $level = (string)($_POST['level'] ?? 'Elementary');
    $school = trim((string)($_POST['school'] ?? ''));
    $entryDate = (string)($_POST['entry_date'] ?? $defaults['entry_date']);
    $fullname = trim((string)($_POST['fullname'] ?? ''));
    $age = trim((string)($_POST['age'] ?? ''));
    $sex = (string)($_POST['sex'] ?? '');
    $address = trim((string)($_POST['address'] ?? ''));
    $dob = (string)($_POST['date_of_birth'] ?? '');
    $civilStatus = trim((string)($_POST['civil_status'] ?? ''));
    $region = trim((string)($_POST['region'] ?? $defaults['region']));
    $division = trim((string)($_POST['division'] ?? $defaults['division']));
    $district = trim((string)($_POST['district'] ?? ''));
    $hmo = trim((string)($_POST['hmo_provider'] ?? ''));

    $divisionLevel = 'DepEd City Schools Division of Cabuyao';
    $validLevels = ['Elementary', 'Secondary', $divisionLevel];
    if (!in_array($level, $validLevels, true)) {
        $errors[] = 'Invalid level.';
    }

    if ($level !== $divisionLevel) {
        $validSchoolList = $level === 'Secondary' ? $secondarySchools : $elementarySchools;
        if ($school === '' || !in_array($school, $validSchoolList, true)) {
            $errors[] = 'Please select a valid school.';
        }
    } else {
        // Not applicable: School is optional for division-level entry
        $school = 'N/A';
    }

    if ($fullname === '') {
        $errors[] = 'Fullname is required.';
    }

    if ($entryDate === '') {
        $errors[] = 'Date is required.';
    }

    if ($age !== '' && (!ctype_digit($age) || (int)$age > 150)) {
        $errors[] = 'Age must be a valid number.';
    }

    $validSex = ['', 'Male', 'Female', 'Others'];
    if (!in_array($sex, $validSex, true)) {
        $errors[] = 'Invalid sex.';
    }

    if (!$errors) {
        $stmt = db()->prepare(
            'INSERT INTO patients (school, level, entry_date, fullname, age, sex, address, date_of_birth, civil_status, region, division, district, hmo_provider)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $school,
            $level,
            $entryDate,
            $fullname,
            $age === '' ? null : (int)$age,
            $sex === '' ? null : $sex,
            $address === '' ? null : $address,
            $dob === '' ? null : $dob,
            $civilStatus === '' ? null : $civilStatus,
            $region === '' ? $defaults['region'] : $region,
            $division === '' ? $defaults['division'] : $division,
            $district === '' ? null : $district,
            $hmo === '' ? null : $hmo,
        ]);

        set_flash('success', 'Patient record saved.');
        redirect('/patient-entry/index.php');
    }
}

$success = get_flash('success');

$postedLevel = (string)($_POST['level'] ?? 'Elementary');
$divisionLevel = 'DepEd City Schools Division of Cabuyao';
$schoolsForLevel = match ($postedLevel) {
    'Secondary' => $secondarySchools,
    $divisionLevel => [],
    default => $elementarySchools,
};

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Patient Entry - <?= e($cfg['app_name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
              <div class="brand h5 mb-0 text-white fw-bold lh-1">Patient Entry</div>
              <div class="small text-white-50 lh-1 mt-1">School Health Section</div>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-auto mt-2 mt-md-0">
          <div class="d-flex align-items-center gap-2 justify-content-md-end">
            <?php if ($user): ?>
              <div class="d-none d-lg-block text-end me-2">
                <div class="small fw-bold text-white lh-1"><?= e($user['fullname']) ?></div>
                <div class="text-white-50 lh-1 mt-1" style="font-size: 0.7rem;"><?= ucfirst($user['role']) ?></div>
              </div>
              <?php if ($user['role'] === 'admin'): ?>
                <a href="<?= url('/admin/index.php') ?>" class="btn btn-light btn-sm fw-bold px-3" style="border-radius: 8px;">Admin</a>
              <?php endif; ?>
              <a href="<?= url('/auth/logout.php') ?>" class="btn btn-outline-light btn-sm px-3" style="border-radius: 8px;">Logout</a>
            <?php else: ?>
              <a href="<?= url('/auth/login.php') ?>" class="btn btn-light btn-sm fw-bold px-4" style="border-radius: 8px;">Admin Login</a>
            <?php endif; ?>
            <a href="<?= url('/') ?>" class="btn btn-outline-light btn-sm px-3" style="border-radius: 8px;">Home</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="container py-4 py-md-5" style="max-width:980px">

    <?php if ($success): ?>
      <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <div class="fw-semibold mb-1">Please fix the following:</div>
        <ul class="mb-0">
          <?php foreach ($errors as $err): ?>
            <li><?= e($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="card shadow-sm">
      <div class="card-body p-4">
        <form method="post">
          <div class="row g-3">
            <div class="col-12 col-md-4">
              <label class="form-label">Level</label>
              <select class="form-select" name="level" id="levelSelect">
                <option value="Elementary" <?= $postedLevel === 'Elementary' ? 'selected' : '' ?>>Elementary</option>
                <option value="Secondary" <?= $postedLevel === 'Secondary' ? 'selected' : '' ?>>Secondary</option>
                <option value="DepEd City Schools Division of Cabuyao" <?= $postedLevel === 'DepEd City Schools Division of Cabuyao' ? 'selected' : '' ?>>DepEd City Schools Division of Cabuyao</option>
              </select>
              <div class="form-text">Changing level will refresh the school list.</div>
            </div>

            <div class="col-12 col-md-8" id="schoolGroup">
              <label class="form-label">School</label>
              <select class="form-select" name="school" id="schoolSelect" <?= $postedLevel === $divisionLevel ? '' : 'required' ?> <?= $postedLevel === $divisionLevel ? 'disabled' : '' ?>>
                <?php if ($postedLevel === $divisionLevel): ?>
                  <option value="">Not applicable</option>
                <?php else: ?>
                  <option value="">Select school</option>
                  <?php foreach ($schoolsForLevel as $s): ?>
                    <option value="<?= e($s) ?>" <?= ((string)($_POST['school'] ?? '') === $s) ? 'selected' : '' ?>><?= e($s) ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
              <div class="form-text" id="schoolHelp" style="display:none;">School is not required for this level.</div>
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label">Date</label>
              <input class="form-control" type="date" name="entry_date" value="<?= e((string)($_POST['entry_date'] ?? $defaults['entry_date'])) ?>" required>
            </div>

            <div class="col-12 col-md-8">
              <label class="form-label">Fullname</label>
              <input class="form-control" name="fullname" placeholder="SURNAME, FIRSTNAME MIDDLENAME" value="<?= e((string)($_POST['fullname'] ?? '')) ?>" required>
            </div>

            <div class="col-12 col-md-3">
              <label class="form-label">Age</label>
              <input class="form-control" name="age" inputmode="numeric" value="<?= e((string)($_POST['age'] ?? '')) ?>">
            </div>

            <div class="col-12 col-md-3">
              <label class="form-label">Sex</label>
              <select class="form-select" name="sex">
                <option value="" <?= ((string)($_POST['sex'] ?? '') === '') ? 'selected' : '' ?>>Select</option>
                <option value="Male" <?= ((string)($_POST['sex'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= ((string)($_POST['sex'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                <option value="Others" <?= ((string)($_POST['sex'] ?? '') === 'Others') ? 'selected' : '' ?>>Others</option>
              </select>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">Address</label>
              <input class="form-control" name="address" value="<?= e((string)($_POST['address'] ?? '')) ?>">
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label">Date of Birth</label>
              <input class="form-control" type="date" name="date_of_birth" value="<?= e((string)($_POST['date_of_birth'] ?? '')) ?>">
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label">Civil Status</label>
              <input class="form-control" name="civil_status" value="<?= e((string)($_POST['civil_status'] ?? '')) ?>">
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label">Region</label>
              <input class="form-control" name="region" value="<?= e((string)($_POST['region'] ?? $defaults['region'])) ?>">
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label">Division</label>
              <input class="form-control" name="division" value="<?= e((string)($_POST['division'] ?? $defaults['division'])) ?>">
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">District</label>
              <input class="form-control" name="district" value="<?= e((string)($_POST['district'] ?? '')) ?>">
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">HMO Provider (optional)</label>
              <input class="form-control" name="hmo_provider" value="<?= e((string)($_POST['hmo_provider'] ?? '')) ?>">
            </div>

            <div class="col-12 d-flex gap-2 mt-2">
              <button class="btn btn-primary" type="submit">Submit</button>
              <a class="btn btn-outline-secondary" href="<?= e(url('/')) ?>">Cancel</a>
            </div>
          </div>
        </form>
      </div>
    </div>

    <script>
      (function(){
        var elementary = <?= json_encode($elementarySchools, JSON_UNESCAPED_UNICODE) ?>;
        var secondary = <?= json_encode($secondarySchools, JSON_UNESCAPED_UNICODE) ?>;
        var divisionLevel = 'DepEd City Schools Division of Cabuyao';
        var levelEl = document.getElementById('levelSelect');
        var schoolEl = document.getElementById('schoolSelect');
        var schoolGroup = document.getElementById('schoolGroup');
        var schoolHelp = document.getElementById('schoolHelp');
        if (!levelEl || !schoolEl) return;

        function setOptions(items){
          var first = schoolEl.value;
          schoolEl.innerHTML = '';
          var opt0 = document.createElement('option');
          opt0.value = '';
          opt0.textContent = 'Select school';
          schoolEl.appendChild(opt0);
          items.forEach(function(name){
            var opt = document.createElement('option');
            opt.value = name;
            opt.textContent = name;
            schoolEl.appendChild(opt);
          });
          if (first && items.indexOf(first) !== -1) {
            schoolEl.value = first;
          }
        }

        function refresh(){
          if (levelEl.value === divisionLevel) {
            schoolEl.required = false;
            schoolEl.disabled = true;
            if (schoolGroup) schoolGroup.style.opacity = '0.65';
            if (schoolHelp) schoolHelp.style.display = 'block';
            schoolEl.innerHTML = '';
            var opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'Not applicable';
            schoolEl.appendChild(opt);
            schoolEl.value = '';
            return;
          }

          schoolEl.disabled = false;
          schoolEl.required = true;
          if (schoolGroup) schoolGroup.style.opacity = '';
          if (schoolHelp) schoolHelp.style.display = 'none';
          setOptions(levelEl.value === 'Secondary' ? secondary : elementary);
        }

        levelEl.addEventListener('change', function(){
          schoolEl.value = '';
          refresh();
        });

        refresh();
      })();
    </script>

  </main>
</body>
</html>
