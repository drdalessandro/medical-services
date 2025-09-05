<?php

/**
 * Tobacco Pack Year Calculator form - report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dr Alejandro Sergio D'Alessandro
 * @copyright Copyright (c) 2025 EPA Bienestar IA
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . '/../../globals.php');
require_once($GLOBALS["srcdir"] . "/api.inc.php");

function tobacco_pack_year_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $data = formFetch("form_tobacco_pack_year", $id);
    
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
            if ($key == "cigarettes_per_day") {
                $key = "Cigarettes per day";
            } elseif ($key == "years_smoking") {
                $key = "Years smoking";
            } elseif ($key == "pack_years") {
                $key = "Pack years";
                $value = $value . " pack-years";
            } elseif ($key == "risk_level") {
                $key = "Risk level";
            } else {
                $key = ucwords(str_replace("_", " ", $key));
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
