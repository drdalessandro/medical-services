<?php

/**
 * Modern Patient List Creation for OpenEMR 7
 * This report provides a comprehensive view of patient data with modern visualizations
 * and enhanced export capabilities including charts, CSV, Excel, and print functionality.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Custom Development for OpenEMR 7 Powered By Alejandro Sergio D'Alessandro for CIMMYT & CGYAR
 * @copyright Copyright (c) 2025
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once "../globals.php";
require_once "$srcdir/patient.inc.php";
require_once "$srcdir/options.inc.php";
require_once "../drugs/drugs.inc.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('patients', 'med')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()
        ->render('core/unauthorized.html.twig', ['pageTitle' => xl("Patient List Creation") ]);
    exit;
}

if (!empty($_POST) && !CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

// Updated search options - removed Patient ID, Ethnicity, Lab Results, Prescriptions, Communication, Insurance Companies
$search_options = array(
    "demos" => array(
        "title" => xl("Demographics"),
        "cols" => array(
            "patient_date"   => array("heading" => xl("Date Created"), "width" => "nowrap"),
            "patient_name"   => array("heading" => xl("Patient Name"), "width" => "10%"),
            "patient_age"    => array("heading" => xl("Age"), "width" => "nowrap"),
            "patient_sex"    => array("heading" => xl("Gender"), "width" => "nowrap"),
            "patient_program" => array("heading" => xl("Program"), "width" => "10%"),
            "patient_office" => array("heading" => xl("Office Location"), "width" => "10%"),
            "patient_country" => array("heading" => xl("Country"), "width" => "nowrap"),
            "users_provider" => array("heading" => xl("Provider"), "width" => "10%")
            
        ),
        "acl" => ["patients", "demo"]
    ),
    "allergs" => array(
        "title" => xl("Allergies"),
        "copy" => "diagnosis_check"
    ),
    "probs" => array(
        "title" => xl("Problems"),
        "copy" => "diagnosis_check"
    ),
    "pathos" => array(
        "title" => xl("Pathological"),
        "copy" => "diagnosis_check"
    ),
    "imms" => array(
        "title" => xl("Immunizations"),
        "copy" => "diagnosis_check"
    ),
    "meds" => array(
        "title" => xl("Medications"),
        "copy" => "diagnosis_check"
    ),
    "diagnosis_check" => array(
        "hidden" => true,
        "cols" => array(
            "other_date"      => array("heading" => xl("Diagnosis Date"), "width" => "nowrap"),
            "patient_name"    => array("heading" => xl("Patient Name"), "width" => "10%"),
            "patient_age"     => array("heading" => xl("Age"), "width" => "nowrap"),
            "patient_sex"     => array("heading" => xl("Gender"), "width" => "nowrap"),
            "patient_program" => array("heading" => xl("Program"), "width" => "10%"),
            "patient_office" => array("heading" => xl("Office Location"), "width" => "10%"),
            "patient_country" => array("heading" => xl("Country"), "width" => "nowrap"),
            "users_provider"  => array("heading" => xl("Provider"), "width" => "10%"),
            "lists_title"     => array("width" => "15%"),
            "pr_diagnosis"    => array("heading" => xl("Diagnosis Codes"), "width" => "20%")
        ),
        "sort_cols" => -1,
        "acl" => ["patients", "med"]
    ),
    "encounts" => array(
        "title" => xl("Encounters"),
        "cols" => array(
            "other_date"     => array("heading" => xl("Encounter Date"), "width" => "nowrap"),
            "patient_name"   => array("heading" => xl("Patient Name"), "width" => "10%"),
            "patient_age"    => array("heading" => xl("Age"), "width" => "nowrap"),
            "patient_sex"    => array("heading" => xl("Gender"), "width" => "nowrap"),
            "patient_program" => array("heading" => xl("Program"), "width" => "10%"),
            "patient_office" => array("heading" => xl("Office Location"), "width" => "10%"),
            "patient_country" => array("heading" => xl("Country"), "width" => "nowrap"),
            "users_provider" => array("heading" => xl("Provider"), "width" => "10%"),
            "enc_type"       => array("heading" => xl("Encounter type"), "width" => "20%"),
            "enc_reason"     => array("heading" => xl("Reason"), "width" => "15%"),
            "enc_facility"   => array("heading" => xl("Facility"), "width" => "10%"),
            "enc_discharge"  => array("heading" => xl("Discharge Disposition"), "width" => "10%")
        ),
        "acl" => ["encounters", "relaxed"]
    ),
    "observs" => array(
        "title" => xl("Observations"),
        "cols" => array(
            "other_date"      => array("heading" => xl("Date"), "width" => "nowrap"),
            "patient_name"    => array("heading" => xl("Patient Name"), "width" => "10%"),
            "patient_age"     => array("heading" => xl("Age"), "width" => "nowrap"),
            "patient_sex"     => array("heading" => xl("Gender"), "width" => "nowrap"),
            "users_provider"  => array("heading" => xl("Provider"), "width" => "10%"),
            "obs_code"        => array("heading" => xl("Code"), "width" => "nowrap"),
            "obs_description" => array("heading" => xl("Description"), "width" => "15%"),
            "obs_type"        => array("heading" => xl("Type"), "width" => "10%"),
            "obs_value"       => array("heading" => xl("Value"), "width" => "nowrap"),
            "obs_units"       => array("heading" => xl("Units"), "width" => "nowrap"),
            "obs_comments"    => array("heading" => xl("Comments"), "width" => "20%")
        ),
        "sort_cols" => -1,
        "acl" => ["encounters", "coding_a"]
    ),
    "procs" => array(
        "title" => xl("Procedures"),
        "cols" => array(
            "other_date"      => array("heading" => xl("Order Date"), "width" => "nowrap"),
            "patient_name"    => array("heading" => xl("Patient Name"), "width" => "10%"),
            "users_provider"  => array("heading" => xl("Procedure Provider"), "width" => "10%"),
            "pr_lab"          => array("heading" => xl("Lab"), "width" => "10%"),
            "pr_status"       => array("heading" => xl("Status"), "width" => "nowrap"),
            "prc_procedure"   => array("heading" => xl("Procedure Test"), "width" => "10%"),
            "pr_diagnosis"    => array("heading" => xl("Primary Diagnosis"), "width" => "20%"),
            "prc_diagnoses"   => array("heading" => xl("Diagnosis Codes"), "width" => "20%")
        ),
        "sort_cols" => -2,
        "acl" => ["encounters", "coding_a"]
    )
);

// All encounter types
$encarr = [];
$encarr_from_db = sqlStatement('SELECT option_id, title FROM list_options WHERE list_id = "encounter-types" ORDER BY seq ASC');
for ($iter = 0; $row = sqlFetchArray($encarr_from_db); $iter++) {
    $encarr[$row['option_id']] = $row['title'];
}

// Get unique programs from patient_data.usertext3
$programs = [];
$programs_query = sqlStatement('SELECT DISTINCT usertext3 FROM patient_data WHERE usertext3 IS NOT NULL AND usertext3 != "" ORDER BY usertext3');
while ($row = sqlFetchArray($programs_query)) {
    $programs[] = $row['usertext3'];
}

// Get unique office locations from patient_data.usertext2
$office_locations = [];
$locations_query = sqlStatement('SELECT DISTINCT usertext2 FROM patient_data WHERE usertext2 IS NOT NULL AND usertext2 != "" ORDER BY usertext2');
while ($row = sqlFetchArray($locations_query)) {
    $office_locations[] = $row['usertext2'];
}

// Get unique countries from patient_data.country_code
$countries = [];
$countries_query = sqlStatement('SELECT DISTINCT country_code FROM patient_data WHERE country_code IS NOT NULL AND country_code != "" ORDER BY country_code');
while ($row = sqlFetchArray($countries_query)) {
    $countries[] = $row['country_code'];
}

// POST inputs
$sql_date_from = (!empty($_POST['date_from'])) ? DateTimeToYYYYMMDDHHMMSS($_POST['date_from']) : date('Y-01-01 H:i:s');
$sql_date_to = (!empty($_POST['date_to'])) ? DateTimeToYYYYMMDDHHMMSS($_POST['date_to']) : date('Y-m-d H:i:s');
$provider_id = isset($_POST['form_provider']) ? $_POST['form_provider'] : '';
$age_from = $_POST["age_from"] ?? '';
$age_to = $_POST["age_to"] ?? '';
$sql_gender = $_POST["gender"] ?? '';
$patient_status = $_POST["patient_status"] ?? '';
$program_filter = $_POST["program_filter"] ?? ''; // Nuevo filtro de Program
$office_location_filter = $_POST["office_location_filter"] ?? ''; // Nuevo filtro de Office Location
$country_filter = $_POST["country_filter"] ?? ''; // Nuevo filtro de Country
$form_drug_name = trim($_POST["form_drug_name"] ?? '');
$form_diagnosis = trim($_POST["form_diagnosis"] ?? '');
$form_lab_results = trim($_POST["form_lab_results"] ?? '');
$form_service_codes = trim($_POST["form_service_codes"] ?? '');
$form_immunization = trim($_POST["form_immunization"] ?? '');
$encounter_type = trim($_POST["encounter_type"] ?? '');
$observation_description = trim($_POST["observation_description"] ?? '');
$procedure_diagnosis = trim($_POST["procedure_diagnosis"] ?? '');

// Separate filter options
$allergy_filter = trim($_POST["allergy_filter"] ?? '');
$problem_filter = trim($_POST["problem_filter"] ?? '');
$patho_filter = trim($_POST["patho_filter"] ?? '');
$immu_filter = trim($_POST["immu_filter"] ?? '');
// Export options
$csv = !empty($_POST['form_csvexport']) && $_POST['form_csvexport'] == true;
$excel = !empty($_POST['form_excelexport']) && $_POST['form_excelexport'] == true;

if ($csv) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=patient_list_custom.csv");
    header("Content-Description: File Transfer");
} else if ($excel) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=patient_list_custom.xlsx");
    header("Content-Description: File Transfer");
} else { ?>
<html>
    <head>
        <title><?php echo xlt('Patient List Creation'); ?></title>
        
        <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>
        
        <!-- Chart.js CDN -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
        
        <!-- SheetJS for Excel export -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
        
        <style>
            :root {
                --primary-color: #77BC1F;
                --secondary-color: #F9f9f9;
                --text-dark: #333;
                --border-color: #eee;
                --hover-color: #f8f9fa;
            }
            
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, var(--secondary-color) 0%, #ffffff 100%);
                margin: 0;
                padding: 20px;
            }
            
            .modern-card {
                background: white;
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                padding: 24px;
                margin-bottom: 24px;
                border-left: 4px solid var(--primary-color);
            }
            
        .header-section {
            background: #77BC1F;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header-section h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 300;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
            .title-header {
                background: linear-gradient(45deg, var(--primary-color), #5a9b15);
                color: white;
                padding: 20px;
                border-radius: 12px;
                text-align: center;
                margin-bottom: 24px;
                box-shadow: 0 4px 12px rgba(119, 188, 31, 0.3);
            }
            
            .filter-section {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
                margin-bottom: 24px;
            }
            
            .filter-group {
                padding: 16px;
                border-radius: 8px;
                border: 2px solid var(--primary-color);
            }
            
            .filter-group h4 {
                color: #bbb;
                margin-top: 0;
                margin-bottom: 16px;
                font-weight: 600;
            }
            
            .form-control {
                border: 2px solid var(--border-color);
                border-radius: 6px;
                padding: 8px 12px;
                transition: border-color 0.3s ease;
            }
            
            .form-control:focus {
                border-color: var(--primary-color);
                outline: none;
                box-shadow: 0 0 0 3px rgba(119, 188, 31, 0.1);
            }
            
            .btn {
                padding: 12px 24px;
                border: none;
                border-radius: 6px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                text-decoration: none;
                display: inline-block;
                text-align: center;
            }
            .btn-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
            .btn-primary {
                background: #2980B9;
                color: white;
            }
            
            .btn-primary:hover {
                background: #5a9b15;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(119, 188, 31, 0.3);
            }
            
            .btn-secondary {
                background: var(--secondary-color);
                color: var(--text-dark);
                border: 2px solid var(--primary-color);
            }
            
            .btn-secondary:hover {
                background: var(--primary-color);
                color: white;
            }
            
            .action-buttons {
                display: flex;
                gap: 12px;
                flex-wrap: wrap;
                justify-content: center;
                margin: 24px 0;
            }
            
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 16px;
                margin-bottom: 24px;
            }
            
            .stat-card {
                background: white;
                border-radius: 8px;
                padding: 20px;
                text-align: center;
                border: 2px solid var(--primary-color);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            
            .stat-number {
                font-size: 2em;
                font-weight: bold;
                color: var(--primary-color);
            }
            
            .stat-label {
                color: var(--text-dark);
                margin-top: 8px;
            }
            
            .visualization-section {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 24px;
                margin-bottom: 24px;
            }
            
            .chart-container {
                background: white;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                height: 400px;
            }
            
            .modern-table {
                width: 100%;
                border-collapse: collapse;
                background: white;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            
            .modern-table th {
                background: var(--primary-color);
                color: white;
                padding: 16px;
                text-align: left;
                font-weight: 600;
            }
            
            .modern-table td {
                padding: 12px 16px;
                border-bottom: 1px solid var(--border-color);
            }
            
            .modern-table tr:hover {
                background: var(--hover-color);
            }
            
            .modern-table tr:nth-child(even) {
                background: #f8f9fa;
            }
            
            @media print {
                #report_parameters {
                    display: none;
                }
                .action-buttons {
                    display: none;
                }
                body {
                    background: white;
                }
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
            @media (max-width: 768px) {
                .visualization-section {
                    grid-template-columns: 1fr;
                }
                .filter-section {
                    grid-template-columns: 1fr;
                }
                .action-buttons {
                    flex-direction: column;
                }
            }
        </style>

        <script>
            function Form_Validate() {
                var d = document.forms[0];
                FromDate = d.date_from.value;
                ToDate = d.date_to.value;
                if (FromDate.length > 0 && ToDate.length > 0 && Date.parse(FromDate) > Date.parse(ToDate)) {
                    alert(<?php echo xlj('To date must be later than From date!'); ?>);
                    return false;
                }
                return true;
            }

            function submitForm() {
                var d_from = new String($('#date_from').val());
                var d_to = new String($('#date_to').val());

                var d_from_arr = d_from.split('-');
                var d_to_arr = d_to.split('-');

                var dt_from = new Date(d_from_arr[0], d_from_arr[1], d_from_arr[2]);
                var dt_to = new Date(d_to_arr[0], d_to_arr[1], d_to_arr[2]);

                var mili_from = dt_from.getTime();
                var mili_to = dt_to.getTime();
                var diff = mili_to - mili_from;

                $('#date_error').css("display", "none");

                if (diff < 0) {
                    $('#date_error').css("display", "inline");
                } else {
                    $("#form_refresh").attr("value","true");
                    top.restoreSession();
                    $("#theform").submit();
                }
            }

            function exportToCSV() {
                $("#form_csvexport").attr("value", "true");
                $("#form_excelexport").attr("value", "");
                submitForm();
            }

            function exportToExcel() {
                // Get table data
                var table = document.getElementById('results-table');
                if (!table) {
                    alert('No data to export');
                    return;
                }
                
                var wb = XLSX.utils.table_to_book(table, {sheet: "Patient List"});
                XLSX.writeFile(wb, "patient_list_" + new Date().toISOString().split('T')[0] + ".xlsx");
            }

            function printReport() {
                window.print();
            }

            function sortingCols(sort_by, sort_order) {
                $("#sortby").val(sort_by);
                $("#sortorder").val(sort_order);
                $("#form_refresh").attr("value", "true");
                $("#theform").submit();
            }

            $(function () {
                $(".numeric_only").keydown(function(event) {
                    if (event.keyCode == 46 || event.keyCode == 8) {
                        // Allow backspace and delete
                    } else {
                        if (!((event.keyCode >= 96 && event.keyCode <= 105) || (event.keyCode >= 48 && event.keyCode <= 57))) {
                            event.preventDefault();
                        }
                    }
                });

                $('.datetimepicker').datetimepicker({
                    <?php
                    $datetimepicker_timepicker = true;
                    $datetimepicker_showseconds = true;
                    $datetimepicker_formatInput = true;
                    include $GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php';
                    ?>
                });

                $('.wildcard_field').on('focus', function() {
                    var field_value = $(this).val();
                    if (field_value === '') {
                        $(this).val('%%');
                        var field = $(this)[0];
                        field.setSelectionRange(1, 1);
                    }
                });
            });

            function srch_option_change(elem) {
                $('#sortby').val('');
                $('#sortorder').val('');

                // Show/hide specific inputs based on search option
                if (elem.value == 'allergs') {
                    $('#allergy_filters').show();
                    $('#problem_filters').hide();
                    $('#patho_filters').hide();
                    $('#immu_filters').hide();
                } else if (elem.value == 'probs') {
                    $('#problem_filters').show();
                    $('#allergy_filters').hide();
                    $('#patho_filters').hide();
                    $('#immu_filters').hide();
                } else if (elem.value == 'pathos') {
		    $('#patho_filters').show();
                    $('#problem_filters').hide();
                    $('#allergy_filters').hide();
                    $('#immu_filters').hide();
                } else if (elem.value == 'imms') {
		    $('#immu_filters').show();
                    $('#problem_filters').hide();
                    $('#allergy_filters').hide();
                    $('#patho_filters').hide();
                } else {
                    $('#allergy_filters').hide();
                    $('#problem_filters').hide();
                    $('#patho_filters').hide();
                    $('#immu_filters').hide();
                }
                if (elem.value == 'encounts') {
                    $('#enc_type').show();
                } else {
                    $('#encounter_type').val('');
                    $('#enc_type').hide();
                }
                if (elem.value == 'observs') {
                    $('#obs_desc').show();
                } else {
                    $('#observation_description').val('');
                    $('#obs_desc').hide();
                }
            }
        </script>
    </head>

    <body>
        <div class="header-section">
            <h1><?php echo xlt('Patient List Creation'); ?></h1>
            <p><?php echo xlt('Visualization and Analytics'); ?></p>
        </div>

        <form name='theform' id='theform' method='post' action='patient_list_creation.php' onSubmit="return Form_Validate();">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"/>
            <input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
            <input type='hidden' name='form_excelexport' id='form_excelexport' value=''/>
            <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
            <input type="hidden" name="sortby" id="sortby" value="<?php echo attr($_POST['sortby'] ?? ''); ?>" />
            <input type="hidden" name="sortorder" id="sortorder" value="<?php echo attr($_POST['sortorder'] ?? ''); ?>" />
            
            <div id="report_parameters" class="modern-card">
                <div class="filter-section">
                    <div class="filter-group">
                        <h4><?php echo xlt('Date Range'); ?></h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <label class='col-form-label'><?php echo xlt('From'); ?>:</label>
                                <input type='text' class='datetimepicker form-control' name='date_from' id="date_from" value='<?php echo attr(oeFormatDateTime($sql_date_from, 0, true)); ?>'>
                            </div>
                            <div>
                                <label class='col-form-label'><?php echo xlt('To'); ?>:</label>
                                <input type='text' class='datetimepicker form-control' name='date_to' id="date_to" value='<?php echo attr(oeFormatDateTime($sql_date_to, 0, true)); ?>'>
                            </div>
                        </div>
                        <div style="margin-top: 12px;">
                            <label class='col-form-label'><?php echo xlt('Status'); ?>:</label>
                            <select class="form-control" name="patient_status" id="patient_status">
                                <option value=""><?php echo xlt('All'); ?></option>
                                <option value="Active" <?php echo ($patient_status == 'Active') ? 'selected' : ''; ?>><?php echo xlt('Active'); ?></option>
                                <option value="Inactive" <?php echo ($patient_status == 'Inactive') ? 'selected' : ''; ?>><?php echo xlt('Inactive'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="filter-group">
                        <h4><?php echo xlt('Report Options'); ?></h4>
                        <div>
                            <label class='col-form-label'><?php echo xlt('Report Type'); ?>:</label>
                            <select class="form-control" name="srch_option" id="srch_option" onchange="srch_option_change(this)">
                                <?php foreach ($search_options as $search_option_key => $search_option_items) {
                                    if (!isset($search_option_items["hidden"]) || !$search_option_items["hidden"]) { ?>
                                        <option <?php echo (!empty($_POST['srch_option']) && ($_POST['srch_option'] == $search_option_key)) ? 'selected' : ''; ?>
                                        value="<?php echo attr($search_option_key); ?>"><?php echo text($search_option_items["title"]); ?></option>
                                    <?php }
                                } ?>
                            </select>
                        </div>
                        
                        <!-- Nuevos filtros de Program, Office Location y Country -->
                        <div style="margin-top: 12px;">
                            <label class='col-form-label'><?php echo xlt('Program'); ?>:</label>
                            <select class="form-control" name="program_filter" id="program_filter">
                                <option value=""><?php echo xlt('All Programs'); ?></option>
                                <?php foreach ($programs as $program) { ?>
                                    <option value="<?php echo attr($program); ?>" <?php echo ($program_filter == $program) ? 'selected' : ''; ?>>
                                        <?php echo text($program); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        
                        <div style="margin-top: 12px;">
                            <label class='col-form-label'><?php echo xlt('Office Location'); ?>:</label>
                            <select class="form-control" name="office_location_filter" id="office_location_filter">
                                <option value=""><?php echo xlt('All Locations'); ?></option>
                                <?php foreach ($office_locations as $location) { ?>
                                    <option value="<?php echo attr($location); ?>" <?php echo ($office_location_filter == $location) ? 'selected' : ''; ?>>
                                        <?php echo text($location); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        
                        <div style="margin-top: 12px;">
                            <label class='col-form-label'><?php echo xlt('Country'); ?>:</label>
                            <select class="form-control" name="country_filter" id="country_filter">
                                <option value=""><?php echo xlt('All Countries'); ?></option>
                                <?php foreach ($countries as $country) { ?>
                                    <option value="<?php echo attr($country); ?>" <?php echo ($country_filter == $country) ? 'selected' : ''; ?>>
                                        <?php echo text($country); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="filter-group">
                        <h4><?php echo xlt('Patient Demographics'); ?></h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <label class='col-form-label'><?php echo xlt('Age From'); ?>:</label>
                                <input name='age_from' class="numeric_only form-control" type='text' id="age_from" value="<?php echo attr($age_from); ?>" size='3' maxlength='3'/>
                            </div>
                            <div>
                                <label class='col-form-label'><?php echo xlt('Age To'); ?>:</label>
                                <input name='age_to' class="numeric_only form-control" type='text' id="age_to" value="<?php echo attr($age_to); ?>" size='3' maxlength='3'/>
                            </div>
                        </div>
                        <div style="margin-top: 12px;">
                            <label class='col-form-label'><?php echo xlt('Gender'); ?>:</label>
                            <?php echo generate_select_list('gender','sex', $sql_gender, 'Select Gender', 'All',); ?>
                        </div>
                        <div style="margin-top: 12px;">
                            <label class='col-form-label'><?php echo xlt('Provider'); ?>:</label>
                            <?php generate_form_field(array('data_type' => 10, 'field_id' => 'provider', 'empty_title' => 'All'), $provider_id); ?>
                        </div>
                    </div>

                    <!-- Separate Allergy Filters -->
                    <div id="allergy_filters" class="filter-group" style="display: none;">
                        <h4><?php echo xlt('Allergy Filters'); ?></h4>
                        <div>
                            <label class='col-form-label'><?php echo xlt('Allergy Type'); ?>:</label>
                            <input class="form-control wildcard_field" name="allergy_filter" id="allergy_filter" 
                                   title="<?php echo xla('(% matches any string, _ matches any character)'); ?>" 
                                   placeholder="<?php echo xla('Allergy Name/Code'); ?>"
                                   value="<?php echo attr($allergy_filter); ?>"/>
                        </div>
                    </div>
                    <!-- Separate Problem Filters -->
                    <div id="problem_filters" class="filter-group" style="display: none;">
                        <h4><?php echo xlt('Problem Filters'); ?></h4>
                        <div>
                            <label class='col-form-label'><?php echo xlt('Problem Type'); ?>:</label>
                            <input class="form-control wildcard_field" name="problem_filter" id="problem_filter" 
                                   title="<?php echo xla('(% matches any string, _ matches any character)'); ?>" 
                                   placeholder="<?php echo xla('Problem Name/Code'); ?>"
                                   value="<?php echo attr($problem_filter); ?>"/>
                        </div>
                    </div>
                    <!-- Separate Patho Filters -->
                    <div id="patho_filters" class="filter-group" style="display: none;">
                        <h4><?php echo xlt('Pathologic Filters'); ?></h4>
                        <div>
                            <label class='col-form-label'><?php echo xlt('Pathologic Type'); ?>:</label>
                            <input class="form-control wildcard_field" name="patho_filter" id="patho_filter" 
                                   title="<?php echo xla('(% matches any string, _ matches any character)'); ?>" 
                                   placeholder="<?php echo xla('Pathologic Name/Code'); ?>"
                                   value="<?php echo attr($patho_filter); ?>"/>
                        </div>
                    </div>
                    <!-- Separate Immu Filters -->
                    <div id="immu_filters" class="filter-group" style="display: none;">
                        <h4><?php echo xlt('Immunization Filters'); ?></h4>
                        <div>
                            <label class='col-form-label'><?php echo xlt('Immunization Type'); ?>:</label>
                            <input class="form-control wildcard_field" name="immu_filter" id="immu_filter" 
                                   title="<?php echo xla('(% matches any string, _ matches any character)'); ?>" 
                                   placeholder="<?php echo xla('Immunization Name/Code'); ?>"
                                   value="<?php echo attr($immu_filter); ?>"/>
                        </div>
                    </div>

                    <!-- Encounter Type Filter -->
                    <div id="enc_type" class="filter-group" style="display: none;">
                        <h4><?php echo xlt('Encounter Filters'); ?></h4>
                        <div>
                            <label class='col-form-label'><?php echo xlt('Encounter Type'); ?>:</label>
                            <select class="form-control" name="encounter_type" id="encounter_type">
                                <option><?php echo xlt('All'); ?></option>
                                <?php foreach ($encarr as $enc_id => $enc_t) { ?>
                                    <option <?php echo (!empty($_POST['encounter_type']) && ($_POST['encounter_type'] == $enc_id)) ? 'selected' : ''; ?> 
                                            value="<?php echo attr($enc_id); ?>"><?php echo text($enc_t); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <!-- Observation Filter -->
                    <div id="obs_desc" class="filter-group" style="display: none;">
                        <h4><?php echo xlt('Observation Filters'); ?></h4>
                        <div>
                            <label class='col-form-label'><?php echo xlt('Code/Description'); ?>:</label>
                            <input class="form-control wildcard_field" name="observation_description" id="observation_description" 
                                   title="<?php echo xla('(% matches any string, _ matches any character)'); ?>" 
                                   placeholder="<?php echo xla('Code/Description'); ?>"
                                   value="<?php echo attr($observation_description); ?>"/>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn btn-primary" onclick="submitForm();">
                        <?php echo xlt('Generate Report'); ?>
                    </button>
                    <?php if (isset($_POST['form_refresh'])) { ?>
                        <button type="button" class="btn btn-secondary" onclick="exportToCSV();">
                            <?php echo xlt('Export CSV'); ?>
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="exportToExcel();">
                            <?php echo xlt('Export Excel'); ?>
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="printReport();">
                            <?php echo xlt('Print Report'); ?>
                        </button>
                    <?php } ?>
                </div>
            </div>

<?php }

// SQL scripts for the various searches
$sqlBindArray = [];
if (!empty($_POST['form_refresh'])) {
    $sqlstmt = "SELECT pd.date AS patient_date, "
        . "CONCAT(pd.lname, ', ', pd.fname) AS patient_name, "
        . "DATE_FORMAT(FROM_DAYS(DATEDIFF('" . date('Y-m-d H:i:s') . "',pd.dob)), '%Y')+0 AS patient_age, "
        . "pd.sex AS patient_sex, "
        . "pd.usertext3 AS patient_program, "
        . "pd.usertext2 AS patient_office, "
        . "pd.country_code AS patient_country, "
        . "CONCAT(u.lname, ', ', u.fname) AS users_provider";

    $srch_option = $_POST['srch_option'];

    // Set all unset fields if the search option is set to copy (inherit), point to the source
    if (isset($search_options[$srch_option]["copy"])) {
        foreach ($search_options[$search_options[$srch_option]["copy"]] as $srch_copy_key => $srch_copy_item) {
            if (!isset($search_options[$srch_option][$srch_copy_key])) {
                $search_options[$srch_option][$srch_copy_key] = $srch_copy_item;
            }
        }
    }
    $srch_option_pointer = isset($search_options[$srch_option]["copy"]) ? $search_options[$srch_option]["copy"] : $srch_option;

    switch ($srch_option_pointer) {
        case "diagnosis_check":
            $sqlstmt .= ", li.date AS other_date, li.diagnosis AS pr_diagnosis, li.title AS lists_title";
            break;
        case "encounts":
            $sqlstmt .= ", enc.date AS other_date, "
                    . "enc.reason AS enc_reason, "
                    . "enc.facility AS enc_facility, "
                    . "enc.encounter_type_description AS enc_type, "
                    . "enc.discharge_disposition AS enc_discharge";
            break;
        case "observs":
            $sqlstmt .= ", obs.date AS other_date, "
                    . "obs.code AS obs_code, "
                    . "obs.observation AS obs_comments, "
                    . "obs.description AS obs_description, "
                    . "obs.ob_type AS obs_type, "
                    . "obs.ob_value AS obs_value, "
                    . "obs.ob_unit AS obs_units";
            break;
        case "procs":
            $sqlstmt .= ", pr_ord.date_ordered AS other_date, "
                    . "pr_ord.order_status AS pr_status, "
                    . "pr_prov.name AS pr_lab, "
                    . "pr_ord.order_diagnosis AS pr_diagnosis, "
                    . "pr_code.procedure_name as prc_procedure, "
                    . "pr_code.diagnoses AS prc_diagnoses";
            break;
    }

    $sqlstmt .= " from patient_data as pd";
    
    // JOINs
    if ($srch_option != "encounts" && $srch_option != "observs") {
        $sqlstmt .= " LEFT OUTER JOIN users AS u ON u.id = pd.providerid";
    }
    
    switch ($srch_option_pointer) {
        case "diagnosis_check":
            $sqlstmt .= " LEFT OUTER JOIN lists AS li ON li.pid = pd.pid AND li.type = '";
            if ($srch_option == "allergs") {
                $sqlstmt .= "allergy";
                $search_options[$srch_option]["cols"]["lists_title"]["heading"] = xl("Allergy");
            } else if ($srch_option == "probs") {
                $sqlstmt .= "medical_problem";
                $search_options[$srch_option]["cols"]["lists_title"]["heading"] = xl("Problem");
            } else if ($srch_option == "pathos") {
                $sqlstmt .= "pathological";
                $search_options[$srch_option]["cols"]["lists_title"]["heading"] = xl("Pathologic");
            } else if ($srch_option == "imms") {
                $sqlstmt .= "immunization";
                $search_options[$srch_option]["cols"]["lists_title"]["heading"] = xl("Immunization");
            } else { // meds
                $sqlstmt .= "medication";
                $search_options[$srch_option]["cols"]["lists_title"]["heading"] = xl("Medication");
            }
            $sqlstmt .= "'";
            break;
        case "encounts":
            $sqlstmt .= " LEFT OUTER JOIN form_encounter AS enc ON pd.pid = enc.pid "
                . "LEFT OUTER JOIN users AS u ON enc.provider_id = u.id";
            break;
        case "observs":
            $sqlstmt .= " LEFT OUTER JOIN form_observation AS obs ON pd.pid = obs.pid "
                . "LEFT OUTER JOIN users AS u ON obs.user = u.username";
            break;
        case "procs":
            $sqlstmt .= " LEFT OUTER JOIN procedure_order AS pr_ord ON pr_ord.patient_id = pd.pid "
                . "LEFT OUTER JOIN procedure_providers AS pr_prov ON pr_prov.ppid = pr_ord.lab_id "
                . "LEFT OUTER JOIN procedure_order_code AS pr_code ON pr_code.procedure_order_id = pr_ord.procedure_order_id";
            break;
    }

    // WHERE conditions started
    $whr_stmt = " WHERE 1";
    switch ($srch_option_pointer) {
        case "diagnosis_check":
            if ($srch_option == "probs") {
                $whr_stmt .= " AND li.title != ''";
            }
            $whr_stmt .= " AND li.date >= ? AND li.date < DATE_ADD(?, INTERVAL 1 DAY) AND li.date <= ?";
            array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
            break;
        case "diagnosis_check":
            if ($srch_option == "pathos") {
                $whr_stmt .= " AND li.title != ''";
            }
            $whr_stmt .= " AND li.date >= ? AND li.date < DATE_ADD(?, INTERVAL 1 DAY) AND li.date <= ?";
            array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
            break;
        case "diagnosis_check":
            if ($srch_option == "imms") {
                $whr_stmt .= " AND li.title != ''";
            }
            $whr_stmt .= " AND li.date >= ? AND li.date < DATE_ADD(?, INTERVAL 1 DAY) AND li.date <= ?";
            array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
            break;

        case "encounts":
            $whr_stmt .= " AND enc.date >= ? AND enc.date < DATE_ADD(?, INTERVAL 1 DAY) AND enc.date <= ?";
            array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
            break;
        case "observs":
            $whr_stmt .= " AND obs.date >= ? AND obs.date < DATE_ADD(?, INTERVAL 1 DAY) AND obs.date <= ?";
            array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
            break;
        case "procs":
            $whr_stmt .= " AND pr_ord.date_ordered >= ? AND pr_ord.date_ordered < DATE_ADD(?, INTERVAL 1 DAY) AND pr_ord.date_ordered <= ?";
            array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
            break;
        default:
            $whr_stmt .= " AND pd.date >= ? AND pd.date < DATE_ADD(?, INTERVAL 1 DAY) AND pd.date <= ?";
            array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
            break;
    }

    // WHERE conditions based on persistent inputs
    if (strlen($provider_id) != 0) {
        $whr_stmt .= " AND u.id = ?";
        array_push($sqlBindArray, $provider_id);
    }
    if (strlen($age_from) != 0) {
        $whr_stmt .= " AND DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 >= ?";
        array_push($sqlBindArray, $age_from);
    }
    if (strlen($age_to) != 0) {
        $whr_stmt .= " AND DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 <= ?";
        array_push($sqlBindArray, $age_to);
    }
    if (strlen($sql_gender) != 0) {
        $whr_stmt .= " AND pd.sex = ?";
        array_push($sqlBindArray, $sql_gender);
    }
    
    // Filtro de Status
    if (strlen($patient_status) != 0) {
        $whr_stmt .= " AND pd.usertext8 = ?";
        array_push($sqlBindArray, $patient_status);
    }
    
    // Nuevos filtros agregados
    if (strlen($program_filter) != 0) {
        $whr_stmt .= " AND pd.usertext3 = ?";
        array_push($sqlBindArray, $program_filter);
    }
    
    if (strlen($office_location_filter) != 0) {
        $whr_stmt .= " AND pd.usertext2 = ?";
        array_push($sqlBindArray, $office_location_filter);
    }
    
    if (strlen($country_filter) != 0) {
        $whr_stmt .= " AND pd.country_code = ?";
        array_push($sqlBindArray, $country_filter);
    }

    // WHERE conditions for specific search options
    if ($srch_option == "encounts" && strlen($encounter_type) > 0 && $encounter_type != "All") {
        $whr_stmt .= " AND enc.encounter_type_code = ?";
        array_push($sqlBindArray, $encounter_type);
    }
    if ($srch_option == "observs" && strlen($observation_description) > 0) {
        $whr_stmt .= " AND (obs.code LIKE ? OR obs.description LIKE ?)";
        array_push($sqlBindArray, $observation_description, $observation_description);
    }
    if ($srch_option == "allergs" && strlen($allergy_filter) > 0) {
        $whr_stmt .= " AND (li.title LIKE ? OR li.diagnosis LIKE ?)";
        array_push($sqlBindArray, $allergy_filter, $allergy_filter);
    }
    if ($srch_option == "probs" && strlen($problem_filter) > 0) {
        $whr_stmt .= " AND (li.title LIKE ? OR li.diagnosis LIKE ?)";
        array_push($sqlBindArray, $problem_filter, $problem_filter);
    }
    if ($srch_option == "pathos" && strlen($patho_filter) > 0) {
        $whr_stmt .= " AND (li.title LIKE ? OR li.diagnosis LIKE ?)";
        array_push($sqlBindArray, $patho_filter, $patho_filter);
    }
    if ($srch_option == "imms" && strlen($immu_filter) > 0) {
        $whr_stmt .= " AND (li.title LIKE ? OR li.diagnosis LIKE ?)";
        array_push($sqlBindArray, $immu_filter, $immu_filter);
    }
    

    if (strlen($procedure_diagnosis) > 0) {
        if ($srch_option_pointer == "diagnosis_check") {
            $whr_stmt .= " AND li.diagnosis LIKE ?";
            array_push($sqlBindArray, $procedure_diagnosis);
        } else if ($srch_option == "procs") {
            $whr_stmt .= " AND (pr_ord.order_diagnosis LIKE ? OR pr_code.diagnoses LIKE ?)";
            array_push($sqlBindArray, $procedure_diagnosis, $procedure_diagnosis);
        }
    }

    if (!AclMain::aclCheckCore($search_options[$srch_option]["acl"][0], $search_options[$srch_option]["acl"][1])) {
        echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()
            ->render('core/unauthorized.html.twig', ['pageTitle' => xl("Patient List Creation") . " (" . $search_options[$srch_option]["title"] . ")"]);
        exit;
    }

    // Sorting
    $sortby = $_POST['sortby'] ?? '';
    $sortorder = $_POST['sortorder'] ?? '';
    $sort = array_keys($search_options[$srch_option]["cols"]);
    
    if ($sortby == "") {
        switch ($srch_option_pointer) {
            case "diagnosis_check":
                $sortby = $sort[1];
                break;
            default:
                $sortby = $sort[0];
        }
    }
    
    if ($sortorder == "") {
        $sortorder = "asc";
    }

    $odrstmt = " ORDER BY " . escape_identifier($sortby, $sort, true) . " " . escape_sort_order($sortorder);
    $sqlstmt .= $whr_stmt . $odrstmt;
    $result = sqlStatement($sqlstmt, $sqlBindArray);

    if (sqlNumRows($result) > 0 || $csv || $excel) {
        $report_data_arr = [];
        $patient_arr = [];
        $gender_stats = ['F' => 0, 'M' => 0, 'N' => 0,];
        $age_groups = ['0-9' => 0, '10-19' => 0, '20-29' => 0, '30-39' => 0, '40-49' => 0, '50-59' => 0, '60-69' => 0, '70-79' => 0, '80-89' => 0, '90-99' => 0, '100-125' => 0,];
        
        while ($row = sqlFetchArray($result)) {
            $report_data = [];
            foreach (array_keys($search_options[$srch_option]["cols"]) as $report_item_name) {
                array_push($report_data, $row[$report_item_name]);
            }
            array_push($report_data_arr, $report_data);
            array_push($patient_arr, $row["patient_age"] . "|" . $row["patient_sex"]);
            
            // Statistics collection
            $gender = $row["patient_sex"];
            if ($gender == 'Female') $gender_stats['F']++;
            else if ($gender == 'Male') $gender_stats['M']++;
            else if ($gender == 'None') $gender_stats['N']++;
            else $gender_stats['Unknown']++;
            
            $age = intval($row["patient_age"]);
            if ($age <= 9) $age_groups['0-9']++;
            else if ($age <= 19) $age_groups['10-19']++;
            else if ($age <= 29) $age_groups['20-29']++;
            else if ($age <= 39) $age_groups['30-39']++;
            else if ($age <= 49) $age_groups['40-49']++;
            else if ($age <= 59) $age_groups['50-59']++;
            else if ($age <= 69) $age_groups['60-69']++;
            else if ($age <= 79) $age_groups['70-79']++;
            else if ($age <= 89) $age_groups['80-89']++;
            else if ($age <= 99) $age_groups['90-99']++;
            else if ($age <= 126) $age_groups['100-125']++;
            else $age_groups['127+']++;
        }

        $total_patients = count(array_unique($patient_arr));

        if ($csv) {
            // CSV Export
            foreach (array_keys($search_options[$srch_option]["cols"]) as $report_col_key => $report_col) {
                echo csvEscape($search_options[$srch_option]["cols"][$report_col]["heading"]);
                if ($report_col_key < count($search_options[$srch_option]["cols"]) - 1) {
                    echo ",";
                } else {
                    echo "\n";
                }
            }
            foreach ($report_data_arr as $report_data) {
                foreach ($report_data as $report_value_key => $report_value) {
                    echo csvEscape($report_value);
                    if ($report_value_key < count($report_data) - 1) {
                        echo ",";
                    } else {
                        echo "\n";
                    }
                }
            }
        } else if ($excel) {
            // Excel Export (simplified)
            echo "<table border='1'>";
            echo "<tr>";
            foreach (array_keys($search_options[$srch_option]["cols"]) as $report_col) {
                echo "<th>" . text($search_options[$srch_option]["cols"][$report_col]["heading"]) . "</th>";
            }
            echo "</tr>";
            foreach ($report_data_arr as $report_data) {
                echo "<tr>";
                foreach ($report_data as $report_value) {
                    echo "<td>" . text($report_value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else { 
            // HTML Display ?>
            
            <div class="modern-card">
                <h3><?php echo xlt('Report Summary'); ?></h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo text(count($report_data_arr)); ?></div>
                        <div class="stat-label"><?php echo xlt('Total Records'); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo text($gender_stats['M']); ?></div>
                        <div class="stat-label"><?php echo xlt('Male Patients'); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo text($gender_stats['F']); ?></div>
                        <div class="stat-label"><?php echo xlt('Female Patients'); ?></div>
                    </div>
                </div>
            </div>

            <div class="visualization-section">
                <div class="chart-container">
                    <canvas id="genderChart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="ageChart"></canvas>
                </div>
            </div>

            <div class="modern-card">
                <h3><?php echo xlt('Patient Data Table'); ?></h3>
                <table class="modern-table" id="results-table">
                    <thead>
                        <tr>
                            <?php foreach (array_keys($search_options[$srch_option]["cols"]) as $report_col_key => $report_col) {
                                echo '<th>' . text($search_options[$srch_option]["cols"][$report_col]["heading"]) . '</th>';
                            } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report_data_arr as $report_data) { ?>
                            <tr>
                                <?php foreach ($report_data as $report_value_key => $report_value) {
                                    $report_col = array_keys($search_options[$srch_option]["cols"])[$report_value_key];
                                    $report_value_print = null;
                                    switch ($report_col) {
                                        case "patient_date":
                                        case "other_date":
                                            $report_value_print = ($report_value != '') ? text(oeFormatDateTime($report_value, "global", true)) : '';
                                            break;
                                        case "pr_diagnosis":
                                        case "prc_diagnoses":
                                            if ($report_value != '') {
                                                $report_value_print = '<ul style="margin: 0; padding-left: 0.5em;">';
                                                foreach (explode(';', $report_value) as $code) {
                                                    $report_value_print .= '<li><abbr title="' . attr($code) . '">' . text(getCodeDescription($code)) . '</abbr></li>';
                                                }
                                                $report_value_print .= '</ul>';
                                            } else {
                                                $report_value_print = text($report_value);
                                            }
                                            break;
                                        default:
                                            $report_value_print = text($report_value);
                                    }
                                    echo '<td>' . $report_value_print . '</td>';
                                } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <script>
                // Gender Distribution Chart
                const genderCtx = document.getElementById('genderChart').getContext('2d');
                const genderChart = new Chart(genderCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Male', 'Female', 'None',],
                        datasets: [{
                            data: [<?php echo $gender_stats['M']; ?>, <?php echo $gender_stats['F']; ?>, <?php echo $gender_stats['N']; ?>],
                            backgroundColor: ['#CCE5FF', '#FFe0F0', '#d5d5d5'],
                            borderColor: '#fff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: '<?php echo xlt("Gender Distribution"); ?>',
                                font: { size: 16, weight: 'bold' }
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });

                // Age Distribution Chart
                const ageCtx = document.getElementById('ageChart').getContext('2d');
                const ageChart = new Chart(ageCtx, {
                    type: 'bar',
                    data: {
                        labels: ['0-9', '10-19', '20-29', '30-39', '40-49', '50-59', '60-69', '70-79', '80-89', '90-99', '100-125',],
                        datasets: [{
                            label: '<?php echo xlt("Number of Patients"); ?>',
                            data: [<?php echo implode(',', array_values($age_groups)); ?>],
                            backgroundColor: '#FEfEC3',
                            borderColor: '#E5E5E5',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: '<?php echo xlt("Age Distribution"); ?>',
                                font: { size: 16, weight: 'bold' }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            </script>

        <?php }
    } else { ?>
        <div class="modern-card">
            <div class="text-center">
                <h3><?php echo xlt('No records found.'); ?></h3>
                <p><?php echo xlt('Please adjust your search criteria and try again.'); ?></p>
                <?php if (isset($allergy_filter) || isset($problem_filter) || isset($patho_filter) || isset($immu_filter) || isset($observation_description) || isset($procedure_diagnosis)) { ?>
                    <p><em><?php echo xlt('Tip: % matches any string, _ matches any character'); ?></em></p>
                <?php } ?>
            </div>
        </div>
    <?php }
} else { ?>
    <div class="modern-card">
        <div class="text-center">
            <h3><?php echo xlt('REPORTS ! CIMMYT & CGIAR'); ?></h3>
            <p><?php echo xlt('Please configure your search criteria above and click "Generate Report" to view results.'); ?></p>
        </div>
    </div>
<?php }

if (!$csv && !$excel) { ?>
        </form>
    </body>
</html>
<?php } ?>
