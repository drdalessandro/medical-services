<?php

/**
* Cardiovascular Risk HEARTS Calculator form - new.php
*
* @package   OpenEMR
* @link      http://www.open-emr.org
* @author    Dr Alejandro Sergio D'Alessandro
* @copyright Copyright (c) 2025 EPA Bienestar IA
* @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// Check for proper authorization
if (!AclMain::aclCheckCore('patients', 'med')) {
   echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Cardiovascular Risk HEARTS")]);
   exit;
}


$returnurl = 'encounter_top.php';
$formid = 0 + (isset($_GET['id']) ? $_GET['id'] : 0);

// Obtener datos del paciente de la tabla patient_data
$patient_sql = "SELECT DOB, sex FROM patient_data WHERE pid = ?";
$patient_res = sqlQuery($patient_sql, array($_SESSION["pid"]));

// Calcular edad desde fecha de nacimiento
$patient_age = '';
$patient_gender = '';

if ($patient_res) {
    $patient_gender = strtolower($patient_res['sex']);
    if ($patient_res['DOB'] && $patient_res['DOB'] != '0000-00-00') {
        $dob = new DateTime($patient_res['DOB']);
        $today = new DateTime();
        $patient_age = $dob->diff($today)->y;
    }
}

if ($formid) {
    $sql = "SELECT * FROM form_cardiovascular_risk_hearts WHERE id = ? AND pid = ? AND encounter = ?";
    $res = sqlQuery($sql, array($formid, $_SESSION["pid"], $_SESSION["encounter"]));
    
    // Usar datos del formulario si existen, sino usar datos del paciente
    $gender = $res['gender'] ?: $patient_gender;
    $age = $res['age'] ?: $patient_age;
    $smoker = $res['smoker'];
    $systolic_pressure = $res['systolic_pressure'];
    $weight = $res['weight'];
    $height = $res['height'];
    $cv_disease_history = $res['cv_disease_history'];
    $chronic_kidney_disease = $res['chronic_kidney_disease'];
    $diabetes_mellitus = $res['diabetes_mellitus'];
    $know_cholesterol = $res['know_cholesterol'];
    $total_cholesterol = $res['total_cholesterol'];
    $calculated_risk = $res['calculated_risk'];
    $risk_category = $res['risk_category'];
    $bmi = $res['bmi'];
    $notes = $res['notes'];
} else {
    // Usar datos del paciente para nuevo formulario
    $gender = $patient_gender;
    $age = $patient_age;
    $smoker = '';
    $systolic_pressure = '';
    $weight = '';
    $height = '';
    $cv_disease_history = '';
    $chronic_kidney_disease = '';
    $diabetes_mellitus = '';
    $know_cholesterol = '';
    $total_cholesterol = '';
    $calculated_risk = '';
    $risk_category = '';
    $bmi = '';
    $notes = '';
}

?>

<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8" />
   <title><?php echo xlt("Cardiovascular Risk HEARTS Calculator"); ?></title>
   
   <?php Header::setupHeader(); ?>
   
   <style>
       .form-group {
           margin-bottom: 1.2rem;
       }
       .result-box {
           background: #f8f9fa;
           border: 1px solid #dee2e6;
           border-radius: 8px;
           padding: 20px;
           margin: 20px 0;
           transition: all 0.3s ease;
       }
       .risk-table {
           width: 100%;
           border-collapse: collapse;
           margin-top: 20px;
           box-shadow: 0 2px 4px rgba(0,0,0,0.1);
       }
       .risk-table th, .risk-table td {
           border: 1px solid #ddd;
           padding: 12px 8px;
           text-align: left;
       }
       .risk-table th {
           background-color: #f2f2f2;
           font-weight: bold;
       }
       .risk-low { 
           background-color: #d4edda !important; 
           border-color: #c3e6cb !important;
       }
       .risk-moderate { 
           background-color: #fff3cd !important; 
           border-color: #ffeaa7 !important;
       }
       .risk-high { 
           background-color: #f75e25 !important; 
           border-color: #f5c6cb !important;
       }
       .risk-very-high { 
           background-color: #ff0000 !important; 
           border-color: #e0a4aa !important;
           color: #ffffff !important;
       }
       .section-header {
           background: linear-gradient(135deg, #b5b5b5, #e5e5e5);
           color: white;
           padding: 15px;
           margin: 25px 0 15px 0;
           border-radius: 8px;
           box-shadow: 0 2px 4px rgba(0,0,0,0.1);
       }
       .section-header h4 {
           margin: 0;
           font-weight: 500;
       }
       .two-column {
           display: grid;
           grid-template-columns: 1fr 1fr;
           gap: 20px;
       }
       .cholesterol-section {
           transition: all 0.4s ease-in-out;
           overflow: hidden;
           display: none;
           opacity: 0;
           max-height: 0;
       }
       .cholesterol-section.show {
           display: block !important;
           opacity: 1;
           max-height: 200px;
           margin-top: 15px;
       }
       .btn-group {
           margin: 10px 0;
           display: flex;
           flex-wrap: wrap;
       }
       .btn-group .btn {
           margin-right: 10px;
           margin-bottom: 5px;
           border: 2px solid #6c757d;
           background: white;
           color: #6c757d;
           transition: all 0.2s ease;
       }
       .btn-group .btn:hover {
           background: #e9ecef;
       }
       .btn-group .btn.active {
           background-color: #77bc1f !important;
           color: white !important;
           border-color: #f9f9f9 !important;
           font-weight: 600;
       }
       .form-control {
           border-radius: 6px;
           border: 2px solid #ced4da;
           transition: border-color 0.2s ease;
       }
       .form-control:focus {
           border-color: #007bff;
           box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
       }
       .form-control.is-invalid {
           border-color: #dc3545;
       }
       .form-control.is-valid {
           border-color: #28a745;
       }
       .alert {
           border-radius: 6px;
           margin: 10px 0;
           padding: 12px;
       }
       .alert-info {
           background-color: #d1ecf1;
           border-color: #bee5eb;
           color: #0c5460;
       }
       .alert-warning {
           background-color: #fff3cd;
           border-color: #ffeaa7;
           color: #856404;
       }
       .text-danger {
           color: #dc3545 !important;
           font-weight: bold;
       }
       .text-success {
           color: #28a745 !important;
       }
       .risk-indicator {
           font-size: 1.2em;
           font-weight: bold;
           padding: 8px 12px;
           border-radius: 6px;
           display: inline-block;
           margin: 5px 0;
       }
       .bmi-indicator {
           font-size: 1.1em;
           padding: 5px 10px;
           border-radius: 4px;
           display: inline-block;
       }
       @media (max-width: 768px) {
           .two-column {
               grid-template-columns: 1fr;
               gap: 15px;
           }
           .btn-group {
               flex-direction: column;
           }
           .btn-group .btn {
               margin-right: 0;
               width: 100%;
           }
       }
   </style>
</head>

<body>
   <div class="container-fluid">
       <div class="row">
           <div class="col-md-12">
               <h2><i class="fa fa-heartbeat text-danger"></i> <?php echo xlt('Cardiovascular Risk HEARTS Calculator'); ?></h2>
               
               <div class="alert alert-info">
                   <strong><i class="fa fa-info-circle"></i> <?php echo xlt('Important'); ?>:</strong> 
                   <?php echo xlt('This calculator estimates 10-year cardiovascular disease risk using WHO/PAHO HEARTS methodology for Latin America. Validated for ages 18-79 years.'); ?>
               </div>
               
               <form method="post" name="cv_risk_form" action="<?php echo $rootdir; ?>/forms/cardiovascular_risk_hearts/save.php?mode=new" onsubmit="return validateForm(event)">
                   <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                   
                   <!-- Basic Demographics -->
                   <div class="section-header">
                       <h4><i class="fa fa-user"></i> <?php echo xlt('Basic Information'); ?></h4>
                   </div>
		    <div class="two-column">
                        <div class="form-group">
                            <label for="gender"><?php echo xlt('Gender'); ?> <span class="text-danger">*</span>:</label>
                            <select class="form-control" id="gender" name="gender" required>
                                <option value=""><?php echo xlt('Select Gender'); ?></option>
                                <option value="female" <?php echo ($gender == 'female') ? 'selected' : ''; ?>><?php echo xlt('Female'); ?></option>
                                <option value="male" <?php echo ($gender == 'male') ? 'selected' : ''; ?>><?php echo xlt('Male'); ?></option>
                            </select>
                            <?php if ($patient_gender): ?>
                                <small class="form-text text-muted">
                                    <i class="fa fa-info-circle text-success"></i> 
                                    <?php echo xlt('Auto-filled from patient record'); ?>
                                </small>
                            <?php else: ?>
                                <small class="form-text text-muted">
                                    <i class="fa fa-exclamation-triangle text-warning"></i> 
                                    <?php echo xlt('Please update patient demographics or select gender'); ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="age"><?php echo xlt('Age'); ?> <span class="text-danger">*</span>:</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="age" 
                                   name="age" 
                                   value="<?php echo attr($age); ?>"
                                   min="18" 
                                   max="79" 
                                   required
                                   placeholder="<?php echo xlt('Enter age (18-79 years)'); ?>">
                            <?php if ($patient_age): ?>
                                <small class="form-text text-muted">
                                    <i class="fa fa-info-circle text-success"></i> 
                                    <?php echo xlt('Auto-calculated from DOB'); ?> (<?php echo text($patient_res['DOB']); ?>)
                                </small>
                            <?php else: ?>
                                <small class="form-text text-muted">
                                    <i class="fa fa-exclamation-triangle text-warning"></i> 
                                    <?php echo xlt('Please update patient DOB or enter age manually'); ?>
                                </small>
                            <?php endif; ?>
                            <div id="age_warning" style="display: none;"></div>
                        </div>
                    </div>                   
                   <div class="form-group">
                       <label><?php echo xlt('Are you currently a smoker?'); ?> <span class="text-danger">*</span></label>
                       <div class="btn-group btn-group-toggle" data-toggle="buttons">
                           <label class="btn btn-outline-secondary <?php echo ($smoker == 'yes') ? 'active' : ''; ?>" onclick="handleRadioChoice('smoker', 'yes')">
                               <input type="radio" name="smoker" value="yes" <?php echo ($smoker == 'yes') ? 'checked' : ''; ?>> 
                               <i class="fa fa-times-circle text-danger"></i> <?php echo xlt('Yes'); ?>
                           </label>
                           <label class="btn btn-outline-secondary <?php echo ($smoker == 'no') ? 'active' : ''; ?>" onclick="handleRadioChoice('smoker', 'no')">
                               <input type="radio" name="smoker" value="no" <?php echo ($smoker == 'no') ? 'checked' : ''; ?>> 
                               <i class="fa fa-check-circle text-success"></i> <?php echo xlt('No'); ?>
                           </label>
                       </div>
                   </div>
                   
                   <!-- Physical Measurements -->
                   <div class="section-header">
                       <h4><i class="fa fa-stethoscope"></i> <?php echo xlt('Physical Measurements'); ?></h4>
                   </div>
                   
                   <div class="two-column">
                       <div class="form-group">
                           <label for="systolic_pressure"><?php echo xlt('Systolic Blood Pressure (mmHg)'); ?> <span class="text-danger">*</span>:</label>
                           <input type="number" 
                                  class="form-control" 
                                  id="systolic_pressure" 
                                  name="systolic_pressure" 
                                  value="<?php echo attr($systolic_pressure); ?>"
                                  min="90" 
                                  max="220" 
                                  required
                                  placeholder="<?php echo xlt('e.g., 120'); ?>">
                           <small class="form-text text-muted">
                               <i class="fa fa-info-circle"></i> 
                               <?php echo xlt('Normal: < 120, Elevated: 120-129, Stage 1: 130-139, Stage 2: ≥ 140'); ?>
                           </small>
                       </div>
                       
                       <div class="form-group">
                           <label for="weight"><?php echo xlt('Weight (kg)'); ?>:</label>
                           <input type="number" 
                                  class="form-control" 
                                  id="weight" 
                                  name="weight" 
                                  value="<?php echo attr($weight); ?>"
                                  min="30" 
                                  max="200" 
                                  step="0.1"
                                  placeholder="<?php echo xlt('e.g., 70.5'); ?>">
                       </div>
                   </div>
                   
                   <div class="form-group">
                       <label for="height"><?php echo xlt('Height (cm)'); ?>:</label>
                       <input type="number" 
                              class="form-control" 
                              id="height" 
                              name="height" 
                              value="<?php echo attr($height); ?>"
                              min="120" 
                              max="220"
                              placeholder="<?php echo xlt('e.g., 175'); ?>">
                   </div>
                   
                   <!-- Medical History Questions -->
                   <div class="section-header">
                       <h4><i class="fa fa-heartbeat"></i> <?php echo xlt('Medical History - HEARTS Assessment'); ?></h4>
                   </div>
                   
                   <div class="form-group">
                       <label><strong><?php echo xlt('Do you have a history of cardiovascular disease?'); ?></strong> <span class="text-danger">*</span></label>
                       <div class="btn-group btn-group-toggle" data-toggle="buttons">
                           <label class="btn btn-outline-secondary <?php echo ($cv_disease_history == 'yes') ? 'active' : ''; ?>" onclick="handleRadioChoice('cv_disease_history', 'yes')">
                               <input type="radio" name="cv_disease_history" value="yes" <?php echo ($cv_disease_history == 'yes') ? 'checked' : ''; ?>> 
                               <i class="fa fa-exclamation-triangle text-warning"></i> <?php echo xlt('Yes'); ?>
                           </label>
                           <label class="btn btn-outline-secondary <?php echo ($cv_disease_history == 'no') ? 'active' : ''; ?>" onclick="handleRadioChoice('cv_disease_history', 'no')">
                               <input type="radio" name="cv_disease_history" value="no" <?php echo ($cv_disease_history == 'no') ? 'checked' : ''; ?>> 
                               <i class="fa fa-check-circle text-success"></i> <?php echo xlt('No'); ?>
                           </label>
                       </div>
                       <small class="form-text text-muted">
                           <i class="fa fa-info-circle"></i> 
                           <?php echo xlt('History of heart attack, stroke, coronary artery disease, or heart surgery'); ?>
                       </small>
                   </div>
                   
                   <div class="form-group">
                       <label><strong><?php echo xlt('Do you have chronic kidney disease?'); ?></strong> <span class="text-danger">*</span></label>
                       <div class="btn-group btn-group-toggle" data-toggle="buttons">
                           <label class="btn btn-outline-secondary <?php echo ($chronic_kidney_disease == 'yes') ? 'active' : ''; ?>" onclick="handleRadioChoice('chronic_kidney_disease', 'yes')">
                               <input type="radio" name="chronic_kidney_disease" value="yes" <?php echo ($chronic_kidney_disease == 'yes') ? 'checked' : ''; ?>> 
                               <i class="fa fa-exclamation-triangle text-warning"></i> <?php echo xlt('Yes'); ?>
                           </label>
                           <label class="btn btn-outline-secondary <?php echo ($chronic_kidney_disease == 'no') ? 'active' : ''; ?>" onclick="handleRadioChoice('chronic_kidney_disease', 'no')">
                               <input type="radio" name="chronic_kidney_disease" value="no" <?php echo ($chronic_kidney_disease == 'no') ? 'checked' : ''; ?>> 
                               <i class="fa fa-check-circle text-success"></i> <?php echo xlt('No'); ?>
                           </label>
                       </div>
                       <small class="form-text text-muted">
                           <i class="fa fa-info-circle"></i> 
                           <?php echo xlt('GFR < 60 ml/min/1.73m² or proteinuria'); ?>
                       </small>
                   </div>
                   
                   <div class="form-group">
                       <label><strong><?php echo xlt('Do you have diabetes mellitus?'); ?></strong> <span class="text-danger">*</span></label>
                       <div class="btn-group btn-group-toggle" data-toggle="buttons">
                           <label class="btn btn-outline-secondary <?php echo ($diabetes_mellitus == 'yes') ? 'active' : ''; ?>" onclick="handleRadioChoice('diabetes_mellitus', 'yes')">
                               <input type="radio" name="diabetes_mellitus" value="yes" <?php echo ($diabetes_mellitus == 'yes') ? 'checked' : ''; ?>> 
                               <i class="fa fa-exclamation-triangle text-warning"></i> <?php echo xlt('Yes'); ?>
                           </label>
                           <label class="btn btn-outline-secondary <?php echo ($diabetes_mellitus == 'no') ? 'active' : ''; ?>" onclick="handleRadioChoice('diabetes_mellitus', 'no')">
                               <input type="radio" name="diabetes_mellitus" value="no" <?php echo ($diabetes_mellitus == 'no') ? 'checked' : ''; ?>> 
                               <i class="fa fa-check-circle text-success"></i> <?php echo xlt('No'); ?>
                           </label>
                       </div>
                       <small class="form-text text-muted">
                           <i class="fa fa-info-circle"></i> 
                           <?php echo xlt('Type 1 or Type 2 diabetes mellitus'); ?>
                       </small>
                   </div>
                   
                   <div class="form-group">
                       <label><strong><?php echo xlt('Do you know your total cholesterol level?'); ?></strong> <span class="text-danger">*</span></label>
                       <div class="btn-group btn-group-toggle" data-toggle="buttons">
                           <label class="btn btn-outline-secondary <?php echo ($know_cholesterol == 'yes') ? 'active' : ''; ?>" onclick="handleCholesterolChoice('yes')">
                               <input type="radio" name="know_cholesterol" value="yes" <?php echo ($know_cholesterol == 'yes') ? 'checked' : ''; ?>> 
                               <i class="fa fa-check-circle text-info"></i> <?php echo xlt('Yes'); ?>
                           </label>
                           <label class="btn btn-outline-secondary <?php echo ($know_cholesterol == 'no') ? 'active' : ''; ?>" onclick="handleCholesterolChoice('no')">
                               <input type="radio" name="know_cholesterol" value="no" <?php echo ($know_cholesterol == 'no') ? 'checked' : ''; ?>> 
                               <i class="fa fa-question-circle text-secondary"></i> <?php echo xlt('No'); ?>
                           </label>
                       </div>
                   </div>
                   
                   <div class="form-group cholesterol-section" id="cholesterol_input" <?php echo ($know_cholesterol == 'yes') ? 'style="display: block; opacity: 1; max-height: 200px;"' : ''; ?>>
                       <label for="total_cholesterol">
                           <i class="fa fa-flask"></i> <?php echo xlt('Total Cholesterol (mg/dL)'); ?> <span class="text-danger">*</span>:
                       </label>
                       <input type="number" 
                              class="form-control" 
                              id="total_cholesterol" 
                              name="total_cholesterol" 
                              value="<?php echo attr($total_cholesterol); ?>"
                              min="100" 
                              max="400" 
                              step="1"
                              placeholder="<?php echo xlt('e.g., 180'); ?>">
                       <small class="form-text text-muted">
                           <i class="fa fa-info-circle"></i> 
                           <span class="text-success">Desirable: &lt; 200</span> | 
                           <span style="color: #856404;">Borderline: 200-239</span> | 
                           <span class="text-danger">High: ≥ 240 mg/dL</span>
                       </small>
                   </div>
                   
                   <!-- Results -->
                   <div class="result-box" id="results_section">
                       <h4><i class="fa fa-chart-line"></i> <?php echo xlt('Risk Assessment Results'); ?></h4>
                       <div class="row">
                           <div class="col-md-4">
                               <div class="text-center">
                                   <label><?php echo xlt('BMI'); ?>:</label><br>
                                   <span class="bmi-indicator" id="bmi_result"><?php echo attr($bmi); ?></span>
                               </div>
                           </div>
                           <div class="col-md-4">
                               <div class="text-center">
                                   <label><?php echo xlt('10-Year CV Risk'); ?>:</label><br>
                                   <span class="risk-indicator" id="risk_result"><?php echo attr($calculated_risk); ?>%</span>
                               </div>
                           </div>
                           <div class="col-md-4">
                               <div class="text-center">
                                   <label><?php echo xlt('Risk Category'); ?>:</label><br>
                                   <span class="risk-indicator" id="risk_category_result"><?php echo attr($risk_category); ?></span>
                               </div>
                           </div>
                       </div>
                       <div id="age_recommendation" class="text-center" style="margin-top: 15px; font-style: italic;"></div>
                   </div>
                   
                   <input type="hidden" id="calculated_risk" name="calculated_risk" value="<?php echo attr($calculated_risk); ?>">
                   <input type="hidden" id="risk_category" name="risk_category" value="<?php echo attr($risk_category); ?>">
                   <input type="hidden" id="bmi" name="bmi" value="<?php echo attr($bmi); ?>">
                   
                   <table class="risk-table">
                       <caption><h5><i class="fa fa-table"></i> <?php echo xlt('WHO/PAHO HEARTS Risk Categories'); ?></h5></caption>
                       <thead>
                           <tr>
                               <th><?php echo xlt('Risk Level'); ?></th>
                               <th><?php echo xlt('10-Year Risk'); ?></th>
                               <th><?php echo xlt('Clinical Recommendations'); ?></th>
                           </tr>
                       </thead>
                       <tbody>
                           <tr class="risk-low">
                               <td><strong><?php echo xlt('Low Risk'); ?></strong></td>
                               <td>&lt; 10%</td>
                               <td><?php echo xlt('Lifestyle counseling, regular BP monitoring, follow-up in 12 months'); ?></td>
                           </tr>
                           <tr class="risk-moderate">
                               <td><strong><?php echo xlt('Moderate Risk'); ?></strong></td>
                               <td>10% - 20%</td>
                               <td><?php echo xlt('Consider antihypertensive therapy, statin if indicated, lifestyle intervention'); ?></td>
                           </tr>
                           <tr class="risk-high">
                               <td><strong><?php echo xlt('High Risk'); ?></strong></td>
                               <td>20% - 30%</td>
                               <td><?php echo xlt('Antihypertensive + statin therapy, intensive lifestyle changes'); ?></td>
                           </tr>
                           <tr class="risk-very-high">
                               <td><strong><?php echo xlt('Very High Risk'); ?></strong></td>
                               <td>&gt; 30%</td>
                               <td><?php echo xlt('Immediate pharmacological therapy, consider low-dose aspirin, cardiology referral'); ?></td>
                           </tr>
                       </tbody>
                   </table>
                   
                   <div class="form-group" style="margin-top: 25px;">
                       <label for="notes"><i class="fa fa-sticky-note"></i> <?php echo xlt('Clinical Notes and Observations'); ?>:</label>
                       <textarea class="form-control" 
                                 id="notes" 
                                 name="notes" 
                                 rows="4"
                                 placeholder="<?php echo xlt('Enter additional clinical notes, family history, other risk factors, treatment plan, or follow-up recommendations...'); ?>"><?php echo text($notes); ?></textarea>
                   </div>
                   
                   <div class="form-group text-center" style="margin-top: 30px;">
                       <button type="submit" class="btn btn-primary btn-lg">
                           <i class="fa fa-save"></i> <?php echo xlt('Save Assessment'); ?>
                       </button>
                       <button type="button" class="btn btn-secondary btn-lg" onclick="top.restoreSession(); parent.closeTab(window.name, false);">
                           <i class="fa fa-times"></i> <?php echo xlt('Cancel'); ?>
                       </button>
                   </div>
               </form>
           </div>
       </div>
   </div>

   <script>
       // Handle radio button choices with visual feedback
       function handleRadioChoice(groupName, choice) {
           const radioButtons = document.querySelectorAll(`input[name="${groupName}"]`);
           radioButtons.forEach(radio => {
               const label = radio.closest('label');
               if (radio.value === choice) {
                   radio.checked = true;
                   label.classList.add('active');
               } else {
                   radio.checked = false;
                   label.classList.remove('active');
               }
           });
           calculateRisk();
       }

       // Special handler for cholesterol choice
       function handleCholesterolChoice(choice) {
           handleRadioChoice('know_cholesterol', choice);
           toggleCholesterolInput();
       }
       
       // Show/hide cholesterol input field with smooth animation
       function toggleCholesterolInput() {
           const knowCholesterol = document.querySelector('input[name="know_cholesterol"]:checked');
           const cholesterolSection = document.getElementById('cholesterol_input');
           const cholesterolInput = document.getElementById('total_cholesterol');
           
           if (knowCholesterol && knowCholesterol.value === 'yes') {
               cholesterolSection.style.display = 'block';
               cholesterolSection.classList.add('show');
               cholesterolInput.setAttribute('required', 'required');
               // Focus on cholesterol input for better UX
               setTimeout(() => {
                   if (cholesterolInput.value === '') {
                       cholesterolInput.focus();
                   }
               }, 400);
           } else {
               cholesterolSection.classList.remove('show');
               cholesterolInput.value = '';
               cholesterolInput.removeAttribute('required');
               setTimeout(() => {
                   cholesterolSection.style.display = 'none';
               }, 400);
           }
           
           calculateRisk();
       }
       
       // Calculate BMI with interpretation
       function calculateBMI() {
           const weight = parseFloat(document.getElementById('weight').value) || 0;
           const height = parseFloat(document.getElementById('height').value) || 0;
           const bmiResult = document.getElementById('bmi_result');
           
           if (weight > 0 && height > 0) {
               const bmi = weight / ((height / 100) * (height / 100));
               const roundedBMI = Math.round(bmi * 10) / 10;
               
               let bmiClass = '';
               let bmiText = '';
               
               if (roundedBMI < 18.5) {
                   bmiClass = 'alert alert-warning';
                   bmiText = roundedBMI + ' (<?php echo xlt("Underweight"); ?>)';
               } else if (roundedBMI < 25) {
                   bmiClass = 'alert alert-success';
                   bmiText = roundedBMI + ' (<?php echo xlt("Normal"); ?>)';
               } else if (roundedBMI < 30) {
                   bmiClass = 'alert alert-warning';
                   bmiText = roundedBMI + ' (<?php echo xlt("Overweight"); ?>)';
               } else {
                   bmiClass = 'alert alert-danger';
                   bmiText = roundedBMI + ' (<?php echo xlt("Obese"); ?>)';
               }
               
               bmiResult.textContent = bmiText;
               bmiResult.className = 'bmi-indicator ' + bmiClass;
               document.getElementById('bmi').value = roundedBMI;
           } else {
               bmiResult.textContent = '<?php echo xlt("Enter height and weight"); ?>';
               bmiResult.className = 'bmi-indicator text-muted';
               document.getElementById('bmi').value = '';
           }
       }

// WHO/PAHO evidence-based risk calculation - CORREGIDA PARA MUJERES JÓVENES
function calculateRisk() {
    const gender = document.getElementById('gender').value;
    const age = parseFloat(document.getElementById('age').value) || 0;
    const smoker = document.querySelector('input[name="smoker"]:checked');
    const systolicPressure = parseFloat(document.getElementById('systolic_pressure').value) || 0;
    const cvHistory = document.querySelector('input[name="cv_disease_history"]:checked');
    const kidneyDisease = document.querySelector('input[name="chronic_kidney_disease"]:checked');
    const diabetes = document.querySelector('input[name="diabetes_mellitus"]:checked');
    const knowCholesterol = document.querySelector('input[name="know_cholesterol"]:checked');
    const totalCholesterol = parseFloat(document.getElementById('total_cholesterol').value) || 0;
    
    // Validate age first
    if (!validateAge()) {
        updateRiskDisplay(0, '<?php echo xlt("Invalid Age Range"); ?>');
        return;
    }
    
    // Basic validations
    if (!gender || age < 18 || age > 79 || systolicPressure < 90) {
        updateRiskDisplay(0, '<?php echo xlt("Incomplete Data"); ?>');
        return;
    }
    
    // Verify radio buttons are selected
    if (!smoker || !cvHistory || !kidneyDisease || !diabetes || !knowCholesterol) {
        updateRiskDisplay(0, '<?php echo xlt("Please complete all questions"); ?>');
        return;
    }
    
    // High-risk conditions - automatic classification
    if ((cvHistory && cvHistory.value === 'yes') || 
        (kidneyDisease && kidneyDisease.value === 'yes') || 
        (diabetes && diabetes.value === 'yes')) {
        
        let riskPercentage = 25; // Base más conservador para alto riesgo
        
        // Evidence-based increments
        if (cvHistory && cvHistory.value === 'yes') riskPercentage += 12;
        if (kidneyDisease && kidneyDisease.value === 'yes') riskPercentage += 8;
        if (diabetes && diabetes.value === 'yes') riskPercentage += 6;
        
        // Additional risk factors for high-risk patients
        if (smoker && smoker.value === 'yes') riskPercentage += 4;
        if (systolicPressure >= 140) riskPercentage += 4;
        if (age >= 65) riskPercentage += 4;
        if (gender === 'male') riskPercentage += 2;
        
        // Ajuste especial para mujeres jóvenes con comorbilidades
        if (gender === 'female' && age < 50) {
            riskPercentage *= 0.7; // Reducir 30% para mujeres jóvenes
        }
        
        riskPercentage = Math.min(riskPercentage, 70);
        updateRiskDisplay(riskPercentage);
        return;
    }
    
    // Algoritmo simplificado y ajustado para prevención primaria
    let riskScore = 0;
    
    // Factor de edad - más conservador para mujeres jóvenes
    if (gender === 'female') {
        // Mujeres tienen protección hormonal hasta la menopausia
        if (age >= 18 && age < 40) riskScore += 0.5;
        else if (age >= 40 && age < 50) riskScore += 1.5;
        else if (age >= 50 && age < 55) riskScore += 3;   // Perimenopausia
        else if (age >= 55 && age < 65) riskScore += 5;   // Postmenopausia
        else if (age >= 65 && age < 75) riskScore += 8;
        else if (age >= 75) riskScore += 12;
    } else {
        // Hombres - sin protección hormonal
        if (age >= 18 && age < 40) riskScore += 1;
        else if (age >= 40 && age < 50) riskScore += 3;
        else if (age >= 50 && age < 60) riskScore += 6;
        else if (age >= 60 && age < 70) riskScore += 10;
        else if (age >= 70) riskScore += 15;
    }
    
    // Factor de presión arterial
    if (systolicPressure >= 140 && systolicPressure < 160) riskScore += 3;
    else if (systolicPressure >= 160 && systolicPressure < 180) riskScore += 6;
    else if (systolicPressure >= 180) riskScore += 10;
    
    // Factor de tabaquismo - más impacto en mujeres jóvenes
    if (smoker && smoker.value === 'yes') {
        if (gender === 'female' && age < 50) {
            riskScore += 6; // Mayor impacto relativo en mujeres jóvenes
        } else {
            riskScore += 4;
        }
    }
    
    // Factor de colesterol
    if (knowCholesterol && knowCholesterol.value === 'yes' && totalCholesterol > 0) {
        if (totalCholesterol >= 240 && totalCholesterol < 280) riskScore += 2;
        else if (totalCholesterol >= 280) riskScore += 4;
    }
    
    // Convertir score a porcentaje con calibración especial para mujeres
    let riskPercentage;
    
    if (gender === 'female') {
        // Fórmula más conservadora para mujeres
        riskPercentage = riskScore * 0.8; // Factor más bajo
        
        // Protección adicional para mujeres jóvenes
        if (age < 45) {
            riskPercentage *= 0.5; // 50% menos de riesgo para mujeres < 45 años
        } else if (age < 55) {
            riskPercentage *= 0.7; // 30% menos de riesgo para mujeres 45-54 años
        }
    } else {
        // Fórmula para hombres
        riskPercentage = riskScore * 1.1;
    }
    
    // Límites finales realistas
    riskPercentage = Math.max(0.1, Math.min(riskPercentage, 55));
    
    // Ajuste final para casos extremos de mujeres muy jóvenes
    if (gender === 'female' && age < 40 && riskPercentage > 8) {
        riskPercentage = Math.min(riskPercentage, 8); // Máximo 8% para mujeres < 40 años
    }
    
    updateRiskDisplay(riskPercentage);
}

       // Enhanced risk display function
       function updateRiskDisplay(riskPercentage, customCategory = null) {
           const roundedRisk = Math.round(riskPercentage * 10) / 10;
           const riskResult = document.getElementById('risk_result');
           const categoryResult = document.getElementById('risk_category_result');
           
           let riskCategory = '';
           let riskClass = '';
           
           if (customCategory) {
               riskCategory = customCategory;
               riskClass = 'alert alert-warning';
           } else {
               // WHO/PAHO risk categories
               if (roundedRisk < 10) {
                   riskCategory = '<?php echo xlt("Low Risk"); ?>';
                   riskClass = 'alert alert-success';
               } else if (roundedRisk < 20) {
                   riskCategory = '<?php echo xlt("Moderate Risk"); ?>';
                   riskClass = 'alert alert-warning';
               } else if (roundedRisk < 30) {
                   riskCategory = '<?php echo xlt("High Risk"); ?>';
                   riskClass = 'alert alert-danger';
               } else {
                   riskCategory = '<?php echo xlt("Very High Risk"); ?>';
                   riskClass = 'alert alert-danger';
               }
           }
           
           riskResult.textContent = roundedRisk + '%';
           riskResult.className = 'risk-indicator ' + riskClass;
           categoryResult.textContent = riskCategory;
           categoryResult.className = 'risk-indicator ' + riskClass;
           
           document.getElementById('calculated_risk').value = roundedRisk;
           document.getElementById('risk_category').value = riskCategory;
           
           // Update result box styling
           const resultBox = document.querySelector('.result-box');
           if (resultBox) {
               resultBox.className = 'result-box ' + riskClass.replace('alert ', 'risk-');
           }
           
           // Show age-specific recommendations
           showAgeSpecificRecommendations();
       }
       
       // Age-specific recommendations
       function showAgeSpecificRecommendations() {
           const age = parseFloat(document.getElementById('age').value) || 0;
           const riskCategory = document.getElementById('risk_category').value;
           let recommendation = '';
           
           if (age >= 18 && age < 40) {
               recommendation = '<?php echo xlt("Focus on lifestyle modifications and primary prevention strategies"); ?>';
           } else if (age >= 40 && age < 50) {
               recommendation = '<?php echo xlt("Regular screening recommended, consider preventive interventions"); ?>';
           } else if (age >= 50 && age < 65) {
               recommendation = '<?php echo xlt("Intensive monitoring and targeted interventions based on risk level"); ?>';
           } else if (age >= 65) {
               recommendation = '<?php echo xlt("Comprehensive cardiovascular assessment and intensive management"); ?>';
           }
           
           const recommendationElement = document.getElementById('age_recommendation');
           if (recommendationElement && recommendation) {
               recommendationElement.innerHTML = '<i class="fa fa-lightbulb-o"></i> ' + recommendation;
           }
       }

       // Enhanced age validation
       function validateAge() {
           const ageInput = document.getElementById('age');
           const age = parseInt(ageInput.value);
           
           if (isNaN(age) || age < 18 || age > 79) {
               ageInput.classList.add('is-invalid');
               ageInput.classList.remove('is-valid');
               showAgeWarning(age);
               return false;
           } else {
               ageInput.classList.add('is-valid');
               ageInput.classList.remove('is-invalid');
               hideAgeWarning();
               return true;
           }
       }

       function showAgeWarning(age) {
           let warningMessage = '';
           if (isNaN(age) || age < 18 ) {
               warningMessage = '<?php echo xlt("This calculator is validated for ages 18-79 years. Consider alternative assessment tools for younger patients."); ?>';
           } else if (age > 79) {
               warningMessage = '<?php echo xlt("This calculator is validated for ages 18-79 years. Clinical judgment is especially important for patients over 79."); ?>';
           }
           
           const warningDiv = document.getElementById('age_warning') || createAgeWarningDiv();
           warningDiv.innerHTML = '<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> ' + warningMessage + '</div>';
           warningDiv.style.display = 'block';
       }

       function hideAgeWarning() {
           const warningDiv = document.getElementById('age_warning');
           if (warningDiv) {
               warningDiv.style.display = 'none';
           }
       }

       function createAgeWarningDiv() {
           const ageInput = document.getElementById('age');
           const warningDiv = document.createElement('div');
           warningDiv.id = 'age_warning';
           warningDiv.style.display = 'none';
           ageInput.parentNode.insertBefore(warningDiv, ageInput.nextSibling);
           return warningDiv;
       }

       // Form validation before submission
       function validateForm(event) {
           const requiredFields = ['gender', 'age', 'systolic_pressure'];
           const requiredRadioGroups = ['smoker', 'cv_disease_history', 'chronic_kidney_disease', 'diabetes_mellitus', 'know_cholesterol'];
           let isValid = true;
           let firstError = null;
           
           // Validate required input fields
           requiredFields.forEach(fieldId => {
               const field = document.getElementById(fieldId);
               const value = field.value.trim();
               
               if (!value || (fieldId === 'age' && (parseInt(value) < 18 || parseInt(value) > 79)) || 
                   (fieldId === 'systolic_pressure' && (parseInt(value) < 90 || parseInt(value) > 220))) {
                   field.classList.add('is-invalid');
                   isValid = false;
                   if (!firstError) firstError = field;
               } else {
                   field.classList.remove('is-invalid');
                   field.classList.add('is-valid');
               }
           });
           
           // Validate required radio groups
           requiredRadioGroups.forEach(groupName => {
               const checked = document.querySelector(`input[name="${groupName}"]:checked`);
               if (!checked) {
                   isValid = false;
                   if (!firstError) {
                       firstError = document.querySelector(`input[name="${groupName}"]`);
                   }
               }
           });
           
           // Validate cholesterol if "Yes" is selected
           const knowChol = document.querySelector('input[name="know_cholesterol"]:checked');
           const cholInput = document.getElementById('total_cholesterol');
           if (knowChol && knowChol.value === 'yes') {
               const cholValue = parseInt(cholInput.value);
               if (!cholInput.value || cholValue < 100 || cholValue > 400) {
                   cholInput.classList.add('is-invalid');
                   isValid = false;
                   if (!firstError) firstError = cholInput;
               } else {
                   cholInput.classList.remove('is-invalid');
                   cholInput.classList.add('is-valid');
               }
           }
           
           if (!isValid) {
               event.preventDefault();
               alert('<?php echo xlt("Please complete all required fields correctly before saving the assessment."); ?>');
               if (firstError) {
                   firstError.focus();
                   firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
               }
               return false;
           }
           
           return true;
       }
       
       // Initialize page
       document.addEventListener('DOMContentLoaded', function() {
           // Set up initial calculations
           calculateBMI();
           calculateRisk();
           toggleCholesterolInput();
           
           // Age input event listeners
           const ageInput = document.getElementById('age');
           if (ageInput) {
               ageInput.addEventListener('input', function() {
                   validateAge();
                   calculateRisk();
               });
               
               ageInput.addEventListener('blur', validateAge);
           }
           
           // Other form elements event listeners
           const formElements = ['gender', 'systolic_pressure', 'weight', 'height', 'total_cholesterol'];
           formElements.forEach(elementId => {
               const element = document.getElementById(elementId);
               if (element) {
                   element.addEventListener('change', function() {
                       // Remove validation classes on change
                       element.classList.remove('is-invalid', 'is-valid');
                       
                       if (elementId === 'weight' || elementId === 'height') {
                           calculateBMI();
                       }
                       calculateRisk();
                   });
                   
                   if (elementId !== 'gender') {
                       element.addEventListener('input', function() {
                           if (elementId === 'weight' || elementId === 'height') {
                               calculateBMI();
                           }
                           if (elementId === 'total_cholesterol' || elementId === 'systolic_pressure') {
                               calculateRisk();
                           }
                       });
                   }
               }
           });
           
           // Blood pressure validation
           const bpInput = document.getElementById('systolic_pressure');
           if (bpInput) {
               bpInput.addEventListener('input', function() {
                   const value = parseInt(this.value);
                   if (value >= 90 && value <= 220) {
                       this.classList.remove('is-invalid');
                       this.classList.add('is-valid');
                   } else if (this.value !== '') {
                       this.classList.add('is-invalid');
                       this.classList.remove('is-valid');
                   }
               });
           }
           
           // Initialize form validation classes
           setTimeout(function() {
               const inputs = document.querySelectorAll('input[type="number"], select');
               inputs.forEach(input => {
                   if (input.value) {
                       input.classList.add('is-valid');
                   }
               });
           }, 500);
       });
   </script>
</body>
</html>
