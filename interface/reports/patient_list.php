<?php

/**
 * Enhanced Patient List Report with Advanced Filtering
 * This report lists patients with comprehensive filtering options including
 * age range, status, program, office location, and country filters, with modern UI and export capabilities.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Alejandro Sergio D'Alessandro
 * @copyright Copyright (c) 2025 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$from_date = (!empty($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01');
$to_date = (!empty($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_provider = empty($_POST['form_provider']) ? 0 : intval($_POST['form_provider']);
$form_age_range = $_POST['form_age_range'] ?? '';
$form_gender = $_POST['form_gender'] ?? '';
$form_status = $_POST['form_status'] ?? '';
$form_program = $_POST['form_program'] ?? '';
$form_office_location = $_POST['form_office_location'] ?? '';
$form_country = $_POST['form_country'] ?? '';

// Handle Excel export
if (!empty($_POST['form_excelexport'])) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=patient_list_" . date('Y-m-d') . ".xls");
    header("Content-Description: File Transfer");
}
// Handle CSV export
elseif (!empty($_POST['form_csvexport'])) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=patient_list_" . date('Y-m-d') . ".csv");
    header("Content-Description: File Transfer");
} else {
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xlt('Patient List Report'); ?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

    <style>
        /* Modern Dashboard Styling */
        body {
            background: #fff;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            background: white;
            margin: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header-section {
	    background: #77BC1F;
            color: #fff;
            padding: 30px;
            text-align: center;
        }

        .header-section h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 300;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .filters-section {
            background: #f8f9fa;
            padding: 30px;
            border-bottom: 1px solid #e9ecef;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .filter-group {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #3498db;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .filter-group.active-filter {
            border-left-color: #E74C3C;
            background: #fef9f9;
        }

        .filter-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 12px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            height: 45px;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            outline: none;
        }

        /* Specific styling for select dropdowns */
        select.form-control {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            padding-right: 40px;
            text-align: left;
            vertical-align: middle;
        }

        select.form-control option {
            padding: 8px 12px;
            color: #495057;
            background-color: #fff;
            border: none;
        }

        select.form-control option:hover {
            background-color: #f8f9fa;
        }

        select.form-control option:checked {
            background-color: #3498db;
            color: white;
        }

        /* Fix for date inputs */
        input.form-control[type="text"] {
            text-align: left;
            padding-left: 15px;
        }

        /* Responsive grid adjustments */
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
            align-items: stretch;
        }

        /* Date range specific styling */
        .date-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .date-inputs input {
            font-size: 0.9rem;
            text-align: center;
        }

        /* Provider dropdown styling */
        #form_provider {
            min-width: 100%;
        }

        /* Ensure all select elements have consistent styling */
        select {
            cursor: pointer;
        }

        select:disabled {
            background-color: #e9ecef;
            opacity: 1;
        }

        /* Fix for Bootstrap conflicts */
        .form-control:not(textarea) {
            height: 45px;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .filter-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .filter-group {
                min-height: auto;
                padding: 15px;
            }
        }

        .btn-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
        }

        .btn-success {
            background: linear-gradient(45deg, #27ae60, #229954);
            color: white;
        }

        .btn-info {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: white;
        }

        .btn-secondary {
            background: linear-gradient(45deg, #6c757d, #5a6268);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .results-section {
            padding: 30px;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(45deg, #ffffff, #f8f9fa);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #FFECB4;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .table {
            margin: 0;
            font-size: 0.9rem;
        }

        .table thead th {
            background: linear-gradient(45deg, #34495e, #2c3e50);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 15px 12px;
            border: none;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .table tbody tr:hover {
            background-color: #e3f2fd;
            transform: scale(1.01);
            transition: all 0.2s ease;
        }

        .table td {
            padding: 12px;
            border-color: #e9ecef;
            vertical-align: middle;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .program-badge {
            background: #e3f2fd;
            color: #0d47a1;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .office-badge {
            background: #f3e5f5;
            color: #4a148c;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .country-badge {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .age-badge {
            background: #fff3cd;
            color: #856404;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .gender-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .gender-male {
            background: #cce5ff;
            color: #0066cc;
        }

        .gender-female {
            background: #ffe0f0;
            color: #cc0066;
        }

        @media print {
            body { background: white !important; }
            .filters-section { display: none !important; }
            .btn-group { display: none !important; }
            .main-container { margin: 0; box-shadow: none; }
        }

        .loading {
            text-align: center;
            padding: 50px;
            color: #7f8c8d;
        }

        .no-results {
            text-align: center;
            padding: 50px;
            color: #7f8c8d;
        }
    </style>

    <script>
        $(function () {
            oeFixedHeaderSetup(document.getElementById('mymaintable'));
            top.printLogSetup(document.getElementById('printbutton'));

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            });

            // Real-time filter updates
            $('select, input').on('change keyup', function() {
                updateFilters();
            });

            // Initialize filters on page load
            updateFilters();
        });

        function updateFilters() {
            // Add visual feedback for active filters
            $('.filter-group').removeClass('active-filter');
            
            // Check each filter and mark as active if it has a value
            if ($('#form_age_range').val() && $('#form_age_range').val() !== '') {
                $('#form_age_range').closest('.filter-group').addClass('active-filter');
            }
            if ($('#form_gender').val() && $('#form_gender').val() !== '') {
                $('#form_gender').closest('.filter-group').addClass('active-filter');
            }
            if ($('#form_status').val() && $('#form_status').val() !== '') {
                $('#form_status').closest('.filter-group').addClass('active-filter');
            }
            if ($('#form_program').val() && $('#form_program').val() !== '') {
                $('#form_program').closest('.filter-group').addClass('active-filter');
            }
            if ($('#form_office_location').val() && $('#form_office_location').val() !== '') {
                $('#form_office_location').closest('.filter-group').addClass('active-filter');
            }
            if ($('#form_country').val() && $('#form_country').val() !== '') {
                $('#form_country').closest('.filter-group').addClass('active-filter');
            }
            if ($('#form_provider').val() && $('#form_provider').val() !== '') {
                $('#form_provider').closest('.filter-group').addClass('active-filter');
            }
            if ($('input[name="form_from_date"]').val() || $('input[name="form_to_date"]').val()) {
                $('input[name="form_from_date"]').closest('.filter-group').addClass('active-filter');
            }
        }

        function exportData(type) {
            $('#form_' + type + 'export').val('1');
            $('#form_refresh').val('true');
            $('#theform').submit();
        }
    </script>
</head>

<body>
    <div class="main-container">
        <div class="header-section">
            <h1><?php echo xlt('Patient List Report'); ?></h1>
            <p>Age - Gender - Program - Status - Office - Country</p>
        </div>

        <form name='theform' id='theform' method='post' action='patient_list.php' onsubmit='return top.restoreSession()'>
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
            <input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
            <input type='hidden' name='form_excelexport' id='form_excelexport' value=''/>

            <div class="filters-section">
                <div class="filter-grid">
                    <div class="filter-group">
                        <div class="filter-label"><?php echo xlt('Date Range'); ?></div>
                        <div class="date-inputs">
                            <input class='datepicker form-control' type='text' name='form_from_date' placeholder='From Date' value='<?php echo attr(oeFormatShortDate($from_date)); ?>'>
                            <input class='datepicker form-control' type='text' name='form_to_date' placeholder='To Date' value='<?php echo attr(oeFormatShortDate($to_date)); ?>'>
                        </div>
                    </div>

                    <div class="filter-group">
                        <div class="filter-label"><?php echo xlt('Provider'); ?></div>
                        <?php generate_form_field(array('data_type' => 10, 'field_id' => 'provider', 'empty_title' => '-- All Providers --', 'class' => 'form-control'), ($_POST['form_provider'] ?? '')); ?>
                    </div>

                    <div class="filter-group">
                        <div class="filter-label"><?php echo xlt('Age Range'); ?></div>
                        <select name='form_age_range' id='form_age_range' class='form-control'>
                            <option value=''><?php echo xlt('-- All Ages --'); ?></option>
                            <option value='0-9' <?php echo ($form_age_range == '0-9') ? 'selected' : ''; ?>><?php echo xlt('0-9 years'); ?></option>
                            <option value='10-19' <?php echo ($form_age_range == '10-19') ? 'selected' : ''; ?>><?php echo xlt('10-19 years'); ?></option>
                            <option value='20-29' <?php echo ($form_age_range == '20-29') ? 'selected' : ''; ?>><?php echo xlt('20-29 years'); ?></option>
                            <option value='30-39' <?php echo ($form_age_range == '30-39') ? 'selected' : ''; ?>><?php echo xlt('30-39 years'); ?></option>
                            <option value='40-49' <?php echo ($form_age_range == '40-49') ? 'selected' : ''; ?>><?php echo xlt('40-49 years'); ?></option>
                            <option value='50-59' <?php echo ($form_age_range == '50-59') ? 'selected' : ''; ?>><?php echo xlt('50-59 years'); ?></option>
                            <option value='60-69' <?php echo ($form_age_range == '60-69') ? 'selected' : ''; ?>><?php echo xlt('60-69 years'); ?></option>
                            <option value='70-79' <?php echo ($form_age_range == '70-79') ? 'selected' : ''; ?>><?php echo xlt('70-79 years'); ?></option>
                            <option value='80+' <?php echo ($form_age_range == '80+') ? 'selected' : ''; ?>><?php echo xlt('80+ years'); ?></option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <div class="filter-label"><?php echo xlt('Gender'); ?></div>
                        <select name='form_gender' id='form_gender' class='form-control'>
                            <option value=''><?php echo xlt('-- All Genders --'); ?></option>
                            <option value='Male' <?php echo ($form_gender == 'Male') ? 'selected' : ''; ?>><?php echo xlt('Male'); ?></option>
                            <option value='Female' <?php echo ($form_gender == 'Female') ? 'selected' : ''; ?>><?php echo xlt('Female'); ?></option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <div class="filter-label"><?php echo xlt('Status'); ?></div>
                        <select name='form_status' id='form_status' class='form-control'>
                            <option value=''><?php echo xlt('-- All Status --'); ?></option>
                            <option value='ACTIVE' <?php echo ($form_status == 'ACTIVE') ? 'selected' : ''; ?>><?php echo xlt('Active'); ?></option>
                            <option value='INACTIVE' <?php echo ($form_status == 'INACTIVE') ? 'selected' : ''; ?>><?php echo xlt('Inactive'); ?></option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <div class="filter-label"><?php echo xlt('Program'); ?></div>
                        <select name='form_program' id='form_program' class='form-control'>
                            <option value=''><?php echo xlt('-- All Programs --'); ?></option>
                            <option value='BISA' <?php echo ($form_program == 'BISA') ? 'selected' : ''; ?>>BISA</option>
                            <option value='BMI' <?php echo ($form_program == 'BMI') ? 'selected' : ''; ?>>BMI</option>
                            <option value='BRS' <?php echo ($form_program == 'BRS') ? 'selected' : ''; ?>>BRS</option>
                            <option value='CSP' <?php echo ($form_program == 'CSP') ? 'selected' : ''; ?>>CSP</option>
                            <option value='DG' <?php echo ($form_program == 'DG') ? 'selected' : ''; ?>>DG</option>
                            <option value='DGP' <?php echo ($form_program == 'DGP') ? 'selected' : ''; ?>>DGP</option>
                            <option value='EIB' <?php echo ($form_program == 'EIB') ? 'selected' : ''; ?>>EIB</option>
                            <option value='GRP' <?php echo ($form_program == 'GRP') ? 'selected' : ''; ?>>GRP</option>
                            <option value='GWP' <?php echo ($form_program == 'GWP') ? 'selected' : ''; ?>>GWP</option>
                            <option value='HR4UNK' <?php echo ($form_program == 'HR4UNK') ? 'selected' : ''; ?>>HR4UNK</option>
                            <option value='IBP' <?php echo ($form_program == 'IBP') ? 'selected' : ''; ?>>IBP</option>
                            <option value='RPP' <?php echo ($form_program == 'RPP') ? 'selected' : ''; ?>>RPP</option>
                            <option value='SAS' <?php echo ($form_program == 'SAS') ? 'selected' : ''; ?>>SAS</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <div class="filter-label"><?php echo xlt('Office Location'); ?></div>
                        <select name='form_office_location' id='form_office_location' class='form-control'>
                            <option value=''><?php echo xlt('-- All Offices --'); ?></option>
                            <?php
                            // Get unique office locations from database
                            $office_query = "SELECT DISTINCT usertext2 FROM patient_data WHERE usertext2 != '' AND usertext2 IS NOT NULL ORDER BY usertext2";
                            $office_result = sqlStatement($office_query);
                            while ($office_row = sqlFetchArray($office_result)) {
                                $selected = ($form_office_location == $office_row['usertext2']) ? 'selected' : '';
                                echo "<option value='" . attr($office_row['usertext2']) . "' $selected>" . text($office_row['usertext2']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <div class="filter-label"><?php echo xlt('Country'); ?></div>
                        <select name='form_country' id='form_country' class='form-control'>
                            <option value=''><?php echo xlt('-- All Countries --'); ?></option>
                            <?php
                            // Get unique countries from database
                            $country_query = "SELECT DISTINCT country_code FROM patient_data WHERE country_code != '' AND country_code IS NOT NULL ORDER BY country_code";
                            $country_result = sqlStatement($country_query);
                            while ($country_row = sqlFetchArray($country_result)) {
                                $selected = ($form_country == $country_row['country_code']) ? 'selected' : '';
                                echo "<option value='" . attr($country_row['country_code']) . "' $selected>" . text($country_row['country_code']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary" onclick='$("#form_refresh").val("true");'>
                        <i class="fa fa-search"></i> <?php echo xlt('Generate Report'); ?>
                    </button>
                    <?php if (!empty($_POST['form_refresh'])) { ?>
                        <button type="button" class="btn btn-success" onclick='exportData("csv")'>
                            <i class="fa fa-download"></i> <?php echo xlt('Export CSV'); ?>
                        </button>
                        <button type="button" class="btn btn-info" onclick='exportData("excel")'>
                            <i class="fa fa-file-excel"></i> <?php echo xlt('Export Excel'); ?>
                        </button>
                        <button type="button" id='printbutton' class="btn btn-secondary">
                            <i class="fa fa-print"></i> <?php echo xlt('Print Report'); ?>
                        </button>
                    <?php } ?>
                </div>
            </div>
        </form>

        <?php
} // end not export

if (!empty($_POST['form_refresh']) || !empty($_POST['form_csvexport']) || !empty($_POST['form_excelexport'])) {
    // Build the query with filters
    $sqlArrayBind = array();
    $whereConditions = array();
    
    // Base query - Updated to include office location and country
    $query = "SELECT DISTINCT " .
        "p.fname, p.mname, p.lname, p.pid, p.pubpid, p.DOB, p.sex, " .
        "p.usertext8 as status, p.usertext3 as program, " .
        "p.usertext2 as office_location, p.country_code as country, " .
        "p.street, p.city, p.state, p.postal_code, " .
        "count(e.date) AS ecount, max(e.date) AS edate " .
        "FROM patient_data AS p ";

    // Date range filter
    if (!empty($from_date)) {
        $query .= "JOIN form_encounter AS e ON e.pid = p.pid AND e.date >= ? AND e.date <= ? ";
        array_push($sqlArrayBind, $from_date . ' 00:00:00', $to_date . ' 23:59:59');
        
        if ($form_provider) {
            $whereConditions[] = "e.provider_id = ?";
            array_push($sqlArrayBind, $form_provider);
        }
    } else {
        if ($form_provider) {
            $query .= "JOIN form_encounter AS e ON e.pid = p.pid ";
            $whereConditions[] = "e.provider_id = ?";
            array_push($sqlArrayBind, $form_provider);
        } else {
            $query .= "LEFT OUTER JOIN form_encounter AS e ON e.pid = p.pid ";
        }
    }

    // Status filter
    if (!empty($form_status)) {
        $whereConditions[] = "p.usertext8 = ?";
        array_push($sqlArrayBind, $form_status);
    }

    // Gender filter
    if (!empty($form_gender)) {
        $genderCode = ($form_gender == 'Male') ? 'Male' : 'Female';
        $whereConditions[] = "p.sex = ?";
        array_push($sqlArrayBind, $genderCode);
    }

    // Program filter
    if (!empty($form_program)) {
        $whereConditions[] = "p.usertext3 = ?";
        array_push($sqlArrayBind, $form_program);
    }

    // Office Location filter
    if (!empty($form_office_location)) {
        $whereConditions[] = "p.usertext2 = ?";
        array_push($sqlArrayBind, $form_office_location);
    }

    // Country filter
    if (!empty($form_country)) {
        $whereConditions[] = "p.country_code = ?";
        array_push($sqlArrayBind, $form_country);
    }

    // Add WHERE conditions
    if (!empty($whereConditions)) {
        $query .= "WHERE " . implode(" AND ", $whereConditions) . " ";
    }

    $query .= "GROUP BY p.pid ORDER BY p.lname, p.fname, p.mname";

    $res = sqlStatement($query, $sqlArrayBind);
    $totalpts = 0;
    $activeCount = 0;
    $inactiveCount = 0;
    $maleCount = 0;
    $femaleCount = 0;
    $programCounts = array();
    $officeCounts = array();
    $countryCounts = array();
    $ageGroups = array();

    // Process results for export or display
    $results = array();
    while ($row = sqlFetchArray($res)) {
        // Calculate age
        $age = '';
        $ageGroup = '';
        if (!empty($row['DOB'])) {
            $dob = $row['DOB'];
            $tdy = $row['edate'] ? $row['edate'] : date('Y-m-d');
            $ageInMonths = (substr($tdy, 0, 4) * 12) + substr($tdy, 5, 2) -
                   (substr($dob, 0, 4) * 12) - substr($dob, 5, 2);
            $dayDiff = substr($tdy, 8, 2) - substr($dob, 8, 2);
            if ($dayDiff < 0) {
                --$ageInMonths;
            }
            $age = intval($ageInMonths / 12);
            
            // Determine age group
            if ($age < 10) $ageGroup = '0-9';
            elseif ($age < 20) $ageGroup = '10-19';
            elseif ($age < 30) $ageGroup = '20-29';
            elseif ($age < 40) $ageGroup = '30-39';
            elseif ($age < 50) $ageGroup = '40-49';
            elseif ($age < 60) $ageGroup = '50-59';
            elseif ($age < 70) $ageGroup = '60-69';
            elseif ($age < 80) $ageGroup = '70-79';
            else $ageGroup = '80+';
        }

        // Apply age range filter
        if (!empty($form_age_range) && $ageGroup !== $form_age_range) {
            continue;
        }

        // Convert gender code to display format
        $displayGender = '';
        if ($row['sex'] == 'Male') $displayGender = 'Male';
        elseif ($row['sex'] == 'Female') $displayGender = 'Female';

        $row['calculated_age'] = $age;
        $row['age_group'] = $ageGroup;
        $row['display_gender'] = $displayGender;
        $results[] = $row;

        // Update statistics
        $totalpts++;
        
        if ($row['status'] == 'ACTIVE') $activeCount++;
        if ($row['status'] == 'INACTIVE') $inactiveCount++;
        
        if ($row['sex'] == 'Male') $maleCount++;
        if ($row['sex'] == 'Female') $femaleCount++;
        
        if (!empty($row['program'])) {
            $programCounts[$row['program']] = ($programCounts[$row['program']] ?? 0) + 1;
        }
        
        if (!empty($row['office_location'])) {
            $officeCounts[$row['office_location']] = ($officeCounts[$row['office_location']] ?? 0) + 1;
        }
        
        if (!empty($row['country'])) {
            $countryCounts[$row['country']] = ($countryCounts[$row['country']] ?? 0) + 1;
        }
        
        if (!empty($ageGroup)) {
            $ageGroups[$ageGroup] = ($ageGroups[$ageGroup] ?? 0) + 1;
        }
    }

    // Handle exports
    if ($_POST['form_csvexport'] || $_POST['form_excelexport']) {
        $isExcel = !empty($_POST['form_excelexport']);
        
        if ($isExcel) {
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            echo "<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\">\n";
            echo "<Worksheet ss:Name=\"Patient List\">\n<Table>\n";
            echo "<Row><Cell><Data ss:Type=\"String\">Last Visit</Data></Cell>";
            echo "<Cell><Data ss:Type=\"String\">First Name</Data></Cell>";
            echo "<Cell><Data ss:Type=\"String\">Last Name</Data></Cell>";
            echo "<Cell><Data ss:Type=\"String\">ID</Data></Cell>";
            echo "<Cell><Data ss:Type=\"String\">Age</Data></Cell>";
            echo "<Cell><Data ss:Type=\"String\">Gender</Data></Cell>";
            echo "<Cell><Data ss:Type=\"String\">Status</Data></Cell>";
            echo "<Cell><Data ss:Type=\"String\">Program</Data></Cell>";
            echo "<Cell><Data ss:Type=\"String\">Office Location</Data></Cell>";
            echo "<Cell><Data ss:Type=\"String\">Country</Data></Cell></Row>\n";
        } else {
            echo csvEscape(xl('Last Visit')) . ',';
            echo csvEscape(xl('First Name')) . ',';
            echo csvEscape(xl('Last Name')) . ',';
            echo csvEscape(xl('Middle Name')) . ',';
            echo csvEscape(xl('ID')) . ',';
            echo csvEscape(xl('Age')) . ',';
            echo csvEscape(xl('Gender')) . ',';
            echo csvEscape(xl('Status')) . ',';
            echo csvEscape(xl('Program')) . ',';
            echo csvEscape(xl('Office Location')) . ',';
            echo csvEscape(xl('Country')) . "\n";
        }

        foreach ($results as $row) {
            if ($isExcel) {
                echo "<Row>";
                echo "<Cell><Data ss:Type=\"String\">" . htmlspecialchars(oeFormatShortDate(substr($row['edate'], 0, 10))) . "</Data></Cell>";
                echo "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['fname']) . "</Data></Cell>";
                echo "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['lname']) . "</Data></Cell>";
                echo "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['pubpid']) . "</Data></Cell>";
                echo "<Cell><Data ss:Type=\"Number\">" . htmlspecialchars($row['calculated_age']) . "</Data></Cell>";
                echo "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['display_gender']) . "</Data></Cell>";
                echo "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['status']) . "</Data></Cell>";
                echo "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['program']) . "</Data></Cell>";
                echo "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['office_location']) . "</Data></Cell>";
                echo "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['country']) . "</Data></Cell>";
                echo "</Row>\n";
            } else {
                echo csvEscape(oeFormatShortDate(substr($row['edate'], 0, 10))) . ',';
                echo csvEscape($row['fname']) . ',';
                echo csvEscape($row['lname']) . ',';
                echo csvEscape($row['pubpid']) . ',';
                echo csvEscape($row['calculated_age']) . ',';
                echo csvEscape($row['display_gender']) . ',';
                echo csvEscape($row['status']) . ',';
                echo csvEscape($row['program']) . ',';
                echo csvEscape($row['office_location']) . ',';
                echo csvEscape($row['country']) . "\n";
            }
        }

        if ($isExcel) {
            echo "</Table>\n</Worksheet>\n</Workbook>\n";
        }
    } else {
        // Display results
        ?>
        <div class="results-section">
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-value"><?php echo text($totalpts); ?></div>
                    <div class="stat-label"><?php echo xlt('Total Patients'); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo text($maleCount); ?></div>
                    <div class="stat-label"><?php echo xlt('Male Patients'); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo text($femaleCount); ?></div>
                    <div class="stat-label"><?php echo xlt('Female Patients'); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo text($activeCount); ?></div>
                    <div class="stat-label"><?php echo xlt('Active Patients'); ?></div>
                </div>
            </div>

            <?php if ($totalpts > 0) { ?>
            <div class="table-container">
                <table class='table' id='mymaintable'>
                    <thead>
                        <tr>
                            <th><?php echo xlt('Last Visit'); ?></th>
                            <th><?php echo xlt('Patient'); ?></th>
                            <th><?php echo xlt('ID'); ?></th>
                            <th><?php echo xlt('Age'); ?></th>
                            <th><?php echo xlt('Gender'); ?></th>
                            <th><?php echo xlt('Status'); ?></th>
                            <th><?php echo xlt('Program'); ?></th>
                            <th><?php echo xlt('Office'); ?></th>
                            <th><?php echo xlt('Country'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row) { ?>
                        <tr>
                            <td><?php echo text(oeFormatShortDate(substr($row['edate'], 0, 10))); ?></td>
                            <td>
                                <strong><?php echo text($row['lname'] . ', ' . $row['fname']); ?></strong>
                                <?php if (!empty($row['mname'])) echo '<br><small>' . text($row['mname']) . '</small>'; ?>
                            </td>
                            <td><span class="badge badge-secondary"><?php echo text($row['pubpid']); ?></span></td>
                            <td><span class="age-badge"><?php echo text($row['calculated_age'] . ' yrs'); ?></span></td>
                            <td>
                                <?php if (!empty($row['display_gender'])) { ?>
                                    <span class="gender-badge gender-<?php echo strtolower($row['display_gender']); ?>">
                                        <?php echo text($row['display_gender']); ?>
                                    </span>
                                <?php } ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                    <?php echo text($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($row['program'])) { ?>
                                    <span class="program-badge"><?php echo text($row['program']); ?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if (!empty($row['office_location'])) { ?>
                                    <span class="office-badge"><?php echo text($row['office_location']); ?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if (!empty($row['country'])) { ?>
                                    <span class="country-badge"><?php echo text($row['country']); ?></span>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php } else { ?>
            <div class="no-results">
                <i class="fa fa-search fa-3x"></i>
                <h3><?php echo xlt('No patients found'); ?></h3>
                <p><?php echo xlt('Try adjusting your filter criteria and search again.'); ?></p>
            </div>
            <?php } ?>
        </div>
        <?php
    }
} // end if refresh or export

if (empty($_POST['form_refresh']) && empty($_POST['form_csvexport']) && empty($_POST['form_excelexport'])) {
    ?>
        <div class="results-section">
            <div class="no-results">
                <i class="fa fa-chart-bar fa-3x"></i>
                <h3><?php echo xlt('Patient List Reports'); ?></h3>
                <p><?php echo xlt('Patient data with advanced analytics.'); ?></p>
            </div>
        </div>
    <?php
}

if (empty($_POST['form_csvexport']) && empty($_POST['form_excelexport'])) {
    ?>
    </div>
</body>
</html>
    <?php
}
?>
