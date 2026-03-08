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

  window.sdoGenerateBlankPdf = async function sdoGenerateBlankPdf(options) {
    var jsPDF = window.jspdf && window.jspdf.jsPDF;
    if (!jsPDF) {
      alert('jsPDF is not loaded.');
      return;
    }

    var assets = window.SDO_MDD_PDF_ASSETS || {};
    var headerUrl = safeText(assets.headerUrl);
    var footerUrl = safeText(assets.footerUrl);
    var likertUrl = safeText(assets.likertUrl);
    var likert2Url = safeText(assets.likert2Url);
    var ape1LogoUrl = safeText(assets.ape1LogoUrl);

    if (!headerUrl || !footerUrl) {
      alert('PDF header/footer assets are not configured.');
      return;
    }

    var headerDataUrl, footerDataUrl, likertDataUrl, likert2DataUrl, ape1DataUrl;
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
          var y0 = shared.headerY + shared.headerH + 15;
          doc.setFontSize(12);
          doc.setFont(undefined, 'bold');
          doc.setTextColor(0);
          doc.text('DENTAL FORM', pageWidth / 2, y0, { align: 'center' });
          doc.setFont(undefined, 'normal');
          doc.setFontSize(10);
          var p = data.patient || {};
          doc.text('Name: ' + safeText(p.fullname), 14, y0 + 10);
          doc.text('School: ' + safeText(p.school), 14, y0 + 16);
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
    window.open(url, '_blank');
  };
})();
