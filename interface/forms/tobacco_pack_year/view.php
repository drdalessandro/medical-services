<?php

/**
 * Tobacco Pack Year Calculator form - view.php
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

$sql = "SELECT * FROM form_tobacco_pack_year WHERE id = ? AND pid = ? AND encounter = ?";
$res = sqlQuery($sql, array($formid, $_SESSION["pid"], $_SESSION["encounter"]));

if (!$res) {
    die(xlt("Error: Form not found"));
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title><?php echo xlt("Tobacco Pack Year Calculator - View"); ?></title>
    <?php Header::setupHeader(); ?>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h2><?php echo xlt('Tobacco Pack Year Calculator - View'); ?></h2>
                
                <table class="table table-bordered">
                    <tr>
                        <th><?php echo xlt('Cigarettes per day'); ?></th>
                        <td><?php echo text($res['cigarettes_per_day']); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo xlt('Years smoking'); ?></th>
                        <td><?php echo text($res['years_smoking']); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo xlt('Pack Years'); ?></th>
                        <td><?php echo text($res['pack_years']); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo xlt('Risk Level'); ?></th>
                        <td><?php echo text($res['risk_level']); ?></td>
                    </tr>
                    <?php if ($res['notes']) { ?>
                    <tr>
                        <th><?php echo xlt('Notes'); ?></th>
                        <td><?php echo nl2br(text($res['notes'])); ?></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <th><?php echo xlt('Date'); ?></th>
                        <td><?php echo text(oeFormatShortDate($res['date'])); ?></td>
                    </tr>
                </table>
                
                <div class="btn-group">
                    <?php if (AclMain::aclCheckCore('patients', 'med', '', 'write')) { ?>
                    <a href="<?php echo $rootdir; ?>/forms/tobacco_pack_year/new.php?id=<?php echo attr($formid); ?>" class="btn btn-primary"><?php echo xlt('Edit'); ?></a>
                    <?php } ?>
                    <button type="button" class="btn btn-secondary" onclick="top.restoreSession(); parent.closeTab(window.name, false);"><?php echo xlt('Close'); ?></button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
