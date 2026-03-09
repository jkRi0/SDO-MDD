<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/auth.php';

require_login('medical', 'medical/index.php');
$cfg = base_config();

$flashSuccess = get_flash('success');
$flashError = get_flash('error');

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

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Medical Dashboard - <?= e($cfg['app_name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= e(asset('public/assets/css/styles.css')) ?>" rel="stylesheet">
</head>
<body class="bg-light">
  <header class="appbar">
    <div class="container py-3">
      <div class="row align-items-center">
        <!-- Logo & Title -->
        <div class="col-12 col-md">
          <div class="d-flex align-items-center gap-3">
            <img src="<?= e(asset('public/assets/sdo-logo.png')) ?>" alt="Logo" style="width: 48px; height: 48px; flex-shrink: 0;">
            <div class="overflow-hidden">
              <div class="brand h5 mb-0 text-white fw-bold lh-1">Medical Dashboard</div>
              <div class="small text-white-50 lh-1 mt-1">School Health Section</div>
            </div>
          </div>
        </div>
        <!-- Header Actions -->
        <div class="col-12 col-md-auto mt-2 mt-md-0">
          <div class="d-flex align-items-center gap-2 justify-content-md-end">
            <div class="d-none d-lg-block text-end me-2">
              <div class="small fw-bold text-white lh-1"><?= e(current_user()['fullname']) ?></div>
              <div class="text-white-50 lh-1 mt-1" style="font-size: 0.7rem;">Medical Staff</div>
            </div>
            <a href="<?= url('/') ?>" class="btn btn-light btn-sm fw-bold px-3" style="border-radius: 8px;">Home</a>
            <a href="<?= url('/auth/logout.php') ?>" class="btn btn-outline-light btn-sm px-3" style="border-radius: 8px;">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="container py-4 py-md-5">
    <?php if ($flashSuccess || $flashError): ?>
      <div class="toast-stack-top-center">
        <?php if ($flashSuccess): ?>
          <div class="toast toast-flash toast-flash--success" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2200">
            <div class="d-flex">
              <div class="toast-body">
                <div class="toast-flash__row">
                  <i class="bi bi-check-circle-fill toast-flash__icon" aria-hidden="true"></i>
                  <div class="toast-flash__text">
                    <div class="toast-flash__title">Success:</div>
                    <div class="toast-flash__message"><?= e($flashSuccess) ?></div>
                  </div>
                </div>
              </div>
              <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
          <div class="toast toast-flash toast-flash--error" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3200">
            <div class="d-flex">
              <div class="toast-body">
                <div class="toast-flash__row">
                  <i class="bi bi-slash-circle-fill toast-flash__icon" aria-hidden="true"></i>
                  <div class="toast-flash__text">
                    <div class="toast-flash__title">Error:</div>
                    <div class="toast-flash__message"><?= e($flashError) ?></div>
                  </div>
                </div>
              </div>
              <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
      <!-- <div>
        <div class="h4 mb-0">Submitted Patients</div>
        <div class="text-secondary small">Updates automatically</div>
      </div> -->
      <div class="text-secondary small" id="lastUpdated">&nbsp;</div>
    </div>

    <div class="card shadow-sm border-0 mb-3" style="border-radius: 16px;">
      <div class="card-body">
        <div class="row g-2 align-items-end" id="filterContainer">
          <div class="col-12 col-md-3">
            <label class="form-label mb-1">Month</label>
            <input class="form-control" type="month" id="fMonth">
          </div>

          <div class="col-12 col-md-3">
            <label class="form-label mb-1">Level</label>
            <select class="form-select" id="fLevel">
              <option value="">All</option>
              <option value="Elementary">Elementary</option>
              <option value="Secondary">Secondary</option>
              <option value="DepEd City Schools Division of Cabuyao">DepEd City Schools Division of Cabuyao</option>
            </select>
          </div>

          <div class="col-12 col-md-3">
            <label class="form-label mb-1">School</label>
            <select class="form-select" id="fSchool">
              <option value="">All</option>
            </select>
          </div>

          <div class="col-12 col-md-3">
            <label class="form-label mb-1">District</label>
            <select class="form-select" id="fDistrict">
              <option value="">All</option>
              <option value="District 1">District 1</option>
              <option value="District 2">District 2</option>
              <option value="District 3">District 3</option>
              <option value="District 4">District 4</option>
              <option value="District 5">District 5</option>
            </select>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label mb-1">Designation</label>
            <div class="ac-wrap">
              <input class="form-control" id="fDesignation" placeholder="Type to search..." autocomplete="off">
              <div class="ac-menu" id="fDesignationMenu" role="listbox" aria-label="Designation options"></div>
            </div>
          </div>

          <div class="col-12 col-md-2">
            <label class="form-label mb-1">Age</label>
            <select class="form-select" id="fAgeRange">
              <option value="">All</option>
              <option value="0-5">0-5</option>
              <option value="6-12">6-12</option>
              <option value="13-17">13-17</option>
              <option value="18-24">18-24</option>
              <option value="25-59">25-59</option>
              <option value="60+">60+</option>
            </select>
          </div>

          <div class="col-12 col-md-2">
            <label class="form-label mb-1">Sex</label>
            <select class="form-select" id="fSex">
              <option value="">All</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Others">Others</option>
            </select>
          </div>

          <div class="col-12 col-md-2">
            <label class="form-label mb-1">Medical Status</label>
            <select class="form-select" id="fStatus">
              <option value="">All</option>
              <option value="checked">Checked</option>
              <option value="pending">Pending</option>
              <option value="completed">Completed</option>
            </select>
          </div>

          
        </div>
      </div>
    </div>

    <div class="card shadow-sm border-0 mb-3" style="border-radius: 16px;">
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <div class="col-12 col-md">
            <div class="fw-semibold">Generations</div>
            <div class="text-secondary small">Generates PDFs for all patients currently listed in the table</div>
          </div>
          <div class="col-12 col-md-auto d-grid">
            <button class="btn btn-primary" type="button" id="btnBulkPdf1">PDF1</button>
          </div>
          <div class="col-12 col-md-auto d-grid">
            <button class="btn btn-outline-primary" type="button" id="btnBulkPdf2">PDF2</button>
          </div>
        </div>
        <div class="mt-2 small text-secondary" id="bulkPdfStatus" style="display:none;"></div>
        <div class="mt-2" id="bulkPdfLinks" style="display:none;"></div>
      </div>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 80px;">ID</th>
                <th>Patient</th>
                <th style="width: 140px;">Entry Date</th>
                <th>School</th>
                <th style="width: 160px;">Medical</th>
                <th style="width: 160px;">Dental</th>
                <th style="width: 260px;">Actions</th>
              </tr>
            </thead>
            <tbody id="patientsBody">
              <tr>
                <td colspan="7" class="text-center text-secondary py-4">Loading...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <script src="<?= e(asset('public/assets/js/polling-config.js')) ?>"></script>
    <script>
      (function(){
        var body = document.getElementById('patientsBody');
        var lastUpdated = document.getElementById('lastUpdated');
        if (!body) return;

        var baseUrl = '<?= e(url('/api/patients.php')) ?>';
        var currentQuery = '';
        var lastRows = [];

        function sleep(ms){
          return new Promise(function(resolve){ setTimeout(resolve, ms || 0); });
        }

        function escHtml(s){
          return String(s ?? '').replace(/[&<>"']/g, function(c){
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'})[c];
          });
        }

        function addBulkLink(link){
          if (!link || !link.blobUrl) return;
          var host = document.getElementById('bulkPdfLinks');
          if (!host) return;
          host.style.display = 'none';
          var a = document.createElement('a');
          a.href = link.blobUrl;
          a.target = '_blank';
          a.rel = 'noopener noreferrer';
          a.className = 'text-decoration-none';
          a.textContent = link.filename || link.title || 'Open PDF';
          host.innerHTML = '';
          host.appendChild(a);
        }

        function showBulkLink(){
          var host = document.getElementById('bulkPdfLinks');
          if (!host) return;
          host.style.display = '';
        }

        var levelEl = document.getElementById('fLevel');
        var schoolEl = document.getElementById('fSchool');
        var districtEl = document.getElementById('fDistrict');

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
            ['Pittland Elementary School', 'District 5'],
            ['Pittland Integrated School', 'District 5'],
            ['Diezmo Integrated School', 'District 5'],
            ['Diezmo Integrated School (Elem & JHS)', 'District 5'],
            ['Casile Elementary School', 'District 5']
          ];
          var map = {};
          for (var i = 0; i < pairs.length; i++) {
            map[norm(pairs[i][0])] = pairs[i][1];
          }
          return map;
        })();

        var divisionLevel = 'DepEd City Schools Division of Cabuyao';

        function rebuildSchoolOptions(){
          if (!schoolEl) return;
          var level = levelEl ? levelEl.value : '';
          var district = districtEl ? districtEl.value : '';
          var list = [];
          if (level === 'Secondary') list = secondarySchools;
          else if (level === 'Elementary') list = elementarySchools;
          else if (level === divisionLevel) list = [];
          else list = elementarySchools.concat(secondarySchools);

          var prev = schoolEl.value;
          var html = '<option value="">All</option>';
          if (level === divisionLevel) {
            schoolEl.disabled = true;
            html += '<option value="">Not applicable</option>';
          } else {
            schoolEl.disabled = false;
            if (district) {
              list = list.filter(function(s){
                return (districtMap[norm(s)] || '') === district;
              });
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

        if (levelEl) {
          levelEl.addEventListener('change', function(){
            rebuildSchoolOptions();
          });
        }
        rebuildSchoolOptions();

        (function(){
          if (!schoolEl || !districtEl) return;

          function levelForSchool(s){
            var n = norm(s);
            for (var i = 0; i < secondarySchools.length; i++) {
              if (norm(secondarySchools[i]) === n) return 'Secondary';
            }
            for (var j = 0; j < elementarySchools.length; j++) {
              if (norm(elementarySchools[j]) === n) return 'Elementary';
            }
            return '';
          }

          function applyFromSchool(){
            var s = schoolEl.value;
            if (!s) return;
            var d = districtMap[norm(s)] || '';
            if (d) districtEl.value = d;
            var lvl = levelForSchool(s);
            if (lvl && levelEl) levelEl.value = lvl;
          }

          districtEl.addEventListener('change', function(){
            rebuildSchoolOptions();
          });

          schoolEl.addEventListener('change', function(){
            applyFromSchool();
            rebuildSchoolOptions();
          });

          applyFromSchool();
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

        function buildQueryFromUI(){
          var monthEl = document.getElementById('fMonth');
          var levelEl = document.getElementById('fLevel');
          var schoolEl = document.getElementById('fSchool');
          var districtEl = document.getElementById('fDistrict');
          var designationEl = document.getElementById('fDesignation');
          var ageRangeEl = document.getElementById('fAgeRange');
          var sexEl = document.getElementById('fSex');
          var statusFieldEl = document.getElementById('fStatusField');
          var statusEl = document.getElementById('fStatus');
          var params = new URLSearchParams();
          params.set('limit', '200');

          if (monthEl && monthEl.value) params.set('month', monthEl.value);

          if (levelEl && levelEl.value) params.set('level', levelEl.value);
          if (schoolEl && schoolEl.value) params.set('school', schoolEl.value);
          if (districtEl && districtEl.value) params.set('district', districtEl.value);
          if (designationEl && designationEl.value.trim()) params.set('designation', designationEl.value.trim());
          if (ageRangeEl && ageRangeEl.value) params.set('age_range', ageRangeEl.value);
          if (sexEl && sexEl.value) params.set('sex', sexEl.value);
          if (statusEl && statusEl.value) {
            params.set('status_field', 'medical');
            params.set('status', statusEl.value);
          }

          return params.toString();
        }

        var applyDebounceTimer = null;
        function applyFiltersDebounced(){
          if (applyDebounceTimer) clearTimeout(applyDebounceTimer);
          applyDebounceTimer = setTimeout(function(){
            currentQuery = buildQueryFromUI();
            poll();
          }, 300);
        }

        function wireRealtimeFilter(id, evt){
          var el = document.getElementById(id);
          if (!el) return;
          el.addEventListener(evt || 'change', function(){
            applyFiltersDebounced();
          });
        }

        wireRealtimeFilter('fMonth', 'change');
        wireRealtimeFilter('fLevel', 'change');
        wireRealtimeFilter('fSchool', 'change');
        wireRealtimeFilter('fDistrict', 'change');
        wireRealtimeFilter('fDesignation', 'input');
        wireRealtimeFilter('fAgeRange', 'change');
        wireRealtimeFilter('fSex', 'change');
        wireRealtimeFilter('fStatusField', 'change');
        wireRealtimeFilter('fStatus', 'change');

        var intervalMs = (window.SDO_MDD_POLLING && window.SDO_MDD_POLLING.intervalMs) ? window.SDO_MDD_POLLING.intervalMs : 3000;

        function esc(s){
          return String(s ?? '').replace(/[&<>"']/g, function(c){
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'})[c];
          });
        }

        function badge(checked){
          return checked ? '<span class="badge text-bg-success">Checked</span>' : '<span class="badge text-bg-secondary">Pending</span>';
        }

        function render(rows){
          lastRows = Array.isArray(rows) ? rows : [];
          if (!Array.isArray(rows) || rows.length === 0) {
            body.innerHTML = '<tr><td colspan="7" class="text-center text-secondary py-4">No submissions yet.</td></tr>';
            return;
          }

          body.innerHTML = rows.map(function(r){
            var id = Number(r.id || 0);
            var patient = esc(r.fullname);
            var school = esc(r.school);
            var level = esc(r.level);
            var entryDate = esc(r.entry_date);
            var medChecked = Number(r.medical_checked) === 1;
            var med = badge(medChecked);
            var dent = badge(Number(r.dental_checked) === 1);

            return (
              '<tr>' +
                '<td class="text-secondary">' + id + '</td>' +
                '<td><div class="fw-semibold">' + patient + '</div><div class="small text-secondary">' + level + '</div></td>' +
                '<td>' + entryDate + '</td>' +
                '<td>' + school + '</td>' +
                '<td>' + med + '</td>' +
                '<td>' + dent + '</td>' +
                '<td>' +
                  '<div class="d-flex flex-nowrap gap-2">' +
                    '<a class="btn btn-sm btn-primary" href="assess.php?id=' + id + '">' + (medChecked ? 'View' : 'Assess') + '</a>' +
                    '<button class="btn btn-sm btn-outline-secondary" type="button" onclick="window.sdoGenerateBlankPdf && window.sdoGenerateBlankPdf({ type: \'medical\', patientId: ' + id + ', title: \'Medical Form\' });">PDF</button>' +
                    '<form class="m-0 d-inline" method="post" action="remove.php" onsubmit="return confirm(\'Remove this patient entry?\');">' +
                      '<input type="hidden" name="id" value="' + id + '">' +
                      '<button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>' +
                    '</form>' +
                  '</div>' +
                '</td>' +
              '</tr>'
            );
          }).join('');
        }

        var inFlight = false;
        async function poll(){
          if (inFlight) return;
          inFlight = true;
          try {
            var url = baseUrl;
            var q = currentQuery;
            if (!q) q = 'limit=200';
            if (q) url = url + '?' + q;
            var res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            var json = await res.json();
            if (json && json.ok) {
              render(json.data);
              if (lastUpdated) {
                var now = new Date();
                lastUpdated.textContent = 'Last updated: ' + now.toLocaleTimeString();
              }
            }
          } catch (e) {
          } finally {
            inFlight = false;
          }
        }

        poll();
        setInterval(poll, intervalMs);

        async function generatePdf1(){
          if (!lastRows || lastRows.length === 0) return;
          if (!window.sdoGenerateMedicalBulkPdf) return;

          var statusEl = document.getElementById('bulkPdfStatus');
          var linksEl = document.getElementById('bulkPdfLinks');
          if (statusEl) {
            statusEl.style.display = '';
            statusEl.textContent = 'Preparing...';
          }
          if (linksEl) {
            linksEl.style.display = 'none';
            linksEl.innerHTML = '';
            linksEl.dataset.inited = '';
          }

          var ids = [];
          for (var i = 0; i < lastRows.length; i++) {
            var id = Number(lastRows[i] && lastRows[i].id ? lastRows[i].id : 0);
            if (!id) continue;
            ids.push(id);
          }
          if (ids.length === 0) return;

          if (statusEl) statusEl.textContent = 'Generating 1 combined PDF (' + ids.length + ' patients)...';
          try {
            var link = await window.sdoGenerateMedicalBulkPdf({ patientIds: ids, title: 'Medical Forms (Bulk)', behavior: 'link' });
            addBulkLink(link);

            var openedOk = false;
            try {
              if (generatePdf1._preOpened && generatePdf1._preOpened.document && link && link.blobUrl) {
                var w = generatePdf1._preOpened;
                w.document.open();
                w.document.write(
                  '<!doctype html><html><head><meta charset="utf-8"><title>' +
                  String(link.filename || link.title || 'PDF').replace(/</g, '&lt;') +
                  '</title><style>html,body{height:100%;margin:0}iframe{border:0;width:100%;height:100%}</style></head>' +
                  '<body><iframe src="' + link.blobUrl + '"></iframe></body></html>'
                );
                w.document.close();
                openedOk = true;
              }
            } catch (e) {
            }

            if (!openedOk) showBulkLink();
          } catch (e) {
            showBulkLink();
          }

          if (statusEl) statusEl.textContent = 'Done.';
        }

        async function generatePdf2(){
          if (!lastRows || lastRows.length === 0) return;
          if (!window.sdoGenerateMedicalDentalBulkPdf) return;

          var statusEl = document.getElementById('bulkPdfStatus');
          var linksEl = document.getElementById('bulkPdfLinks');
          if (statusEl) {
            statusEl.style.display = '';
            statusEl.textContent = 'Preparing...';
          }
          if (linksEl) {
            linksEl.style.display = 'none';
            linksEl.innerHTML = '';
            linksEl.dataset.inited = '';
          }

          var ids = [];
          for (var i = 0; i < lastRows.length; i++) {
            var id = Number(lastRows[i] && lastRows[i].id ? lastRows[i].id : 0);
            if (!id) continue;
            ids.push(id);
          }
          if (ids.length === 0) return;

          if (statusEl) statusEl.textContent = 'Generating 1 combined PDF (Medical then Dental) for ' + ids.length + ' patients...';
          try {
            var link = await window.sdoGenerateMedicalDentalBulkPdf({ patientIds: ids, title: 'Medical + Dental Forms (Bulk)', behavior: 'link' });
            addBulkLink(link);

            var openedOk = false;
            try {
              if (generatePdf2._preOpened && generatePdf2._preOpened.document && link && link.blobUrl) {
                var w = generatePdf2._preOpened;
                w.document.open();
                w.document.write(
                  '<!doctype html><html><head><meta charset="utf-8"><title>' +
                  String(link.filename || link.title || 'PDF').replace(/</g, '&lt;') +
                  '</title><style>html,body{height:100%;margin:0}iframe{border:0;width:100%;height:100%}</style></head>' +
                  '<body><iframe src="' + link.blobUrl + '"></iframe></body></html>'
                );
                w.document.close();
                openedOk = true;
              }
            } catch (e) {
            }

            if (!openedOk) showBulkLink();
          } catch (e) {
            showBulkLink();
          }

          if (statusEl) statusEl.textContent = 'Done.';
        }

        var btnPdf1 = document.getElementById('btnBulkPdf1');
        if (btnPdf1) {
          btnPdf1.addEventListener('click', function(){
            try {
              generatePdf1._preOpened = window.open('', '_blank');
            } catch (e) {
              generatePdf1._preOpened = null;
            }
            generatePdf1();
          });
        }

        var btnPdf2 = document.getElementById('btnBulkPdf2');
        if (btnPdf2) {
          btnPdf2.addEventListener('click', function(){
            try {
              generatePdf2._preOpened = window.open('', '_blank');
            } catch (e) {
              generatePdf2._preOpened = null;
            }
            generatePdf2();
          });
        }
      })();
    </script>
  </main>

  <!-- jsPDF must load before blank-pdf.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script>
    window.SDO_MDD_PDF_ASSETS = {
      headerUrl: <?= json_encode(asset('public/assets/header.jpg'), JSON_UNESCAPED_SLASHES) ?>,
      footerUrl: <?= json_encode(asset('public/assets/footer.jpg'), JSON_UNESCAPED_SLASHES) ?>,
      likertUrl: <?= json_encode(asset('public/assets/likertScale.png'), JSON_UNESCAPED_SLASHES) ?>,
      likert2Url: <?= json_encode(asset('public/assets/likertScale2.png'), JSON_UNESCAPED_SLASHES) ?>,
      ape1LogoUrl: <?= json_encode(asset('public/assets/ape1-logo.png'), JSON_UNESCAPED_SLASHES) ?>,
      ape2LogoUrl: <?= json_encode(asset('public/assets/ape2-logo.png'), JSON_UNESCAPED_SLASHES) ?>,
    };
    window.SDO_MDD_PDF_API = {
      pdfUrl: <?= json_encode(url('/api/pdf.php'), JSON_UNESCAPED_SLASHES) ?>,
    };
  </script>
  <script src="<?= e(asset('public/assets/js/medical-pdf.js')) ?>"></script>
  <script src="<?= e(asset('public/assets/js/dental-pdf.js')) ?>"></script>
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
</body>
</html>
