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

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
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

    $divisionLevel = 'DepEd City Schools Division of Cabuyao';
    $validLevels = ['Elementary', 'Secondary', $divisionLevel];
    if (!in_array($data['level'], $validLevels, true)) {
        $errors[] = 'Invalid level.';
    }

    if ($data['level'] !== $divisionLevel) {
        $validSchoolList = $data['level'] === 'Secondary' ? $secondarySchools : $elementarySchools;
        if ($data['school'] === '' || !in_array($data['school'], $validSchoolList, true)) {
            $errors[] = 'Please select a valid school.';
        }
    } else {
        // Not applicable: School is optional for division-level entry
        $data['school'] = 'N/A';
    }

    if ($data['fullname'] === '') {
        $errors[] = 'Fullname is required.';
    }

    if ($data['age'] === '') {
        $errors[] = 'Age is required.';
    }

    if ($data['entry_date'] === '') {
        $errors[] = 'Date is required.';
    }

    if ($data['age'] !== '' && (!ctype_digit($data['age']) || (int)$data['age'] > 150)) {
        $errors[] = 'Age must be a valid number.';
    }

    if ($data['sex'] === '') {
        $errors[] = 'Sex is required.';
    }

    $validSex = ['', 'Male', 'Female', 'Others'];
    if (!in_array($data['sex'], $validSex, true)) {
        $errors[] = 'Invalid sex.';
    }

    if ($data['address'] === '') {
        $errors[] = 'Address is required.';
    }

    if ($data['contact_number'] !== '' && strlen($data['contact_number']) > 30) {
        $errors[] = 'Contact number is too long.';
    }
    if ($data['contact_number'] !== '' && !preg_match('/^[0-9+()\-\s]*$/', $data['contact_number'])) {
        $errors[] = 'Contact number must not contain letters.';
    }

    if ($data['date_of_birth'] === '') {
        $errors[] = 'Date of Birth is required.';
    }

    $validCivilStatus = [
        '',
        'Single',
        'Married',
        'Widowed',
        'Divorced',
        'Separated',
        'Registered Partnership/Civil Union',
        'Common-Law/Cohabitating',
    ];
    if ($data['civil_status'] === '') {
        $errors[] = 'Civil status is required.';
    }
    if (!in_array($data['civil_status'], $validCivilStatus, true)) {
        $errors[] = 'Invalid civil status.';
    }

    if ($data['designation'] !== '' && !in_array($data['designation'], $designationOptions, true)) {
        $errors[] = 'Invalid designation.';
    }

    if (!$errors) {
        try {
            $stmt = db()->prepare(
                'INSERT INTO patients (school, level, entry_date, fullname, age, sex, address, contact_number, date_of_birth, civil_status, designation, region, division, district, hmo_provider)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $data['school'],
                $data['level'],
                $data['entry_date'],
                $data['fullname'],
                (int)$data['age'],
                $data['sex'],
                $data['address'],
                $data['contact_number'] === '' ? null : $data['contact_number'],
                $data['date_of_birth'],
                $data['civil_status'],
                $data['designation'] === '' ? null : $data['designation'],
                $data['region'],
                $data['division'],
                $data['district'] === '' ? null : $data['district'],
                $data['hmo_provider'] === '' ? null : $data['hmo_provider'],
            ]);

            set_flash('success', 'Patient record saved.');
            redirect('/');
        } catch (Throwable $e) {
            $errors[] = 'Failed to save patient record. Please try again.';
        }
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

    <?php if ($success || $errors): ?>
      <div class="toast-stack-top-center">
        <?php if ($success): ?>
          <div class="toast toast-flash toast-flash--success" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2200">
            <div class="d-flex">
              <div class="toast-body">
                <div class="toast-flash__row">
                  <i class="bi bi-check-circle-fill toast-flash__icon" aria-hidden="true"></i>
                  <div class="toast-flash__text">
                    <div class="toast-flash__title">Success:</div>
                    <div class="toast-flash__message"><?= e($success) ?></div>
                  </div>
                </div>
              </div>
              <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($errors): ?>
          <?php foreach ($errors as $err): ?>
            <div class="toast toast-flash toast-flash--error" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
              <div class="d-flex">
                <div class="toast-body">
                  <div class="toast-flash__row">
                    <i class="bi bi-slash-circle-fill toast-flash__icon" aria-hidden="true"></i>
                    <div class="toast-flash__text">
                      <div class="toast-flash__title">Error:</div>
                      <div class="toast-flash__message"><?= e($err) ?></div>
                    </div>
                  </div>
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
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
              <input class="form-control" name="fullname" id="fullnameInput" placeholder="SURNAME, FIRSTNAME MIDDLENAME" value="<?= e((string)($_POST['fullname'] ?? '')) ?>" required>
            </div>

            <div class="col-12 col-md-3">
              <label class="form-label">Age</label>
              <input class="form-control" type="number" name="age" inputmode="numeric" min="0" max="150" step="1" value="<?= e((string)($_POST['age'] ?? '')) ?>" required>
            </div>

            <div class="col-12 col-md-3">
              <label class="form-label">Sex</label>
              <div class="d-flex align-items-center gap-3" style="min-height: 38px;">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="sex" id="sexM" value="M" <?= ((string)($_POST['sex'] ?? '') === 'M') ? 'checked' : '' ?> required>
                  <label class="form-check-label" for="sexM">M</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="sex" id="sexF" value="F" <?= ((string)($_POST['sex'] ?? '') === 'F') ? 'checked' : '' ?>>
                  <label class="form-check-label" for="sexF">F</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="sex" id="sexO" value="Others" <?= ((string)($_POST['sex'] ?? '') === 'Others') ? 'checked' : '' ?>>
                  <label class="form-check-label" for="sexO">Others</label>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">Address</label>
              <input class="form-control" name="address" value="<?= e((string)($_POST['address'] ?? '')) ?>" required>
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label">Contact Number</label>
              <input class="form-control" name="contact_number" value="<?= e((string)($_POST['contact_number'] ?? '')) ?>" type="tel" inputmode="tel" pattern="[0-9+()\-\s]{0,30}" maxlength="30" oninput="this.value=this.value.replace(/[^0-9+()\-\s]/g,'');">
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label">Date of Birth</label>
              <input class="form-control" type="date" name="date_of_birth" value="<?= e((string)($_POST['date_of_birth'] ?? '')) ?>" required>
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label">Civil Status</label>
              <select class="form-select" name="civil_status" required>
                <option value="" <?= ((string)($_POST['civil_status'] ?? '') === '') ? 'selected' : '' ?>>Select</option>
                <option value="Single" <?= ((string)($_POST['civil_status'] ?? '') === 'Single') ? 'selected' : '' ?>>Single</option>
                <option value="Married" <?= ((string)($_POST['civil_status'] ?? '') === 'Married') ? 'selected' : '' ?>>Married</option>
                <option value="Widowed" <?= ((string)($_POST['civil_status'] ?? '') === 'Widowed') ? 'selected' : '' ?>>Widowed</option>
                <option value="Divorced" <?= ((string)($_POST['civil_status'] ?? '') === 'Divorced') ? 'selected' : '' ?>>Divorced</option>
                <option value="Separated" <?= ((string)($_POST['civil_status'] ?? '') === 'Separated') ? 'selected' : '' ?>>Separated</option>
                <option value="Registered Partnership/Civil Union" <?= ((string)($_POST['civil_status'] ?? '') === 'Registered Partnership/Civil Union') ? 'selected' : '' ?>>Registered Partnership/Civil Union</option>
                <option value="Common-Law/Cohabitating" <?= ((string)($_POST['civil_status'] ?? '') === 'Common-Law/Cohabitating') ? 'selected' : '' ?>>Common-Law/Cohabitating</option>
              </select>
            </div>

            <div class="col-12 col-md-12">
              <label class="form-label">Designation</label>
              <div class="ac-wrap">
                <input class="form-control" id="designationInput" name="designation" value="<?= e((string)($_POST['designation'] ?? '')) ?>" placeholder="Type to search..." autocomplete="off">
                <div class="ac-menu" id="designationMenu" role="listbox" aria-label="Designation options"></div>
              </div>
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label">Region</label>
              <input class="form-control" name="region" value="<?= e((string)($_POST['region'] ?? $defaults['region'])) ?>">
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label">Division</label>
              <input class="form-control" name="division" value="<?= e((string)($_POST['division'] ?? $defaults['division'])) ?>">
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label">District</label>
              <input class="form-control" name="district" value="<?= e((string)($_POST['district'] ?? '')) ?>">
            </div>

            <div class="col-12">
              <label class="form-label">HMO Provider</label>
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

    <script>
      (function(){
        var el = document.getElementById('fullnameInput');
        if (!el) return;
        el.addEventListener('input', function(){
          var start = el.selectionStart;
          var end = el.selectionEnd;
          var next = (el.value || '').toUpperCase();
          if (el.value !== next) {
            el.value = next;
            try { el.setSelectionRange(start, end); } catch (e) {}
          }
        });
      })();
    </script>

  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function(){
      if (!window.bootstrap) return;
      var toasts = document.querySelectorAll('.toast');
      toasts.forEach(function(el){
        try { new bootstrap.Toast(el).show(); } catch (e) {}
      });
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
