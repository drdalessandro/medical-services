<?php

/**
 * Cardiovascular Risk HEARTS Calculator form - view.php
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
use OpenEMR\Core\Header;

$returnurl = 'encounter_top.php';
$formid = 0 + (isset($_GET['id']) ? $_GET['id'] : 0);

$sql = "SELECT * FROM form_cardiovascular_risk_hearts WHERE id = ? AND pid = ? AND encounter = ?";
$res = sqlQuery($sql, array($formid, $_SESSION["pid"], $_SESSION["encounter"]));

if (!$res) {
    die(xlt("Error: Form not found"));
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title><?php echo xlt("Cardiovascular Risk HEARTS Calculator - View"); ?></title>
    <?php Header::setupHeader(); ?>
    
    <style>
        .risk-low { background-color: #d4edda; }
        .risk-moderate { background-color: #fff3cd; }
        .risk-high { background-color: #f8d7da; }
        .risk-very-high { background-color: #f5c6cb; }
        .risk-result {
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h2><?php echo xlt('Cardiovascular Risk HEARTS Calculator - View'); ?></h2>
                
                <div class="row">
                    <div class="col-md-6">
                        <h4><?php echo xlt('Basic Information'); ?></h4>
                        <table class="table table-bordered">
                            <tr>
                                <th><?php echo xlt('Gender'); ?></th>
                                <td><?php echo text(ucfirst($res['gender'])); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo xlt('Age'); ?></th>
                                <td><?php echo text($res['age']); ?> years</td>
                            </tr>
                            <tr>
                                <th><?php echo xlt('Smoker'); ?></th>
                                <td><?php echo text(ucfirst($res['smoker'])); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo xlt('Weight'); ?></th>
                                <td><?php echo text($res['weight']); ?> kg</td>
                            </tr>
                            <tr>
                                <th><?php echo xlt('Height'); ?></th>
                                <td><?php echo text($res['height']); ?> cm</td>
                            </tr>
                            <tr>
                                <th><?php echo xlt('BMI'); ?></th>
                                <td><?php echo text($res['bmi']); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo xlt('Systolic Pressure'); ?></th>
                                <td><?php echo text($res['systolic_pressure']); ?> mmHg</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h4><?php echo xlt('Medical History'); ?></h4>
                        <table class="table table-bordered">
                            <tr>
                                <th><?php echo xlt('Cardiovascular Disease History'); ?></th>
<td><?php echo text(ucfirst($res['cv_disease_history'])); ?></td>
                           </tr>
                           <tr>
                               <th><?php echo xlt('Chronic Kidney Disease'); ?></th>
                               <td><?php echo text(ucfirst($res['chronic_kidney_disease'])); ?></td>
                           </tr>
                           <tr>
                               <th><?php echo xlt('Diabetes Mellitus'); ?></th>
                               <td><?php echo text(ucfirst($res['diabetes_mellitus'])); ?></td>
                           </tr>
                           <tr>
                               <th><?php echo xlt('Knows Cholesterol Level'); ?></th>
                               <td><?php echo text(ucfirst($res['know_cholesterol'])); ?></td>
                           </tr>
                           <?php if ($res['total_cholesterol']) { ?>
                           <tr>
                               <th><?php echo xlt('Total Cholesterol'); ?></th>
                               <td><?php echo text($res['total_cholesterol']); ?> mg/dL</td>
                           </tr>
                           <?php } ?>
                       </table>
                   </div>
               </div>
               
               <div class="row">
                   <div class="col-md-12">
                       <h4><?php echo xlt('Risk Assessment Results'); ?></h4>
                       <div class="risk-result 
                           <?php 
                           $risk = strtolower(str_replace(' ', '-', $res['risk_category']));
                           echo 'risk-' . $risk;
                           ?>">
                           <p><strong><?php echo xlt('10-Year Cardiovascular Risk'); ?>:</strong> <?php echo text($res['calculated_risk']); ?>%</p>
                           <p><strong><?php echo xlt('Risk Category'); ?>:</strong> <?php echo text($res['risk_category']); ?></p>
                       </div>
                   </div>
               </div>
               
               <?php if ($res['notes']) { ?>
               <div class="row">
                   <div class="col-md-12">
                       <h4><?php echo xlt('Notes'); ?></h4>
                       <div class="alert alert-info">
                           <?php echo nl2br(text($res['notes'])); ?>
                       </div>
                   </div>
               </div>
               <?php } ?>
               
               <div class="row">
                   <div class="col-md-12">
                       <table class="table table-bordered">
                           <tr>
                               <th><?php echo xlt('Assessment Date'); ?></th>
                               <td><?php echo text(oeFormatShortDate($res['date'])); ?></td>
                           </tr>
                           <tr>
                               <th><?php echo xlt('Assessed by'); ?></th>
                               <td><?php echo text($res['user']); ?></td>
                           </tr>
                       </table>
                   </div>
               </div>
               
               <div class="btn-group">
                   <?php if (AclMain::aclCheckCore('patients', 'med', '', 'write')) { ?>
                   <a href="<?php echo $rootdir; ?>/forms/cardiovascular_risk_hearts/new.php?id=<?php echo attr($formid); ?>" class="btn btn-primary"><?php echo xlt('Edit'); ?></a>
                   <?php } ?>
                   <button type="button" class="btn btn-secondary" onclick="top.restoreSession(); parent.closeTab(window.name, false);"><?php echo xlt('Close'); ?></button>
               </div>
           </div>
       </div>
   </div>
</body>
</html>
