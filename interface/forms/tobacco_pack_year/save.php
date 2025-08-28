<?php

/**
 * Tobacco Pack Year Calculator form - save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    [Your Name]
 * @copyright Copyright (c) 2025 [Your Name]
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit("form_tobacco_pack_year", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "Tobacco Pack Year", $newid, "tobacco_pack_year", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    sqlStatement("UPDATE form_tobacco_pack_year SET 
        pid = ?, 
        groupname = ?, 
        user = ?, 
        authorized = ?, 
        activity = 1, 
        date = NOW(), 
        cigarettes_per_day = ?, 
        years_smoking = ?, 
        pack_years = ?, 
        risk_level = ?, 
        notes = ? 
        WHERE id = ?", 
        array(
            $_SESSION["pid"], 
            $_SESSION["authProvider"], 
            $_SESSION["authUser"], 
            $userauthorized, 
            $_POST["cigarettes_per_day"], 
            $_POST["years_smoking"], 
            $_POST["pack_years"], 
            $_POST["risk_level"], 
            $_POST["notes"], 
            $_GET["id"]
        )
    );
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
