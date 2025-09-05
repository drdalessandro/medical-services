<?php

/**
 * Cardiovascular Risk HEARTS Calculator form - report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dr Alejandro Sergio D'Alessandro
 * @copyright Copyright (c) 2025 EPA Bienestar IA
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . '/../../globals.php');
require_once($GLOBALS["srcdir"] . "/api.inc.php");

function cardiovascular_risk_hearts_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $data = formFetch("form_cardiovascular_risk_hearts", $id);
    
    if ($data) {
        print "<table><tr>";
        foreach ($data as $key => $value) {
            if ($key == "id" || $key == "pid" || $key == "user" || $key == "groupname" || $key == "authorized" || $key == "activity" || $key == "date" || $value == "" || $value == "0000-00-00 00:00:00") {
                continue;
            }
            
            if ($value == "on") {
                $value = "yes";
            }
            
            // Format specific fields for better display
            switch ($key) {
                case "gender":
                    $key = "Gender";
                    $value = ucfirst($value);
                    break;
                case "age":
                    $key = "Age";
                    $value = $value . " years";
                    break;
                case "smoker":
                    $key = "Smoker";
                    $value = ucfirst($value);
                    break;
                case "systolic_pressure":
                    $key = "Systolic Pressure";
                    $value = $value . " mmHg";
                    break;
                case "weight":
                    $key = "Weight";
                    $value = $value . " kg";
                    break;
                case "height":
                    $key = "Height";
                    $value = $value . " cm";
                    break;
                case "cv_disease_history":
                    $key = "CV Disease History";
                    $value = ucfirst($value);
                    break;
                case "chronic_kidney_disease":
                    $key = "Kidney Disease";
                    $value = ucfirst($value);
                    break;
                case "diabetes_mellitus":
                    $key = "Diabetes";
                    $value = ucfirst($value);
                    break;
                case "know_cholesterol":
                    $key = "Knows Cholesterol";
                    $value = ucfirst($value);
                    break;
                case "total_cholesterol":
                    $key = "Total Cholesterol";
                    $value = $value . " mg/dL";
                    break;
                case "calculated_risk":
                    $key = "Cardiovascular Risk";
                    $value = $value . "%";
                    break;
                case "risk_category":
                    $key = "Cardiovascular Risk";
                    break;
                case "bmi":
                    $key = "BMI";
                    break;
                default:
                    $key = ucwords(str_replace("_", " ", $key));
                    break;
            }
            
            print "<td><span class=bold>" . xlt($key) . ": </span><span class=text>" . text($value) . "</span></td>";
            $count++;
            
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
        print "</tr></table>";
    }
    
    return $count;
}
?>
