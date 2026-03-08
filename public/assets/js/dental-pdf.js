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

  function checkMark(doc, x, y, checked) {
    if (!checked) return;
    var prevSize = null;
    try {
      if (doc.internal && typeof doc.internal.getFontSize === 'function') {
        prevSize = doc.internal.getFontSize();
      } else if (typeof doc.getFontSize === 'function') {
        prevSize = doc.getFontSize();
      }
    } catch (e) {
      prevSize = null;
    }

    doc.setFont(undefined, 'bold');
    doc.setFontSize(9);
    doc.text('/', x, y);

    doc.setFont(undefined, 'normal');
    if (prevSize != null) doc.setFontSize(prevSize);
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

  function renderDentalForm(doc, shared, data, assets) {
    var pageWidth = shared.pageWidth;
    var pageHeight = shared.pageHeight;
    var left = 10;
    var right = pageWidth - 10;
    var top = shared.headerY + shared.headerH + 5;

    var p = data.patient || {};
    var a = data.assessment || {};

    doc.setTextColor(0);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(9);
    doc.text('SCHOOL GOVERNANCE AND OPERATIONS DIVISION', pageWidth / 2, top, { align: 'center' });
    doc.text('SCHOOL HEALTH AND NUTRITION UNIT', pageWidth / 2, top + 4, { align: 'center' });
    doc.setFontSize(11);
    doc.text('DENTAL FORM', pageWidth / 2, top + 10, { align: 'center' });

    var y = top + 14;
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

    y += 5.5;
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

    y += 5.5;
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

    y += 5.5;
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

    y += 4.5;
    var tableTop = y;
    var mhX = left;
    var mhW = 75;
    var ohX = mhX + mhW + 6;
    var ohW = right - ohX;
    var rowH = 3.6;
    doc.setFontSize(6.8);
    doc.setFont(undefined, 'bold');
    doc.text('MEDICAL HISTORY', mhX + mhW / 2, tableTop, { align: 'center' });
    doc.text('ORAL HEALTH CONDITION', ohX + ohW / 2, tableTop, { align: 'center' });

    var mhY = tableTop + 2.0;
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
      doc.text(mhRows[i].label, mhX + 2, ry + 3.0);
      var yes = normalizeYesNo(a[mhRows[i].key]);
      checkMark(doc, mhX + mhW - 16.5, ry + 3.0, yes);
      checkMark(doc, mhX + mhW - 5.5, ry + 3.0, !yes);
    }
    var mhBottomY = mhY + rowH * (mhRows.length + 1) + 3.6;
    doc.setFont(undefined, 'bold');
    doc.text('OTHERS (Specify):', mhX, mhBottomY);
    doc.setFont(undefined, 'normal');
    var mhOthersX = mhX + 32;
    doc.line(mhOthersX, mhBottomY + 1.2, mhX + mhW, mhBottomY + 1.2);
    doc.text(safeText(a.mh_others), mhOthersX, mhBottomY);

    var ohY = tableTop + 2.5;
    var ohRowH = 3.6;
    // Rows: Date, Age, (Y/N header), then 4 condition rows
    var ohRowsCount = 7;
    var labelW = 62;
    var labelSepX = ohX + labelW;
    // Remaining width is split into 2 columns: Y and N (no extra column on the right)
    var ynW = (ohW - labelW) / 2;
    var ySepX = labelSepX + ynW;
    var nSepX = ohX + ohW;
    var ynCenterY = labelSepX + ynW / 2;
    var ynCenterN = ySepX + ynW / 2;

    doc.rect(ohX, ohY, ohW, ohRowH * ohRowsCount);
    doc.line(labelSepX, ohY, labelSepX, ohY + ohRowH * ohRowsCount);
    // Split into Y/N columns starting at the Y/N header row
    var ynTopY = ohY + ohRowH * 2;
    doc.line(ySepX, ynTopY, ySepX, ohY + ohRowH * ohRowsCount);
    for (var r = 1; r < ohRowsCount; r++) {
      doc.line(ohX, ohY + ohRowH * r, ohX + ohW, ohY + ohRowH * r);
    }

    doc.setFont(undefined, 'bold');
    doc.setFontSize(6.8);
    doc.text('Date of Examination', ohX + 2, ohY + 2.7);
    doc.setFont(undefined, 'normal');
    doc.text(safeText(a.exam_date), labelSepX + 2, ohY + 2.7);
    doc.setFont(undefined, 'bold');
    doc.text('Age last birthday', ohX + 2, ohY + ohRowH + 2.7);
    doc.setFont(undefined, 'normal');
    doc.text(safeText(a.age_last_birthday), labelSepX + 2, ohY + ohRowH + 2.7);

    // Y / N headers (dedicated header row)
    doc.setFont(undefined, 'bold');
    doc.setFontSize(6.4);
    // Header row is rowIndex=2 (0-based): center Y/N in that row
    var ynHeaderCenterY = ohY + ohRowH * 2 + 2.7;
    doc.text('YES', ynCenterY, ynHeaderCenterY, { align: 'center' });
    doc.text('NO', ynCenterN, ynHeaderCenterY, { align: 'center' });
    doc.setFontSize(6.8);

    function ynCheck(xCenter, y, checked) {
      if (!checked) return;
      doc.setFontSize(8);
      doc.setFont(undefined, 'bold');
      doc.text('/', xCenter, y, { align: 'center' });
      doc.setFont(undefined, 'normal');
      doc.setFontSize(6.8);
    }

    function ynRow(rowIndex, label, key) {
      var base = ohY + ohRowH * rowIndex;
      doc.setFont(undefined, 'bold');
      doc.text(label, ohX + 2, base + 3.0);
      doc.setFont(undefined, 'normal');
      var yes = normalizeYesNo(a[key]);
      ynCheck(ynCenterY, base + 3.0, yes);
      ynCheck(ynCenterN, base + 3.0, !yes);
    }

    ynRow(3, 'Presence of Debris', 'debris');
    ynRow(4, 'Inflammation of Gingiva', 'gingiva_inflammation');
    ynRow(5, 'Presence of Calculus', 'calculus');
    ynRow(6, 'Under Orthodontic Treatment', 'orthodontic_treatment');

    // Occlusion / TMJ / Dentofacial below the oral health condition table (so table stays compact)
    var afterOhY = ohY + ohRowH * ohRowsCount;
    var occY = afterOhY + 4.2;
    doc.setFontSize(6.8);
    doc.setFont(undefined, 'bold');
    doc.text('OCCLUSION:', ohX, occY);
    var occ = safeText(a.occlusion);
    function occBox2(x, label, v) {
      doc.rect(x, occY - 3.0, 3, 3);
      checkMark(doc, x + 0.8, occY, occ === v);
      doc.setFont(undefined, 'normal');
      doc.text(label, x + 5, occY);
      doc.setFont(undefined, 'bold');
    }
    occBox2(ohX + 28, 'Class 1', 'Class 1');
    occBox2(ohX + 52, 'Class II', 'Class 2');
    occBox2(ohX + 78, 'Class III', 'Class 3');

    var tmjY = occY + 4.8;
    doc.setFont(undefined, 'bold');
    doc.text('T.M.J. EXAM:', ohX, tmjY);
    var tmj = safeText(a.tmj_exam);
    function tmjBox2(x, label, v) {
      doc.rect(x, tmjY - 3.0, 3, 3);
      checkMark(doc, x + 0.8, tmjY, tmj === v);
      doc.setFont(undefined, 'normal');
      doc.text(label, x + 5, tmjY);
      doc.setFont(undefined, 'bold');
    }
    doc.setFontSize(7.0);
    tmjBox2(ohX + 28, 'Pain', 'Pain');
    tmjBox2(ohX + 44, 'Popping', 'Popping');
    tmjBox2(ohX + 64, 'Deviation', 'Deviation');
    tmjBox2(ohX + 88, 'Tooth Wear', 'Tooth wear');
    doc.setFontSize(7.6);

    var dentoY = tmjY + 4.8;
    doc.setFont(undefined, 'bold');
    doc.text('Dentofacial Anomaly, Neoplasm, Others, specify:', ohX, dentoY);
    doc.setFont(undefined, 'normal');
    line(doc, ohX, dentoY + 1.3, right, dentoY + 1.3, 0.2);

    // Legend block (as in template) below tables before tooth chart
    var legendY = Math.max(mhBottomY + 5, dentoY + 6.0);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(5.6);
    doc.text('LEGEND CONDITION RESTORATIONS AND PROSTHETIC SURGERY', (left + right) / 2, legendY, { align: 'center' });

    var ly = legendY + 3.5;
    var lh = 3.0;
    doc.setFontSize(5.6);

    function legendItem(x, y, code, text, opts) {
      opts = opts || {};
      doc.setFont(undefined, 'bold');
      doc.text(code, x, y);
      doc.setFont(undefined, 'normal');
      doc.text(text, x + (opts.textDx != null ? opts.textDx : 4.2), y);
    }

    // Column x positions chosen to match template-like spacing
    // Keep the last column inside the right margin (avoid overflow)
    var col1 = left;
    var col2 = left + 48;
    var col3 = left + 82;
    var col4 = left + 112;
    // Rightmost column for S/Rm/X/XO (same column)
    var col5 = right - 50;

    function legendNote(x, y, text, maxW) {
      doc.setFont(undefined, 'normal');
      doc.text(text, x, y, { maxWidth: maxW || 46 });
    }

    // Col 1
    legendItem(col1, ly + 0 * lh, 'V', '- Present Teeth');
    legendItem(col1, ly + 1 * lh, 'D', '- Decayed (Caries ind. for filling)');
    legendItem(col1, ly + 2 * lh, 'M', '- Missing (Due to Caries)');
    legendItem(col1, ly + 3 * lh, 'MO', '- Missing (Due to other causes)');

    // Col 2
    legendItem(col2, ly + 0 * lh, 'Imp', '- Impacted Tooth');
    legendItem(col2, ly + 1 * lh, 'Sp', '- Supernumerary Tooth');
    legendItem(col2, ly + 2 * lh, 'Rf', '- Root Fragment');
    legendItem(col2, ly + 3 * lh, 'Un', '- Unerupted');

    // Col 3
    legendItem(col3, ly + 0 * lh, 'Am', '- Amalgam Filling');
    legendItem(col3, ly + 1 * lh, 'Co', '- Composite Filling');
    legendItem(col3, ly + 2 * lh, 'JC', '- Jacket Crown');
    legendItem(col3, ly + 3 * lh, 'Ab', '- Abutment');

    // Col 4
    legendItem(col4, ly + 0 * lh, 'Att', '- Attachment');
    legendItem(col4, ly + 1 * lh, 'P', '- Pontic');
    legendItem(col4, ly + 2 * lh, 'In', '- Inlay');
    legendItem(col4, ly + 3 * lh, 'Imp', '- Implant');

    // Col 5 (S / Rm / X / XO in one column)
    legendItem(col5, ly + 0 * lh, 'S', '- Sealants');
    legendItem(col5, ly + 1 * lh, 'Rm', '- Removable Denture');
    legendItem(col5, ly + 2 * lh, 'X', '- Extraction (due to Caries)');
    legendItem(col5, ly + 3 * lh, 'XO', '- Extraction (due to other causes)');

    var chartY = ly + 28;
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

    var boxY = chartY + 2;
    var startX = left;
    var cellW = 11.2;
    var cellH = 7.2;
    var cols = 16;
    var rows = 6;
    var teethTop = ['18','17','16','15','14','13','12','11','21','22','23','24','25','26','27','28'];
    var teethBottom = ['48','47','46','45','44','43','42','41','31','32','33','34','35','36','37','38'];

    // Outer grid
    doc.rect(startX, boxY, cellW * cols, cellH * rows);
    for (var gx = 1; gx < cols; gx++) {
      doc.line(startX + cellW * gx, boxY, startX + cellW * gx, boxY + cellH * rows);
    }
    for (var gy = 1; gy < rows; gy++) {
      doc.line(startX, boxY + cellH * gy, startX + cellW * cols, boxY + cellH * gy);
    }

    function cellCenter(col, row) {
      return {
        x: startX + col * cellW + cellW / 2,
        y: boxY + row * cellH + cellH / 2,
      };
    }

    // Row 0 and row 5: rounded input boxes
    for (var i = 0; i < cols; i++) {
      var rx = startX + i * cellW + 1.0;
      var ryTop = boxY + 0 * cellH + 1.0;
      var ryBot = boxY + 5 * cellH + 1.0;
      doc.roundedRect(rx, ryTop, cellW - 2.0, cellH - 2.0, 1.2, 1.2);
      doc.roundedRect(rx, ryBot, cellW - 2.0, cellH - 2.0, 1.2, 1.2);
    }

    // Row 1 and row 4: target circles
    function drawTarget(col, row) {
      var c = cellCenter(col, row);
      var r = 2.1;
      doc.circle(c.x, c.y, r);
      doc.line(c.x - r, c.y, c.x + r, c.y);
      doc.line(c.x, c.y - r, c.x, c.y + r);
    }
    for (var j = 0; j < cols; j++) {
      drawTarget(j, 1);
      drawTarget(j, 4);
    }

    // Tooth numbers rows
    doc.setFont(undefined, 'bold');
    doc.setFontSize(9);
    for (var t1 = 0; t1 < cols; t1++) {
      var cTop = cellCenter(t1, 2);
      var cBot = cellCenter(t1, 3);
      doc.text(teethTop[t1], cTop.x, cTop.y + 2.2, { align: 'center' });
      doc.text(teethBottom[t1], cBot.x, cBot.y + 2.2, { align: 'center' });
    }

    // Codes in rounded boxes (top = upper teeth, bottom = lower teeth)
    doc.setFont(undefined, 'normal');
    doc.setFontSize(8);
    for (var t2 = 0; t2 < cols; t2++) {
      var topTooth = teethTop[t2];
      var botTooth = teethBottom[t2];
      var codeTop = safeText(chart[topTooth] || '').toUpperCase();
      var codeBot = safeText(chart[botTooth] || '').toUpperCase();
      if (codeTop === 'V') codeTop = '✓';
      if (codeBot === 'V') codeBot = '✓';
      if (codeTop) {
        var ct = cellCenter(t2, 0);
        doc.text(codeTop, ct.x, ct.y + 2.0, { align: 'center' });
      }
      if (codeBot) {
        var cb = cellCenter(t2, 5);
        doc.text(codeBot, cb.x, cb.y + 2.0, { align: 'center' });
      }
    }

    var blockY = boxY + cellH * rows + 8;
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
            doc.text('/', x + 4.5, yy);
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

  window.sdoGenerateDentalPdf = async function sdoGenerateDentalPdf(options) {
    options = options || {};
    var patientId = options.patientId;
    var title = safeText(options.title || 'Dental Form');

    var jsPDFCtor = null;
    if (typeof window.jsPDF === 'function') {
      jsPDFCtor = window.jsPDF;
    } else if (window.jspdf && typeof window.jspdf.jsPDF === 'function') {
      jsPDFCtor = window.jspdf.jsPDF;
    }

    if (!jsPDFCtor) {
      alert('jsPDF is not loaded.');
      return;
    }

    var assets = window.SDO_MDD_PDF_ASSETS || {};
    var headerUrl = safeText(assets.headerUrl);
    var footerUrl = safeText(assets.footerUrl);
    var ape2LogoUrl = safeText(assets.ape2LogoUrl);

    if (!headerUrl || !footerUrl) {
      alert('PDF header/footer assets are not configured.');
      return;
    }

    var headerDataUrl, footerDataUrl, ape2DataUrl;
    try {
      var tasks = [loadImageAsDataURL(headerUrl), loadImageAsDataURL(footerUrl)];
      if (ape2LogoUrl) tasks.push(loadImageAsDataURL(ape2LogoUrl));
      var res = await Promise.all(tasks);
      headerDataUrl = res[0];
      footerDataUrl = res[1];
      ape2DataUrl = ape2LogoUrl ? res[2] : null;
    } catch (e) {
      alert('Failed to load header/footer images.');
      return;
    }

    var doc = new jsPDFCtor({ unit: 'mm', format: 'a4', orientation: 'portrait' });
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

    var dentalAssets = { ape2DataUrl: ape2DataUrl, ape2Aspect: null };
    try {
      if (ape2DataUrl) {
        var s = await getImageSize(ape2DataUrl);
        dentalAssets.ape2Aspect = (s.width || 1) / (s.height || 1);
      }
    } catch (e) {
    }

    var pid = toNum(patientId);
    if (pid) {
      try {
        var data = await fetchPdfData('dental', pid);
        renderDentalForm(doc, shared, data, dentalAssets);
      } catch (e) {
        var fallbackY = shared.headerY + shared.headerH + 15;
        doc.setFontSize(11);
        doc.setFont(undefined, 'bold');
        doc.setTextColor(200, 0, 0);
        doc.text('Failed to load data for PDF.', 14, fallbackY);
      }
    }

    var pdfBlob = doc.output('blob');
    openPdfInNewTabOrDownload(pdfBlob, title);
  };
})();
