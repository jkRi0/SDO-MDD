<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/auth.php';

require_login('dental', 'dental/assess.php');

$cfg = base_config();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$errors = [];

if ($id <= 0) {
    redirect('/dental/index.php');
}

try {
    $stmt = db()->prepare('SELECT id, school, level, entry_date, fullname, age, sex, address, contact_number, date_of_birth, civil_status, designation, region, division, district, hmo_provider FROM patients WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $patient = null;
}

if (!$patient) {
    redirect('/dental/index.php');
}

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

$divisionLevel = 'DepEd City Schools Division of Cabuyao';
$validLevels = ['Elementary', 'Secondary', $divisionLevel];

$postedLevel = (string)($patient['level'] ?? '');
if ($postedLevel === '' || !in_array($postedLevel, $validLevels, true)) $postedLevel = 'Elementary';
$schoolsForLevel = match ($postedLevel) {
    'Secondary' => $secondarySchools,
    $divisionLevel => [],
    default => $elementarySchools,
};

$storedSchool = (string)($patient['school'] ?? '');
if ($storedSchool !== '' && $postedLevel !== $divisionLevel && !in_array($storedSchool, $schoolsForLevel, true)) {
    $schoolsForLevel[] = $storedSchool;
}

$allowedToothCodes = [
    '✓',
    'D',
    'M',
    'MO',
    'IMP',
    'IMPL',
    'SP',
    'RF',
    'UN',
    'AM',
    'CO',
    'JC',
    'AB',
    'ATT',
    'P',
    'IN',
    'S',
    'F',
    'RM',
    'X',
    'XO',
];

$recOptions = [
    'Caries Free' => 'Caries Free',
    'Poor Oral Hygiene (Materia Alba, Calculus, Stain)' => 'Poor Oral Hygiene (Materia Alba, Calculus, Stain)',
    'Gingival Inflammation' => 'Gingival Inflammation',
    'Needs Oral Prophylaxis' => 'Needs Oral Prophylaxis',
    'No Dental Treatment Needed at Present' => 'No Dental Treatment Needed at Present',
    'For Endodontic Treatment' => 'For Endodontic Treatment',
    'Indicated for Restoration/Filling' => 'Indicated for Restoration/Filling',
    'Indicated for Extraction' => 'Indicated for Extraction',
    'Needs Prosthesis (Denture)' => 'Needs Prosthesis (Denture)',
    'For Orthodontic Consultation' => 'For Orthodontic Consultation',
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

$defaults = [
    'mh_allergy' => 0,
    'mh_asthma' => 0,
    'mh_bleeding_problem' => 0,
    'mh_heart_ailment' => 0,
    'mh_diabetes' => 0,
    'mh_epilepsy' => 0,
    'mh_kidney_disease' => 0,
    'mh_convulsion' => 0,
    'mh_fainting' => 0,
    'mh_others' => '',
    'exam_date' => '',
    'age_last_birthday' => '',
    'debris' => 0,
    'gingiva_inflammation' => 0,
    'calculus' => 0,
    'orthodontic_treatment' => 0,
    'occlusion' => '',
    'tmj_exam' => '',
    'tooth_chart' => [],
    'soft_tissue_exam' => '',
    'perio_gingival_inflammation' => '',
    'perio_soft_plaque' => '',
    'perio_hard_calc' => '',
    'perio_stains' => '',
    'home_care_effectiveness' => '',
    'periodontal_condition' => '',
    'periodontal_diagnosis' => '',
    'periodontitis' => '',
    'recommendations' => [],
    'recommendation_others' => '',
    'assessed_by_name' => current_user()['fullname'] ?? '',
    'license_no' => '',
];

try {
    $stmt = db()->prepare(
        'SELECT
            assessed_by_name, license_no,
            mh_allergy, mh_asthma, mh_bleeding_problem, mh_heart_ailment, mh_diabetes, mh_epilepsy, mh_kidney_disease, mh_convulsion, mh_fainting, mh_others,
            exam_date, age_last_birthday,
            debris, gingiva_inflammation, calculus, orthodontic_treatment, occlusion, tmj_exam,
            tooth_chart_json,
            soft_tissue_exam,
            perio_gingival_inflammation, perio_soft_plaque, perio_hard_calc, perio_stains,
            home_care_effectiveness, periodontal_condition, periodontal_diagnosis, periodontitis,
            recommendations_json, recommendation_others
         FROM dental_assessments
         WHERE patient_id = ?
         ORDER BY id DESC
         LIMIT 1'
    );
    $stmt->execute([$id]);
    $last = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($last) {
        foreach (['mh_allergy','mh_asthma','mh_bleeding_problem','mh_heart_ailment','mh_diabetes','mh_epilepsy','mh_kidney_disease','mh_convulsion','mh_fainting','debris','gingiva_inflammation','calculus','orthodontic_treatment'] as $k) {
            $defaults[$k] = (int)($last[$k] ?? 0) ? 1 : 0;
        }
        $defaults['mh_others'] = (string)($last['mh_others'] ?? '');
        $defaults['exam_date'] = (string)($last['exam_date'] ?? '');
        $defaults['age_last_birthday'] = $last['age_last_birthday'] === null ? '' : (string)$last['age_last_birthday'];
        $defaults['occlusion'] = (string)($last['occlusion'] ?? '');
        $defaults['tmj_exam'] = (string)($last['tmj_exam'] ?? '');
        $defaults['soft_tissue_exam'] = (string)($last['soft_tissue_exam'] ?? '');
        $defaults['perio_gingival_inflammation'] = (string)($last['perio_gingival_inflammation'] ?? '');
        $defaults['perio_soft_plaque'] = (string)($last['perio_soft_plaque'] ?? '');
        $defaults['perio_hard_calc'] = (string)($last['perio_hard_calc'] ?? '');
        $defaults['perio_stains'] = (string)($last['perio_stains'] ?? '');
        $defaults['home_care_effectiveness'] = (string)($last['home_care_effectiveness'] ?? '');
        $defaults['periodontal_condition'] = (string)($last['periodontal_condition'] ?? '');
        $defaults['periodontal_diagnosis'] = (string)($last['periodontal_diagnosis'] ?? '');
        $defaults['periodontitis'] = (string)($last['periodontitis'] ?? '');
        $defaults['recommendation_others'] = (string)($last['recommendation_others'] ?? '');
        $defaults['assessed_by_name'] = (string)($last['assessed_by_name'] ?? $defaults['assessed_by_name']);
        $defaults['license_no'] = (string)($last['license_no'] ?? '');

        $chart = (string)($last['tooth_chart_json'] ?? '');
        if ($chart !== '') {
            $decoded = json_decode($chart, true);
            if (is_array($decoded)) $defaults['tooth_chart'] = $decoded;
        }
        $recs = (string)($last['recommendations_json'] ?? '');
        if ($recs !== '') {
            $decoded = json_decode($recs, true);
            if (is_array($decoded)) $defaults['recommendations'] = array_values(array_filter($decoded, fn($v) => is_string($v) && $v !== ''));
        }
    }
} catch (Throwable $e) {
}

function compute_dmft(array $chart): array
{
    $present = 0;
    $d = 0;
    $m = 0;
    $f = 0;

    foreach ($chart as $code) {
        $c = strtoupper(trim((string)$code));
        if ($c === '') continue;

        if ($c === '✓') {
            $present++;
            continue;
        }

        $present++;

        if ($c === 'D') $d++;
        if ($c === 'M' || $c === 'X') $m++;
        if (in_array($c, ['F','AM','CO','JC','IN'], true)) $f++;
    }

    return [
        'teeth_present_count' => $present,
        'd_count' => $d,
        'm_count' => $m,
        'f_count' => $f,
        'dmft_total' => $d + $m + $f,
    ];
}

$dmft = compute_dmft($defaults['tooth_chart']);
$cariesFree = 0;
foreach ((array)($defaults['tooth_chart'] ?? []) as $code) {
    $c = strtoupper(trim((string)$code));
    if ($c === 'V') $c = '✓';
    if ($c === '✓') $cariesFree++;
}

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

    $d = [
        'mh_allergy' => isset($_POST['mh_allergy']) ? 1 : 0,
        'mh_asthma' => isset($_POST['mh_asthma']) ? 1 : 0,
        'mh_bleeding_problem' => isset($_POST['mh_bleeding_problem']) ? 1 : 0,
        'mh_heart_ailment' => isset($_POST['mh_heart_ailment']) ? 1 : 0,
        'mh_diabetes' => isset($_POST['mh_diabetes']) ? 1 : 0,
        'mh_epilepsy' => isset($_POST['mh_epilepsy']) ? 1 : 0,
        'mh_kidney_disease' => isset($_POST['mh_kidney_disease']) ? 1 : 0,
        'mh_convulsion' => isset($_POST['mh_convulsion']) ? 1 : 0,
        'mh_fainting' => isset($_POST['mh_fainting']) ? 1 : 0,
        'mh_others' => trim((string)($_POST['mh_others'] ?? '')),
        'exam_date' => (string)($_POST['exam_date'] ?? ''),
        'age_last_birthday' => trim((string)($_POST['age_last_birthday'] ?? '')),
        'debris' => isset($_POST['debris']) ? 1 : 0,
        'gingiva_inflammation' => isset($_POST['gingiva_inflammation']) ? 1 : 0,
        'calculus' => isset($_POST['calculus']) ? 1 : 0,
        'orthodontic_treatment' => isset($_POST['orthodontic_treatment']) ? 1 : 0,
        'occlusion' => trim((string)($_POST['occlusion'] ?? '')),
        'tmj_exam' => trim((string)($_POST['tmj_exam'] ?? '')),
        'soft_tissue_exam' => trim((string)($_POST['soft_tissue_exam'] ?? '')),
        'perio_gingival_inflammation' => trim((string)($_POST['perio_gingival_inflammation'] ?? '')),
        'perio_soft_plaque' => trim((string)($_POST['perio_soft_plaque'] ?? '')),
        'perio_hard_calc' => trim((string)($_POST['perio_hard_calc'] ?? '')),
        'perio_stains' => trim((string)($_POST['perio_stains'] ?? '')),
        'home_care_effectiveness' => trim((string)($_POST['home_care_effectiveness'] ?? '')),
        'periodontal_condition' => trim((string)($_POST['periodontal_condition'] ?? '')),
        'periodontal_diagnosis' => trim((string)($_POST['periodontal_diagnosis'] ?? '')),
        'periodontitis' => trim((string)($_POST['periodontitis'] ?? '')),
        'recommendations' => (array)($_POST['recommendations'] ?? []),
        'recommendation_others' => trim((string)($_POST['recommendation_others'] ?? '')),
        'assessed_by_name' => trim((string)($_POST['assessed_by_name'] ?? '')),
        'license_no' => trim((string)($_POST['license_no'] ?? '')),
    ];

    if ($p['level'] === '' || !in_array($p['level'], $validLevels, true)) {
        $errors[] = 'Invalid level.';
    }

    if (!$errors) {
        if ($p['level'] !== $divisionLevel) {
            $validSchoolList = $p['level'] === 'Secondary' ? $secondarySchools : $elementarySchools;
            if ($p['school'] === '' || !in_array($p['school'], $validSchoolList, true)) {
                $errors[] = 'Please select a valid school.';
            }
        } else {
            $p['school'] = 'N/A';
        }
    }

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

    $chart = (array)($_POST['tooth_chart'] ?? []);
    $cleanChart = [];
    foreach ($chart as $tooth => $code) {
        $t = preg_replace('/[^0-9]/', '', (string)$tooth);
        if ($t === '') continue;
        $c = strtoupper(trim((string)$code));
        if ($c === '') continue;
        if ($c === 'V') $c = '✓';
        $cleanChart[$t] = $c;
    }

    foreach ($cleanChart as $t => $c) {
        if ($c !== '✓' && !in_array($c, $allowedToothCodes, true)) {
            $errors[] = 'Invalid tooth condition code: ' . $c;
            break;
        }
    }

    $validOcclusion = ['', 'Class 1', 'Class 2', 'Class 3'];
    if (!in_array($d['occlusion'], $validOcclusion, true)) $errors[] = 'Invalid occlusion.';

    $validTmj = ['', 'Pain', 'Popping', 'Deviation', 'Tooth wear'];
    if (!in_array($d['tmj_exam'], $validTmj, true)) $errors[] = 'Invalid TMJ exam.';

    $validSoft = ['', 'Lips', 'Floor of mouth', 'Palate', 'Tongue', 'Neck & nodes'];
    if (!in_array($d['soft_tissue_exam'], $validSoft, true)) $errors[] = 'Invalid soft tissue exam.';

    $validGI = ['', 'Slight', 'Moderate', 'Severe'];
    if (!in_array($d['perio_gingival_inflammation'], $validGI, true)) $errors[] = 'Invalid gingival inflammation.';

    $validSlightHeavy = ['', 'Slight', 'Moderate', 'Heavy'];
    if (!in_array($d['perio_soft_plaque'], $validSlightHeavy, true)) $errors[] = 'Invalid soft plaque buildup.';

    $validLightHeavy = ['', 'Light', 'Moderate', 'Heavy'];
    if (!in_array($d['perio_hard_calc'], $validLightHeavy, true)) $errors[] = 'Invalid hard calc buildup.';
    if (!in_array($d['perio_stains'], $validLightHeavy, true)) $errors[] = 'Invalid stains.';

    $validGFP = ['', 'Good', 'Fair', 'Poor'];
    if (!in_array($d['home_care_effectiveness'], $validGFP, true)) $errors[] = 'Invalid home care effectiveness.';
    if (!in_array($d['periodontal_condition'], $validGFP, true)) $errors[] = 'Invalid periodontal condition.';

    $validDx = ['', 'Normal', 'Gingivitis'];
    if (!in_array($d['periodontal_diagnosis'], $validDx, true)) $errors[] = 'Invalid periodontal diagnosis.';

    $validPerio = ['', 'Early', 'Moderate', 'Advanced'];
    if (!in_array($d['periodontitis'], $validPerio, true)) $errors[] = 'Invalid periodontitis.';

    if ($d['age_last_birthday'] !== '' && (!ctype_digit($d['age_last_birthday']) || (int)$d['age_last_birthday'] > 150)) {
        $errors[] = 'Age last birthday must be a valid number.';
    }

    if ($d['assessed_by_name'] === '') $errors[] = 'Assessed by (Name) is required.';
    if ($d['license_no'] === '') $errors[] = 'License No. is required.';

    $d['recommendations'] = array_values(array_filter($d['recommendations'], fn($v) => is_string($v) && $v !== ''));
    foreach ($d['recommendations'] as $v) {
        if (!array_key_exists($v, $recOptions)) {
            $errors[] = 'Invalid recommendation.';
            break;
        }
    }

    if (!$errors) {
        $dmft = compute_dmft($cleanChart);
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

            $stmt = db()->prepare(
                'INSERT INTO dental_assessments (
                    patient_id, assessed_by_name, license_no,
                    mh_allergy, mh_asthma, mh_bleeding_problem, mh_heart_ailment, mh_diabetes, mh_epilepsy, mh_kidney_disease, mh_convulsion, mh_fainting, mh_others,
                    exam_date, age_last_birthday,
                    debris, gingiva_inflammation, calculus, orthodontic_treatment, occlusion, tmj_exam,
                    tooth_chart_json, teeth_present_count, d_count, m_count, f_count, dmft_total,
                    soft_tissue_exam,
                    perio_gingival_inflammation, perio_soft_plaque, perio_hard_calc, perio_stains,
                    home_care_effectiveness, periodontal_condition, periodontal_diagnosis, periodontitis,
                    recommendations_json, recommendation_others
                 ) VALUES (
                    ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?
                 )'
            );

            $stmt->execute([
                $id,
                $d['assessed_by_name'],
                $d['license_no'],
                $d['mh_allergy'],
                $d['mh_asthma'],
                $d['mh_bleeding_problem'],
                $d['mh_heart_ailment'],
                $d['mh_diabetes'],
                $d['mh_epilepsy'],
                $d['mh_kidney_disease'],
                $d['mh_convulsion'],
                $d['mh_fainting'],
                $d['mh_others'] === '' ? null : $d['mh_others'],
                $d['exam_date'] === '' ? null : $d['exam_date'],
                $d['age_last_birthday'] === '' ? null : (int)$d['age_last_birthday'],
                $d['debris'],
                $d['gingiva_inflammation'],
                $d['calculus'],
                $d['orthodontic_treatment'],
                $d['occlusion'] === '' ? null : $d['occlusion'],
                $d['tmj_exam'] === '' ? null : $d['tmj_exam'],
                json_encode($cleanChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                $dmft['teeth_present_count'],
                $dmft['d_count'],
                $dmft['m_count'],
                $dmft['f_count'],
                $dmft['dmft_total'],
                $d['soft_tissue_exam'] === '' ? null : $d['soft_tissue_exam'],
                $d['perio_gingival_inflammation'] === '' ? null : $d['perio_gingival_inflammation'],
                $d['perio_soft_plaque'] === '' ? null : $d['perio_soft_plaque'],
                $d['perio_hard_calc'] === '' ? null : $d['perio_hard_calc'],
                $d['perio_stains'] === '' ? null : $d['perio_stains'],
                $d['home_care_effectiveness'] === '' ? null : $d['home_care_effectiveness'],
                $d['periodontal_condition'] === '' ? null : $d['periodontal_condition'],
                $d['periodontal_diagnosis'] === '' ? null : $d['periodontal_diagnosis'],
                $d['periodontitis'] === '' ? null : $d['periodontitis'],
                json_encode($d['recommendations'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                $d['recommendation_others'] === '' ? null : $d['recommendation_others'],
            ]);

            $stmt = db()->prepare('UPDATE patients SET dental_checked = 1, dental_checked_at = NOW() WHERE id = ?');
            $stmt->execute([$id]);

            db()->commit();
            redirect('/dental/index.php');
        } catch (Throwable $e) {
            try { db()->rollBack(); } catch (Throwable $e2) {}
            $errors[] = 'Failed to save dental assessment. Please try again.';
        }
    }

    $patient = array_merge($patient, $p);
    $defaults = array_merge($defaults, $d);
    $defaults['tooth_chart'] = $cleanChart;
    $dmft = compute_dmft($defaults['tooth_chart']);
}

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dental Assessment - <?= e($cfg['app_name']) ?></title>
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
              <div class="brand h5 mb-0 text-white fw-bold lh-1">Dental Assessment</div>
              <div class="small text-white-50 lh-1 mt-1">School Health Section</div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-auto mt-2 mt-md-0">
          <div class="d-flex align-items-center gap-2 justify-content-md-end">
            <a href="<?= url('/dental/index.php') ?>" class="btn btn-light btn-sm fw-bold px-3" style="border-radius: 8px;">Back</a>
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
            <div class="h5 mb-2 text-primary">PERSONAL DETAILS</div>
            <div class="row g-3">
              <div class="col-12 col-md-4">
                <label class="form-label">Level</label>
                <select class="form-select" name="level" id="levelSelect" required>
                  <option value="Elementary" <?= $postedLevel === 'Elementary' ? 'selected' : '' ?>>Elementary</option>
                  <option value="Secondary" <?= $postedLevel === 'Secondary' ? 'selected' : '' ?>>Secondary</option>
                  <option value="<?= e($divisionLevel) ?>" <?= $postedLevel === $divisionLevel ? 'selected' : '' ?>><?= e($divisionLevel) ?></option>
                </select>
              </div>
              <div class="col-12 col-md-8" id="schoolGroup">
                <label class="form-label">School</label>
                <select class="form-select" name="school" id="schoolSelect" <?= $postedLevel === $divisionLevel ? '' : 'required' ?> <?= $postedLevel === $divisionLevel ? 'disabled' : '' ?>>
                  <?php if ($postedLevel === $divisionLevel): ?>
                    <option value="">Not applicable</option>
                  <?php else: ?>
                    <option value="">Select school</option>
                    <?php foreach ($schoolsForLevel as $s): if ($s === '') continue; ?>
                      <option value="<?= e($s) ?>" <?= ((string)($patient['school'] ?? '') === $s) ? 'selected' : '' ?>><?= e($s) ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <div class="form-text" id="schoolHelp" style="display: <?= $postedLevel === $divisionLevel ? 'block' : 'none' ?>;">Not applicable for division-level entry.</div>
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
                <input class="form-control" type="number" min="0" max="150" step="1" name="age" value="<?= e((string)($patient['age'] ?? '')) ?>" required>
              </div>

              <div class="col-12 col-md-3">
                <label class="form-label">Sex</label>
                <select class="form-select" name="sex" required>
                  <option value="" <?= ((string)($patient['sex'] ?? '') === '') ? 'selected' : '' ?>>Select</option>
                  <option value="Male" <?= ((string)($patient['sex'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                  <option value="Female" <?= ((string)($patient['sex'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                  <option value="Others" <?= ((string)($patient['sex'] ?? '') === 'Others') ? 'selected' : '' ?>>Others</option>
                </select>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label">Address</label>
                <input class="form-control" name="address" value="<?= e((string)($patient['address'] ?? '')) ?>" required>
              </div>

              <div class="col-12 col-md-4">
                <label class="form-label">Contact Number</label>
                <input class="form-control" name="contact_number" value="<?= e((string)($patient['contact_number'] ?? '')) ?>" type="tel" inputmode="tel" maxlength="30" oninput="this.value=this.value.replace(/[^0-9+()\-\s]/g,'');">
              </div>

              <div class="col-12 col-md-4">
                <label class="form-label">Date of Birth</label>
                <input class="form-control" type="date" name="date_of_birth" value="<?= e((string)($patient['date_of_birth'] ?? '')) ?>" required>
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label">Civil Status</label>
                <select class="form-select" name="civil_status" required>
                  <option value="" <?= ((string)($patient['civil_status'] ?? '') === '') ? 'selected' : '' ?>>Select</option>
                  <option value="Single" <?= ((string)($patient['civil_status'] ?? '') === 'Single') ? 'selected' : '' ?>>Single</option>
                  <option value="Married" <?= ((string)($patient['civil_status'] ?? '') === 'Married') ? 'selected' : '' ?>>Married</option>
                  <option value="Widowed" <?= ((string)($patient['civil_status'] ?? '') === 'Widowed') ? 'selected' : '' ?>>Widowed</option>
                  <option value="Divorced" <?= ((string)($patient['civil_status'] ?? '') === 'Divorced') ? 'selected' : '' ?>>Divorced</option>
                  <option value="Separated" <?= ((string)($patient['civil_status'] ?? '') === 'Separated') ? 'selected' : '' ?>>Separated</option>
                  <option value="Registered Partnership/Civil Union" <?= ((string)($patient['civil_status'] ?? '') === 'Registered Partnership/Civil Union') ? 'selected' : '' ?>>Registered Partnership/Civil Union</option>
                  <option value="Common-Law/Cohabitating" <?= ((string)($patient['civil_status'] ?? '') === 'Common-Law/Cohabitating') ? 'selected' : '' ?>>Common-Law/Cohabitating</option>
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
                <input class="form-control" name="region" value="<?= e((string)($patient['region'] ?? '')) ?>" required>
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label">Division</label>
                <input class="form-control" name="division" value="<?= e((string)($patient['division'] ?? '')) ?>" required>
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label">District</label>
                <input class="form-control" name="district" id="districtInput" value="<?= e((string)($patient['district'] ?? '')) ?>">
              </div>

              <div class="col-12">
                <label class="form-label">HMO Provider</label>
                <input class="form-control" name="hmo_provider" value="<?= e((string)($patient['hmo_provider'] ?? '')) ?>">
              </div>
            </div>
          </div>
<br>
          <div>
            <div class="h5 mb-2 text-primary">MEDICAL HISTORY</div>
            <div class="row g-2">
              <div class="col-12 col-md-4"><label class="form-check"><input class="form-check-input" type="checkbox" name="mh_allergy" <?= ((int)($defaults['mh_allergy'] ?? 0) === 1) ? 'checked' : '' ?>> <span class="form-check-label">Allergy</span></label></div>
              <div class="col-12 col-md-4"><label class="form-check"><input class="form-check-input" type="checkbox" name="mh_asthma" <?= ((int)($defaults['mh_asthma'] ?? 0) === 1) ? 'checked' : '' ?>> <span class="form-check-label">Asthma</span></label></div>
              <div class="col-12 col-md-4"><label class="form-check"><input class="form-check-input" type="checkbox" name="mh_bleeding_problem" <?= ((int)($defaults['mh_bleeding_problem'] ?? 0) === 1) ? 'checked' : '' ?>> <span class="form-check-label">Bleeding Problem</span></label></div>
              <div class="col-12 col-md-4"><label class="form-check"><input class="form-check-input" type="checkbox" name="mh_heart_ailment" <?= ((int)($defaults['mh_heart_ailment'] ?? 0) === 1) ? 'checked' : '' ?>> <span class="form-check-label">Heart Ailment</span></label></div>
              <div class="col-12 col-md-4"><label class="form-check"><input class="form-check-input" type="checkbox" name="mh_diabetes" <?= ((int)($defaults['mh_diabetes'] ?? 0) === 1) ? 'checked' : '' ?>> <span class="form-check-label">Diabetes</span></label></div>
              <div class="col-12 col-md-4"><label class="form-check"><input class="form-check-input" type="checkbox" name="mh_epilepsy" <?= ((int)($defaults['mh_epilepsy'] ?? 0) === 1) ? 'checked' : '' ?>> <span class="form-check-label">Epilepsy</span></label></div>
              <div class="col-12 col-md-4"><label class="form-check"><input class="form-check-input" type="checkbox" name="mh_kidney_disease" <?= ((int)($defaults['mh_kidney_disease'] ?? 0) === 1) ? 'checked' : '' ?>> <span class="form-check-label">Kidney Disease</span></label></div>
              <div class="col-12 col-md-4"><label class="form-check"><input class="form-check-input" type="checkbox" name="mh_convulsion" <?= ((int)($defaults['mh_convulsion'] ?? 0) === 1) ? 'checked' : '' ?>> <span class="form-check-label">Convulsion</span></label></div>
              <div class="col-12 col-md-4"><label class="form-check"><input class="form-check-input" type="checkbox" name="mh_fainting" <?= ((int)($defaults['mh_fainting'] ?? 0) === 1) ? 'checked' : '' ?>> <span class="form-check-label">Fainting</span></label></div>
              <div class="col-12">
                <label class="form-label">Others (Specify)</label>
                <textarea class="form-control" name="mh_others" rows="2"><?= e((string)($defaults['mh_others'] ?? '')) ?></textarea>
              </div>
            </div>
          </div>
<br>
          <div>
            <div class="h5 mb-2 text-primary">ORAL HEALTH CONDITION</div>
            <div class="row g-3">
              <div class="col-12 col-md-4">
                <label class="form-label">Date of Examination</label>
                <input class="form-control" type="date" name="exam_date" value="<?= e((string)($defaults['exam_date'] ?? '')) ?>">
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label">Age last Birthday</label>
                <input class="form-control" type="number" name="age_last_birthday" min="0" max="150" step="1" value="<?= e((string)($defaults['age_last_birthday'] ?? '')) ?>">
              </div>
              <div class="col-12 col-md-4"></div>

              <div class="col-12 col-md-3"><label class="form-check"><input class="form-check-input" type="checkbox" name="debris" <?= ((int)($defaults['debris'] ?? 0) === 1) ? 'checked' : '' ?>> <span class="form-check-label">Presence of debris</span></label></div>
              <div class="col-12 col-md-3"><label class="form-check"><input class="form-check-input" type="checkbox" name="gingiva_inflammation" <?= ((int)($defaults['gingiva_inflammation'] ?? 0) === 1) ? 'checked' : '' ?>> <span class="form-check-label">Inflammation of Gingiva</span></label></div>
              <div class="col-12 col-md-3"><label class="form-check"><input class="form-check-input" type="checkbox" name="calculus" <?= ((int)($defaults['calculus'] ?? 0) === 1) ? 'checked' : '' ?>> <span class="form-check-label">Presence of Calculus</span></label></div>
              <div class="col-12 col-md-3"><label class="form-check"><input class="form-check-input" type="checkbox" name="orthodontic_treatment" <?= ((int)($defaults['orthodontic_treatment'] ?? 0) === 1) ? 'checked' : '' ?>> <span class="form-check-label">Under Orthodontic Treatment</span></label></div>

              <div class="col-12 col-md-6">
                <label class="form-label">Occlusion</label>
                <div class="d-flex flex-wrap gap-3" style="min-height: 38px;">
                  <?php foreach (['Class 1','Class 2','Class 3'] as $v): ?>
                    <label class="form-check"><input class="form-check-input" type="radio" name="occlusion" value="<?= e($v) ?>" <?= ((string)($defaults['occlusion'] ?? '') === $v) ? 'checked' : '' ?>> <span class="form-check-label"><?= e($v) ?></span></label>
                  <?php endforeach; ?>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">T.M.J. Exam</label>
                <div class="d-flex flex-wrap gap-3" style="min-height: 38px;">
                  <?php foreach (['Pain','Popping','Deviation','Tooth wear'] as $v): ?>
                    <label class="form-check"><input class="form-check-input" type="radio" name="tmj_exam" value="<?= e($v) ?>" <?= ((string)($defaults['tmj_exam'] ?? '') === $v) ? 'checked' : '' ?>> <span class="form-check-label"><?= e($v) ?></span></label>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
<br>
          <div>
            <div class="h5 mb-2 text-primary">DENTAL HEALTH STATUS</div>

            <div class="mb-3 small">
              <div class="row g-2">
                <div class="col-6 col-md-3">
                  <div><strong>✓ (V)</strong> - Present Teeth</div>
                  <div class="mt-1"><strong>D</strong> - Decayed<br>(Caries ind. For filling)</div>
                  <div class="mt-1"><strong>M</strong> - Missing<br>(Due to Caries)</div>
                  <div class="mt-1"><strong>MO</strong> - Missing<br>(Due to other causes)</div>
                </div>
                <div class="col-6 col-md-3">
                  <div><strong>IMP</strong> - Impacted Tooth</div>
                  <div class="mt-1"><strong>SP</strong> - Supernumerary Tooth</div>
                  <div class="mt-1"><strong>RF</strong> - Root Fragment</div>
                  <div class="mt-1"><strong>UN</strong> - Unerupted</div>
                </div>
                <div class="col-6 col-md-3">
                  <div><strong>AM</strong> - Amalgam Filling</div>
                  <div class="mt-1"><strong>CO</strong> - Composite Filling</div>
                  <div class="mt-1"><strong>JC</strong> - Jacket Crown</div>
                  <div class="mt-1"><strong>AB</strong> - Abutment</div>
                </div>
                <div class="col-6 col-md-3">
                  <div><strong>ATT</strong> - Attachment</div>
                  <div class="mt-1"><strong>P</strong> - Pontic</div>
                  <div class="mt-1"><strong>IN</strong> - Inlay</div>
                  <div class="mt-1"><strong>IMPL</strong> - Implant</div>
                </div>
                <div class="col-6 col-md-3">
                  <div><strong>S</strong> - Sealants</div>
                  <div class="mt-1"><strong>F</strong> - Filled</div>
                  <div class="mt-1"><strong>RM</strong> - Removable Denture</div>
                </div>
                <div class="col-6 col-md-3">
                  <div><strong>X</strong> - Extraction<br>(due to Caries)</div>
                  <div class="mt-1"><strong>XO</strong> - Extraction<br>(due to other causes)</div>
                </div>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered align-middle text-center" style="table-layout: fixed; min-width: 820px;">
                <tbody>
                  <?php
                    $upperRight = ['18','17','16','15','14','13','12','11'];
                    $upperLeft = ['21','22','23','24','25','26','27','28'];
                    $lowerLeft = ['38','37','36','35','34','33','32','31'];
                    $lowerRight = ['41','42','43','44','45','46','47','48'];

                    $upperNumbers = array_merge($upperRight, $upperLeft);
                    $lowerNumbers = ['48','47','46','45','44','43','42','41','31','32','33','34','35','36','37','38'];

                    $toothSvg = '<svg width="34" height="34" viewBox="0 0 48 48" aria-hidden="true" focusable="false">'
                      . '<circle cx="24" cy="24" r="20" fill="none" stroke="currentColor" stroke-width="2" />'
                      . '<circle cx="24" cy="24" r="10" fill="none" stroke="currentColor" stroke-width="2" />'
                      . '<path d="M24 4v40M4 24h40" fill="none" stroke="currentColor" stroke-width="2" />'
                      . '</svg>';
                  ?>

                  <tr>
                    <?php foreach ($upperNumbers as $t): ?>
                      <td style="padding: 6px 4px; min-width: 48px;">
                        <input class="form-control form-control-sm tooth-code text-center" name="tooth_chart[<?= e($t) ?>]" value="<?= e((string)($defaults['tooth_chart'][$t] ?? '')) ?>" maxlength="4" style="max-width: 54px; margin: 0 auto; height: 34px; font-size: 16px;">
                      </td>
                    <?php endforeach; ?>
                  </tr>
                  <tr>
                    <?php foreach ($upperNumbers as $t): ?>
                      <td style="padding: 6px 4px; min-width: 48px;">
                        <div class="text-secondary" style="line-height: 1; display: flex; align-items: center; justify-content: center; min-height: 40px;">
                          <?= $toothSvg ?>
                        </div>
                      </td>
                    <?php endforeach; ?>
                  </tr>
                  <tr>
                    <?php foreach ($upperNumbers as $t): ?>
                      <td style="padding: 6px 4px; min-width: 48px;"><div class="fw-bold" style="font-size: 1.05rem; line-height: 1; white-space: nowrap;">
                        <?= e($t) ?>
                      </div></td>
                    <?php endforeach; ?>
                  </tr>

                  <tr>
                    <?php foreach ($lowerNumbers as $t): ?>
                      <td style="padding: 6px 4px; min-width: 48px;"><div class="fw-bold" style="font-size: 1.05rem; line-height: 1; white-space: nowrap;">
                        <?= e($t) ?>
                      </div></td>
                    <?php endforeach; ?>
                  </tr>
                  <tr>
                    <?php foreach ($lowerNumbers as $t): ?>
                      <td style="padding: 6px 4px; min-width: 48px;">
                        <div class="text-secondary" style="line-height: 1; display: flex; align-items: center; justify-content: center; min-height: 40px;">
                          <?= $toothSvg ?>
                        </div>
                      </td>
                    <?php endforeach; ?>
                  </tr>
                  <tr>
                    <?php foreach ($lowerNumbers as $t): ?>
                      <td style="padding: 6px 4px; min-width: 48px;">
                        <input class="form-control form-control-sm tooth-code text-center" name="tooth_chart[<?= e($t) ?>]" value="<?= e((string)($defaults['tooth_chart'][$t] ?? '')) ?>" maxlength="4" style="max-width: 54px; margin: 0 auto; height: 34px; font-size: 16px;">
                      </td>
                    <?php endforeach; ?>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="row g-3">
              <div class="col-12 col-sm-6 col-md-2"><div class="p-3 bg-light h-100 d-flex flex-column" style="border-radius: 12px;"><div class="small text-secondary" style="min-height: 38px;">Total Present Teeth</div><div class="h5 mb-0 mt-auto" id="teethPresent"><?= (int)$dmft['teeth_present_count'] ?></div></div></div>
              <div class="col-12 col-sm-6 col-md-2"><div class="p-3 bg-light h-100 d-flex flex-column" style="border-radius: 12px;"><div class="small text-secondary" style="min-height: 38px;">Caries-Free Teeth (✓)</div><div class="h5 mb-0 mt-auto" id="cariesFree"><?= (int)$cariesFree ?></div></div></div>
              <div class="col-12 col-sm-6 col-md-2"><div class="p-3 bg-light h-100 d-flex flex-column" style="border-radius: 12px;"><div class="small text-secondary" style="min-height: 38px;">Decayed (D)</div><div class="h5 mb-0 mt-auto" id="dmftD"><?= (int)$dmft['d_count'] ?></div></div></div>
              <div class="col-12 col-sm-6 col-md-2"><div class="p-3 bg-light h-100 d-flex flex-column" style="border-radius: 12px;"><div class="small text-secondary" style="min-height: 38px;">Missing/Extracted (M/X)</div><div class="h5 mb-0 mt-auto" id="dmftM"><?= (int)$dmft['m_count'] ?></div></div></div>
              <div class="col-12 col-sm-6 col-md-2"><div class="p-3 bg-light h-100 d-flex flex-column" style="border-radius: 12px;"><div class="small text-secondary" style="min-height: 38px;">Filled (F/AM/CO/JC/IN)</div><div class="h5 mb-0 mt-auto" id="dmftF"><?= (int)$dmft['f_count'] ?></div></div></div>
              <div class="col-12 col-sm-6 col-md-2"><div class="p-3 bg-primary text-white h-100 d-flex flex-column" style="border-radius: 12px;"><div class="small text-white-50" style="min-height: 38px;">DMFT Total</div><div class="h5 mb-0 mt-auto" id="dmftTotal"><?= (int)$dmft['dmft_total'] ?></div></div></div>
            </div>
          </div>
<br>
          <div>
            <div class="h5 mb-2 text-primary">INITIAL SOFT TISSUE EXAM</div>
            <div class="d-flex flex-wrap gap-3">
              <?php foreach (['Lips','Floor of mouth','Palate','Tongue','Neck & nodes'] as $v): ?>
                <label class="form-check"><input class="form-check-input" type="radio" name="soft_tissue_exam" value="<?= e($v) ?>" <?= ((string)($defaults['soft_tissue_exam'] ?? '') === $v) ? 'checked' : '' ?>> <span class="form-check-label"><?= e($v) ?></span></label>
              <?php endforeach; ?>
            </div>
          </div>
<br>
          <div>
            <div class="h5 mb-2 text-primary">INITIAL PERIODONTAL EXAM</div>
            <div class="row g-3">
              <div class="col-12 col-md-4">
                <label class="form-label">Gingival Inflammation</label>
                <select class="form-select" name="perio_gingival_inflammation">
                  <option value="" <?= ((string)($defaults['perio_gingival_inflammation'] ?? '') === '') ? 'selected' : '' ?>></option>
                  <?php foreach (['Slight','Moderate','Severe'] as $v): ?>
                    <option value="<?= e($v) ?>" <?= ((string)($defaults['perio_gingival_inflammation'] ?? '') === $v) ? 'selected' : '' ?>><?= e($v) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label">Soft Plaque Buildup</label>
                <select class="form-select" name="perio_soft_plaque">
                  <option value="" <?= ((string)($defaults['perio_soft_plaque'] ?? '') === '') ? 'selected' : '' ?>></option>
                  <?php foreach (['Slight','Moderate','Heavy'] as $v): ?>
                    <option value="<?= e($v) ?>" <?= ((string)($defaults['perio_soft_plaque'] ?? '') === $v) ? 'selected' : '' ?>><?= e($v) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label">Hard Calc Buildup</label>
                <select class="form-select" name="perio_hard_calc">
                  <option value="" <?= ((string)($defaults['perio_hard_calc'] ?? '') === '') ? 'selected' : '' ?>></option>
                  <?php foreach (['Light','Moderate','Heavy'] as $v): ?>
                    <option value="<?= e($v) ?>" <?= ((string)($defaults['perio_hard_calc'] ?? '') === $v) ? 'selected' : '' ?>><?= e($v) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label">Stains</label>
                <select class="form-select" name="perio_stains">
                  <option value="" <?= ((string)($defaults['perio_stains'] ?? '') === '') ? 'selected' : '' ?>></option>
                  <?php foreach (['Light','Moderate','Heavy'] as $v): ?>
                    <option value="<?= e($v) ?>" <?= ((string)($defaults['perio_stains'] ?? '') === $v) ? 'selected' : '' ?>><?= e($v) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label">Home Care Effectiveness</label>
                <select class="form-select" name="home_care_effectiveness">
                  <option value="" <?= ((string)($defaults['home_care_effectiveness'] ?? '') === '') ? 'selected' : '' ?>></option>
                  <?php foreach (['Good','Fair','Poor'] as $v): ?>
                    <option value="<?= e($v) ?>" <?= ((string)($defaults['home_care_effectiveness'] ?? '') === $v) ? 'selected' : '' ?>><?= e($v) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label">Periodontal Condition</label>
                <select class="form-select" name="periodontal_condition">
                  <option value="" <?= ((string)($defaults['periodontal_condition'] ?? '') === '') ? 'selected' : '' ?>></option>
                  <?php foreach (['Good','Fair','Poor'] as $v): ?>
                    <option value="<?= e($v) ?>" <?= ((string)($defaults['periodontal_condition'] ?? '') === $v) ? 'selected' : '' ?>><?= e($v) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Periodontal Diagnosis</label>
                <select class="form-select" name="periodontal_diagnosis">
                  <option value="" <?= ((string)($defaults['periodontal_diagnosis'] ?? '') === '') ? 'selected' : '' ?>></option>
                  <?php foreach (['Normal','Gingivitis'] as $v): ?>
                    <option value="<?= e($v) ?>" <?= ((string)($defaults['periodontal_diagnosis'] ?? '') === $v) ? 'selected' : '' ?>><?= e($v) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Periodontitis</label>
                <select class="form-select" name="periodontitis">
                  <option value="" <?= ((string)($defaults['periodontitis'] ?? '') === '') ? 'selected' : '' ?>></option>
                  <?php foreach (['Early','Moderate','Advanced'] as $v): ?>
                    <option value="<?= e($v) ?>" <?= ((string)($defaults['periodontitis'] ?? '') === $v) ? 'selected' : '' ?>><?= e($v) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
<br>
          <div>
            <div class="h5 mb-2 text-primary">DENTAL/ORAL EXAMINATION REVEALED THE FOLLOWING CONDITIONS AND RECOMMENDATIONS</div>
            <div class="row g-2">
              <?php foreach ($recOptions as $k => $label): ?>
                <div class="col-12 col-md-6">
                  <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="recommendations[]" value="<?= e($k) ?>" <?= in_array($k, (array)($defaults['recommendations'] ?? []), true) ? 'checked' : '' ?>>
                    <span class="form-check-label"><?= e($label) ?></span>
                  </label>
                </div>
              <?php endforeach; ?>
              <div class="col-12">
                <label class="form-label">Others (Specify)</label>
                <input class="form-control" name="recommendation_others" value="<?= e((string)($defaults['recommendation_others'] ?? '')) ?>">
              </div>
            </div>
          </div>
<br>
          <div>
            <div class="h5 mb-2 text-primary">Assessed by</div>
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

          <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Save Assessment</button>
            <a class="btn btn-outline-secondary" href="<?= url('/dental/index.php') ?>">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </main>

  <script>
    (function(){
      var allowed = <?php echo json_encode($allowedToothCodes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
      var inputs = document.querySelectorAll('.tooth-code');

      var elD = document.getElementById('dmftD');
      var elM = document.getElementById('dmftM');
      var elF = document.getElementById('dmftF');
      var elT = document.getElementById('dmftTotal');
      var elPresent = document.getElementById('teethPresent');
      var elCariesFree = document.getElementById('cariesFree');

      function normalizeCode(v){
        var up = String(v || '').trim().toUpperCase();
        if (up === 'V') up = '✓';
        return up;
      }

      function recalcDmft(){
        var present = 0;
        var cariesFree = 0;
        var d = 0, m = 0, f = 0;
        inputs.forEach(function(inp){
          var c = normalizeCode(inp.value);
          if (!c) return;
          present++;
          if (c === '✓') {
            cariesFree++;
            return;
          }
          if (c === 'D') d++;
          if (c === 'M' || c === 'X') m++;
          if (['F','AM','CO','JC','IN'].indexOf(c) !== -1) f++;
        });
        if (elPresent) elPresent.textContent = String(present);
        if (elCariesFree) elCariesFree.textContent = String(cariesFree);
        if (elD) elD.textContent = String(d);
        if (elM) elM.textContent = String(m);
        if (elF) elF.textContent = String(f);
        if (elT) elT.textContent = String(d + m + f);
      }

      inputs.forEach(function(inp){
        inp.addEventListener('input', function(){
          var v = (inp.value || '').trim();
          if (v === '') {
            inp.classList.remove('is-invalid');
            recalcDmft();
            return;
          }
          inp.value = normalizeCode(v);
          var c = normalizeCode(inp.value);
          if (c === '✓' || allowed.indexOf(c) !== -1) {
            inp.classList.remove('is-invalid');
          }
          recalcDmft();
        });
        inp.addEventListener('blur', function(){
          var v = (inp.value || '').trim();
          if (v === '') {
            inp.classList.remove('is-invalid');
            recalcDmft();
            return;
          }
          var up = normalizeCode(v);
          inp.value = up;
          if (up !== '✓' && allowed.indexOf(up) === -1) {
            alert('Invalid tooth condition code: ' + up);
            inp.value = '';
            inp.classList.add('is-invalid');
          }
          recalcDmft();
        });
      });

      recalcDmft();
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

      menu.addEventListener('click', function(e){
        var btn = e.target.closest('.ac-item');
        if (!btn) return;
        var idx = parseInt(btn.getAttribute('data-idx') || '-1', 10);
        if (idx >= 0 && visible[idx]) {
          input.value = visible[idx];
          closeMenu();
          input.focus();
        }
      });

      window.addEventListener('scroll', function(){
        if (menu.style.display === 'block') placeMenu();
      }, true);
      window.addEventListener('resize', function(){
        if (menu.style.display === 'block') placeMenu();
      });

      document.addEventListener('click', function(e){
        if (e.target === input || menu.contains(e.target)) return;
        closeMenu();
      });
    })();
  </script>

  <script>
    (function(){
      var elementary = <?= json_encode($elementarySchools, JSON_UNESCAPED_UNICODE) ?>;
      var secondary = <?= json_encode($secondarySchools, JSON_UNESCAPED_UNICODE) ?>;
      var divisionLevel = <?= json_encode($divisionLevel, JSON_UNESCAPED_UNICODE) ?>;
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
          if (!name) return;
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
    })();
  </script>

  <script>
    (function(){
      var schoolEl = document.getElementById('schoolSelect');
      var districtEl = document.getElementById('districtInput') || document.querySelector('input[name="district"]');
      if (!schoolEl || !districtEl) return;

      function norm(s){
        return String(s || '').toLowerCase().replace(/[^a-z0-9]+/g, '');
      }

      var pairs = [
        // District 1
        ['Banlic Elementary School', 'District 1'],
        ['Pulo Senior High School', 'District 1'],
        ['Pulo Elementary School', 'District 1'],
        ['San Isidro Elementary School', 'District 1'],
        ['Mamatid Elementary School', 'District 1'],
        ['Banay Banay Elementary School', 'District 1'],
        ['Banay-Banay Elementary School', 'District 1'],

        // District 2
        ['Baclaran Elementary School', 'District 2'],
        ['Mamatid National High School', 'District 2'],
        ['Mamatid Senior High School', 'District 2'],
        ['Mamatid Senior High School Stand Alone', 'District 2'],
        ['Gulod Elementary School', 'District 2'],
        ['Gulod National High School', 'District 2'],
        ['Marinig South Elementary School', 'District 2'],

        // District 3
        ['Southville I Elementary School', 'District 3'],
        ['Southville 1 Elementary School', 'District 3'],
        ['Southville I Integrated National High School', 'District 3'],
        ['Southville 1 Integrated National High School', 'District 3'],
        ['North Marinig Elementary School', 'District 3'],
        ['Marinig National High School', 'District 3'],
        ['Butong Elementary School', 'District 3'],

        // District 4
        ['Cabuyao Central School', 'District 4'],
        ['Bigaa Integrated National High School', 'District 4'],
        ['Bigaa Elementary School', 'District 4'],
        ['Cabuyao Integrated National High School', 'District 4'],
        ['Sala Elementary School', 'District 4'],
        ['Niugan Elementary School', 'District 4'],

        // District 5
        ['Casile Integrated National High School', 'District 5'],
        ['Pulo National High School', 'District 5'],
        ['Guinding Elementary School', 'District 5'],
        ['Guinting Elementary School', 'District 5'],
        ['Pittland Elementary School', 'District 5'],
        ['Pittland Integrated School', 'District 5'],
        ['Diezmo Integrated School', 'District 5'],
        ['Diezmo Integrated School (Elem & JHS)', 'District 5'],
        ['Casile Elementary School', 'District 5'],
      ];

      var map = {};
      for (var i = 0; i < pairs.length; i++) {
        map[norm(pairs[i][0])] = pairs[i][1];
      }

      var userEdited = false;
      districtEl.addEventListener('input', function(){ userEdited = true; });

      function applyDistrict(){
        var school = schoolEl.value;
        if (!school) return;
        var d = map[norm(school)] || '';
        if (!d) return;
        if (userEdited && districtEl.value) return;
        districtEl.value = d;
      }

      schoolEl.addEventListener('change', function(){
        userEdited = false;
        applyDistrict();
      });

      applyDistrict();
    })();
  </script>
</body>
</html>
