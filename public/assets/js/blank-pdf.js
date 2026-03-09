(function () {
  function loadImageAsDataURL(url) {
    return new Promise(function (resolve, reject) {
      var img = new window.Image();
      img.crossOrigin = 'anonymous';
      img.onload = function () {
        try {
          var canvas = document.createElement('canvas');
          canvas.width = img.naturalWidth;
          canvas.height = img.naturalHeight;
          var ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0);
          var dataURL = canvas.toDataURL('image/jpeg', 0.98);
          resolve(dataURL);
        } catch (err) {
          reject(err);
        }

      };
      img.onerror = function (e) {
        reject(e);
      };
      img.src = url;
    });
  }

  function getImageSize(dataUrl) {
    return new Promise(function (resolve, reject) {
      var img = new window.Image();
      img.onload = function () {
        resolve({ width: img.naturalWidth, height: img.naturalHeight });
      };
      img.onerror = function (e) {
        reject(e);
      };
      img.src = dataUrl;
    });
  }

  function safeText(v) {
    return String(v == null ? '' : v);
  }

  function toNum(v) {
    var n = Number(v);
    return Number.isFinite(n) ? n : null;
  }

  function apiUrlWithParams(baseUrl, params) {
    try {
      var u = new URL(baseUrl, window.location.origin);
      Object.keys(params || {}).forEach(function (k) {
        if (params[k] === undefined || params[k] === null) return;
        u.searchParams.set(k, String(params[k]));
      });
      return u.toString();
    } catch (e) {
      var qs = Object.keys(params || {})
        .filter(function (k) { return params[k] !== undefined && params[k] !== null; })
        .map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(String(params[k])); })
        .join('&');
      return baseUrl + (baseUrl.indexOf('?') >= 0 ? '&' : '?') + qs;
    }
  }

  async function fetchPdfData(type, patientId) {
    var api = window.SDO_MDD_PDF_API || {};
    var pdfUrl = safeText(api.pdfUrl);
    if (!pdfUrl) {
      throw new Error('PDF API not configured');
    }
    var fullUrl = apiUrlWithParams(pdfUrl, { type: type, patient_id: patientId });
    var res = await fetch(fullUrl, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) {
      throw new Error('HTTP ' + res.status);
    }
    var json = await res.json();
    if (!json || !json.ok) {
      throw new Error(json && json.error ? json.error : 'Failed');
    }
    return json;
  }

  function line(doc, x1, y1, x2, y2, w) {
    if (w != null) doc.setLineWidth(w);
    doc.line(x1, y1, x2, y2);
  }

  function labelLine(doc, x, y, label, value, lineWidth) {
    doc.setFont(undefined, 'bold');
    doc.text(label, x, y);
    var lw = doc.getTextWidth(label);
    var start = x + lw + 1;
    var end = x + (lineWidth || 70);
    doc.setFont(undefined, 'normal');
    if (value) {
      doc.text(String(value), start, y);
    }
    line(doc, start, y + 1.3, end, y + 1.3, 0.2);
  }

  function labelLineTo(doc, x, y, label, value, endX) {
    doc.setFont(undefined, 'bold');
    doc.text(label, x, y);
    var lw = doc.getTextWidth(label);
    var start = x + lw + 1;
    var end = Math.max(start + 10, endX);
    doc.setFont(undefined, 'normal');
    if (value) {
      doc.text(String(value), start, y);
    }
    line(doc, start, y + 1.3, end, y + 1.3, 0.2);
  }

  function checkbox(doc, x, y, checked) {
    doc.rect(x, y - 3.0, 3.0, 3.0);
    if (checked) {
      doc.setFontSize(10);
      doc.text('✓', x + 0.5, y - 0.4);
    }
  }

  function checkMark(doc, x, y, checked) {
    if (!checked) return;
    doc.setFontSize(9);
    doc.setFont(undefined, 'bold');
    doc.text('✓', x, y);
    doc.setFont(undefined, 'normal');
  }

  function normalizeYesNo(v) {
    if (v == null) return false;
    if (typeof v === 'boolean') return v;
    if (typeof v === 'number') return v === 1;
    var s = String(v).trim().toLowerCase();
    return s === '1' || s === 'y' || s === 'yes' || s === 'true';
  }

  function computeDmftFromChart(chart) {
    var present = 0;
    var d = 0;
    var m = 0;
    var f = 0;
    if (!chart || typeof chart !== 'object') {
      return { present: 0, d: 0, m: 0, f: 0, total: 0 };
    }
    Object.keys(chart).forEach(function (tooth) {
      var code = chart[tooth];
      var c = String(code || '').trim().toUpperCase();
      if (!c) return;
      if (c === 'V') c = '✓';
      present++;
      if (c === 'D') d++;
      if (c === 'M' || c === 'X') m++;
      if (['F', 'AM', 'CO', 'JC', 'IN'].indexOf(c) >= 0) f++;
    });
    return { present: present, d: d, m: m, f: f, total: d + m + f };
  }

  function normalizeLikert(v) {
    if (v == null) return null;
    var s = String(v).trim();
    if (!s) return null;
    if (/^[1-4]$/.test(s)) return Number(s);
    var t = s.toLowerCase();
    if (t.indexOf('not') >= 0) return 1;
    if (t.indexOf('mild') >= 0) return 2;
    if (t.indexOf('moderate') >= 0) return 3;
    if (t.indexOf('high') >= 0) return 4;
    if (t.indexOf('poor') >= 0) return 1;
    if (t.indexOf('fair') >= 0) return 2;
    if (t.indexOf('good') >= 0) return 3;
    if (t.indexOf('excellent') >= 0) return 4;
    return null;
  }

  function renderMedicalPage2MentalHealth(doc, shared, data, assets) {
    var pageWidth = shared.pageWidth;
    var pageHeight = shared.pageHeight;
    var left = 12;
    var right = pageWidth - 12;
    var top = shared.headerY + shared.headerH + 6;

    var p = data.patient || {};
    var a = data.assessment || {};

    var stress = normalizeLikert(a.stress_level);
    var coping = normalizeLikert(a.coping_level);

    doc.setTextColor(0);
    doc.setFontSize(9.5);
    doc.setFont(undefined, 'bold');
    doc.setTextColor(0, 51, 153);
    doc.text('PART V. MENTAL HEALTH:', pageWidth / 2, top, { align: 'center' });
    doc.setTextColor(0);
    doc.setFont(undefined, 'normal');
    doc.setFontSize(8.5);
    doc.text('Instruction: Please answer honestly the following mental health parameters using these Likert Scale.', left, top + 7);
    doc.text('', left, top + 12);

    var y = top + 20;
    doc.setFont(undefined, 'bold');
    doc.text('A. Stress Level at Work (Select One)', left, y);

    // Likert image
    var likertW = 72;
    var likertH = 0;
    if (assets.likertAspect) {
      likertH = likertW / assets.likertAspect;
    } else {
      likertH = 22;
    }
    var likertX = (pageWidth - likertW) / 2;
    var likertY = y + 6;
    if (assets.likertDataUrl) {
      doc.addImage(assets.likertDataUrl, 'PNG', likertX, likertY, likertW, likertH);
    }

    var optY = likertY + likertH + 6;
    doc.setFontSize(8.2);
    var stressOpts = [
      { n: 1, title: 'Not Stressed', desc: 'I feel no stress related to my work responsibilities or environment in DepED.' },
      { n: 2, title: 'Mildly Stressed', desc: 'I experience some stress, but it is manageable and does not interfere with my performance or well-being.' },
      { n: 3, title: 'Moderately Stressed', desc: 'I feel stress frequently, which sometimes affects my performance or well-being.' },
      { n: 4, title: 'Highly Stressed', desc: 'I feel significant stress that often impacts my performance, well-being, or ability to cope effectively.' },
    ];

    function drawOptionRow(baseY, opt, selected) {
      var boxX = left;
      // Template style: underscore blank before the option number
      doc.setFont(undefined, 'normal');
      doc.setFontSize(8.2);
      doc.text('_____', boxX, baseY);
      if (selected) {
        doc.setFont(undefined, 'bold');
        doc.text('X', boxX + 5.0, baseY);
      }

      var textX = boxX + 10;
      doc.setFont(undefined, 'bold');
      // Use a normal dash
      var lead = opt.n + '. ' + opt.title + ' - ';
      doc.text(lead, textX, baseY);
      var leadW = doc.getTextWidth(lead);
      doc.setFont(undefined, 'normal');
      var wrap = doc.splitTextToSize(opt.desc, right - (textX + leadW));
      doc.text(wrap, textX + leadW, baseY);
      return (Array.isArray(wrap) ? wrap.length : 1) * 3.9;
    }

    var dy = 0;
    for (var i = 0; i < stressOpts.length; i++) {
      dy += drawOptionRow(optY + dy, stressOpts[i], stress === stressOpts[i].n) + 1.2;
    }

    y = optY + dy + 5;
    doc.setFont(undefined, 'bold');
    doc.text('B. Coping Level at Work (Select One)', left, y);

    // Likert image again (same asset)
    var likertY2 = y + 6;
    var likert2H = likertH;
    if (assets.likert2Aspect) {
      likert2H = likertW / assets.likert2Aspect;
    }
    if (assets.likert2DataUrl) {
      doc.addImage(assets.likert2DataUrl, 'PNG', likertX, likertY2, likertW, likert2H);
    } else if (assets.likertDataUrl) {
      doc.addImage(assets.likertDataUrl, 'PNG', likertX, likertY2, likertW, likertH);
    }
    var optY2 = likertY2 + likert2H + 6;
    var copingOpts = [
      { n: 1, title: 'Poor Coping', desc: 'Struggles significantly to manage stress, often feeling overwhelmed.' },
      { n: 2, title: 'Fair Coping', desc: 'Occasionally manages stress but struggles with consistent strategies; sometimes feels overwhelmed.' },
      { n: 3, title: 'Good Coping', desc: 'Generally effective in managing stress; uses strategies and resources to maintain a balanced response to stressors.' },
      { n: 4, title: 'Excellent Coping', desc: 'Highly effective in handling stress; demonstrates resilience and consistently employs positive coping mechanisms.' },
    ];
    dy = 0;
    for (var j = 0; j < copingOpts.length; j++) {
      dy += drawOptionRow(optY2 + dy, copingOpts[j], coping === copingOpts[j].n) + 1.2;
    }

    var bottomStart = optY2 + dy + 8;
    doc.setDrawColor(0);
    doc.setLineWidth(0.2);
    doc.line(left, bottomStart, right, bottomStart);

    var assessedY = bottomStart + 9;
    doc.setFontSize(8.5);
    doc.setFont(undefined, 'bold');
    doc.text('Assessed by:', left, assessedY);
    doc.setFont(undefined, 'normal');
    doc.text('(Nurse/ Medical Officer)', left, assessedY + 4.5);

    var nameY = assessedY + 16;
    doc.setFont(undefined, 'bold');
    doc.text('Name:', left, nameY);
    doc.setFont(undefined, 'normal');
    var assessedName = safeText(a.assessed_by_name || '');
    if (!assessedName) assessedName = safeText(p.fullname || '');
    doc.text(assessedName, left + 14, nameY);
    doc.line(left + 14, nameY + 1.3, left + 85, nameY + 1.3);

    var licY = nameY + 9;
    doc.setFont(undefined, 'bold');
    doc.text('License No.', left, licY);
    doc.setFont(undefined, 'normal');
    doc.text(safeText(a.license_no || ''), left + 22, licY);
    doc.line(left + 22, licY + 1.3, left + 85, licY + 1.3);

    var encY = licY + 14;
    doc.setFont(undefined, 'bold');
    doc.text('Encoding Status (Put E/Initials if Encoded)', left, encY);
    doc.rect(left, encY + 6, 110, 20);

    // APE logo bottom-right
    if (assets.ape1DataUrl) {
      var logoW = 42;
      var logoH = assets.ape1Aspect ? (logoW / assets.ape1Aspect) : 22;
      var logoX = right - logoW;
      var logoY = pageHeight - shared.footerH - 8 - logoH;
      doc.addImage(assets.ape1DataUrl, 'PNG', logoX, logoY, logoW, logoH);
    }
  }

  function renderDentalForm(doc, shared, data, assets) {
    var pageWidth = shared.pageWidth;
    var pageHeight = shared.pageHeight;
    var left = 10;
    var right = pageWidth - 10;
    var top = shared.headerY + shared.headerH + 6;

    var p = data.patient || {};
    var a = data.assessment || {};

    doc.setTextColor(0);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(9);
    doc.text('SCHOOL GOVERNANCE AND OPERATIONS DIVISION', pageWidth / 2, top, { align: 'center' });
    doc.text('SCHOOL HEALTH AND NUTRITION UNIT', pageWidth / 2, top + 4, { align: 'center' });
    doc.setFontSize(11);
    doc.text('DENTAL FORM', pageWidth / 2, top + 12, { align: 'center' });

    var y = top + 20;
    doc.setFontSize(8.5);
    doc.setFont(undefined, 'bold');
    doc.text('School:', left, y);
    doc.setFont(undefined, 'normal');
    doc.text(safeText(p.school), left + 12, y);
    doc.line(left + 12, y + 1.2, left + 85, y + 1.2);
    doc.setFont(undefined, 'bold');
    doc.text('Date:', left + 130, y);
    doc.setFont(undefined, 'normal');
    var d = safeText(a.exam_date || p.entry_date || '');
    doc.text(d, left + 140, y);
    doc.line(left + 140, y + 1.2, right, y + 1.2);

    y += 7;
    doc.setFont(undefined, 'bold');
    doc.text('Name:', left, y);
    doc.setFont(undefined, 'normal');
    doc.text(safeText(p.fullname), left + 12, y);
    doc.line(left + 12, y + 1.2, left + 120, y + 1.2);
    doc.setFont(undefined, 'bold');
    doc.text('Age:', left + 125, y);
    doc.setFont(undefined, 'normal');
    doc.text(safeText(p.age), left + 135, y);
    doc.line(left + 135, y + 1.2, left + 150, y + 1.2);
    doc.setFont(undefined, 'bold');
    doc.text('Gender:', left + 154, y);
    doc.setFont(undefined, 'normal');
    doc.text(safeText(p.sex), left + 170, y);
    doc.line(left + 170, y + 1.2, right, y + 1.2);

    y += 7;
    doc.setFont(undefined, 'bold');
    doc.text('Date of Birth:', left, y);
    doc.setFont(undefined, 'normal');
    doc.text(safeText(p.date_of_birth), left + 22, y);
    doc.line(left + 22, y + 1.2, left + 70, y + 1.2);
    doc.setFont(undefined, 'bold');
    doc.text('Civil Status:', left + 74, y);
    doc.setFont(undefined, 'normal');
    doc.text(safeText(p.civil_status), left + 94, y);
    doc.line(left + 94, y + 1.2, left + 125, y + 1.2);
    doc.setFont(undefined, 'bold');
    doc.text('Designation:', left + 128, y);
    doc.setFont(undefined, 'normal');
    doc.text(safeText(p.designation), left + 150, y);
    doc.line(left + 150, y + 1.2, right, y + 1.2);

    y += 7;
    doc.setFont(undefined, 'bold');
    doc.text('Region:', left, y);
    doc.setFont(undefined, 'normal');
    doc.text(safeText(p.region), left + 13, y);
    doc.line(left + 13, y + 1.2, left + 70, y + 1.2);
    doc.setFont(undefined, 'bold');
    doc.text('Division:', left + 74, y);
    doc.setFont(undefined, 'normal');
    doc.text(safeText(p.division), left + 92, y);
    doc.line(left + 92, y + 1.2, left + 135, y + 1.2);
    doc.setFont(undefined, 'bold');
    doc.text('District:', left + 138, y);
    doc.setFont(undefined, 'normal');
    doc.text(safeText(p.district), left + 156, y);
    doc.line(left + 156, y + 1.2, right, y + 1.2);

    y += 6;
    // Tables row
    var tableTop = y;
    var mhX = left;
    var mhW = 75;
    var ohX = mhX + mhW + 6;
    var ohW = right - ohX;
    var rowH = 5;
    doc.setFontSize(8);
    doc.setFont(undefined, 'bold');
    doc.text('MEDICAL HISTORY', mhX + mhW / 2, tableTop, { align: 'center' });
    doc.text('ORAL HEALTH CONDITION', ohX + ohW / 2, tableTop, { align: 'center' });

    // Medical history table
    var mhY = tableTop + 2.5;
    var mhRows = [
      { label: 'Allergy', key: 'mh_allergy' },
      { label: 'Asthma', key: 'mh_asthma' },
      { label: 'Anemia', key: 'mh_anemia' },
      { label: 'Bleeding Problem', key: 'mh_bleeding_problem' },
      { label: 'Heart Ailment', key: 'mh_heart_ailment' },
      { label: 'Diabetes', key: 'mh_diabetes' },
      { label: 'Epilepsy', key: 'mh_epilepsy' },
      { label: 'Kidney Disease', key: 'mh_kidney_disease' },
      { label: 'Convulsion', key: 'mh_convulsion' },
      { label: 'Fainting', key: 'mh_fainting' },
    ];
    doc.rect(mhX, mhY, mhW, rowH * (mhRows.length + 1));
    doc.line(mhX, mhY + rowH, mhX + mhW, mhY + rowH);
    doc.line(mhX + mhW - 22, mhY, mhX + mhW - 22, mhY + rowH * (mhRows.length + 1));
    doc.line(mhX + mhW - 11, mhY, mhX + mhW - 11, mhY + rowH * (mhRows.length + 1));
    doc.text('YES', mhX + mhW - 16.5, mhY + 3.6, { align: 'center' });
    doc.text('NO', mhX + mhW - 5.5, mhY + 3.6, { align: 'center' });

    doc.setFont(undefined, 'normal');
    for (var i = 0; i < mhRows.length; i++) {
      var ry = mhY + rowH * (i + 1);
      doc.line(mhX, ry, mhX + mhW, ry);
      doc.text(mhRows[i].label, mhX + 2, ry + 3.6);
      var yes = normalizeYesNo(a[mhRows[i].key]);
      checkMark(doc, mhX + mhW - 16.5, ry + 3.6, yes);
      checkMark(doc, mhX + mhW - 5.5, ry + 3.6, !yes);
    }
    var mhBottomY = mhY + rowH * (mhRows.length + 1) + 6;
    doc.setFont(undefined, 'bold');
    doc.text('OTHERS (Specify):', mhX, mhBottomY);
    doc.setFont(undefined, 'normal');
    doc.line(mhX + 26, mhBottomY + 1.2, mhX + mhW, mhBottomY + 1.2);
    doc.text(safeText(a.mh_others), mhX + 26, mhBottomY);

    // Oral health condition table
    var ohY = tableTop + 2.5;
    var ohRowH = 5;
    doc.rect(ohX, ohY, ohW, ohRowH * 7);
    doc.line(ohX + 45, ohY, ohX + 45, ohY + ohRowH * 7);
    doc.line(ohX + 70, ohY, ohX + 70, ohY + ohRowH * 7);
    doc.line(ohX + 95, ohY, ohX + 95, ohY + ohRowH * 7);
    for (var r = 1; r < 7; r++) {
      doc.line(ohX, ohY + ohRowH * r, ohX + ohW, ohY + ohRowH * r);
    }
    doc.setFont(undefined, 'bold');
    doc.setFontSize(7.8);
    doc.text('Date of Examination', ohX + 2, ohY + 3.6);
    doc.setFont(undefined, 'normal');
    doc.text(safeText(a.exam_date), ohX + 47, ohY + 3.6);
    doc.setFont(undefined, 'bold');
    doc.text('Age last birthday', ohX + 2, ohY + 8.6);
    doc.setFont(undefined, 'normal');
    doc.text(safeText(a.age_last_birthday), ohX + 47, ohY + 8.6);

    function ynRow(rowIndex, label, key) {
      var base = ohY + ohRowH * rowIndex;
      doc.setFont(undefined, 'bold');
      doc.text(label, ohX + 2, base + 3.6);
      doc.setFont(undefined, 'normal');
      var yes = normalizeYesNo(a[key]);
      checkMark(doc, ohX + 72, base + 3.6, yes);
      checkMark(doc, ohX + 97, base + 3.6, yes);
    }

    ynRow(2, 'Presence of Debris', 'debris');
    ynRow(3, 'Inflammation of Gingiva', 'gingiva_inflammation');
    ynRow(4, 'Presence of Calculus', 'calculus');
    ynRow(5, 'Under Orthodontic Treatment', 'orthodontic_treatment');

    var ocY = ohY + ohRowH * 6 + 7;
    doc.setFontSize(8);
    doc.setFont(undefined, 'bold');
    doc.text('OCCLUSION:', ohX, ocY);
    var occ = safeText(a.occlusion);
    function occBox(x, label, v) {
      doc.rect(x, ocY - 3.0, 3, 3);
      checkMark(doc, x + 0.7, ocY - 0.2, occ === v);
      doc.setFont(undefined, 'normal');
      doc.text(label, x + 5, ocY);
    }
    occBox(ohX + 30, 'Class 1', 'Class 1');
    occBox(ohX + 55, 'Class II', 'Class 2');
    occBox(ohX + 82, 'Class III', 'Class 3');

    var tmjY = ocY + 6;
    doc.setFont(undefined, 'bold');
    doc.text('T.M.J. EXAM:', ohX, tmjY);
    var tmj = safeText(a.tmj_exam);
    function tmjBox(x, label, v) {
      doc.rect(x, tmjY - 3.0, 3, 3);
      checkMark(doc, x + 0.7, tmjY - 0.2, tmj === v);
      doc.setFont(undefined, 'normal');
      doc.text(label, x + 5, tmjY);
    }
    tmjBox(ohX + 30, 'Pain', 'Pain');
    tmjBox(ohX + 50, 'Popping', 'Popping');
    tmjBox(ohX + 74, 'Deviation', 'Deviation');
    tmjBox(ohX + 102, 'Tooth Wear', 'Tooth wear');

    var specY = tmjY + 6;
    doc.setFont(undefined, 'bold');
    doc.text('Dentofacial Anomaly, Neoplasm, Others, specify:', ohX, specY);
    doc.line(ohX, specY + 1.2, right, specY + 1.2);

    var chartY = specY + 10;
    doc.setFont(undefined, 'bold');
    doc.setFontSize(8.5);
    doc.text('DENTAL HEALTH STATUS:', left, chartY);
    doc.setFontSize(8);

    var chart = {};
    try {
      var raw = a.tooth_chart_json;
      chart = raw && typeof raw === 'string' ? JSON.parse(raw) : (raw || {});
    } catch (e) {
      chart = {};
    }

    var boxY = chartY + 3;
    var cellW = 11.5;
    var cellH = 10;
    var startX = left;
    var teethTop = ['18','17','16','15','14','13','12','11','21','22','23','24','25','26','27','28'];
    var teethBottom = ['48','47','46','45','44','43','42','41','31','32','33','34','35','36','37','38'];
    doc.rect(startX, boxY, cellW * 16, cellH * 2);
    for (var c = 1; c < 16; c++) {
      doc.line(startX + cellW * c, boxY, startX + cellW * c, boxY + cellH * 2);
    }
    doc.line(startX, boxY + cellH, startX + cellW * 16, boxY + cellH);
    doc.setFontSize(9);
    doc.setFont(undefined, 'bold');
    for (var t1 = 0; t1 < 16; t1++) {
      var tx = startX + cellW * t1 + cellW / 2;
      doc.text(teethTop[t1], tx, boxY + 8.2, { align: 'center' });
      doc.text(teethBottom[t1], tx, boxY + cellH + 8.2, { align: 'center' });
    }
    doc.setFontSize(8);
    doc.setFont(undefined, 'normal');
    for (var t2 = 0; t2 < 16; t2++) {
      var topTooth = teethTop[t2];
      var botTooth = teethBottom[t2];
      var codeTop = safeText(chart[topTooth] || '');
      var codeBot = safeText(chart[botTooth] || '');
      if (codeTop === 'V') codeTop = '✓';
      if (codeBot === 'V') codeBot = '✓';
      if (codeTop) doc.text(codeTop, startX + cellW * t2 + 1.2, boxY + 3.2);
      if (codeBot) doc.text(codeBot, startX + cellW * t2 + 1.2, boxY + cellH + 3.2);
    }

    var blockY = boxY + cellH * 2 + 10;
    doc.setFont(undefined, 'bold');
    doc.setFontSize(8.5);
    doc.text('INITIAL SOFT TISSUE EXAM', left, blockY);
    doc.setFontSize(8);
    var soft = safeText(a.soft_tissue_exam);
    var softOpts = ['Lips', 'Floor of mouth', 'Palate', 'Tongue', 'Neck & nodes'];
    var sx = left;
    for (var so = 0; so < softOpts.length; so++) {
      doc.rect(sx, blockY + 3, 3, 3);
      checkMark(doc, sx + 0.7, blockY + 5.8, soft === softOpts[so]);
      doc.setFont(undefined, 'normal');
      doc.text(softOpts[so], sx + 5, blockY + 6);
      sx += 32;
    }

    var perioY = blockY + 14;
    doc.setFont(undefined, 'bold');
    doc.text('INITIAL PERIODONTAL EXAM', left + 50, perioY);

    var dmftBoxX = right - 55;
    var dmftBoxY = blockY;
    doc.setFont(undefined, 'bold');
    doc.text('DMFT SCORES', dmftBoxX + 27.5, dmftBoxY, { align: 'center' });
    doc.rect(dmftBoxX, dmftBoxY + 2, 55, 32);
    for (var dl = 1; dl < 6; dl++) {
      doc.line(dmftBoxX, dmftBoxY + 2 + dl * 5.3, dmftBoxX + 55, dmftBoxY + 2 + dl * 5.3);
    }
    doc.line(dmftBoxX + 35, dmftBoxY + 2, dmftBoxX + 35, dmftBoxY + 34);
    doc.setFontSize(7.3);
    doc.text('Tooth Count', dmftBoxX + 2, dmftBoxY + 6);
    doc.text('PERMANENT', dmftBoxX + 36.5, dmftBoxY + 6);
    doc.text('Number of Teeth Present', dmftBoxX + 2, dmftBoxY + 11.3);
    doc.text('Number of Caries Free Teeth', dmftBoxX + 2, dmftBoxY + 16.6);
    doc.text('Number of Decayed Teeth', dmftBoxX + 2, dmftBoxY + 21.9);
    doc.text('Number of Missing Teeth', dmftBoxX + 2, dmftBoxY + 27.2);
    doc.text('Number of Filled Teeth', dmftBoxX + 2, dmftBoxY + 32.5);

    var dmft = computeDmftFromChart(chart);
    var present = a.teeth_present_count != null ? a.teeth_present_count : dmft.present;
    var decayed = a.d_count != null ? a.d_count : dmft.d;
    var missing = a.m_count != null ? a.m_count : dmft.m;
    var filled = a.f_count != null ? a.f_count : dmft.f;
    var total = a.dmft_total != null ? a.dmft_total : dmft.total;
    var cariesFree = Math.max(0, Number(present || 0) - Number(decayed || 0) - Number(missing || 0) - Number(filled || 0));
    doc.setFont(undefined, 'normal');
    doc.setFontSize(8);
    doc.text(String(present || ''), dmftBoxX + 38, dmftBoxY + 11.3);
    doc.text(String(cariesFree || ''), dmftBoxX + 38, dmftBoxY + 16.6);
    doc.text(String(decayed || ''), dmftBoxX + 38, dmftBoxY + 21.9);
    doc.text(String(missing || ''), dmftBoxX + 38, dmftBoxY + 27.2);
    doc.text(String(filled || ''), dmftBoxX + 38, dmftBoxY + 32.5);

    var recY = dmftBoxY + 44;
    doc.setFont(undefined, 'bold');
    doc.setFontSize(8.5);
    doc.text('DENTAL/ORAL EXAMINATION REVEALED THE FOLLOWING CONDITIONS AND RECOMMENDATIONS', pageWidth / 2, recY, { align: 'center' });

    var recs = [];
    try {
      var rr = a.recommendations_json;
      recs = rr && typeof rr === 'string' ? JSON.parse(rr) : (Array.isArray(rr) ? rr : []);
    } catch (e) {
      recs = [];
    }
    function hasRec(label) {
      return Array.isArray(recs) && recs.indexOf(label) >= 0;
    }

    doc.setFontSize(8);
    var recLeftY = recY + 8;
    var colA = left;
    var colB = left + 70;
    var colC = left + 140;
    var recItemsA = ['Caries Free', 'Poor Oral Hygiene (Materia Alba, Calculus, Stain)', 'Gingival Inflammation', 'Needs Oral Prophylaxis'];
    var recItemsB = ['No Dental Treatment Needed at Present', 'For Endodontic Treatment', 'Indicated for Restoration/Filling', 'Others (Specify)'];
    var recItemsC = ['Indicated for Extraction', 'Needs Prosthesis (Denture)', 'For Orthodontic Consultation'];
    function drawRecCol(items, x, y0) {
      var yy = y0;
      for (var k = 0; k < items.length; k++) {
        var label = items[k];
        doc.text('____', x, yy);
        if (label !== 'Others (Specify)') {
          if (hasRec(label)) {
            doc.setFont(undefined, 'bold');
            doc.text('X', x + 4.5, yy);
            doc.setFont(undefined, 'normal');
          }
          doc.setFont(undefined, 'bold');
          doc.text(label, x + 10, yy);
          doc.setFont(undefined, 'normal');
        } else {
          var otherText = safeText(a.recommendation_others);
          doc.setFont(undefined, 'bold');
          doc.text('Others (Specify):', x + 10, yy);
          doc.setFont(undefined, 'normal');
          doc.line(x + 40, yy + 1.1, x + 65, yy + 1.1);
          doc.text(otherText, x + 40, yy);
        }
        yy += 5.2;
      }
    }
    drawRecCol(recItemsA, colA, recLeftY);
    drawRecCol(recItemsB, colB, recLeftY);
    drawRecCol(recItemsC, colC, recLeftY);

    var sigY = recLeftY + 26;
    doc.setFont(undefined, 'normal');
    doc.line(left + 115, sigY, right - 60, sigY);
    doc.setFontSize(7.8);
    doc.text('Dentist signature over printed name', left + 125, sigY + 4);
    doc.line(left + 115, sigY + 10, right - 60, sigY + 10);
    doc.text('License #:', left + 117, sigY + 14);

    var consentY = sigY + 18;
    doc.setFont(undefined, 'bold');
    doc.setFontSize(8.5);
    doc.text('INFORMED CONSENT:', left, consentY);
    doc.setFont(undefined, 'normal');
    doc.setFontSize(7.6);
    doc.text('I, ________________________________, DO HEREBY CONSENT TO THE PERFORMANCE UPON MYSELF OF', left, consentY + 6);
    doc.text('DENTAL PROCEDURES, WHETHER RESTORATIONS/ EXTRACTION OF TEETH OR ANY OTHER TREATMENT DEEMED NECESSARY TO RESTORE MY', left, consentY + 11);
    doc.text('DENTAL HEALTH IN FAVOR OF THE ATTENDING DENTIST.', left, consentY + 16);

    var psY = pageHeight - shared.footerH - 12;
    doc.line(pageWidth / 2 - 50, psY, pageWidth / 2 + 50, psY);
    doc.setFontSize(8);
    doc.text("Patient's Signature over Printed Name", pageWidth / 2, psY + 4, { align: 'center' });

    if (assets.ape2DataUrl) {
      var logoW = 34;
      var logoH = assets.ape2Aspect ? (logoW / assets.ape2Aspect) : 18;
      var logoX = right - logoW;
      var logoY = pageHeight - shared.footerH - 8 - logoH;
      doc.addImage(assets.ape2DataUrl, 'PNG', logoX, logoY, logoW, logoH);
    }
  }

  function renderMedicalForm(doc, shared, data) {
    var pageWidth = shared.pageWidth;
    var startY = shared.headerY + shared.headerH + 5;
    var left = 12;
    var right = pageWidth - 12;

    var p = data.patient || {};
    var a = data.assessment || {};

    var pmhChecked = [];
    var pmhCancerType = '';
    var pmhOperation = '';
    var pmhConfinement = '';
    var pmhOthers = '';
    try {
      var raw = a.past_medical_history;
      if (raw) {
        var decoded = typeof raw === 'string' ? JSON.parse(raw) : raw;
        if (decoded && typeof decoded === 'object') {
          if (Array.isArray(decoded.checked)) pmhChecked = decoded.checked;
          if (decoded.cancer_type) pmhCancerType = decoded.cancer_type;
          if (decoded.operation) pmhOperation = decoded.operation;
          if (decoded.confinement) pmhConfinement = decoded.confinement;
          if (decoded.others) pmhOthers = decoded.others;
        }
      }
    } catch (e) {
    }

    doc.setTextColor(0);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(8.5);
    doc.text('SCHOOLS GOVERNANCE AND OPERATIONS DIVISION', pageWidth / 2, startY, { align: 'center' });
    doc.text('SCHOOL HEALTH AND NUTRITION SECTION', pageWidth / 2, startY + 4, { align: 'center' });
    doc.text('ANNUAL MEDICAL AND DENTAL EXAMINATION', pageWidth / 2, startY + 8, { align: 'center' });
    doc.setFontSize(9.5);
    doc.text('MEDICAL FORM', pageWidth / 2, startY + 14, { align: 'center' });

    var y = startY + 21;
    doc.setFontSize(9);
    doc.setTextColor(0, 51, 153);
    doc.setFont(undefined, 'bold');
    doc.text('PART I. EMPLOYEE DETAILS:', pageWidth / 2, y, { align: 'center' });
    doc.setTextColor(0);

    y += 7;
    doc.setFontSize(8.5);
    labelLine(doc, left, y, 'SCHOOL:', p.school || '', 82);
    labelLine(doc, left + 98, y, 'DATE:', (p.entry_date || ''), right - (left + 98));

    y += 6;
    labelLine(doc, left, y, 'NAME:', p.fullname || '', 82);
    labelLine(doc, left + 98, y, 'AGE:', p.age || '', 25);
    labelLine(doc, left + 138, y, 'SEX:', p.sex || '', right - (left + 138));

    y += 6;
    labelLine(doc, left, y, 'ADDRESS:', p.address || '', right - left);

    y += 6;
    labelLine(doc, left, y, 'DATE OF BIRTH:', p.date_of_birth || '', 74);
    labelLine(doc, left + 95, y, 'CIVIL STATUS:', p.civil_status || '', right - (left + 95));

    y += 6;
    labelLine(doc, left, y, 'DESIGNATION:', p.designation || '', right - left);

    y += 6;
    doc.setFont(undefined, 'bold');
    doc.text('REGION:', left, y);
    doc.setFont(undefined, 'normal');
    doc.text(String(p.region || ''), left + 15, y);
    line(doc, left + 15, y + 1.3, left + 72, y + 1.3, 0.2);

    doc.setFont(undefined, 'bold');
    doc.text('DIVISION:', left + 75, y);
    doc.setFont(undefined, 'normal');
    doc.text(String(p.division || ''), left + 95, y);
    line(doc, left + 95, y + 1.3, left + 140, y + 1.3, 0.2);

    doc.setFont(undefined, 'bold');
    doc.text('DISTRICT:', left + 140, y);
    doc.setFont(undefined, 'normal');
    doc.text(String(p.district || ''), left + 162, y);
    line(doc, left + 162, y + 1.3, right, y + 1.3, 0.2);

    y += 6;
    labelLine(doc, left, y, 'HMO Provider:', p.hmo_provider || '', right - left);

    y += 10;
    doc.setTextColor(0, 51, 153);
    doc.setFont(undefined, 'bold');
    doc.text('PART II. MEDICAL DETAILS:', pageWidth / 2, y, { align: 'center' });
    doc.setTextColor(0);

    y += 6;
    doc.setFontSize(8.5);
    doc.setFont(undefined, 'bold');
    doc.text('Vital Signs:', left, y);
    doc.setFont(undefined, 'normal');

    y += 5.5;
    // Line 1: Height / Weight / Temperature / Pulse Rate / RR
    var rrBoxW = 18;
    var rrX = right - rrBoxW;
    labelLineTo(doc, left, y, 'Height:', a.height_cm ? (a.height_cm + '') : '', left + 38);
    labelLineTo(doc, left + 44, y, 'Weight:', a.weight_kg ? (a.weight_kg + '') : '', left + 82);
    labelLineTo(doc, left + 88, y, 'Temperature:', a.temperature_c ? (a.temperature_c + '') : '', left + 132);
    labelLineTo(doc, left + 138, y, 'Pulse Rate:', a.pulse_rate ? (a.pulse_rate + '') : '', rrX - 6);
    labelLineTo(doc, rrX, y, 'RR:', a.rr ? (a.rr + '') : '', right);

    y += 5.5;
    // Line 2: O2 Sat / Blood Pressure
    labelLine(doc, left, y, 'O2 Sat:', a.o2_sat ? (a.o2_sat + '') : '', 40);
    var bp = '';
    if (a.bp_systolic || a.bp_diastolic) {
      bp = String(a.bp_systolic || '') + (a.bp_diastolic ? '/' + a.bp_diastolic : '');
    }
    labelLine(doc, left + 48, y, 'Blood Pressure:', bp, right - (left + 48));

    y += 10;
    doc.setTextColor(0, 51, 153);
    doc.setFont(undefined, 'bold');
    doc.text('PART III. PAST MEDICAL HISTORY:', pageWidth / 2, y, { align: 'center' });
    doc.setTextColor(0);

    y += 6;
    doc.setFontSize(8.5);
    function hasPmh(k) {
      return Array.isArray(pmhChecked) && pmhChecked.indexOf(k) >= 0;
    }

    var col1X = left;
    var col2X = left + 52;
    var col3X = left + 104;
    var rowH = 4.8;

    checkbox(doc, col1X, y + 3, hasPmh('DM'));
    doc.setFont(undefined, 'bold');
    doc.text('DM', col1X + 6, y + 3);
    checkbox(doc, col2X, y + 3, hasPmh('HPN'));
    doc.text('HPN', col2X + 6, y + 3);
    checkbox(doc, col3X, y + 3, hasPmh('Asthma'));
    doc.text('Asthma', col3X + 6, y + 3);
    doc.setFont(undefined, 'normal');

    y += rowH;
    checkbox(doc, col1X, y + 3, hasPmh('Heart Dse.'));
    doc.setFont(undefined, 'bold');
    doc.text('Heart Dse.', col1X + 6, y + 3);
    checkbox(doc, col2X, y + 3, hasPmh('Lung Dse.'));
    doc.text('Lung Dse.', col2X + 6, y + 3);
    checkbox(doc, col3X, y + 3, hasPmh('Allergies'));
    doc.text('Allergies', col3X + 6, y + 3);
    doc.setFont(undefined, 'normal');

    y += rowH;
    checkbox(doc, col1X, y + 3, hasPmh('Kidney Dse.'));
    doc.setFont(undefined, 'bold');
    doc.text('Kidney Dse.', col1X + 6, y + 3);
    checkbox(doc, col2X, y + 3, hasPmh('PTB'));
    doc.text('PTB', col2X + 6, y + 3);
    doc.setFont(undefined, 'bold');
    doc.text('Cancer/Type:', col3X, y + 3);
    doc.setFont(undefined, 'normal');
    doc.text(pmhCancerType || '', col3X + 25, y + 3);
    line(doc, col3X + 25, y + 4.3, right, y + 4.3, 0.2);

    y += rowH;
    checkbox(doc, col1X, y + 3, hasPmh('Brain Dse.'));
    doc.setFont(undefined, 'bold');
    doc.text('Brain Dse.', col1X + 6, y + 3);
    doc.setFont(undefined, 'bold');
    doc.text('Operations:', col2X, y + 3);
    doc.setFont(undefined, 'normal');
    doc.text(pmhOperation || '', col2X + 20, y + 3);
    line(doc, col2X + 20, y + 4.3, col3X - 5, y + 4.3, 0.2);
    doc.setFont(undefined, 'bold');
    doc.text('Confinement:', col3X, y + 3);
    doc.setFont(undefined, 'normal');
    doc.text(pmhConfinement || '', col3X + 27, y + 3);
    line(doc, col3X + 27, y + 4.3, right, y + 4.3, 0.2);

    y += 7;
    doc.setFont(undefined, 'bold');
    doc.text('Others:', left, y);
    doc.setFont(undefined, 'normal');
    var othersText = (pmhOthers || '').toString();
    if (othersText) {
      var lines = doc.splitTextToSize(othersText, right - left - 20);
      doc.text(lines, left + 15, y);
    }
    for (var i = 0; i < 4; i++) {
      line(doc, left + 15, y + 1.3 + i * 5.5, right, y + 1.3 + i * 5.5, 0.2);
    }

    y += 26;
    doc.setTextColor(0, 51, 153);
    doc.setFont(undefined, 'bold');
    doc.text('PART IV. OB HISTORY:', pageWidth / 2, y, { align: 'center' });
    doc.setTextColor(0);
    y += 6;
    doc.setFontSize(8.5);
    labelLine(doc, left, y, 'LMP:', a.ob_lmp || '', 70);
    labelLine(doc, left + 90, y, 'OB SCORING (GTPAL):', a.ob_gtpal || '', right - (left + 90));

    y += 10;
    labelLine(doc, left, y, 'CHEST XRAY:', a.ob_chest_xray || '', right - left);
    y += 6;
    labelLine(doc, left, y, 'ECG:', a.ob_ecg || '', right - left);

    y += 10;
    doc.setTextColor(0, 51, 153);
    doc.setFont(undefined, 'bold');
    doc.text('PART IV.  PHYSICAL & LABORATORY ASSESSMENT/FINDINGS:', pageWidth / 2, y, { align: 'center' });
    doc.setTextColor(0);
    y += 5;
    doc.setFontSize(8.5);
    var pf = (a.physical_findings || '').toString();
    if (pf) {
      var pfLines = doc.splitTextToSize(pf, right - left);
      doc.text(pfLines, left, y + 5);
    }
    for (var j = 0; j < 5; j++) {
      line(doc, left, y + 6 + j * 6.5, right, y + 6 + j * 6.5, 0.2);
    }
  }

  function drawHeaderFooter(doc, shared) {
    var pageWidth = shared.pageWidth;
    var headerX = (pageWidth - shared.headerW) / 2;
    doc.addImage(shared.headerDataUrl, 'JPEG', headerX, shared.headerY, shared.headerW, shared.headerH);

    var footerX = (pageWidth - shared.footerW) / 2;
    doc.addImage(shared.footerDataUrl, 'JPEG', footerX, shared.footerY, shared.footerW, shared.footerH);

    var lineMargin = 10;
    var headerLineY = shared.headerY + shared.headerH + 1.5;
    var footerLineY = shared.footerY - 1.5;
    doc.setDrawColor(0);
    doc.setLineWidth(0.3);
    doc.line(lineMargin, headerLineY, pageWidth - lineMargin, headerLineY);
    doc.line(lineMargin, footerLineY, pageWidth - lineMargin, footerLineY);
  }

  function openPdfInNewTabOrDownload(blob, title) {
    var filename = (title ? String(title) : 'form') + '.pdf';
    var blobUrl = URL.createObjectURL(blob);

    try {
      var w = window.open('', '_blank');
      if (w && w.document) {
        w.document.open();
        w.document.write(
          '<!doctype html><html><head><meta charset="utf-8"><title>' +
          filename.replace(/</g, '&lt;') +
          '</title><style>html,body{height:100%;margin:0}iframe{border:0;width:100%;height:100%}</style></head>' +
          '<body><iframe src="' + blobUrl + '"></iframe></body></html>'
        );
        w.document.close();
        return;
      }
    } catch (e) {
    }

    try {
      var a = document.createElement('a');
      a.href = blobUrl;
      a.download = filename;
      a.style.display = 'none';
      document.body.appendChild(a);
      a.click();
      setTimeout(function () {
        try { document.body.removeChild(a); } catch (e) {}
      }, 0);
    } catch (e) {
      window.location.href = blobUrl;
    }
  }

  window.sdoGenerateBlankPdf = async function sdoGenerateBlankPdf(options) {
    options = options || {};
    var type = safeText(options.type || 'medical');
    var patientId = options.patientId;
    var title = safeText(options.title || (type === 'medical' ? 'Medical Form' : 'Dental Form'));
    var behavior = safeText(options.behavior || 'open');

    var assets = window.SDO_MDD_PDF_ASSETS || {};
    var headerUrl = safeText(assets.headerUrl);
    var footerUrl = safeText(assets.footerUrl);
    var likertUrl = safeText(assets.likertUrl);
    var likert2Url = safeText(assets.likert2Url);
    var ape1LogoUrl = safeText(assets.ape1LogoUrl);
    var ape2LogoUrl = safeText(assets.ape2LogoUrl);

    if (!headerUrl || !footerUrl) {
      alert('PDF header/footer assets are not configured.');
      return;
    }

    var headerDataUrl, footerDataUrl, likertDataUrl, likert2DataUrl, ape1DataUrl, ape2DataUrl;
    try {
      var tasks = [loadImageAsDataURL(headerUrl), loadImageAsDataURL(footerUrl)];
      var needPage2 = false;
      var t = safeText(options && options.type ? options.type : '');
      if (t === 'medical') {
        needPage2 = true;
      }
      if (needPage2 && likertUrl) tasks.push(loadImageAsDataURL(likertUrl));
      if (needPage2 && likert2Url) tasks.push(loadImageAsDataURL(likert2Url));
      if (needPage2 && ape1LogoUrl) tasks.push(loadImageAsDataURL(ape1LogoUrl));
      if (t === 'dental' && ape2LogoUrl) tasks.push(loadImageAsDataURL(ape2LogoUrl));

      var res = await Promise.all(tasks);
      headerDataUrl = res[0];
      footerDataUrl = res[1];
      var idx = 2;
      if (needPage2 && likertUrl) {
        likertDataUrl = res[idx++];
      }
      if (needPage2 && likert2Url) {
        likert2DataUrl = res[idx++];
      }
      if (needPage2 && ape1LogoUrl) {
        ape1DataUrl = res[idx++];
      }
      if (t === 'dental' && ape2LogoUrl) {
        ape2DataUrl = res[idx++];
      }
    } catch (e) {
      alert('Failed to load header/footer images.');
      return;
    }

    var doc = new jsPDF({ unit: 'mm', format: 'a4', orientation: 'portrait' });
    var pageWidth = doc.internal.pageSize.getWidth();
    var pageHeight = doc.internal.pageSize.getHeight();

    var headerSize = await getImageSize(headerDataUrl);
    var headerAspect = (headerSize.width || 1) / (headerSize.height || 1);
    var headerW = Math.min(55, pageWidth - 20);
    var headerH = headerW / headerAspect;
    var headerY = 5;

    var footerSize = await getImageSize(footerDataUrl);
    var footerAspect = (footerSize.width || 1) / (footerSize.height || 1);
    var footerW = pageWidth - 20;
    var footerH = footerW / footerAspect;
    var footerY = pageHeight - footerH - 3;

    var shared = {
      pageWidth: pageWidth,
      pageHeight: pageHeight,
      headerDataUrl: headerDataUrl,
      footerDataUrl: footerDataUrl,
      headerW: headerW,
      headerH: headerH,
      headerY: headerY,
      footerW: footerW,
      footerH: footerH,
      footerY: footerY,
    };

    drawHeaderFooter(doc, shared);

    var type = safeText(options && options.type ? options.type : '');
    var patientId = toNum(options && options.patientId ? options.patientId : null);

    if (type && patientId) {
      try {
        var data = await fetchPdfData(type, patientId);
        if (type === 'medical') {
          renderMedicalForm(doc, shared, data);

          // Page 2
          doc.addPage();
          drawHeaderFooter(doc, shared);
          var page2Assets = {
            likertDataUrl: likertDataUrl,
            likert2DataUrl: likert2DataUrl,
            ape1DataUrl: ape1DataUrl,
            likertAspect: null,
            likert2Aspect: null,
            ape1Aspect: null,
          };
          try {
            if (likertDataUrl) {
              var s1 = await getImageSize(likertDataUrl);
              page2Assets.likertAspect = (s1.width || 1) / (s1.height || 1);
            }
            if (likert2DataUrl) {
              var s1b = await getImageSize(likert2DataUrl);
              page2Assets.likert2Aspect = (s1b.width || 1) / (s1b.height || 1);
            }
            if (ape1DataUrl) {
              var s2 = await getImageSize(ape1DataUrl);
              page2Assets.ape1Aspect = (s2.width || 1) / (s2.height || 1);
            }
          } catch (e) {
          }
          renderMedicalPage2MentalHealth(doc, shared, data, page2Assets);
        } else {
          var dentalAssets = {
            ape2DataUrl: ape2DataUrl,
            ape2Aspect: null,
          };
          try {
            if (ape2DataUrl) {
              var s3 = await getImageSize(ape2DataUrl);
              dentalAssets.ape2Aspect = (s3.width || 1) / (s3.height || 1);
            }
          } catch (e) {
          }
          renderDentalForm(doc, shared, data, dentalAssets);
        }
      } catch (e) {
        var fallbackY = shared.headerY + shared.headerH + 15;
        doc.setFontSize(11);
        doc.setFont(undefined, 'bold');
        doc.setTextColor(200, 0, 0);
        doc.text('Failed to load data for PDF.', 14, fallbackY);
      }
    }

    var pdfBlob = doc.output('blob');
    var url = URL.createObjectURL(pdfBlob);
    if (behavior === 'link') {
      return {
        ok: true,
        blob: pdfBlob,
        blobUrl: url,
        filename: (title ? String(title) : 'form') + '.pdf',
        title: title,
      };
    }
    openPdfInNewTabOrDownload(pdfBlob, title);
  };

  window.sdoGenerateMedicalPdf = function sdoGenerateMedicalPdf(options) {
    options = options || {};
    return window.sdoGenerateBlankPdf && window.sdoGenerateBlankPdf({
      type: 'medical',
      patientId: options.patientId,
      title: options.title || 'Medical Form',
    });
  };

  window.sdoGenerateDentalPdf = function sdoGenerateDentalPdf(options) {
    options = options || {};
    return window.sdoGenerateBlankPdf && window.sdoGenerateBlankPdf({
      type: 'dental',
      patientId: options.patientId,
      title: options.title || 'Dental Form',
    });
  };

})();
