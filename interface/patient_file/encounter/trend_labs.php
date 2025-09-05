<?php

/**
 * trend_labs.php - Display trends and graphs for laboratory results
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org  
 */

require_once(__DIR__ . "/../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc.php");
require_once($GLOBALS['fileroot'] . "/library/patient.inc.php");

$pid = $GLOBALS['pid'];

if (empty($pid)) {
    die("Invalid patient ID");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Laboratory Results Trends and Graphs'); ?></title>
    <?php Header::setupHeader(['common', 'datetime-picker']); ?>
    <script src="https://cdn.plot.ly/plotly-1.58.5.js" charset="utf-8"></script>
</head>
<body class="body_top">

<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-12">
            <h2><i class="fas fa-flask mr-2"></i><?php echo xlt('Laboratory Results Trends'); ?></h2>
            <hr>
        </div>
    </div>

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
            <i class="fas fa-info-circle mr-2"></i>
            <?php echo xlt('No laboratory data found for this patient'); ?>
        </div>
    <?php else: ?>
        
        <div class="row">
            <!-- Glucose Graph -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-tint mr-2"></i><?php echo xlt('Glucose Trend'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="glucoseChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Cholesterol Graph -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-heartbeat mr-2"></i><?php echo xlt('Cholesterol Trend'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="cholesterolChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Triglycerides Graph -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line mr-2"></i><?php echo xlt('Triglycerides Trend'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="triglyceridesChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Uric Acid Graph -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-vial mr-2"></i><?php echo xlt('Uric Acid Trend'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="uricAcidChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Cholinesterase Graph -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-microscope mr-2"></i><?php echo xlt('Cholinesterase Trend'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="cholinesteraseChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Urinary Phenol Graph -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-flask mr-2"></i><?php echo xlt('Urinary Phenol Trend'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="urinaryPhenolChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-table mr-2"></i><?php echo xlt('Laboratory Results History'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead-light">
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
                                        <td class="<?php echo $row['glucose'] > 100 ? 'text-danger font-weight-bold' : ''; ?>">
                                            <?php echo $row['glucose'] > 0 ? number_format($row['glucose'], 2) : '-'; ?>
                                        </td>
                                        <td class="<?php echo $row['cholesterol'] > 200 ? 'text-danger font-weight-bold' : ''; ?>">
                                            <?php echo $row['cholesterol'] > 0 ? number_format($row['cholesterol'], 2) : '-'; ?>
                                        </td>
                                        <td class="<?php echo $row['triglycerides'] > 150 ? 'text-danger font-weight-bold' : ''; ?>">
                                            <?php echo $row['triglycerides'] > 0 ? number_format($row['triglycerides'], 2) : '-'; ?>
                                        </td>
                                        <td class="<?php echo $row['uric_acid'] > 6.5 ? 'text-danger font-weight-bold' : ''; ?>">
                                            <?php echo $row['uric_acid'] > 0 ? number_format($row['uric_acid'], 2) : '-'; ?>
                                        </td>
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
            line: { color: 'rgb(219, 64, 82)' },
            marker: { size: 8 }
        };

        const glucoseLayout = {
            title: '<?php echo xlt("Glucose Levels Over Time"); ?>',
            xaxis: { title: '<?php echo xlt("Date"); ?>' },
            yaxis: { title: 'mg/dL' },
            shapes: [{
                type: 'line',
                x0: dates[0],
                x1: dates[dates.length-1],
                y0: 100,
                y1: 100,
                line: { color: 'red', dash: 'dash', width: 2 },
                name: 'Normal Limit'
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
            line: { color: 'rgb(54, 162, 235)' },
            marker: { size: 8 }
        };

        const cholesterolLayout = {
            title: '<?php echo xlt("Cholesterol Levels Over Time"); ?>',
            xaxis: { title: '<?php echo xlt("Date"); ?>' },
            yaxis: { title: 'mg/dL' },
            shapes: [{
                type: 'line',
                x0: dates[0],
                x1: dates[dates.length-1],
                y0: 200,
                y1: 200,
                line: { color: 'red', dash: 'dash', width: 2 }
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
            line: { color: 'rgb(255, 206, 86)' },
            marker: { size: 8 }
        };

        const triglyceridesLayout = {
            title: '<?php echo xlt("Triglycerides Levels Over Time"); ?>',
            xaxis: { title: '<?php echo xlt("Date"); ?>' },
            yaxis: { title: 'mg/dL' },
            shapes: [{
                type: 'line',
                x0: dates[0],
                x1: dates[dates.length-1],
                y0: 150,
                y1: 150,
                line: { color: 'red', dash: 'dash', width: 2 }
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
            line: { color: 'rgb(75, 192, 192)' },
            marker: { size: 8 }
        };

        const uricAcidLayout = {
            title: '<?php echo xlt("Uric Acid Levels Over Time"); ?>',
            xaxis: { title: '<?php echo xlt("Date"); ?>' },
            yaxis: { title: 'mg/dL' },
            shapes: [{
                type: 'line',
                x0: dates[0],
                x1: dates[dates.length-1],
                y0: 6.5,
                y1: 6.5,
                line: { color: 'red', dash: 'dash', width: 2 }
            }]
        };

        Plotly.newPlot('uricAcidChart', [uricAcidTrace], uricAcidLayout);

        // Cholinesterase Chart
        const cholinesteraseTrace = {
            x: dates,
            y: labData.cholinesterase,
            type: 'scatter',
            mode: 'lines+markers',
            name: 'Cholinesterase',
            line: { color: 'rgb(153, 102, 255)' },
            marker: { size: 8 }
        };

        const cholinesteraseLayout = {
            title: '<?php echo xlt("Cholinesterase Levels Over Time"); ?>',
            xaxis: { title: '<?php echo xlt("Date"); ?>' },
            yaxis: { title: 'U/L' }
        };

        Plotly.newPlot('cholinesteraseChart', [cholinesteraseTrace], cholinesteraseLayout);

        // Urinary Phenol Chart
        const urinaryPhenolTrace = {
            x: dates,
            y: labData.urinary_phenol,
            type: 'scatter',
            mode: 'lines+markers',
            name: 'Urinary Phenol',
            line: { color: 'rgb(255, 159, 64)' },
            marker: { size: 8 }
        };

        const urinaryPhenolLayout = {
            title: '<?php echo xlt("Urinary Phenol Levels Over Time"); ?>',
            xaxis: { title: '<?php echo xlt("Date"); ?>' },
            yaxis: { title: 'mg/L' }
        };

        Plotly.newPlot('urinaryPhenolChart', [urinaryPhenolTrace], urinaryPhenolLayout);
        </script>

    <?php endif; ?>

    <div class="row mt-4">
        <div class="col-12 text-center">
            <button type="button" class="btn btn-secondary btn-lg" onclick="window.close();">
<i class="fas fa-times mr-2"></i><?php echo xlt('Close'); ?>
           </button>
       </div>
   </div>
</div>

</body>
</html>
