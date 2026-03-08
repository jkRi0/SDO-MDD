<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/auth.php';

require_login('dental', 'dental/index.php');
$cfg = base_config();

$flashSuccess = get_flash('success');
$flashError = get_flash('error');

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dental Dashboard - <?= e($cfg['app_name']) ?></title>
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
              <div class="brand h5 mb-0 text-white fw-bold lh-1">Dental Dashboard</div>
              <div class="small text-white-50 lh-1 mt-1">School Health Section</div>
            </div>
          </div>
        </div>
        <!-- Header Actions -->
        <div class="col-12 col-md-auto mt-2 mt-md-0">
          <div class="d-flex align-items-center gap-2 justify-content-md-end">
            <div class="d-none d-lg-block text-end me-2">
              <div class="small fw-bold text-white lh-1"><?= e(current_user()['fullname']) ?></div>
              <div class="text-white-50 lh-1 mt-1" style="font-size: 0.7rem;">Dental Staff</div>
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
            var med = badge(Number(r.medical_checked) === 1);
            var dentChecked = Number(r.dental_checked) === 1;
            var dent = badge(dentChecked);

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
                    '<a class="btn btn-sm btn-primary" href="assess.php?id=' + id + '">' + (dentChecked ? 'View' : 'Assess') + '</a>' +
                    '<button class="btn btn-sm btn-outline-secondary" type="button" onclick="window.sdoGenerateDentalPdf && window.sdoGenerateDentalPdf({ patientId: ' + id + ' });">PDF</button>' +
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
            var res = await fetch('<?= e(url('/api/patients.php')) ?>', { headers: { 'Accept': 'application/json' } });
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
