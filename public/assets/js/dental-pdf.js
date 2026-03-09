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
      var host = document.getElementById('singlePdfLinks');
      if (!host) {
        host = document.createElement('div');
        host.id = 'singlePdfLinks';
        host.style.position = 'fixed';
        host.style.right = '12px';
        host.style.bottom = '12px';
        host.style.zIndex = '99999';
        host.style.maxWidth = '360px';
        host.style.maxHeight = '55vh';
        host.style.overflow = 'auto';
        host.style.background = '#fff';
        host.style.border = '1px solid rgba(0,0,0,0.12)';
        host.style.borderRadius = '12px';
        host.style.boxShadow = '0 12px 28px rgba(0,0,0,0.18)';
        host.style.padding = '10px';
        host.style.fontFamily = 'system-ui, -apple-system, Segoe UI, Roboto, sans-serif';
        host.style.fontSize = '12px';
        host.innerHTML = '<div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:8px;">' +
          '<div style="font-weight:700;">PDF Link</div>' +
          '<button type="button" id="singlePdfLinksClose" style="border:0;background:transparent;font-size:16px;line-height:1;cursor:pointer;">×</button>' +
        '</div>' +
        '<div id="singlePdfLinksList" style="display:flex;flex-direction:column;gap:6px;"></div>';
        document.body.appendChild(host);
        var closeBtn = document.getElementById('singlePdfLinksClose');
        if (closeBtn) {
          closeBtn.addEventListener('click', function(){
            try { host.remove(); } catch (e) {}
          });
        }
      }

      var list = document.getElementById('singlePdfLinksList') || host;
      var a = document.createElement('a');
      a.href = blobUrl;
      a.target = '_blank';
      a.rel = 'noopener noreferrer';
      a.textContent = 'Open: ' + filename;
      a.style.display = 'block';
      a.style.color = '#0d6efd';
      a.style.textDecoration = 'none';
      list.appendChild(a);
    } catch (e) {
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

    var y = top + 13;
    doc.setFontSize(8.0);
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

    y += 4.6;
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

    y += 4.6;
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

    y += 4.6;
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

    y += 3.8;
    var tableTop = y;
    var mhX = left;
    var mhW = 75;
    var ohX = mhX + mhW + 6;
    var ohW = right - ohX;
    var rowH = 3.2;
    doc.setFontSize(6.6);
    doc.setFont(undefined, 'bold');
    doc.text('MEDICAL HISTORY', mhX + mhW / 2, tableTop, { align: 'center' });
    doc.text('ORAL HEALTH CONDITION', ohX + ohW / 2, tableTop, { align: 'center' });

    var mhY = tableTop + 1.6;
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
    doc.text('YES', mhX + mhW - 16.5, mhY + 2.55, { align: 'center' });
    doc.text('NO', mhX + mhW - 5.5, mhY + 2.55, { align: 'center' });

    doc.setFont(undefined, 'normal');
    for (var i = 0; i < mhRows.length; i++) {
      var ry = mhY + rowH * (i + 1);
      doc.line(mhX, ry, mhX + mhW, ry);
      doc.text(mhRows[i].label, mhX + 1.8, ry + 2.55);
      var yes = normalizeYesNo(a[mhRows[i].key]);
      checkMark(doc, mhX + mhW - 16.5, ry + 2.55, yes);
      checkMark(doc, mhX + mhW - 5.5, ry + 2.55, !yes);
    }
    var mhBottomY = mhY + rowH * (mhRows.length + 1) + 3.0;
    doc.setFont(undefined, 'bold');
    doc.text('OTHERS (Specify):', mhX, mhBottomY);
    doc.setFont(undefined, 'normal');
    var mhOthersX = mhX + 32;
    doc.line(mhOthersX, mhBottomY + 1.2, mhX + mhW, mhBottomY + 1.2);
    doc.text(safeText(a.mh_others), mhOthersX, mhBottomY);

    var ohY = tableTop + 2.0;
    var ohRowH = 3.2;
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
    doc.setFontSize(6.6);
    doc.text('Date of Examination', ohX + 1.8, ohY + 2.4);
    doc.setFont(undefined, 'normal');
    doc.text(safeText(a.exam_date), labelSepX + 1.8, ohY + 2.4);
    doc.setFont(undefined, 'bold');
    doc.text('Age last birthday', ohX + 1.8, ohY + ohRowH + 2.4);
    doc.setFont(undefined, 'normal');
    doc.text(safeText(a.age_last_birthday), labelSepX + 1.8, ohY + ohRowH + 2.4);

    // Y / N headers (dedicated header row)
    doc.setFont(undefined, 'bold');
    doc.setFontSize(6.4);
    // Header row is rowIndex=2 (0-based): center Y/N in that row
    var ynHeaderCenterY = ohY + ohRowH * 2 + 2.35;
    doc.text('YES', ynCenterY, ynHeaderCenterY, { align: 'center' });
    doc.text('NO', ynCenterN, ynHeaderCenterY, { align: 'center' });
    doc.setFontSize(6.6);

    function ynCheck(xCenter, y, checked) {
      if (!checked) return;
      doc.setFontSize(7.2);
      doc.setFont(undefined, 'bold');
      doc.text('/', xCenter, y, { align: 'center' });
      doc.setFont(undefined, 'normal');
      doc.setFontSize(6.6);
    }

    function ynRow(rowIndex, label, key) {
      var base = ohY + ohRowH * rowIndex;
      doc.setFont(undefined, 'bold');
      doc.text(label, ohX + 1.8, base + 2.55);
      doc.setFont(undefined, 'normal');
      var yes = normalizeYesNo(a[key]);
      ynCheck(ynCenterY, base + 2.55, yes);
      ynCheck(ynCenterN, base + 2.55, !yes);
    }

    ynRow(3, 'Presence of Debris', 'debris');
    ynRow(4, 'Inflammation of Gingiva', 'gingiva_inflammation');
    ynRow(5, 'Presence of Calculus', 'calculus');
    ynRow(6, 'Under Orthodontic Treatment', 'orthodontic_treatment');

    // Occlusion / TMJ / Dentofacial below the oral health condition table (so table stays compact)
    var afterOhY = ohY + ohRowH * ohRowsCount;
    var occY = afterOhY + 3.6;
    doc.setFontSize(6.6);
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

    var ly = legendY + 3.4;
    var lh = 2.9;
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
    legendItem(col1, ly + 0 * lh, '/', '- Present Teeth');
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

    var chartY = ly + lh * 4 + 2.1;
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
    var cellW = 10.0;
    var cellH = 5.8;
    var cols = 16;
    var rows = 6;
    var chartW = cellW * cols;
    var startX = left + ((right - left) - chartW) / 2;
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

    // Row 1 and row 4: target circles
    function drawTarget(col, row) {
      var c = cellCenter(col, row);
      var r = 1.6;
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
    doc.setFontSize(7.6);
    for (var t1 = 0; t1 < cols; t1++) {
      var cTop = cellCenter(t1, 2);
      var cBot = cellCenter(t1, 3);
      doc.text(teethTop[t1], cTop.x, cTop.y + 1.7, { align: 'center' });
      doc.text(teethBottom[t1], cBot.x, cBot.y + 1.7, { align: 'center' });
    }

    // Codes in rounded boxes (top = upper teeth, bottom = lower teeth)
    doc.setFont(undefined, 'normal');
    doc.setFontSize(7.0);
    for (var t2 = 0; t2 < cols; t2++) {
      var topTooth = teethTop[t2];
      var botTooth = teethBottom[t2];
      var codeTop = safeText(chart[topTooth] || '').toUpperCase();
      var codeBot = safeText(chart[botTooth] || '').toUpperCase();
      // Treat common “present” markers as '/'
      if (codeTop === 'V' || codeTop === '.' || codeTop === '✓' || codeTop === '/') codeTop = '/';
      if (codeBot === 'V' || codeBot === '.' || codeBot === '✓' || codeBot === '/') codeBot = '/';
      if (codeTop) {
        var ct = cellCenter(t2, 0);
        doc.text(codeTop, ct.x, ct.y + 1.55, { align: 'center' });
      }
      if (codeBot) {
        var cb = cellCenter(t2, 5);
        doc.text(codeBot, cb.x, cb.y + 1.55, { align: 'center' });
      }
    }

    var blockY = boxY + cellH * rows + 5.0;

    // Right-side DMFT table geometry (template style)
    var dmftW = 70;
    var dmftBoxX = right - dmftW;
    var dmftBoxY = blockY;

    // Left-side blocks (Soft tissue + Periodontal)
    var leftBlockX = left;
    var leftBlockW = dmftBoxX - left - 4;

    // INITIAL SOFT TISSUE EXAM (bordered row with checkboxes)
    var softRowH = 6.2;
    doc.setFont(undefined, 'bold');
    doc.setFontSize(7.2);
    doc.text('INITIAL SOFT TISSUE EXAM', leftBlockX, blockY);

    var softY = blockY + 1.05;
    doc.rect(leftBlockX, softY, leftBlockW, softRowH);
    var soft = safeText(a.soft_tissue_exam);
    var softOpts = ['Lips', 'Floor of mouth', 'Palate', 'Tongue', 'Neck & nodes'];
    doc.setFontSize(6.4);
    var softItemW = leftBlockW / softOpts.length;
    for (var so = 0; so < softOpts.length; so++) {
      var cx = leftBlockX + softItemW * so + 2.0;
      doc.rect(cx, softY + 2.35, 1.8, 1.8);
      checkMark(doc, cx + 0.35, softY + 3.85, soft === softOpts[so]);
      doc.setFont(undefined, 'bold');
      doc.text(softOpts[so], cx + 3.0, softY + 3.9);
      doc.setFont(undefined, 'normal');
    }

    // INITIAL PERIODONTAL EXAM (table below)
    var perioY = softY + softRowH;
    var perioHeaderH = 4.1;
    var perioRowH = 3.2;
    var perioRows = [
      { label: 'GINGIVAL INFLAMATION:', key: 'perio_gingival_inflammation', opts: ['Slight', 'Moderate', 'Severe'] },
      { label: 'SOFT PLAQUE BUILDUP:', key: 'perio_soft_plaque', opts: ['Slight', 'Moderate', 'Heavy'] },
      { label: 'HARD CALC BUILDUP:', key: 'perio_hard_calc', opts: ['Light', 'Moderate', 'Heavy'] },
      { label: 'STAINS:', key: 'perio_stains', opts: ['Light', 'Moderate', 'Heavy'] },
      { label: 'HOME CARE EFFECTIVENESS:', key: 'home_care_effectiveness', opts: ['Good', 'Fair', 'Poor'] },
      { label: 'PERIODONTAL CONDITION:', key: 'periodontal_condition', opts: ['Good', 'Fair', 'Poor'] },
      { label: 'PERIODONTAL DIAGNOSIS:', key: 'periodontal_diagnosis', opts: ['Normal', 'Gingivitis', ''] },
      { label: 'PERIODONTITIS:', key: 'periodontitis', opts: ['Early', 'Moderate', 'Advanced'] },
      { label: 'MUCOGINGIVAL DEFECTS:', key: 'mucogingival_defects', opts: ['', '', ''] },
    ];
    var perioH = perioHeaderH + perioRowH * perioRows.length;
    doc.rect(leftBlockX, perioY, leftBlockW, perioH);
    // Header
    doc.setFont(undefined, 'bold');
    doc.setFontSize(7.2);
    doc.text('INITIAL PERIODONTAL EXAM', leftBlockX + leftBlockW / 2, perioY + 3.05, { align: 'center' });
    doc.line(leftBlockX, perioY + perioHeaderH, leftBlockX + leftBlockW, perioY + perioHeaderH);

    var perioLabelW = 50;
    var perioSep1 = leftBlockX + perioLabelW;
    var perioOptW = (leftBlockW - perioLabelW) / 3;
    // Merge header across all columns: start vertical separators below the header line
    doc.line(perioSep1, perioY + perioHeaderH, perioSep1, perioY + perioH);
    doc.line(perioSep1 + perioOptW, perioY + perioHeaderH, perioSep1 + perioOptW, perioY + perioH);
    doc.line(perioSep1 + perioOptW * 2, perioY + perioHeaderH, perioSep1 + perioOptW * 2, perioY + perioH);

    doc.setFontSize(6.4);
    for (var pr = 0; pr < perioRows.length; pr++) {
      var row = perioRows[pr];
      var y0 = perioY + perioHeaderH + perioRowH * pr;
      doc.line(leftBlockX, y0 + perioRowH, leftBlockX + leftBlockW, y0 + perioRowH);
      doc.setFont(undefined, 'bold');
      doc.text(row.label, leftBlockX + 1.2, y0 + 2.35);
      doc.setFont(undefined, 'normal');

      var val = safeText(a[row.key]);
      var v = String(val || '').trim();
      for (var oi = 0; oi < 3; oi++) {
        var opt = row.opts[oi] || '';
        if (!opt) continue;
        var ox = perioSep1 + perioOptW * oi;
        var bx = ox + 1.2;
        doc.rect(bx, y0 + 0.85, 1.5, 1.5);
        checkMark(doc, bx + 0.28, y0 + 2.35, v.toLowerCase() === opt.toLowerCase());
        doc.setFont(undefined, 'bold');
        doc.text(opt, bx + 2.45, y0 + 2.35);
        doc.setFont(undefined, 'normal');
      }

      if (row.key === 'mucogingival_defects') {
        var md = safeText(a.mucogingival_defects);
        if (md) {
          doc.text(md, perioSep1 + 1.2, y0 + 2.35);
        }
      }
    }

    // DMFT SCORES (3-column table)
    doc.setFont(undefined, 'bold');
    doc.setFontSize(7.2);
    doc.text('DMFT SCORES', dmftBoxX + dmftW / 2, dmftBoxY, { align: 'center' });

    var dmftHeaderH = 4.0;
    var dmftRowH = 3.2;
    var dmftFooterH = 3.2;
    var dmftRows = [
      'Number of Teeth Present',
      'Number of Caries Free Teeth',
      'Number of Decayed Teeth',
      'Number of Missing Teeth',
      'Number of Filled Teeth',
      'Total DMF Teeth',
    ];
    var dmftH = dmftHeaderH + dmftRowH * (dmftRows.length + 1) + dmftFooterH;
    doc.rect(dmftBoxX, dmftBoxY + 2, dmftW, dmftH);

    var c1 = 38;
    var c2 = 14;
    var c3 = dmftW - c1 - c2;
    var dmftX1 = dmftBoxX + c1;
    var dmftX2 = dmftBoxX + c1 + c2;
    doc.line(dmftX1, dmftBoxY + 2, dmftX1, dmftBoxY + 2 + dmftH);
    doc.line(dmftX2, dmftBoxY + 2, dmftX2, dmftBoxY + 2 + dmftH);

    doc.setFontSize(6.4);
    // Header row
    doc.text('Tooth Count', dmftBoxX + 1.15, dmftBoxY + 2 + 2.75);
    doc.text('PERMANENT', dmftX1 + c2 / 2, dmftBoxY + 2 + 2.75, { align: 'center' });
    doc.text('REMARKS', dmftX2 + c3 / 2, dmftBoxY + 2 + 2.75, { align: 'center' });
    doc.line(dmftBoxX, dmftBoxY + 2 + dmftHeaderH, dmftBoxX + dmftW, dmftBoxY + 2 + dmftHeaderH);

    var dmft = computeDmftFromChart(chart);
    var present = a.teeth_present_count != null ? a.teeth_present_count : dmft.present;
    var decayed = a.d_count != null ? a.d_count : dmft.d;
    var missing = a.m_count != null ? a.m_count : dmft.m;
    var filled = a.f_count != null ? a.f_count : dmft.f;
    var total = a.dmft_total != null ? a.dmft_total : dmft.total;
    var cariesFree = Math.max(0, Number(present || 0) - Number(decayed || 0) - Number(missing || 0) - Number(filled || 0));
    var dmftVals = [present, cariesFree, decayed, missing, filled, total];

    doc.setFont(undefined, 'normal');
    for (var di = 0; di < dmftRows.length; di++) {
      var yRow = dmftBoxY + 2 + dmftHeaderH + dmftRowH * (di + 1);
      doc.line(dmftBoxX, yRow, dmftBoxX + dmftW, yRow);
      doc.text(dmftRows[di], dmftBoxX + 1.15, yRow - 0.88);
      doc.text(String(dmftVals[di] != null ? dmftVals[di] : ''), dmftX1 + c2 / 2, yRow - 0.88, { align: 'center' });
    }

    // Footer
    var footerY = dmftBoxY + 2 + dmftHeaderH + dmftRowH * (dmftRows.length + 1);
    doc.line(dmftBoxX, footerY, dmftBoxX + dmftW, footerY);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(6.6);
    doc.text('DMFT SCORES', dmftBoxX + 1.15, footerY + 2.25);

    // Continue below the taller of the two blocks
    var afterBlocksY = Math.max(perioY + perioH, dmftBoxY + 2 + dmftH) + 6;

    var recY = afterBlocksY;
    doc.setFont(undefined, 'bold');
    doc.setFontSize(6.6);
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

    doc.setFontSize(5.8);
    var recRowStep = 3.2;
    var recLeftY = recY + 4.6;
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
            doc.text('/', x + 4.0, yy);
            doc.setFont(undefined, 'normal');
          }
          doc.setFont(undefined, 'bold');
          doc.text(label, x + 8.4, yy);
          doc.setFont(undefined, 'normal');
        } else {
          var otherText = safeText(a.recommendation_others);
          doc.setFont(undefined, 'bold');
          doc.text('Others (Specify):', x + 8.4, yy);
          doc.setFont(undefined, 'normal');
          var otherY = yy + 3.5;
          var otherX = x + 8.4;
          doc.line(otherX, otherY + 0.8, x + 40, otherY + 0.8);
          doc.text(otherText, otherX, otherY);
        }
        yy += (label === 'Others (Specify)') ? (recRowStep + 4.4) : recRowStep;
      }
    }
    drawRecCol(recItemsA, colA, recLeftY);
    drawRecCol(recItemsB, colB, recLeftY);
    drawRecCol(recItemsC, colC, recLeftY);

    var recMaxRows = Math.max(recItemsA.length, recItemsB.length, recItemsC.length);
    var recEndY = recLeftY + recRowStep * recMaxRows + 3.0;

    // Anchor the patient's signature above the footer (to match original and avoid footer overlap)
    var footerSafeY = pageHeight - shared.footerH - 6.0;
    var psLineY = footerSafeY - 2.0;
    doc.setFont(undefined, 'normal');
    doc.line(pageWidth / 2 - 55, psLineY, pageWidth / 2 + 55, psLineY);
    doc.setFontSize(6.6);
    doc.text("Patient's Signature over Printed Name", pageWidth / 2, psLineY + 3.0, { align: 'center' });

    // Informed consent block goes above the patient's signature
    // IMPORTANT: derive height from wrapped line count so the signatory clamp stays stable
    var consentTitleGap = 3.4;
    var consentLineStep = 3.2;
    var consentBottomGap = 3.0;
    var consentText =
      'I, ________________________________, DO HEREBY CONSENT TO THE PERFORMANCE UPON MYSELF OF ' +
      'DENTAL PROCEDURES, WHETHER RESTORATIONS/ EXTRACTION OF TEETH OR ANY OTHER TREATMENT DEEMED NECESSARY TO RESTORE MY ' +
      'DENTAL HEALTH IN FAVOR OF THE ATTENDING DENTIST.';
    // Wrap so it never runs under the signatory block on the right
    var consentMaxW = (left + 140) - left - 4;
    var consentLines = typeof doc.splitTextToSize === 'function' ? doc.splitTextToSize(consentText, consentMaxW) : [consentText];
    var consentBlockH = consentTitleGap + consentLineStep * consentLines.length;
    // Lowest (closest to footer) we can place the consent block without hitting the patient signature
    var consentYMax = psLineY - consentBlockH - consentBottomGap;

    // Dentist signature / license sits between recommendations and informed consent
    // Keep its position stable (anchored under recommendations). If space is tight,
    // shift it upward only as much as needed so consent still fits above the patient signature.
    var sigGapToConsent = 1.6;
    var minSigY = recEndY + 2.0;
    var sigY = recEndY + 5.0;
    if (sigY < minSigY) sigY = minSigY;

    // Keep this block away from the logo on the far right
    var sigX = left + 122;
    var sigEndX = right - 30;
    doc.setFont(undefined, 'normal');
    doc.setFontSize(6.0);

    function sigBlockBottomAt(y) {
      var sigLineY0 = y;
      var licLineY0 = sigLineY0 + 5.2;
      return licLineY0 + 2.5;
    }

    // Consent must start below signature block, and end above patient signature
    var sigBlockBottomY = sigBlockBottomAt(sigY);
    var consentY = sigBlockBottomY + sigGapToConsent;
    var consentYMaxAllowed = consentYMax;
    if (consentY > consentYMaxAllowed) {
      var overflow = consentY - consentYMaxAllowed;
      sigY -= overflow;
      if (sigY < minSigY) sigY = minSigY;
      sigBlockBottomY = sigBlockBottomAt(sigY);
      consentY = sigBlockBottomY + sigGapToConsent;
    }

    // Draw signatory block at final position
    var sigLineY = sigY;
    doc.line(sigX, sigLineY, sigEndX, sigLineY);
    doc.text('Dentist signature over printed name', sigX, sigLineY + 2.5);
    var licLineY = sigLineY + 5.2;
    var licLabel = 'License #:';
    var licLabelW = 0;
    try {
      if (typeof doc.getTextWidth === 'function') licLabelW = doc.getTextWidth(licLabel);
    } catch (e) {
      licLabelW = 0;
    }
    if (!licLabelW) licLabelW = licLabel.length * 1.6;

    // Same-row layout:  License #: _______________________
    var licTextY = licLineY - 0.4;
    doc.text(licLabel, sigX, licTextY);
    var licLineStartX = sigX + licLabelW + 0.8;
    if (licLineStartX > sigEndX - 10) licLineStartX = sigX + 30;
    doc.line(licLineStartX, licLineY, sigEndX, licLineY);

    // Draw consent below signatory
    doc.setFont(undefined, 'bold');
    doc.setFontSize(7.0);
    doc.text('INFORMED CONSENT:', left, consentY);
    doc.setFont(undefined, 'normal');
    doc.setFontSize(6.0);
    doc.text(consentLines, left, consentY + consentTitleGap);

    if (assets.ape2DataUrl) {
      var logoW = 26;
      var logoH = assets.ape2Aspect ? (logoW / assets.ape2Aspect) : 14;
      var logoX = right - logoW;
      var logoY = pageHeight - shared.footerH - 4 - logoH;
      doc.addImage(assets.ape2DataUrl, 'PNG', logoX, logoY, logoW, logoH);
    }
  }

  window.sdoGenerateDentalPdf = async function sdoGenerateDentalPdf(options) {
    options = options || {};
    var patientId = options.patientId;
    var title = safeText(options.title || 'Dental Form');
    var behavior = safeText(options.behavior || 'open');

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

  window.sdoGenerateDentalBulkPdf = async function sdoGenerateDentalBulkPdf(options) {
    options = options || {};
    var patientIds = Array.isArray(options.patientIds) ? options.patientIds : [];
    var title = safeText(options.title || 'Dental Forms (Bulk)');
    var behavior = safeText(options.behavior || 'open');

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

    var dentalAssets = { ape2DataUrl: ape2DataUrl, ape2Aspect: null };
    try {
      if (ape2DataUrl) {
        var s = await getImageSize(ape2DataUrl);
        dentalAssets.ape2Aspect = (s.width || 1) / (s.height || 1);
      }
    } catch (e) {
    }

    var first = true;
    for (var i = 0; i < patientIds.length; i++) {
      var pid = toNum(patientIds[i]);
      if (!pid) continue;

      if (!first) {
        doc.addPage();
      }
      first = false;
      drawHeaderFooter(doc, shared);

      try {
        var data = await fetchPdfData('dental', pid);
        renderDentalForm(doc, shared, data, dentalAssets);
      } catch (e) {
        var fallbackY = shared.headerY + shared.headerH + 15;
        doc.setFontSize(11);
        doc.setFont(undefined, 'bold');
        doc.setTextColor(200, 0, 0);
        doc.text('Failed to load data for PDF (Patient ID: ' + pid + ').', 14, fallbackY);
      }
    }

    var pdfBlob = doc.output('blob');
    var url = URL.createObjectURL(pdfBlob);
    if (behavior === 'link') {
      return {
        ok: true,
        blob: pdfBlob,
        blobUrl: url,
        filename: (title ? String(title) : 'dental-bulk') + '.pdf',
        title: title,
      };
    }
    openPdfInNewTabOrDownload(pdfBlob, title);
  };

  window.sdoRenderDentalForm = renderDentalForm;
  window.sdoDrawDentalHeaderFooter = drawHeaderFooter;
})();
