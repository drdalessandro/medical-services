<?php

/**
 * Trending script for graphing objects.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @copyright Copyright (c) 2011 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2010-2018 Brady Miller <brady.g.miller@gmail.com>
 */

require_once("../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$formname = $_GET["formname"];
$is_lbf = substr($formname, 0, 3) === 'LBF';

if ($is_lbf) {
  // Determine the default field ID and its title for graphing.
  // This is from the last graphable field in the form.
    $default = sqlQuery(
        "SELECT field_id, title FROM layout_options WHERE " .
        "form_id = ? AND uor > 0 AND edit_options LIKE '%G%' " .
        "ORDER BY group_id DESC, seq DESC, title DESC LIMIT 1",
        array($formname)
    );
}

//Bring in the style sheet
?>
<?php require $GLOBALS['srcdir'] . '/js/xl/dygraphs.js.php'; ?>

<?php
// Special case where not setting up the header for a script, so using setupAssets function,
//  which does not autoload anything. The actual header is set up in the script called at
//  the bottom of this script.
Header::setupAssets(['dygraphs', 'jquery']);
?>

<?php
// Hide the current value css entries. This is currently specific
//  for the vitals form but could use this mechanism for other
//  forms.
// Hiding classes:
//  currentvalues - input boxes
//  valuesunfocus - input boxes that are auto-calculated
//  editonly      - the edit and cancel buttons
// Showing class:
//  readonly      - the link back to summary screen
// Also customize the 'graph' class to look like links.
?>
<style>
  .currentvalues {
    display: none;
  }
  .valuesunfocus {
    display: none;
  }
  .editonly {
    display: none !important;
  }

  .graph {
    color: #0000cc;
  }

  #chart {
    margin:0em 1em 2em 2em;
  }
</style>

<script>


// Show the selected chart in the 'chart' div element
function show_graph(table_graph, name_graph, title_graph)
{
    top.restoreSession();
    $.ajax({ url: '../../../library/ajax/graphs.php',
    type: 'POST',
        data: ({
            table: table_graph,
            name: name_graph,
            title: title_graph,
            csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
        }),
        dataType: "json",
        success: function(returnData){

        g2 = new Dygraph(
            document.getElementById("chart"),
            returnData.data_final,
            {
                title: returnData.title,
                delimiter: '\t',
                xRangePad: 20,
                yRangePad: 20,
                width: 480,
                height: 320,
                xlabel: xlabel_translate
            }
        );

            // ensure show the chart div
            $('#chart').show();
        },
        error: function() {
            // hide the chart div
          $('#chart').hide();
          <?php if ($GLOBALS['graph_data_warning']) { ?>
          if(!title_graph){
              alert(<?php echo xlj('This item does not have enough data to graph');?> + ".\n" + <?php echo xlj('Please select an item that has more data');?> + ".");
          }
          else {
              alert(title_graph + " " + <?php echo xlj('does not have enough data to graph');?> + ".\n" + <?php echo xlj('Please select an item that has more data');?> + ".");
          }
          <?php } ?>

        }
    });
}

$(function () {

  // Use jquery to show the 'readonly' class entries
  $('.readonly').show();

  // Place click callback for graphing
<?php if ($is_lbf) { ?>
  // For LBF the <td> has an id of label_id_$fieldid
  $(".graph").on("click", function(e){ show_graph(<?php echo js_escape($formname); ?>, this.id.substring(9), $(this).text()) });
<?php } else { ?>
  $(".graph").on("click", function(e){ show_graph('form_vitals', this.id, '$(this).text()') });
<?php } ?>

  // Show hovering effects for the .graph links
  $(".graph").on("mouseenter",
    function(){
         $(this).css({color:'#ff5555'});
    }).on("mouseleave",
    function(){
         $(this).css({color:'#0000cc'});
    }
  );

  // show blood pressure graph by default
<?php if ($is_lbf) { ?>
    <?php if (!empty($default)) { ?>
  show_graph(<?php echo js_escape($formname); ?>,<?php echo js_escape($default['field_id']); ?>,<?php echo js_escape($default['title']); ?>);
<?php } ?>
<?php } else { ?>
  show_graph('form_vitals','bps','');
<?php } ?>
});
</script>

<?php
if ($is_lbf) {
  // Use the List Based Forms engine for all LBFxxxxx forms.
    include_once("$incdir/forms/LBF/new.php");
} else {
  // ensure the path variable has no illegal characters
    check_file_dir_name($formname);

    include_once("$incdir/forms/$formname/new.php");
}
?>
<?php

/**
 * trend_form.php - Display trends and graphs for forms
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org  
 */

require_once(__DIR__ . "/../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc.php");
require_once($GLOBALS['fileroot'] . "/library/patient.inc.php");

$formname = $_GET['formname'] ?? '';
$pid = $GLOBALS['pid'];

if (empty($formname) || empty($pid)) {
    die("Invalid parameters");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Trends and Graphs'); ?></title>
    <?php Header::setupHeader(['common', 'datetime-picker', 'chart']); ?>
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
</head>
<body class="body_top">

<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-12">
            <h2><?php 
            if ($formname == 'labs') {
                echo xlt('Laboratory Results Trends');
            } else {
                echo xlt('Form') . ': ' . text($formname);
            }
            ?></h2>
        </div>
    </div>

    <?php if ($formname == 'labs'): ?>
        
        <?php
        // Get lab data for graphs
        $sql = "SELECT * FROM form_labs WHERE pid = ? ORDER BY date ASC";
        $results = sqlStatement($sql, [$pid]);
        
        $labData = [];
        $dates = [];
        
        while ($row = sqlFetchArray($results)) {
            $dates[] = $row['date'];
            $labData['glucose'][] = floatval($row['glucose']);
            $labData['cholesterol'][] = floatval($row['cholesterol']);
            $labData['triglycerides'][] = floatval($row['triglycerides']);
            $labData['uric_acid'][] = floatval($row['uric_acid']);
            $labData['cholinesterase'][] = floatval($row['cholinesterase']);
            $labData['urinary_phenol'][] = floatval($row['urinary_phenol']);
        }
        
        if (empty($dates)): ?>
            <div class="alert alert-info">
                <?php echo xlt('No laboratory data found for this patient'); ?>
            </div>
        <?php else: ?>
            
            <div class="row">
                <!-- Glucose Graph -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><?php echo xlt('Glucose Trend'); ?></h5>
                        </div>
                        <div class="card-body">
                            <div id="glucoseChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Cholesterol Graph -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><?php echo xlt('Cholesterol Trend'); ?></h5>
                        </div>
                        <div class="card-body">
                            <div id="cholesterolChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Triglycerides Graph -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><?php echo xlt('Triglycerides Trend'); ?></h5>
                        </div>
                        <div class="card-body">
                            <div id="triglyceridesChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Uric Acid Graph -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><?php echo xlt('Uric Acid Trend'); ?></h5>
                        </div>
                        <div class="card-body">
                            <div id="uricAcidChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><?php echo xlt('Laboratory Results History'); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th><?php echo xlt('Date'); ?></th>
                                            <th><?php echo xlt('Glucose'); ?><br><small>(mg/dL)</small></th>
                                            <th><?php echo xlt('Cholesterol'); ?><br><small>(mg/dL)</small></th>
                                            <th><?php echo xlt('Triglycerides'); ?><br><small>(mg/dL)</small></th>
                                            <th><?php echo xlt('Uric Acid'); ?><br><small>(mg/dL)</small></th>
                                            <th><?php echo xlt('Cholinesterase'); ?><br><small>(U/L)</small></th>
                                            <th><?php echo xlt('Urinary Phenol'); ?><br><small>(mg/L)</small></th>
                                            <th><?php echo xlt('Notes'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT * FROM form_labs WHERE pid = ? ORDER BY date DESC";
                                        $results = sqlStatement($sql, [$pid]);
                                        while ($row = sqlFetchArray($results)): ?>
                                        <tr>
                                            <td><?php echo text(date('Y-m-d H:i', strtotime($row['date']))); ?></td>
                                            <td><?php echo $row['glucose'] > 0 ? number_format($row['glucose'], 2) : '-'; ?></td>
                                            <td><?php echo $row['cholesterol'] > 0 ? number_format($row['cholesterol'], 2) : '-'; ?></td>
                                            <td><?php echo $row['triglycerides'] > 0 ? number_format($row['triglycerides'], 2) : '-'; ?></td>
                                            <td><?php echo $row['uric_acid'] > 0 ? number_format($row['uric_acid'], 2) : '-'; ?></td>
                                            <td><?php echo $row['cholinesterase'] > 0 ? number_format($row['cholinesterase'], 2) : '-'; ?></td>
                                            <td><?php echo $row['urinary_phenol'] > 0 ? number_format($row['urinary_phenol'], 2) : '-'; ?></td>
                                            <td><?php echo text($row['note']); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            // Chart data
            const dates = <?php echo json_encode($dates); ?>;
            const labData = <?php echo json_encode($labData); ?>;

            // Glucose Chart
            const glucoseTrace = {
                x: dates,
                y: labData.glucose,
                type: 'scatter',
                mode: 'lines+markers',
                name: 'Glucose',
                line: { color: 'rgb(219, 64, 82)' }
            };

            const glucoseLayout = {
                title: '<?php echo xlt("Glucose Levels"); ?>',
                xaxis: { title: '<?php echo xlt("Date"); ?>' },
                yaxis: { title: 'mg/dL' },
                shapes: [{
                    type: 'line',
                    x0: dates[0],
                    x1: dates[dates.length-1],
                    y0: 100,
                    y1: 100,
                    line: { color: 'red', dash: 'dash' }
                }]
            };

            Plotly.newPlot('glucoseChart', [glucoseTrace], glucoseLayout);

            // Cholesterol Chart
            const cholesterolTrace = {
                x: dates,
                y: labData.cholesterol,
                type: 'scatter',
                mode: 'lines+markers',
                name: 'Cholesterol',
                line: { color: 'rgb(54, 162, 235)' }
            };

            const cholesterolLayout = {
                title: '<?php echo xlt("Cholesterol Levels"); ?>',
                xaxis: { title: '<?php echo xlt("Date"); ?>' },
                yaxis: { title: 'mg/dL' },
                shapes: [{
                    type: 'line',
                    x0: dates[0],
                    x1: dates[dates.length-1],
                    y0: 200,
                    y1: 200,
                    line: { color: 'red', dash: 'dash' }
                }]
            };

            Plotly.newPlot('cholesterolChart', [cholesterolTrace], cholesterolLayout);

            // Triglycerides Chart
            const triglyceridesTrace = {
                x: dates,
                y: labData.triglycerides,
                type: 'scatter',
                mode: 'lines+markers',
                name: 'Triglycerides',
                line: { color: 'rgb(255, 206, 86)' }
            };

            const triglyceridesLayout = {
                title: '<?php echo xlt("Triglycerides Levels"); ?>',
                xaxis: { title: '<?php echo xlt("Date"); ?>' },
                yaxis: { title: 'mg/dL' },
                shapes: [{
                    type: 'line',
                    x0: dates[0],
                    x1: dates[dates.length-1],
                    y0: 150,
                    y1: 150,
                    line: { color: 'red', dash: 'dash' }
                }]
            };

            Plotly.newPlot('triglyceridesChart', [triglyceridesTrace], triglyceridesLayout);

            // Uric Acid Chart
            const uricAcidTrace = {
                x: dates,
                y: labData.uric_acid,
                type: 'scatter',
                mode: 'lines+markers',
                name: 'Uric Acid',
                line: { color: 'rgb(75, 192, 192)' }
            };

            const uricAcidLayout = {
                title: '<?php echo xlt("Uric Acid Levels"); ?>',
                xaxis: { title: '<?php echo xlt("Date"); ?>' },
                yaxis: { title: 'mg/dL' },
                shapes: [{
                    type: 'line',
                    x0: dates[0],
                    x1: dates[dates.length-1],
                    y0: 6.5,
                    y1: 6.5,
                    line: { color: 'red', dash: 'dash' }
                }]
            };

            Plotly.newPlot('uricAcidChart', [uricAcidTrace], uricAcidLayout);
            </script>

       <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-warning">
            <?php echo xlt('CIMMYT - CGIAR'); ?>
        </div>
    <?php endif; ?>

    <div class="row mt-4">
        <div class="col-12 text-center">
            <button type="button" class="btn btn-secondary" onclick="window.close();">
                <?php echo xlt('Close'); ?>
            </button>
        </div>
    </div>
</div>
