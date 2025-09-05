<?php

/**
 * labs_fragment.php - Fragment for patient dashboard
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@example.com>
 * @copyright Copyright (c) 2025 Your Name <your.email@example.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once($GLOBALS['fileroot'] . '/interface/forms/labs/C_FormLabs.class.php');

try {
    $pid = $GLOBALS['pid'];
    
    if (empty($pid)) {
        echo "<div class='alert alert-warning'>" . xlt("No patient selected") . "</div>";
        return;
    }

    // Get the most recent lab results
    $sql = "SELECT * FROM form_labs WHERE pid = ? ORDER BY date DESC LIMIT 1";
    $result = sqlQuery($sql, [$pid]);
    
    if (!$result) {
        echo "<div class='text-center mt-3'>";
        echo "<p>" . xlt("No laboratory results found") . "</p>";
        echo "<a href='{$GLOBALS['web_root']}/interface/forms/labs/new.php' class='btn btn-primary btn-sm' onclick='top.restoreSession()'>";
        echo xlt("Add Laboratory Results") . "</a>";
        echo "</div>";
        return;
    }

    // Display the most recent results
    echo "<div class='labs-summary'>";
    echo "<div class='row'>";
    
    $labValues = [
        'glucose' => ['label' => xl('Glucose'), 'unit' => 'mg/dL', 'normal' => '< 100'],
        'cholesterol' => ['label' => xl('Cholesterol'), 'unit' => 'mg/dL', 'normal' => '< 200'],
        'triglycerides' => ['label' => xl('Triglycerides'), 'unit' => 'mg/dL', 'normal' => '< 150'],
        'uric_acid' => ['label' => xl('Uric Acid'), 'unit' => 'mg/dL', 'normal' => '< 6.5'],
        'cholinesterase' => ['label' => xl('Cholinesterase'), 'unit' => 'U/L', 'normal' => 'Variable'],
        'urinary_phenol' => ['label' => xl('Urinary Phenol'), 'unit' => 'mg/L', 'normal' => 'Variable']
    ];

    foreach ($labValues as $key => $info) {
        $value = $result[$key] ?? 0;
        if ($value > 0) {
            echo "<div class='col-md-4 col-sm-6 mb-2'>";
            echo "<div class='card border-0 bg-light'>";
            echo "<div class='card-body p-2'>";
            echo "<h6 class='card-title mb-1 text-primary'>" . $info['label'] . "</h6>";
            echo "<p class='card-text mb-0'>";
            echo "<strong>" . number_format($value, 2) . " " . $info['unit'] . "</strong><br>";
            echo "<small class='text-muted'>" . xlt('Normal') . ": " . $info['normal'] . "</small>";
            echo "</p>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
        }
    }
    
    echo "</div>";
    
    // Show date and notes if available
    $date = date('M j, Y g:i A', strtotime($result['date']));
    echo "<div class='mt-2'>";
    echo "<small class='text-muted'>" . xlt('Date') . ": " . text($date) . "</small>";
    
    if (!empty($result['note'])) {
        echo "<br><small class='text-muted'>" . xlt('Notes') . ": " . text($result['note']) . "</small>";
    }
    echo "</div>";
    
    // Action buttons
    echo "<div class='text-center mt-3'>";
    echo "<a href='../encounter/trend_form.php?formname=labs' ";
    echo "class='btn btn-outline-primary btn-sm' onclick='top.restoreSession()' target='_blank'>";
    echo xlt("Click here to view and graph all labs") . "</a>";
    echo "&nbsp;";
    echo "<a href='../../forms/labs/new.php' ";
    echo "class='btn btn-primary btn-sm' onclick='top.restoreSession()'>";
    echo xlt("Add New Results") . "</a>";
    echo "</div>";
    
    echo "</div>";

} catch (Exception $e) {
    error_log("Labs fragment error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>" . xlt("Error loading laboratory results") . "</div>";
}
?>

<style>
.labs-summary .card {
    transition: transform 0.2s;
}
.labs-summary .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
