<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/auth.php';

require_login('admin', 'admin/statistics.php');
$cfg = base_config();

$month = (string)($_GET['month'] ?? '');
$dateFrom = (string)($_GET['date_from'] ?? '');
$dateTo = (string)($_GET['date_to'] ?? '');
$level = trim((string)($_GET['level'] ?? ''));
$school = trim((string)($_GET['school'] ?? ''));
$district = trim((string)($_GET['district'] ?? ''));
$designation = trim((string)($_GET['designation'] ?? ''));
$sex = trim((string)($_GET['sex'] ?? ''));
$ageRange = trim((string)($_GET['age_range'] ?? ''));

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
    'Master Teacher II (Senior High School Master Teacher II - Academic and Core Subjects)',
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

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Statistics - <?= e($cfg['app_name']) ?></title>
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
              <div class="brand h5 mb-0 text-white fw-bold lh-1">Admin Statistics</div>
              <div class="small text-white-50 lh-1 mt-1">School Health Section</div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-auto mt-2 mt-md-0">
          <div class="d-flex align-items-center gap-2 justify-content-md-end">
            <div class="d-none d-lg-block text-end me-2">
              <div class="small fw-bold text-white lh-1"><?= e(current_user()['fullname']) ?></div>
              <div class="text-white-50 lh-1 mt-1" style="font-size: 0.7rem;">Administrator</div>
            </div>
            <a href="<?= url('/admin/index.php') ?>" class="btn btn-light btn-sm fw-bold px-3" style="border-radius: 8px;">Dashboard</a>
            <a href="<?= url('/') ?>" class="btn btn-light btn-sm fw-bold px-3" style="border-radius: 8px;">Home</a>
            <a href="<?= url('/auth/logout.php') ?>" class="btn btn-outline-light btn-sm px-3" style="border-radius: 8px;">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="container py-4 py-md-5">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
      <div>
        <div class="h4 fw-bold mb-0">Statistics</div>
        <div class="text-muted small">Overall coverage and key indicators (Medical + Dental)</div>
      </div>
      <div class="text-muted small" id="statsServerTime"></div>
    </div>

    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; overflow: hidden;">
      <div class="card-body p-3 p-md-4">
        <form method="get" class="row g-2 align-items-end">
          <div class="col-12 col-md-2">
            <label class="form-label small text-secondary fw-bold">Month</label>
            <input type="month" name="month" class="form-control" value="<?= e($month) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label small text-secondary fw-bold">Date from</label>
            <input type="date" name="date_from" class="form-control" value="<?= e($dateFrom) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label small text-secondary fw-bold">Date to</label>
            <input type="date" name="date_to" class="form-control" value="<?= e($dateTo) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label small text-secondary fw-bold">Level</label>
            <select name="level" class="form-select">
              <option value="" <?= $level === '' ? 'selected' : '' ?>>All</option>
              <option value="Elementary" <?= $level === 'Elementary' ? 'selected' : '' ?>>Elementary</option>
              <option value="Secondary" <?= $level === 'Secondary' ? 'selected' : '' ?>>Secondary</option>
              <option value="DepEd City Schools Division of Cabuyao" <?= $level === 'DepEd City Schools Division of Cabuyao' ? 'selected' : '' ?>>DepEd City Schools Division of Cabuyao</option>
            </select>
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label small text-secondary fw-bold">Sex</label>
            <select name="sex" class="form-select">
              <option value="" <?= $sex === '' ? 'selected' : '' ?>>All</option>
              <option value="Male" <?= $sex === 'Male' ? 'selected' : '' ?>>Male</option>
              <option value="Female" <?= $sex === 'Female' ? 'selected' : '' ?>>Female</option>
              <option value="Others" <?= $sex === 'Others' ? 'selected' : '' ?>>Others</option>
            </select>
          </div>
          <div class="col-12 col-md-2">
            <label class="form-label small text-secondary fw-bold">Age range</label>
            <select name="age_range" class="form-select">
              <option value="" <?= $ageRange === '' ? 'selected' : '' ?>>All</option>
              <option value="0-5" <?= $ageRange === '0-5' ? 'selected' : '' ?>>0-5</option>
              <option value="6-12" <?= $ageRange === '6-12' ? 'selected' : '' ?>>6-12</option>
              <option value="13-17" <?= $ageRange === '13-17' ? 'selected' : '' ?>>13-17</option>
              <option value="18-24" <?= $ageRange === '18-24' ? 'selected' : '' ?>>18-24</option>
              <option value="25-59" <?= $ageRange === '25-59' ? 'selected' : '' ?>>25-59</option>
              <option value="60+" <?= $ageRange === '60+' ? 'selected' : '' ?>>60+</option>
            </select>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label small text-secondary fw-bold">School</label>
            <select class="form-select" id="fSchool" name="school">
              <option value="">All</option>
            </select>
          </div>
          <div class="col-6 col-md-4">
            <label class="form-label small text-secondary fw-bold">District</label>
            <select class="form-select" id="fDistrict" name="district">
              <option value="" <?= $district === '' ? 'selected' : '' ?>>All</option>
              <option value="District 1" <?= $district === 'District 1' ? 'selected' : '' ?>>District 1</option>
              <option value="District 2" <?= $district === 'District 2' ? 'selected' : '' ?>>District 2</option>
              <option value="District 3" <?= $district === 'District 3' ? 'selected' : '' ?>>District 3</option>
              <option value="District 4" <?= $district === 'District 4' ? 'selected' : '' ?>>District 4</option>
              <option value="District 5" <?= $district === 'District 5' ? 'selected' : '' ?>>District 5</option>
            </select>
          </div>
          <div class="col-6 col-md-4">
            <label class="form-label small text-secondary fw-bold">Designation</label>
            <div class="ac-wrap">
              <input class="form-control" id="fDesignation" name="designation" value="<?= e($designation) ?>" placeholder="Type to search..." autocomplete="off">
              <div class="ac-menu" id="fDesignationMenu" role="listbox" aria-label="Designation options"></div>
            </div>
          </div>

          <div class="col-12 d-flex gap-2 justify-content-end mt-2">
            <a href="<?= url('/admin/statistics.php') ?>" class="btn btn-outline-secondary">Reset</a>
            <button class="btn btn-primary fw-bold" type="submit">Apply</button>
          </div>
        </form>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
          <div class="card-body">
            <div class="text-muted small">Patients</div>
            <div class="h4 fw-bold mb-0" id="kpiPatients">-</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
          <div class="card-body">
            <div class="text-muted small">Medical checked</div>
            <div class="h4 fw-bold mb-0" id="kpiMedChecked">-</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
          <div class="card-body">
            <div class="text-muted small">Dental checked</div>
            <div class="h4 fw-bold mb-0" id="kpiDenChecked">-</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
          <div class="card-body">
            <div class="text-muted small">Both completed</div>
            <div class="h4 fw-bold mb-0" id="kpiBoth">-</div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-lg-6">
        <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
          <div class="card-header bg-white border-0 py-3"><div class="fw-bold">Completion status</div></div>
          <div class="card-body">
            <div style="height: 260px;"><canvas id="chartCompletion"></canvas></div>
            <div class="mt-3" id="tblCompletion"></div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
          <div class="card-header bg-white border-0 py-3"><div class="fw-bold">Top schools (patients)</div></div>
          <div class="card-body">
            <div style="height: 260px;"><canvas id="chartTopSchools"></canvas></div>
            <div class="mt-3" id="tblTopSchools"></div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
          <div class="card-header bg-white border-0 py-3"><div class="fw-bold">Level</div></div>
          <div class="card-body">
            <div style="height: 220px;"><canvas id="chartLevel"></canvas></div>
            <div class="mt-3" id="tblLevel"></div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
          <div class="card-header bg-white border-0 py-3"><div class="fw-bold">Sex</div></div>
          <div class="card-body">
            <div style="height: 220px;"><canvas id="chartSex"></canvas></div>
            <div class="mt-3" id="tblSex"></div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
          <div class="card-header bg-white border-0 py-3"><div class="fw-bold">Age</div></div>
          <div class="card-body">
            <div style="height: 220px;"><canvas id="chartAge"></canvas></div>
            <div class="mt-3" id="tblAge"></div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
          <div class="card-header bg-white border-0 py-3"><div class="fw-bold">Medical summary (BMI)</div></div>
          <div class="card-body">
            <div style="height: 260px;"><canvas id="chartBmi"></canvas></div>
            <div class="mt-3" id="tblBmi"></div>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
          <div class="card-header bg-white border-0 py-3"><div class="fw-bold">Dental summary (DMFT)</div></div>
          <div class="card-body">
            <div style="height: 260px;"><canvas id="chartDmft"></canvas></div>
            <div class="mt-3" id="tblDmft"></div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <script>
    (function(){
      function esc(s){
        return String(s == null ? '' : s)
          .replace(/&/g,'&amp;')
          .replace(/</g,'&lt;')
          .replace(/>/g,'&gt;')
          .replace(/"/g,'&quot;')
          .replace(/'/g,'&#039;');
      }

      function setText(id, v){
        var el = document.getElementById(id);
        if (el) el.textContent = v == null ? '' : String(v);
      }

      function setHtml(id, html){
        var el = document.getElementById(id);
        if (el) el.innerHTML = html || '';
      }

      function chartColors(n){
        var base = [
          'rgba(13,110,253,0.75)',
          'rgba(25,135,84,0.75)',
          'rgba(255,193,7,0.75)',
          'rgba(220,53,69,0.75)',
          'rgba(111,66,193,0.75)',
          'rgba(32,201,151,0.75)',
          'rgba(13,202,240,0.75)',
          'rgba(253,126,20,0.75)',
        ];
        var out = [];
        for (var i = 0; i < n; i++) out.push(base[i % base.length]);
        return out;
      }

      function destroyChart(c){
        try { if (c && typeof c.destroy === 'function') c.destroy(); } catch (e) {}
      }

      function objToPairs(obj, options){
        obj = obj || {};
        var keys = Object.keys(obj);
        if (options && options.order && Array.isArray(options.order)) {
          keys = options.order.filter(function(k){ return Object.prototype.hasOwnProperty.call(obj, k); })
            .concat(keys.filter(function(k){ return options.order.indexOf(k) < 0; }));
        }
        var labels = [];
        var values = [];
        keys.forEach(function(k){
          labels.push((options && options.labels && options.labels[k]) ? options.labels[k] : k);
          values.push(Number(obj[k] || 0));
        });
        return { labels: labels, values: values };
      }

      function kvTable(obj, options){
        obj = obj || {};
        var rows = Object.keys(obj);
        if (options && options.order && Array.isArray(options.order)) {
          rows = options.order.filter(function(k){ return Object.prototype.hasOwnProperty.call(obj, k); })
            .concat(rows.filter(function(k){ return options.order.indexOf(k) < 0; }));
        }
        if (!rows.length) return '<div class="text-muted small">No data</div>';
        var html = '<div class="table-responsive"><table class="table table-sm align-middle mb-0">';
        html += '<thead><tr><th>Category</th><th class="text-end">Count</th></tr></thead><tbody>';
        rows.forEach(function(k){
          var label = (options && options.labels && options.labels[k]) ? options.labels[k] : k;
          html += '<tr><td>' + esc(label) + '</td><td class="text-end fw-semibold">' + esc(obj[k]) + '</td></tr>';
        });
        html += '</tbody></table></div>';
        return html;
      }

      function listTable(items, columns){
        items = Array.isArray(items) ? items : [];
        if (!items.length) return '<div class="text-muted small">No data</div>';
        var html = '<div class="table-responsive"><table class="table table-sm align-middle mb-0">';
        html += '<thead><tr>';
        (columns || []).forEach(function(c){ html += '<th>' + esc(c.label) + '</th>'; });
        html += '</tr></thead><tbody>';
        items.forEach(function(it){
          html += '<tr>';
          (columns || []).forEach(function(c){ html += '<td class="' + esc(c.className || '') + '">' + esc(it[c.key]) + '</td>'; });
          html += '</tr>';
        });
        html += '</tbody></table></div>';
        return html;
      }

      function apiUrlWithParams(baseUrl, params) {
        try {
          var u = new URL(baseUrl, window.location.origin);
          Object.keys(params || {}).forEach(function(k){
            if (params[k] === undefined || params[k] === null || params[k] === '') return;
            u.searchParams.set(k, String(params[k]));
          });
          return u.toString();
        } catch (e) {
          var qs = Object.keys(params || {})
            .filter(function(k){ return params[k] !== undefined && params[k] !== null && params[k] !== ''; })
            .map(function(k){ return encodeURIComponent(k) + '=' + encodeURIComponent(String(params[k])); })
            .join('&');
          return baseUrl + (baseUrl.indexOf('?') >= 0 ? '&' : '?') + qs;
        }
      }

      function renderDoughnut(canvasId, obj, options){
        if (!window.Chart) return null;
        var el = document.getElementById(canvasId);
        if (!el) return null;
        var pairs = objToPairs(obj, options);
        if (!pairs.labels.length) return null;
        return new Chart(el, {
          type: 'doughnut',
          data: { labels: pairs.labels, datasets: [{ data: pairs.values, backgroundColor: chartColors(pairs.values.length), borderWidth: 0 }] },
          options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
      }

      function renderBar(canvasId, labels, values, color){
        if (!window.Chart) return null;
        var el = document.getElementById(canvasId);
        if (!el) return null;
        if (!Array.isArray(labels) || !labels.length) return null;
        return new Chart(el, {
          type: 'bar',
          data: { labels: labels, datasets: [{ data: values, backgroundColor: color || 'rgba(13,110,253,0.75)', borderRadius: 8, borderSkipped: false }] },
          options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
      }

      var params = {
        month: <?= json_encode($month) ?>,
        date_from: <?= json_encode($dateFrom) ?>,
        date_to: <?= json_encode($dateTo) ?>,
        level: <?= json_encode($level) ?>,
        school: <?= json_encode($school) ?>,
        district: <?= json_encode($district) ?>,
        designation: <?= json_encode($designation) ?>,
        sex: <?= json_encode($sex) ?>,
        age_range: <?= json_encode($ageRange) ?>,
        limit: 5000,
      };

      (function(){
        var levelEl = document.querySelector('select[name="level"]');
        var schoolEl = document.getElementById('fSchool');
        var districtEl = document.getElementById('fDistrict');
        if (!levelEl || !schoolEl || !districtEl) return;

        function norm(s){
          return String(s || '')
            .toLowerCase()
            .replace(/\s+/g,' ')
            .replace(/\./g,'')
            .replace(/\s*\(.*?\)\s*/g,'')
            .trim();
        }

        var elementarySchools = [
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
          'Southville Elementary School'
        ];

        var secondarySchools = [
          'Bigaa Integrated National High School',
          'Cabuyao Integrated National High School',
          'Casile Integrated National High School',
          'Gulod National High School',
          'Mamatid National High School',
          'Mamatid Senior High School Stand Alone',
          'Marinig National High School',
          'Pulo National High School',
          'Pulo Senior High School',
          'Southville 1 Integrated National High School'
        ];

        var districtMap = (function(){
          var pairs = [
            ['Banay-Banay Elementary School', 'District 1'],
            ['Banlic Elementary School', 'District 1'],
            ['Baclaran Elementary School', 'District 1'],
            ['Pulo Elementary School', 'District 1'],
            ['Sala Elementary School', 'District 1'],
            ['San Isidro Elementary School', 'District 1'],

            ['Butong Elementary School', 'District 2'],
            ['Gulod Elementary School', 'District 2'],
            ['Mamatid Elementary School', 'District 2'],
            ['Marinig South Elementary School', 'District 2'],
            ['Marinig National High School', 'District 2'],
            ['Gulod National High School', 'District 2'],
            ['Mamatid National High School', 'District 2'],
            ['Mamatid Senior High School Stand Alone', 'District 2'],

            ['Cabuyao Central School', 'District 3'],
            ['Cabuyao Integrated National High School', 'District 3'],
            ['Southville Elementary School', 'District 3'],
            ['Southville 1 Integrated National High School', 'District 3'],

            ['Bigaa Elementary School', 'District 4'],
            ['Bigaa Integrated National High School', 'District 4'],
            ['North Marinig Elementary School', 'District 4'],
            ['Niugan Elementary School', 'District 4'],

            ['Casile Integrated National High School', 'District 5'],
            ['Pulo National High School', 'District 5'],
            ['Guinding Elementary School', 'District 5'],
            ['Guinting Elementary School', 'District 5'],
            ['Pittland Integrated School', 'District 5'],
            ['Diezmo Integrated School', 'District 5'],
            ['Diezmo Integrated School (Elem & JHS)', 'District 5'],
            ['Casile Elementary School', 'District 5']
          ];
          var map = {};
          for (var i = 0; i < pairs.length; i++) map[norm(pairs[i][0])] = pairs[i][1];
          return map;
        })();

        var divisionLevel = 'DepEd City Schools Division of Cabuyao';

        function rebuildSchoolOptions(){
          var level = levelEl.value || '';
          var district = districtEl.value || '';
          var list = [];
          if (level === 'Secondary') list = secondarySchools;
          else if (level === 'Elementary') list = elementarySchools;
          else if (level === divisionLevel) list = [];
          else list = elementarySchools.concat(secondarySchools);

          var prev = params.school || '';
          var html = '<option value="">All</option>';
          if (level === divisionLevel) {
            schoolEl.disabled = true;
            html += '<option value="">Not applicable</option>';
          } else {
            schoolEl.disabled = false;
            if (district) {
              list = list.filter(function(s){ return (districtMap[norm(s)] || '') === district; });
            }
            html += list.map(function(s){
              var safe = String(s)
                .replace(/&/g,'&amp;')
                .replace(/</g,'&lt;')
                .replace(/>/g,'&gt;')
                .replace(/"/g,'&quot;')
                .replace(/'/g,'&#39;');
              return '<option value="' + safe + '">' + safe + '</option>';
            }).join('');
          }
          schoolEl.innerHTML = html;
          if (prev) schoolEl.value = prev;
          if (prev && schoolEl.value !== prev) schoolEl.value = '';
        }

        levelEl.addEventListener('change', rebuildSchoolOptions);
        districtEl.addEventListener('change', rebuildSchoolOptions);
        rebuildSchoolOptions();
      })();

      (function(){
        var input = document.getElementById('fDesignation');
        var menu = document.getElementById('fDesignationMenu');
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

      var charts = {
        completion: null,
        schools: null,
        level: null,
        sex: null,
        age: null,
        bmi: null,
        dmft: null,
      };

      async function load(){
        setText('kpiPatients', '...');
        setText('kpiMedChecked', '...');
        setText('kpiDenChecked', '...');
        setText('kpiBoth', '...');

        try {
          var url = apiUrlWithParams(<?= json_encode(url('/api/admin_stats.php')) ?>, params);
          var res = await fetch(url, { headers: { 'Accept': 'application/json' } });
          var json = await res.json();
          if (!res.ok || !json || !json.ok) throw new Error((json && json.error) ? json.error : ('HTTP ' + res.status));

          setText('statsServerTime', json.server_time ? ('Updated: ' + String(json.server_time)) : '');
          var totals = json.totals || {};
          setText('kpiPatients', totals.patients ?? '-');
          setText('kpiMedChecked', totals.medical_checked ?? '-');
          setText('kpiDenChecked', totals.dental_checked ?? '-');
          setText('kpiBoth', totals.both_completed ?? '-');

          var b = json.breakdowns || {};

          setHtml('tblCompletion', kvTable(b.completion || {}, {
            order: ['both','medical_only','dental_only','neither'],
            labels: { both: 'Both completed', medical_only: 'Medical only', dental_only: 'Dental only', neither: 'Neither' }
          }));
          setHtml('tblLevel', kvTable(b.level || {}));
          setHtml('tblSex', kvTable(b.sex || {}));
          setHtml('tblAge', kvTable(b.age_bucket || {}, { order: ['0-5','6-12','13-17','18-24','25-59','60+','unknown'] }));
          setHtml('tblBmi', kvTable(b.bmi_category || {}));
          setHtml('tblDmft', kvTable(b.dmft_bucket || {}, { order: ['0','1-3','4-6','7+','unknown'] }));
          setHtml('tblTopSchools', listTable(b.top_schools || [], [
            { key: 'school', label: 'School' },
            { key: 'count', label: 'Count', className: 'text-end fw-semibold' },
          ]));

          Object.keys(charts).forEach(function(k){ destroyChart(charts[k]); charts[k] = null; });

          charts.completion = renderDoughnut('chartCompletion', (b.completion || {}), {
            order: ['both','medical_only','dental_only','neither'],
            labels: { both: 'Both completed', medical_only: 'Medical only', dental_only: 'Dental only', neither: 'Neither' }
          });

          var schools = Array.isArray(b.top_schools) ? b.top_schools : [];
          charts.schools = renderBar('chartTopSchools', schools.map(function(x){ return x.school; }), schools.map(function(x){ return Number(x.count || 0); }), 'rgba(13,110,253,0.75)');

          charts.level = renderDoughnut('chartLevel', (b.level || {}));
          charts.sex = renderDoughnut('chartSex', (b.sex || {}));

          var agePairs = objToPairs((b.age_bucket || {}), { order: ['0-5','6-12','13-17','18-24','25-59','60+','unknown'] });
          charts.age = renderBar('chartAge', agePairs.labels, agePairs.values, 'rgba(111,66,193,0.75)');

          charts.bmi = renderDoughnut('chartBmi', (b.bmi_category || {}));
          charts.dmft = renderDoughnut('chartDmft', (b.dmft_bucket || {}), { order: ['0','1-3','4-6','7+','unknown'] });

        } catch (e) {
          setText('statsServerTime', '');
          setText('kpiPatients', '-');
          setText('kpiMedChecked', '-');
          setText('kpiDenChecked', '-');
          setText('kpiBoth', '-');
          var msg = '<div class="alert alert-danger mb-0">Failed to load statistics.</div>';
          setHtml('tblCompletion', msg);
          setHtml('tblTopSchools', msg);
          setHtml('tblLevel', msg);
          setHtml('tblSex', msg);
          setHtml('tblAge', msg);
          setHtml('tblBmi', msg);
          setHtml('tblDmft', msg);
          Object.keys(charts).forEach(function(k){ destroyChart(charts[k]); charts[k] = null; });
        }
      }

      load();
    })();
  </script>
</body>
</html>
