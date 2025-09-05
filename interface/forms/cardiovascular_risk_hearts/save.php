<?php

/**
 * Cardiovascular Risk HEARTS Calculator form - save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dr Alejandro Sergio D'Alessandro
 * @copyright Copyright (c) 2025 EPA Bienestar IA
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
    $newid = formSubmit("form_cardiovascular_risk_hearts", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "Cardiovascular Risk HEARTS", $newid, "cardiovascular_risk_hearts", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    sqlStatement("UPDATE form_cardiovascular_risk_hearts SET 
        pid = ?, 
        groupname = ?, 
        user = ?, 
        authorized = ?, 
        activity = 1, 
        date = NOW(), 
        gender = ?,
        age = ?,
        smoker = ?,
        systolic_pressure = ?,
        weight = ?,
        height = ?,
        cv_disease_history = ?,
        chronic_kidney_disease = ?,
        diabetes_mellitus = ?,
        know_cholesterol = ?,
        total_cholesterol = ?,
        calculated_risk = ?,
        risk_category = ?,
        bmi = ?,
        notes = ?
        WHERE id = ?", 
        array(
            $_SESSION["pid"], 
            $_SESSION["authProvider"], 
            $_SESSION["authUser"], 
            $userauthorized, 
            $_POST["gender"],
            $_POST["age"],
            $_POST["smoker"],
            $_POST["systolic_pressure"],
            $_POST["weight"],
            $_POST["height"],
            $_POST["cv_disease_history"],
            $_POST["chronic_kidney_disease"],
            $_POST["diabetes_mellitus"],
            $_POST["know_cholesterol"],
            $_POST["total_cholesterol"],
            $_POST["calculated_risk"],
            $_POST["risk_category"],
            $_POST["bmi"],
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
