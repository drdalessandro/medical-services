<?php

/**
 * This report cross-references appointments with encounters.
 * Modified version to show demographics instead of billing information.
 *
 * Shows Gender, Age Groups, Status, and Program data with visualizations
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Modified for Demographics Visualization
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once("../../custom/code_types.inc.php");

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

if (!AclMain::aclCheckCore('acct', 'rep_a')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Appointments and Encounters")]);
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$facilityService = new FacilityService();

$errmsg  = "";
$alertmsg = '';
$grand_total_encounters = 0;

// Demographics counters
$gender_counts = array('Male' => 0, 'Female' => 0, 'Other' => 0);
$age_groups = array(
    '0-9' => 0,
    '10-19' => 0,
    '20-29' => 0,
    '30-39' => 0,
    '40-49' => 0,
    '50-59' => 0,
    '60-69' => 0,
    '70-79' => 0,
    '80+' => 0
);
$status_counts = array('ACTIVE' => 0, 'INACTIVE' => 0, 'Other' => 0);
$program_counts = array(
    'BISA' => 0, 'BMI' => 0, 'BRS' => 0, 'CSP' => 0,
    'DG' => 0, 'DGP' => 0, 'EIB' => 0, 'GRP' => 0,
    'GWP' => 0, 'HR4UNK' => 0, 'IBP' => 0, 'RPP' => 0,
    'SAS' => 0, 'Other' => 0
);

function calculateAge($dob) {
    if (!$dob || $dob == '0000-00-00') return null;
    $birthDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
    return $age;
}

function getAgeGroup($age) {
    if ($age === null) return 'Unknown';
    if ($age < 10) return '0-9';
    if ($age < 20) return '10-19';
    if ($age < 30) return '20-29';
    if ($age < 40) return '30-39';
    if ($age < 50) return '40-49';
    if ($age < 60) return '50-59';
    if ($age < 70) return '60-69';
    if ($age < 80) return '70-79';
    return '80+';
}

function postError($msg) {
    global $errmsg;
    if ($errmsg) {
        $errmsg .= '<br />';
    }
    $errmsg .= text($msg);
}

function endDoctor(&$docrow) {
    global $grand_total_encounters;
    if (!$docrow['docname']) {
        return;
    }

    echo " <tr class='report_totals' style='background-color: #f0f0f0;'>\n";
    echo "  <td colspan='7'>\n";
    echo "   &nbsp;<strong>" . xlt('Totals for') . ' ' . text($docrow['docname']) . "</strong>\n";
    echo "  </td>\n";
    echo "  <td align='right'>\n";
    echo "   &nbsp;<strong>" . text($docrow['encounters']) . "</strong>&nbsp;\n";
    echo "  </td>\n";
    echo "  <td colspan='2'>\n";
    echo "   &nbsp;\n";
    echo "  </td>\n";
    echo " </tr>\n";

    $grand_total_encounters += $docrow['encounters'];
    $docrow['encounters'] = 0;
}

$form_facility  = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
$form_from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$form_to_date   = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');

if (!empty($_POST['form_refresh'])) {
    $sqlBindArray = array();
    $query = "( " .
    "SELECT " .
    "e.pc_eventDate, e.pc_startTime, " .
    "fe.encounter, fe.date AS encdate, " .
    "f.authorized, " .
    "p.fname, p.lname, p.pid, p.pubpid, p.DOB, p.sex, " .
    "p.usertext3 AS program, p.usertext8 AS status, " .
    "CONCAT( u.lname, ', ', u.fname ) AS docname " .
    "FROM openemr_postcalendar_events AS e " .
    "LEFT OUTER JOIN form_encounter AS fe " .
    "ON fe.date = e.pc_eventDate AND fe.pid = e.pc_pid " .
    "LEFT OUTER JOIN forms AS f ON f.pid = fe.pid AND f.encounter = fe.encounter AND f.formdir = 'newpatient' " .
    "LEFT OUTER JOIN patient_data AS p ON p.pid = e.pc_pid " .
    "LEFT OUTER JOIN users AS u ON u.id = fe.provider_id WHERE ";
   
    if ($form_to_date) {
        $query .= "e.pc_eventDate >= ? AND e.pc_eventDate <= ? ";
        array_push($sqlBindArray, $form_from_date, $form_to_date);
    } else {
        $query .= "e.pc_eventDate = ? ";
        array_push($sqlBindArray, $form_from_date);
    }

    if ($form_facility !== '') {
        $query .= "AND e.pc_facility = ? ";
        array_push($sqlBindArray, $form_facility);
    }

    $query .= "AND e.pc_pid != '' AND e.pc_apptstatus != ? " .
    ") UNION ( " .
    "SELECT " .
    "e.pc_eventDate, e.pc_startTime, " .
    "fe.encounter, fe.date AS encdate, " .
    "f.authorized, " .
    "p.fname, p.lname, p.pid, p.pubpid, p.DOB, p.sex, " .
    "p.usertext3 AS program, p.usertext8 AS status, " .
    "CONCAT( u.lname, ', ', u.fname ) AS docname " .
    "FROM form_encounter AS fe " .
    "LEFT OUTER JOIN openemr_postcalendar_events AS e " .
    "ON fe.date = e.pc_eventDate AND fe.pid = e.pc_pid AND " .
    "e.pc_pid != '' AND e.pc_apptstatus != ? " .
    "LEFT OUTER JOIN forms AS f ON f.pid = fe.pid AND f.encounter = fe.encounter AND f.formdir = 'newpatient' " .
    "LEFT OUTER JOIN patient_data AS p ON p.pid = fe.pid " .
    "LEFT OUTER JOIN users AS u ON u.id = fe.provider_id WHERE ";
   
    array_push($sqlBindArray, '?', '?');
   
    if ($form_to_date) {
        $query .= "fe.date >= ? AND fe.date <= ? ";
        array_push($sqlBindArray, $form_from_date . ' 00:00:00', $form_to_date . ' 23:59:59');
    } else {
        $query .= "fe.date >= ? AND fe.date <= ? ";
        array_push($sqlBindArray, $form_from_date . ' 00:00:00', $form_from_date . ' 23:59:59');
    }

    if ($form_facility !== '') {
        $query .= "AND fe.facility_id = ? ";
        array_push($sqlBindArray, $form_facility);
    }

    $query .= ") ORDER BY docname, IFNULL(pc_eventDate, encdate), pc_startTime";

    $res = sqlStatement($query, $sqlBindArray);
}
?>
<html>
<head>
    <title><?php echo xlt('Appointments and Encounters'); ?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>
   
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <style>
        /* Custom theme color */
        .theme-color {
            color: #77bc1f;
        }
       
        .theme-bg {
            background-color: #77bc1f;
            color: white;
        }
       
        .theme-border {
            border-color: #77bc1f;
        }
       
        /* specifically include & exclude from printing */
        @media print {
            #report_parameters {
                visibility: hidden;
                display: none;
            }
            #report_parameters_daterange {
                visibility: visible;
                display: inline;
            }
            #report_results table {
               margin-top: 0px;
            }
        }

        /* specifically exclude some from the screen */
        @media screen {
            #report_parameters_daterange {
                visibility: hidden;
                display: none;
            }
        }
       
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            margin: 20px 0;
        }
       
        .charts-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 20px 0;
        }
       
        .chart-box {
            flex: 1;
            min-width: 300px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
        }
       
        .chart-title {
            text-align: center;
            color: #77bc1f;
            font-weight: bold;
            margin-bottom: 10px;
        }
       
        .report_totals {
            font-weight: bold;
            background-color: #f0f0f0;
        }
       
        .grand-total {
            background-color: #77bc1f !important;
            color: white;
        }
    </style>

    <script>
        $(function () {
            oeFixedHeaderSetup(document.getElementById('mymaintable'));
            var win = top.printLogSetup ? top : opener.top;
            win.printLogSetup(document.getElementById('printbutton'));

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            });
        });
    </script>
</head>

<body class="body_top">

<span class='title theme-color'><?php echo xlt('Report'); ?> - <?php echo xlt('Appointments and Encounters'); ?></span>

<div id="report_parameters_daterange">
    <?php echo text(oeFormatShortDate($form_from_date)) . " &nbsp; " . xlt('to{{Range}}') . " &nbsp; " . text(oeFormatShortDate($form_to_date)); ?>
</div>

<form method='post' id='theform' action='appt_encounter_report.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">

<table>
 <tr>
  <td width='630px'>
    <div style='float:left'>

    <table class='text'>
        <tr>
            <td class='col-form-label'>
                <?php echo xlt('Facility'); ?>:
            </td>
            <td>
                <?php
                 $fres = $facilityService->getAllFacility();
                echo "   <select name='form_facility' class='form-control'>\n";
                echo "    <option value=''>-- " . xlt('All Facilities') . " --\n";
                foreach ($fres as $frow) {
                    $facid = $frow['id'];
                    echo "    <option value='" . attr($facid) . "'";
                    if ($facid == $form_facility) {
                        echo " selected";
                    }
                    echo ">" . text($frow['name']) . "\n";
                }

                echo "    <option value='0'";
                if ($form_facility === '0') {
                    echo " selected";
                }

                 echo ">-- " . xlt('Unspecified') . " --\n";
                 echo "   </select>\n";
                ?>
            </td>
            <td class='col-form-label'>
                <?php echo xlt('DOS'); ?>:
            </td>
            <td>
               <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>' >
            </td>
            <td class='col-form-label'>
                <?php echo xlt('To{{Range}}'); ?>:
            </td>
            <td>
               <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php  echo attr(oeFormatShortDate($form_to_date)); ?>' >
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>
        <div class="checkbox">
                <label><input type='checkbox' name='form_details'
                  value='1'<?php echo (!empty($_POST['form_details'])) ? " checked" : ""; ?>><?php echo xlt('Details') ?></label>
        </div>
            </td>
        </tr>
    </table>

    </div>

  </td>
  <td class='h-100' align='left' valign='middle'>
    <table class='w-100 h-100' style='border-left:1px solid;'>
        <tr>
            <td>
                <div class="text-center">
          <div class="btn-group" role="group">
                    <a href='#' class='btn btn-secondary btn-save theme-bg' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();' style="background-color: #77bc1f; border-color: #77bc1f;">
                        <?php echo xlt('Submit'); ?>
                    </a>
                    <?php if (!empty($_POST['form_refresh'])) { ?>
                      <a href='#' class='btn btn-secondary btn-print' id='printbutton'>
                            <?php echo xlt('Print'); ?>
                      </a>
                    <?php } ?>
          </div>
                </div>
            </td>
        </tr>
    </table>
  </td>
 </tr>
</table>

</div> <!-- end apptenc_report_parameters -->

<?php
if (!empty($_POST['form_refresh'])) {
    ?>
   
<!-- Charts Section -->
<div class="charts-row">
    <div class="chart-box">
        <div class="chart-title"><?php echo xlt('Gender Distribution'); ?></div>
        <div class="chart-container">
            <canvas id="genderChart"></canvas>
        </div>
    </div>
    <div class="chart-box">
        <div class="chart-title"><?php echo xlt('Age Group Distribution'); ?></div>
        <div class="chart-container">
            <canvas id="ageChart"></canvas>
        </div>
    </div>
    <div class="chart-box">
        <div class="chart-title"><?php echo xlt('Status Distribution'); ?></div>
        <div class="chart-container">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
    <div class="chart-box">
        <div class="chart-title"><?php echo xlt('Program Distribution'); ?></div>
        <div class="chart-container">
            <canvas id="programChart"></canvas>
        </div>
    </div>
</div>

<div id="report_results">
<table class='table' id='mymaintable'>

<thead class='thead-light theme-bg'>
<th> &nbsp;<?php echo xlt('Practitioner'); ?> </th>
<th> &nbsp;<?php echo xlt('Date/Appt'); ?> </th>
<th> &nbsp;<?php echo xlt('Patient'); ?> </th>
<th> &nbsp;<?php echo xlt('ID'); ?> </th>
<th> &nbsp;<?php echo xlt('Gender'); ?> </th>
<th> &nbsp;<?php echo xlt('Age Group'); ?> </th>
<th> &nbsp;<?php echo xlt('Status'); ?> </th>
<th> &nbsp;<?php echo xlt('Program'); ?> </th>
<th align='right'> <?php echo xlt('Encounter'); ?>&nbsp; </th>
<th> &nbsp;<?php echo xlt('Notes'); ?> </th>
</thead>
<tbody>
    <?php
    if ($res) {
        $docrow = array('docname' => '', 'encounters' => 0);

        while ($row = sqlFetchArray($res)) {
            $patient_id = $row['pid'];
            $encounter  = $row['encounter'];
            $docname    = $row['docname'] ? $row['docname'] : xl('Unknown');

            if ($docname != $docrow['docname']) {
                endDoctor($docrow);
            }

            $errmsg  = "";
           
            // Calculate demographics
            $age = calculateAge($row['DOB']);
            $age_group = getAgeGroup($age);
           
            $gender = $row['sex'];
            if ($gender == 'Male' || $gender == 'Female') {
                $gender_counts[$gender]++;
            } else {
                $gender_counts['Other']++;
            }
           
            if ($age_group != 'Unknown' && isset($age_groups[$age_group])) {
                $age_groups[$age_group]++;
            }
           
            $status = strtoupper($row['status']);
            if ($status == 'ACTIVE' || $status == 'INACTIVE') {
                $status_counts[$status]++;
            } else {
                $status_counts['Other']++;
            }
           
            $program = strtoupper($row['program']);
            $valid_programs = array('BISA', 'BMI', 'BRS', 'CSP', 'DG', 'DGP', 'EIB', 'GRP', 'GWP', 'HR4UNK', 'IBP', 'RPP', 'SAS');
            if (in_array($program, $valid_programs)) {
                $program_counts[$program]++;
            } else {
                $program_counts['Other']++;
            }

            if (!$encounter) {
                postError(xl('No visit'));
            }

            if ($encounter) {
                ++$docrow['encounters'];
            }

            if (!empty($_POST['form_details'])) {
                ?>
         <tr>
          <td>
            &nbsp;<?php echo ($docname == $docrow['docname']) ? "" : text($docname); ?>
          </td>
          <td>
            &nbsp;<?php
            if (empty($row['pc_eventDate'])) {
                echo text(oeFormatShortDate(substr($row['encdate'], 0, 10)));
            } else {
                echo text(oeFormatShortDate($row['pc_eventDate'])) . ' ' . text(substr($row['pc_startTime'], 0, 5));
            }
            ?>
          </td>
          <td>
            &nbsp;<?php echo text($row['fname']) . " " . text($row['lname']); ?>
          </td>
          <td>
            &nbsp;<?php echo text($row['pubpid']); ?>
          </td>
          <td>
            &nbsp;<?php echo text($gender ?: 'Unknown'); ?>
          </td>
          <td>
            &nbsp;<?php echo text($age_group); ?>
          </td>
          <td>
            &nbsp;<?php echo text($status ?: 'Unknown'); ?>
          </td>
          <td>
            &nbsp;<?php echo text($program ?: 'Unknown'); ?>
          </td>
          <td align='right'>
            <?php echo text($encounter); ?>&nbsp;
          </td>
          <td style='color:#cc0000'>
            <?php echo $errmsg; ?>&nbsp;
          </td>
        </tr>
                <?php
            } // end of details line

            $docrow['docname'] = $docname;
        } // end of row

        endDoctor($docrow);

        echo " <tr class='report_totals grand-total'>\n";
        echo "  <td colspan='8'>\n";
        echo "   &nbsp;<strong>" . xlt('Grand Totals') . "</strong>\n";
        echo "  </td>\n";
        echo "  <td align='right'>\n";
        echo "   &nbsp;<strong>" . text($grand_total_encounters) . "</strong>&nbsp;\n";
        echo "  </td>\n";
        echo "  <td>\n";
        echo "   &nbsp;\n";
        echo "  </td>\n";
        echo " </tr>\n";
    }
    ?>
</tbody>
</table>
</div> <!-- end the apptenc_report_results -->

<script>
// Gender Chart
const genderCtx = document.getElementById('genderChart').getContext('2d');
new Chart(genderCtx, {
    type: 'pie',
    data: {
        labels: ['Male', 'Female', 'Other'],
        datasets: [{
            data: [<?php echo $gender_counts['Male']; ?>, <?php echo $gender_counts['Female']; ?>, <?php echo $gender_counts['Other']; ?>],
            backgroundColor: ['#77bc1f', '#5a9216', '#3d6310']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Age Group Chart
const ageCtx = document.getElementById('ageChart').getContext('2d');
new Chart(ageCtx, {
    type: 'bar',
    data: {
        labels: ['0-9', '10-19', '20-29', '30-39', '40-49', '50-59', '60-69', '70-79', '80+'],
        datasets: [{
            label: 'Encounters',
            data: [
                <?php echo $age_groups['0-9']; ?>,
                <?php echo $age_groups['10-19']; ?>,
                <?php echo $age_groups['20-29']; ?>,
                <?php echo $age_groups['30-39']; ?>,
                <?php echo $age_groups['40-49']; ?>,
                <?php echo $age_groups['50-59']; ?>,
                <?php echo $age_groups['60-69']; ?>,
                <?php echo $age_groups['70-79']; ?>,
                <?php echo $age_groups['80+']; ?>
            ],
            backgroundColor: '#77bc1f'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Active', 'Inactive', 'Other'],
        datasets: [{
            data: [<?php echo $status_counts['ACTIVE']; ?>, <?php echo $status_counts['INACTIVE']; ?>, <?php echo $status_counts['Other']; ?>],
            backgroundColor: ['#77bc1f', '#ff6b6b', '#cccccc']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Program Chart
const programCtx = document.getElementById('programChart').getContext('2d');
const programData = [
    <?php
    $programs = ['BISA', 'BMI', 'BRS', 'CSP', 'DG', 'DGP', 'EIB', 'GRP', 'GWP', 'HR4UNK', 'IBP', 'RPP', 'SAS', 'Other'];
    $values = [];
    foreach ($programs as $prog) {
        $values[] = $program_counts[$prog];
    }
    echo implode(',', $values);
    ?>
];

// Filter out programs with 0 encounters for better visualization
const programLabels = <?php echo json_encode($programs); ?>;
const filteredProgramData = [];
const filteredProgramLabels = [];

for (let i = 0; i < programData.length; i++) {
    if (programData[i] > 0) {
        filteredProgramData.push(programData[i]);
        filteredProgramLabels.push(programLabels[i]);
    }
}

new Chart(programCtx, {
    type: 'pie',
    data: {
        labels: filteredProgramLabels,
        datasets: [{
            data: filteredProgramData,
            backgroundColor: [
                '#77bc1f', '#5a9216', '#3d6310', '#8fce3f',
                '#a1d65a', '#b3de74', '#c5e68f', '#d7eeaa',
                '#4a7c0f', '#6ba42d', '#8ccb4b', '#adf369',
                '#669900', '#88bb22'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    boxWidth: 15,
                    font: {
                        size: 11
                    }
                }
            }
        }
    }
});
</script>

<?php } else { ?>
<div class='text'>
    <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
<?php } ?>

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

</form>
<script>
<?php if ($alertmsg) {
    echo " alert(" . js_escape($alertmsg) . ");\n";
} ?>
</script>
</body>

</html>
