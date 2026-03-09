-- Sample data for SDO-MDD (v2)
-- Inserts realistic demo records for patients + medical_assessments + dental_assessments
-- DOES NOT insert/modify users

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

TRUNCATE TABLE dental_assessments;
TRUNCATE TABLE medical_assessments;
TRUNCATE TABLE patients;

-- --------------------------------------------------------
-- patients
-- --------------------------------------------------------
INSERT INTO patients
  (id, school, level, entry_date, fullname, age, sex, address, contact_number, date_of_birth, civil_status, designation,
   region, division, district, hmo_provider, medical_checked, medical_checked_at, dental_checked, dental_checked_at, created_at)
VALUES
  (1,  'Banlic Elementary School', 'Elementary', '2026-02-03', 'DELA CRUZ, MARIA LOURDES S.', 29, 'Female', 'Brgy. Banlic, Cabuyao City', '09171234567', '1996-07-14', 'Married', 'Teacher III (Elementary)', 'IV-A CALABARZON', 'Cabuyao City', 'District 1', NULL, 1, '2026-02-05 02:15:00', 1, '2026-02-05 03:05:00', '2026-02-03 01:10:00'),
  (2,  'Mamatid National High School', 'Secondary', '2026-02-04', 'SANTOS, JOSHUA M.', 17, 'Male', 'Brgy. Mamatid, Cabuyao City', '09981230001', '2008-09-21', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 2', NULL, 1, '2026-02-06 01:20:00', 0, NULL, '2026-02-04 00:55:00'),
  (3,  'Cabuyao Central School', 'Elementary', '2026-02-05', 'REYES, ALYSSA P.', 11, 'Female', 'Brgy. Poblacion Uno, Cabuyao City', '09170002233', '2014-01-08', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 4', NULL, 0, NULL, 1, '2026-02-07 04:30:00', '2026-02-05 02:20:00'),
  (4,  'Gulod National High School', 'Secondary', '2026-02-06', 'RAMOS, KYLE A.', 16, 'Male', 'Brgy. Gulod, Cabuyao City', '09281234560', '2009-03-02', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 2', NULL, 1, '2026-02-08 02:05:00', 1, '2026-02-08 02:45:00', '2026-02-06 01:05:00'),
  (5,  'Pulo Elementary School', 'Elementary', '2026-02-06', 'GARCIA, JANELLE R.', 10, 'Female', 'Brgy. Pulo, Cabuyao City', NULL, '2015-10-19', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 1', NULL, 0, NULL, 0, NULL, '2026-02-06 03:35:00'),
  (6,  'Marinig National High School', 'Secondary', '2026-02-07', 'TORRES, NICOLE D.', 15, 'Female', 'Brgy. Marinig, Cabuyao City', '09183334455', '2010-12-03', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 3', NULL, 1, '2026-02-10 01:40:00', 1, '2026-02-10 02:30:00', '2026-02-07 02:20:00'),
  (7,  'Bigaa Integrated National High School', 'Secondary', '2026-02-08', 'MENDOZA, CARLO V.', 14, 'Male', 'Brgy. Bigaa, Cabuyao City', '09081230002', '2011-05-17', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 4', NULL, 0, NULL, 1, '2026-02-11 03:10:00', '2026-02-08 00:40:00'),
  (8,  'Niugan Elementary School', 'Elementary', '2026-02-09', 'FLORES, JONATHAN P.', 28, 'Male', 'Brgy. Niugan, Cabuyao City', '09191230003', '1997-04-29', 'Married', 'Teacher II (Elementary)', 'IV-A CALABARZON', 'Cabuyao City', 'District 4', NULL, 1, '2026-02-12 01:55:00', 0, NULL, '2026-02-09 02:10:00'),
  (9,  'Diezmo Integrated School (Elem & JHS)', 'Elementary', '2026-02-09', 'AQUINO, SHERYL A.', 12, 'Female', 'Brgy. Diezmo, Cabuyao City', NULL, '2013-11-09', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 5', NULL, 0, NULL, 1, '2026-02-12 04:05:00', '2026-02-09 04:30:00'),
  (10, 'Casile Integrated National High School', 'Secondary', '2026-02-10', 'BAUTISTA, MIGUEL C.', 16, 'Male', 'Brgy. Casile, Cabuyao City', '09991230004', '2009-08-12', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 5', NULL, 1, '2026-02-13 01:10:00', 1, '2026-02-13 02:35:00', '2026-02-10 01:12:00'),
  (11, 'Baclaran Elementary School', 'Elementary', '2026-02-11', 'CASTILLO, PATRICIA N.', 9, 'Female', 'Brgy. Baclaran, Cabuyao City', NULL, '2016-02-20', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 2', NULL, 0, NULL, 0, NULL, '2026-02-11 02:40:00'),
  (12, 'Southville 1 Integrated National High School', 'Secondary', '2026-02-11', 'LOPEZ, ANDREI J.', 15, 'Male', 'Southville 1, Cabuyao City', '09175556677', '2010-07-30', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 3', NULL, 1, '2026-02-14 02:10:00', 0, NULL, '2026-02-11 03:20:00'),
  (13, 'Pittland Integrated School', 'Elementary', '2026-02-12', 'VALDEZ, RICA M.', 13, 'Female', 'Brgy. Pittland, Cabuyao City', '09201234561', '2012-06-06', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 5', NULL, 1, '2026-02-15 01:05:00', 1, '2026-02-15 01:55:00', '2026-02-12 01:00:00'),
  (14, 'Sala Elementary School', 'Elementary', '2026-02-12', 'VILLANUEVA, PAOLO S.', 10, 'Male', 'Brgy. Sala, Cabuyao City', NULL, '2015-03-11', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 4', NULL, 0, NULL, 1, '2026-02-16 03:00:00', '2026-02-12 03:10:00'),
  (15, 'Pulo Senior High School', 'Secondary', '2026-02-13', 'CRUZ, JESSICA R.', 18, 'Female', 'Brgy. Pulo, Cabuyao City', '09178889900', '2007-01-15', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 1', NULL, 1, '2026-02-16 02:30:00', 1, '2026-02-16 03:20:00', '2026-02-13 01:25:00'),
  (16, 'Banay-Banay Elementary School', 'Elementary', '2026-02-14', 'NAVARRO, DANIEL P.', 31, 'Male', 'Brgy. Banay-Banay, Cabuyao City', '09223334444', '1994-09-07', 'Married', 'Teacher V (Elementary)', 'IV-A CALABARZON', 'Cabuyao City', 'District 1', NULL, 1, '2026-02-18 01:35:00', 0, NULL, '2026-02-14 02:10:00'),
  (17, 'North Marinig Elementary School', 'Elementary', '2026-02-14', 'PEREZ, SHANE T.', 12, 'Female', 'Brgy. North Marinig, Cabuyao City', NULL, '2013-05-02', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 3', NULL, 0, NULL, 0, NULL, '2026-02-14 03:05:00'),
  (18, 'San Isidro Elementary School', 'Elementary', '2026-02-15', 'HERNANDEZ, MARK A.', 10, 'Male', 'Brgy. San Isidro, Cabuyao City', '09160001122', '2015-08-27', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 1', NULL, 0, NULL, 1, '2026-02-19 03:55:00', '2026-02-15 02:00:00'),
  (19, 'Guinting Elementary School', 'Elementary', '2026-02-15', 'SORIANO, KATRINA L.', 27, 'Female', 'Brgy. Guinting, Cabuyao City', '09335556666', '1998-01-22', 'Single', 'Guidance Counselor I', 'IV-A CALABARZON', 'Cabuyao City', 'District 5', NULL, 1, '2026-02-19 01:15:00', 0, NULL, '2026-02-15 03:15:00'),
  (20, 'Cabuyao Integrated National High School', 'Secondary', '2026-02-16', 'DE GUZMAN, PATRICK E.', 16, 'Male', 'Brgy. Poblacion Dos, Cabuyao City', NULL, '2009-10-06', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 4', NULL, 1, '2026-02-20 02:05:00', 1, '2026-02-20 02:40:00', '2026-02-16 01:10:00'),
  (21, 'Casile Elementary School', 'Elementary', '2026-02-16', 'SANTIAGO, LIA M.', 9, 'Female', 'Brgy. Casile, Cabuyao City', NULL, '2016-09-18', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 5', NULL, 0, NULL, 0, NULL, '2026-02-16 02:05:00'),
  (22, 'Gulod Elementary School', 'Elementary', '2026-02-17', 'CHAVEZ, RONALD B.', 11, 'Male', 'Brgy. Gulod, Cabuyao City', NULL, '2014-04-10', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 2', NULL, 1, '2026-02-21 01:40:00', 1, '2026-02-21 02:15:00', '2026-02-17 01:35:00'),
  (23, 'Butong Elementary School', 'Elementary', '2026-02-18', 'RIVERA, JOANNA C.', 13, 'Female', 'Brgy. Butong, Cabuyao City', '09181112233', '2012-11-23', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', 'District 3', NULL, 0, NULL, 1, '2026-02-22 03:05:00', '2026-02-18 02:30:00'),
  (24, 'DepEd City Schools Division of Cabuyao', 'DepEd City Schools Division of Cabuyao', '2026-02-18', 'LIM, ANGELICA P.', 34, 'Female', 'Cabuyao City Hall Complex, Cabuyao City', '09170007788', '1991-02-01', 'Married', 'Nurse II', 'IV-A CALABARZON', 'Cabuyao City', NULL, 'PhilCare', 1, '2026-02-22 01:10:00', 0, NULL, '2026-02-18 03:10:00'),
  (25, 'DepEd City Schools Division of Cabuyao', 'DepEd City Schools Division of Cabuyao', '2026-02-19', 'TAN, ROBERTO Q.', 41, 'Male', 'Cabuyao City Hall Complex, Cabuyao City', NULL, '1984-06-15', 'Married', 'Dentist II', 'IV-A CALABARZON', 'Cabuyao City', NULL, NULL, 0, NULL, 1, '2026-02-23 02:50:00', '2026-02-19 01:55:00');

-- --------------------------------------------------------
-- medical_assessments
-- past_medical_history is JSON {checked:[], allergies:'', cancer_type:'', operation:'', confinement:'', others:''}
-- stress_level/coping_level use 1-4
-- --------------------------------------------------------
INSERT INTO medical_assessments
  (id, patient_id, assessed_by_name, license_no,
   height_cm, weight_kg, bmi_value, bmi_category, bmi_percentile,
   temperature_c, pulse_rate, rr, o2_sat, bp_systolic, bp_diastolic,
   past_medical_history, ob_lmp, ob_gtpal, ob_chest_xray, ob_ecg,
   physical_findings, stress_level, coping_level, created_at)
VALUES
  (1, 1,  'med1', 'MED-10231', 156.20, 54.30, 22.25, 'Normal', 58.2, 36.8, 78, 18, 98, 110, 70,
   '{"checked":["HPN"],"allergies":"","cancer_type":"","operation":"","confinement":"","others":""}', NULL, NULL, NULL, NULL,
   'No acute distress. Lungs clear. Heart regular. Abdomen soft.', 2, 2, '2026-02-05 02:15:00'),
  (2, 2,  'med1', 'MED-10231', 167.00, 58.00, 20.79, 'Normal', 49.5, 36.7, 82, 18, 99, 112, 72,
   '{"checked":["Asthma"],"allergies":"","cancer_type":"","operation":"","confinement":"","others":"Uses inhaler PRN."}', NULL, NULL, NULL, NULL,
   'Mild wheeze on forced expiration. Otherwise normal.', 2, 3, '2026-02-06 01:20:00'),
  (3, 4,  'med1', 'MED-10231', 170.50, 64.20, 22.07, 'Normal', 55.1, 36.6, 76, 18, 98, 114, 74,
   '{"checked":["Lung Dse.","Allergies"],"allergies":"Allergic rhinitis","cancer_type":"","operation":"","confinement":"","others":""}', NULL, NULL, NULL, NULL,
   'Nasal congestion noted. Lungs clear. No murmurs.', 1, 2, '2026-02-08 02:05:00'),
  (4, 6,  'med1', 'MED-10231', 160.00, 52.00, 20.31, 'Normal', 52.0, 36.9, 80, 18, 99, 108, 68,
   '{"checked":[],"allergies":"","cancer_type":"","operation":"","confinement":"","others":""}', NULL, NULL, NULL, NULL,
   'Physical exam within normal limits.', 2, 2, '2026-02-10 01:40:00'),
  (5, 8,  'med1', 'MED-10231', 171.00, 72.00, 24.62, 'Overweight', 78.0, 36.8, 84, 18, 98, 120, 78,
   '{"checked":["DM"],"allergies":"","cancer_type":"","operation":"","confinement":"","others":"Family history of DM."}', NULL, NULL, NULL, NULL,
   'Advised lifestyle modification. No alarming findings.', 3, 2, '2026-02-12 01:55:00'),
  (6, 10, 'med1', 'MED-10231', 168.00, 60.00, 21.26, 'Normal', 52.4, 36.7, 78, 18, 99, 116, 74,
   '{"checked":["HPN","Operation"],"allergies":"","cancer_type":"","operation":"Appendectomy (2018)","confinement":"","others":""}', NULL, NULL, NULL, NULL,
   'Healed surgical scar; otherwise normal.', 2, 2, '2026-02-13 01:10:00'),
  (7, 12, 'med1', 'MED-10231', 165.20, 57.50, 21.06, 'Normal', 50.0, 36.6, 74, 18, 99, 112, 70,
   '{"checked":[],"allergies":"","cancer_type":"","operation":"","confinement":"","others":""}', NULL, NULL, NULL, NULL,
   'Normal exam. Cleared for school activities.', 1, 1, '2026-02-14 02:10:00'),
  (8, 13, 'med1', 'MED-10231', 149.00, 41.00, 18.47, 'Normal', 35.0, 36.7, 86, 20, 98, 108, 66,
   '{"checked":["PTB"],"allergies":"","cancer_type":"","operation":"","confinement":"","others":"No active symptoms; for screening."}', NULL, NULL, 'For reading', NULL,
   'No cough, no fever. For TB screening per protocol.', 2, 2, '2026-02-15 01:05:00'),
  (9, 15, 'med1', 'MED-10231', 158.40, 50.80, 20.24, 'Normal', 48.0, 36.8, 78, 18, 99, 110, 70,
   '{"checked":["Allergies"],"allergies":"Shellfish","cancer_type":"","operation":"","confinement":"","others":""}', NULL, NULL, NULL, NULL,
   'No acute findings. Allergy precautions discussed.', 1, 2, '2026-02-16 02:30:00'),
  (10,16, 'med1', 'MED-10231', 172.00, 79.00, 26.70, 'Overweight', 82.0, 36.9, 88, 18, 98, 124, 80,
   '{"checked":["HPN","Heart Dse."],"allergies":"","cancer_type":"","operation":"","confinement":"","others":"Occasional palpitations."}', NULL, NULL, NULL, 'Normal',
   'BP borderline. For monitoring and lifestyle advice.', 3, 3, '2026-02-18 01:35:00'),
  (11,19, 'med1', 'MED-10231', 154.00, 49.00, 20.66, 'Normal', 46.0, 36.7, 76, 18, 99, 108, 68,
   '{"checked":["Allergies","Kidney Dse."],"allergies":"Penicillin","cancer_type":"","operation":"","confinement":"","others":"Hx of UTI (2024)."}', NULL, NULL, NULL, NULL,
   'No acute symptoms. Advised hydration and follow-up as needed.', 2, 2, '2026-02-19 01:15:00'),
  (12,20, 'med1', 'MED-10231', 169.20, 62.40, 21.78, 'Normal', 54.0, 36.6, 74, 18, 99, 114, 72,
   '{"checked":[],"allergies":"","cancer_type":"","operation":"","confinement":"","others":""}', NULL, NULL, NULL, NULL,
   'Normal physical examination.', 1, 1, '2026-02-20 02:05:00'),
  (13,22, 'med1', 'MED-10231', 147.80, 39.50, 18.08, 'Normal', 33.0, 36.7, 88, 20, 98, 104, 64,
   '{"checked":["Asthma"],"allergies":"","cancer_type":"","operation":"","confinement":"","others":"No recent exacerbation."}', NULL, NULL, NULL, NULL,
   'Clear lungs on exam. Continue meds PRN.', 2, 2, '2026-02-21 01:40:00'),
  (14,24, 'med1', 'MED-10231', 160.00, 55.00, 21.48, 'Normal', NULL, 36.8, 76, 18, 99, 112, 72,
   '{"checked":["Operation"],"allergies":"","cancer_type":"","operation":"CS (2015)","confinement":"","others":""}', '2026-01-27', 'G2T2P0A0L2', NULL, NULL,
   'Fit for work. No acute concerns.', 1, 2, '2026-02-22 01:10:00');

-- --------------------------------------------------------
-- dental_assessments
-- tooth_chart_json is JSON {"toothNo":"code", ...}
-- recommendations_json is JSON array ["Caries Free", ...]
-- --------------------------------------------------------
INSERT INTO dental_assessments
  (id, patient_id, assessed_by_name, license_no,
   mh_allergy, mh_asthma, mh_bleeding_problem, mh_heart_ailment, mh_diabetes, mh_epilepsy, mh_kidney_disease, mh_convulsion, mh_fainting, mh_others,
   exam_date, age_last_birthday,
   debris, gingiva_inflammation, calculus, orthodontic_treatment, occlusion, tmj_exam,
   tooth_chart_json, teeth_present_count, d_count, m_count, f_count, dmft_total,
   soft_tissue_exam,
   perio_gingival_inflammation, perio_soft_plaque, perio_hard_calc, perio_stains,
   home_care_effectiveness, periodontal_condition, periodontal_diagnosis, periodontitis,
   recommendations_json, recommendation_others, created_at)
VALUES
  (1, 1,  'den1', 'DEN-55012', 0,0,0,0,0,0,0,0,0, NULL, '2026-02-05', 29,
   1,1,0,0,'Class 1','Popping',
   '{"11":"D","12":"✓","21":"F","22":"✓","31":"✓","41":"✓"}', 6, 1, 0, 1, 2,
   'Tongue', 'Slight', 'Slight', 'Light', 'Light',
   'Fair','Fair','Gingivitis','Early',
   '["Needs Oral Prophylaxis","Indicated for Restoration/Filling"]', NULL, '2026-02-05 03:05:00'),

  (2, 3,  'den1', 'DEN-55012', 1,0,0,0,0,0,0,0,0, 'Allergic to local anesthetic (reported)', '2026-02-07', 11,
   1,1,1,0,'Class 1','Deviation',
   '{"55":"D","54":"✓","53":"✓","52":"✓","51":"✓"}', 5, 1, 0, 0, 1,
   'Lips', 'Moderate', 'Moderate', 'Moderate', 'Light',
   'Fair','Fair','Gingivitis','Moderate',
   '["Poor Oral Hygiene (Materia Alba, Calculus, Stain)","Gingival Inflammation","Needs Oral Prophylaxis"]', NULL, '2026-02-07 04:30:00'),

  (3, 4,  'den1', 'DEN-55012', 0,0,0,0,0,0,0,0,0, NULL, '2026-02-08', 16,
   0,0,0,1,'Class 2','Pain',
   '{"16":"✓","15":"✓","14":"✓","13":"✓","12":"✓","11":"✓","21":"✓","22":"✓"}', 8, 0, 0, 0, 0,
   'Palate', 'Slight', 'Slight', 'Light', 'Light',
   'Good','Good','Normal', NULL,
   '["Caries Free","No Dental Treatment Needed at Present"]', NULL, '2026-02-08 02:45:00'),

  (4, 6,  'den1', 'DEN-55012', 0,1,0,0,0,0,0,0,0, 'Asthma - controlled', '2026-02-10', 15,
   1,1,1,0,'Class 1','Tooth wear',
   '{"26":"M","25":"✓","24":"✓","23":"D","22":"✓","21":"✓"}', 6, 1, 1, 0, 2,
   'Neck & nodes', 'Moderate', 'Heavy', 'Moderate', 'Moderate',
   'Fair','Fair','Gingivitis','Early',
   '["Needs Oral Prophylaxis","For Endodontic Treatment","Indicated for Restoration/Filling"]', 'For follow-up in 2 weeks.', '2026-02-10 02:30:00'),

  (5, 7,  'den1', 'DEN-55012', 0,0,0,0,0,0,0,0,0, NULL, '2026-02-11', 14,
   1,1,0,1,'Class 3','Popping',
   '{"36":"✓","35":"✓","34":"✓","33":"✓","32":"✓","31":"✓"}', 6, 0, 0, 0, 0,
   'Floor of mouth', 'Slight', 'Slight', 'Light', 'Light',
   'Good','Good','Normal', NULL,
   '["For Orthodontic Consultation"]', NULL, '2026-02-11 03:10:00'),

  (6, 9,  'den1', 'DEN-55012', 0,0,0,0,0,0,0,0,0, NULL, '2026-02-12', 12,
   1,1,1,0,'Class 1', NULL,
   '{"75":"D","74":"✓","73":"✓","72":"F","71":"✓"}', 5, 1, 0, 1, 2,
   'Tongue', 'Moderate', 'Moderate', 'Heavy', 'Moderate',
   'Fair','Fair','Gingivitis','Moderate',
   '["Poor Oral Hygiene (Materia Alba, Calculus, Stain)","Needs Oral Prophylaxis","Indicated for Restoration/Filling"]', NULL, '2026-02-12 04:05:00'),

  (7, 10, 'den1', 'DEN-55012', 0,0,0,0,0,0,0,0,0, NULL, '2026-02-13', 16,
   0,0,0,0,'Class 1', NULL,
   '{"11":"✓","12":"✓","13":"✓","21":"✓","22":"✓","23":"✓"}', 6, 0, 0, 0, 0,
   'Palate', 'Slight', 'Slight', 'Light', 'Light',
   'Good','Good','Normal', NULL,
   '["Caries Free","No Dental Treatment Needed at Present"]', NULL, '2026-02-13 02:35:00'),

  (8, 13, 'den1', 'DEN-55012', 0,0,0,0,0,0,0,0,0, NULL, '2026-02-15', 13,
   1,1,1,0,'Class 2','Deviation',
   '{"41":"✓","42":"D","43":"✓","44":"✓","45":"M"}', 5, 1, 1, 0, 2,
   'Lips', 'Moderate', 'Moderate', 'Moderate', 'Light',
   'Fair','Fair','Gingivitis','Early',
   '["Needs Oral Prophylaxis","Indicated for Extraction"]', NULL, '2026-02-15 01:55:00'),

  (9, 14, 'den1', 'DEN-55012', 0,0,0,0,0,0,0,0,0, NULL, '2026-02-16', 10,
   1,1,0,0,'Class 1', NULL,
   '{"55":"✓","54":"✓","53":"✓","52":"✓","51":"✓"}', 5, 0, 0, 0, 0,
   'Tongue', 'Slight', 'Slight', 'Light', 'Light',
   'Good','Good','Normal', NULL,
   '["Caries Free","No Dental Treatment Needed at Present"]', NULL, '2026-02-16 03:00:00'),

  (10,15,'den1', 'DEN-55012', 0,0,0,0,0,0,0,0,0, NULL, '2026-02-16', 18,
   1,1,1,0,'Class 1','Popping',
   '{"11":"✓","12":"✓","13":"✓","14":"F","15":"✓","16":"✓"}', 6, 0, 0, 1, 1,
   'Neck & nodes', 'Slight', 'Moderate', 'Moderate', 'Light',
   'Fair','Fair','Gingivitis','Early',
   '["Needs Oral Prophylaxis","Indicated for Restoration/Filling"]', NULL, '2026-02-16 03:20:00'),

  (11,18,'den1', 'DEN-55012', 0,0,0,0,0,0,0,0,0, NULL, '2026-02-19', 10,
   1,1,0,0,'Class 1', NULL,
   '{"61":"✓","62":"✓","63":"D","64":"✓"}', 4, 1, 0, 0, 1,
   'Floor of mouth', 'Moderate', 'Moderate', 'Light', 'Light',
   'Fair','Fair','Gingivitis','Early',
   '["Needs Oral Prophylaxis","Indicated for Restoration/Filling"]', NULL, '2026-02-19 03:55:00'),

  (12,20,'den1', 'DEN-55012', 0,0,0,0,0,0,0,0,0, NULL, '2026-02-20', 16,
   0,0,0,0,'Class 1', NULL,
   '{"11":"✓","12":"✓","21":"✓","22":"✓","31":"✓","32":"✓"}', 6, 0, 0, 0, 0,
   'Palate', 'Slight', 'Slight', 'Light', 'Light',
   'Good','Good','Normal', NULL,
   '["Caries Free","No Dental Treatment Needed at Present"]', NULL, '2026-02-20 02:40:00'),

  (13,22,'den1', 'DEN-55012', 0,0,0,0,0,0,0,0,0, NULL, '2026-02-21', 11,
   1,1,1,0,'Class 2','Deviation',
   '{"54":"✓","53":"D","52":"✓","51":"✓","61":"F","62":"✓"}', 6, 1, 0, 1, 2,
   'Tongue', 'Moderate', 'Moderate', 'Heavy', 'Moderate',
   'Fair','Fair','Gingivitis','Moderate',
   '["Poor Oral Hygiene (Materia Alba, Calculus, Stain)","Needs Oral Prophylaxis","Indicated for Restoration/Filling"]', NULL, '2026-02-21 02:15:00'),

  (14,23,'den1', 'DEN-55012', 0,0,0,0,0,0,0,0,0, NULL, '2026-02-22', 13,
   1,1,1,0,'Class 1', NULL,
   '{"75":"✓","74":"✓","73":"✓","72":"D","71":"✓"}', 5, 1, 0, 0, 1,
   'Lips', 'Moderate', 'Moderate', 'Moderate', 'Light',
   'Fair','Fair','Gingivitis','Early',
   '["Needs Oral Prophylaxis","Indicated for Restoration/Filling"]', NULL, '2026-02-22 03:05:00'),

  (15,25,'den1', 'DEN-55012', 0,0,0,0,0,0,0,0,0, NULL, '2026-02-23', 41,
   0,0,0,0,'Class 1', NULL,
   '{"11":"✓","12":"✓","21":"✓","22":"✓"}', 4, 0, 0, 0, 0,
   'Palate', 'Slight', 'Slight', 'Light', 'Light',
   'Good','Good','Normal', NULL,
   '["Caries Free"]', NULL, '2026-02-23 02:50:00');

COMMIT;
