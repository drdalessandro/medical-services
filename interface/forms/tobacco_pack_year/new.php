<?php

/**
 * Tobacco Pack Year Calculator form - new.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    [Your Name]
 * @copyright Copyright (c) 2025 [Your Name]
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// Check for proper authorization
if (!AclMain::aclCheckCore('patients', 'med')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Tobacco Pack Year")]);
    exit;
}

$returnurl = 'encounter_top.php';
$formid = 0 + (isset($_GET['id']) ? $_GET['id'] : 0);

if ($formid) {
    $sql = "SELECT * FROM form_tobacco_pack_year WHERE id = ? AND pid = ? AND encounter = ?";
    $res = sqlQuery($sql, array($formid, $_SESSION["pid"], $_SESSION["encounter"]));
    
    $cigarettes_per_day = $res['cigarettes_per_day'];
    $years_smoking = $res['years_smoking'];
    $pack_years = $res['pack_years'];
    $risk_level = $res['risk_level'];
    $notes = $res['notes'];
} else {
    $cigarettes_per_day = '';
    $years_smoking = '';
    $pack_years = '';
    $risk_level = '';
    $notes = '';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title><?php echo xlt("Tobacco Pack Year Calculator"); ?></title>
    
    <?php Header::setupHeader(); ?>
    
    <style>
        .form-group {
            margin-bottom: 1rem;
        }
        .result-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        .risk-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .risk-table th, .risk-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .risk-table th {
            background-color: #f2f2f2;
        }
        .risk-low { background-color: #d4edda; }
        .risk-moderate { background-color: #fff3cd; }
        .risk-high { background-color: #f75e25; }
        .risk-very-high { color: #f7f7f7; background-color: #ff0000; }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h2><?php echo xlt('Tobacco Pack Year Calculator'); ?></h2>
                
                <form method="post" name="tobacco_form" action="<?php echo $rootdir; ?>/forms/tobacco_pack_year/save.php?mode=new" onsubmit="return top.restoreSession()">
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    
                    <div class="form-group">
                        <label for="cigarettes_per_day"><?php echo xlt('Cigarettes per day'); ?>:</label>
                        <input type="number" 
                               class="form-control" 
                               id="cigarettes_per_day" 
                               name="cigarettes_per_day" 
                               value="<?php echo attr($cigarettes_per_day); ?>"
                               min="0" 
                               max="100" 
                               onchange="calculatePackYears()">
                    </div>
                    
                    <div class="form-group">
                        <label for="years_smoking"><?php echo xlt('Years smoking'); ?>:</label>
                        <input type="number" 
                               class="form-control" 
                               id="years_smoking" 
                               name="years_smoking" 
                               value="<?php echo attr($years_smoking); ?>"
                               min="0" 
                               max="100" 
                               step="0.1"
                               onchange="calculatePackYears()">
                    </div>
                    
                    <div class="result-box">
                        <h4><?php echo xlt('Pack Years Calculation'); ?></h4>
                        <p><strong><?php echo xlt('Pack Years'); ?>:</strong> <span id="pack_years_result"><?php echo attr($pack_years); ?></span></p>
                        <p><strong><?php echo xlt('Risk Level'); ?>:</strong> <span id="risk_level_result"><?php echo attr($risk_level); ?></span></p>
                        <small class="text-muted"><?php echo xlt('Formula: (Cigarettes per day × Years smoking) ÷ 20'); ?></small>
                    </div>
                    
                    <input type="hidden" id="pack_years" name="pack_years" value="<?php echo attr($pack_years); ?>">
                    <input type="hidden" id="risk_level" name="risk_level" value="<?php echo attr($risk_level); ?>">
                    
                    <table class="risk-table">
                        <caption><h5><?php echo xlt('Risk Assessment Table'); ?></h5></caption>
                        <thead>
                            <tr>
                                <th><?php echo xlt('Pack Years'); ?></th>
                                <th><?php echo xlt('Risk Level'); ?></th>
                                <th><?php echo xlt('Description'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="risk-low">
                                <td>&lt; 10</td>
                                <td><?php echo xlt('Low Risk'); ?></td>
                                <td><?php echo xlt('Minimal tobacco-related health risk'); ?></td>
                            </tr>
                            <tr class="risk-moderate">
                                <td>10 - 20</td>
                                <td><?php echo xlt('Moderate Risk'); ?></td>
                                <td><?php echo xlt('Increased risk for respiratory diseases'); ?></td>
                            </tr>
                            <tr class="risk-high">
                                <td>20 - 30</td>
                                <td><?php echo xlt('High Risk'); ?></td>
                                <td><?php echo xlt('Significant risk for lung cancer and COPD'); ?></td>
                            </tr>
                            <tr class="risk-very-high">
                                <td>&gt; 30</td>
                                <td><?php echo xlt('Very High Risk'); ?></td>
                                <td><?php echo xlt('Extreme risk for multiple tobacco-related diseases'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="form-group">
                        <label for="notes"><?php echo xlt('Additional Notes'); ?>:</label>
                        <textarea class="form-control" 
                                  id="notes" 
                                  name="notes" 
                                  rows="4"><?php echo text($notes); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary"><?php echo xlt('Save'); ?></button>
                        <button type="button" class="btn btn-secondary" onclick="top.restoreSession(); parent.closeTab(window.name, false);"><?php echo xlt('Cancel'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function calculatePackYears() {
            const cigarettesPerDay = parseFloat(document.getElementById('cigarettes_per_day').value) || 0;
            const yearsSmoking = parseFloat(document.getElementById('years_smoking').value) || 0;
            
            const packYears = (cigarettesPerDay * yearsSmoking) / 20;
            const roundedPackYears = Math.round(packYears * 100) / 100;
            
            let riskLevel = '';
            if (roundedPackYears < 10) {
                riskLevel = '<?php echo xlt("Low Risk"); ?>';
            } else if (roundedPackYears < 20) {
                riskLevel = '<?php echo xlt("Moderate Risk"); ?>';
            } else if (roundedPackYears < 30) {
                riskLevel = '<?php echo xlt("High Risk"); ?>';
            } else {
                riskLevel = '<?php echo xlt("Very High Risk"); ?>';
            }
            
            document.getElementById('pack_years_result').textContent = roundedPackYears;
            document.getElementById('risk_level_result').textContent = riskLevel;
            document.getElementById('pack_years').value = roundedPackYears;
            document.getElementById('risk_level').value = riskLevel;
        }
        
        // Calculate on page load if values exist
        document.addEventListener('DOMContentLoaded', function() {
            calculatePackYears();
        });
    </script>
</body>
</html>
