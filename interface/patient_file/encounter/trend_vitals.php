<?php

/**
 * trend_vitals.php - Display trends and graphs for vitals
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
    <title><?php echo xlt('Vitals Trends and Graphs'); ?></title>
    <?php Header::setupHeader(['common', 'datetime-picker']); ?>
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
</head>
<body class="body_top">

<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-12">
            <h2><i class="fas fa-heartbeat mr-2"></i><?php echo xlt('Vitals Signs Trends'); ?></h2>
            <hr>
        </div>
    </div>

    <?php
    // Get vitals data for graphs
    $sql = "SELECT * FROM form_vitals WHERE pid = ? ORDER BY date ASC";
    $results = sqlStatement($sql, [$pid]);
    
    $vitalsData = [];
    $dates = [];
    
    while ($row = sqlFetchArray($results)) {
        $dates[] = $row['date'];
        $vitalsData['bps'][] = floatval($row['bps']);
        $vitalsData['bpd'][] = floatval($row['bpd']);
        $vitalsData['pulse'][] = floatval($row['pulse']);
        $vitalsData['respiration'][] = floatval($row['respiration']);
        $vitalsData['temperature'][] = floatval($row['temperature']);
        $vitalsData['weight'][] = floatval($row['weight']);
        $vitalsData['height'][] = floatval($row['height']);
        $vitalsData['BMI'][] = floatval($row['BMI']);
    }
    
    if (empty($dates)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-2"></i>
            <?php echo xlt('No vitals data found for this patient'); ?>
        </div>
    <?php else: ?>
        
        <div class="row">
            <!-- Blood Pressure Graph -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-tint mr-2"></i><?php echo xlt('Blood Pressure Trend'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="bpChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Pulse Graph -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-heartbeat mr-2"></i><?php echo xlt('Pulse Trend'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="pulseChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Temperature Graph -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-thermometer-half mr-2"></i><?php echo xlt('Temperature Trend'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="temperatureChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Weight Graph -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-weight mr-2"></i><?php echo xlt('Weight Trend'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="weightChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- BMI Graph -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-calculator mr-2"></i><?php echo xlt('BMI Trend'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="bmiChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Respiration Graph -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-lungs mr-2"></i><?php echo xlt('Respiration Trend'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="respirationChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-table mr-2"></i><?php echo xlt('Vitals History'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th><?php echo xlt('Date'); ?></th>
                                        <th><?php echo xlt('BP Sys'); ?><br><small>(mmHg)</small></th>
                                        <th><?php echo xlt('BP Dia'); ?><br><small>(mmHg)</small></th>
                                        <th><?php echo xlt('Pulse'); ?><br><small>(/min)</small></th>
                                        <th><?php echo xlt('Resp'); ?><br><small>(/min)</small></th>
                                        <th><?php echo xlt('Temp'); ?><br><small>(°F)</small></th>
                                        <th><?php echo xlt('Weight'); ?><br><small>(lbs)</small></th>
                                        <th><?php echo xlt('Height'); ?><br><small>(in)</small></th>
                                        <th><?php echo xlt('BMI'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT * FROM form_vitals WHERE pid = ? ORDER BY date DESC";
                                    $results = sqlStatement($sql, [$pid]);
                                    while ($row = sqlFetchArray($results)): ?>
                                    <tr>
                                        <td><?php echo text(date('Y-m-d H:i', strtotime($row['date']))); ?></td>
                                        <td><?php echo $row['bps'] > 0 ? text($row['bps']) : '-'; ?></td>
                                        <td><?php echo $row['bpd'] > 0 ? text($row['bpd']) : '-'; ?></td>
                                        <td><?php echo $row['pulse'] > 0 ? text($row['pulse']) : '-'; ?></td>
                                        <td><?php echo $row['respiration'] > 0 ? text($row['respiration']) : '-'; ?></td>
                                        <td><?php echo $row['temperature'] > 0 ? number_format($row['temperature'], 1) : '-'; ?></td>
                                        <td><?php echo $row['weight'] > 0 ? number_format($row['weight'], 1) : '-'; ?></td>
                                        <td><?php echo $row['height'] > 0 ? number_format($row['height'], 1) : '-'; ?></td>
                                        <td><?php echo $row['BMI'] > 0 ? number_format($row['BMI'], 1) : '-'; ?></td>
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
        const vitalsData = <?php echo json_encode($vitalsData); ?>;

        // Blood Pressure Chart (Combined Systolic and Diastolic)
        const bpsTrace = {
            x: dates,
            y: vitalsData.bps,
            type: 'scatter',
            mode: 'lines+markers',
            name: 'Systolic',
            line: { color: 'rgb(219, 64, 82)' }
        };

        const bpdTrace = {
            x: dates,
            y: vitalsData.bpd,
            type: 'scatter',
            mode: 'lines+markers',
            name: 'Diastolic',
            line: { color: 'rgb(54, 162, 235)' }
        };

        const bpLayout = {
            title: '<?php echo xlt("Blood Pressure"); ?>',
            xaxis: { title: '<?php echo xlt("Date"); ?>' },
            yaxis: { title: 'mmHg' }
        };

        Plotly.newPlot('bpChart', [bpsTrace, bpdTrace], bpLayout);

        // Pulse Chart
        const pulseTrace = {
            x: dates,
            y: vitalsData.pulse,
            type: 'scatter',
            mode: 'lines+markers',
            name: 'Pulse',
            line: { color: 'rgb(255, 99, 132)' }
        };

        const pulseLayout = {
            title: '<?php echo xlt("Pulse Rate"); ?>',
            xaxis: { title: '<?php echo xlt("Date"); ?>' },
            yaxis: { title: 'beats/min' }
        };

        Plotly.newPlot('pulseChart', [pulseTrace], pulseLayout);

        // Temperature Chart
        const temperatureTrace = {
            x: dates,
            y: vitalsData.temperature,
            type: 'scatter',
            mode: 'lines+markers',
            name: 'Temperature',
            line: { color: 'rgb(255, 206, 86)' }
        };

        const temperatureLayout = {
            title: '<?php echo xlt("Body Temperature"); ?>',
            xaxis: { title: '<?php echo xlt("Date"); ?>' },
            yaxis: { title: '°F' },
            shapes: [{
                type: 'line',
                x0: dates[0],
                x1: dates[dates.length-1],
                y0: 98.6,
                y1: 98.6,
                line: { color: 'green', dash: 'dash' }
            }]
        };

        Plotly.newPlot('temperatureChart', [temperatureTrace], temperatureLayout);

        // Weight Chart
        const weightTrace = {
            x: dates,
            y: vitalsData.weight,
            type: 'scatter',
            mode: 'lines+markers',
            name: 'Weight',
            line: { color: 'rgb(75, 192, 192)' }
        };

        const weightLayout = {
            title: '<?php echo xlt("Weight"); ?>',
            xaxis: { title: '<?php echo xlt("Date"); ?>' },
            yaxis: { title: 'lbs' }
        };

        Plotly.newPlot('weightChart', [weightTrace], weightLayout);

        // BMI Chart
        const bmiTrace = {
            x: dates,
            y: vitalsData.BMI,
            type: 'scatter',
            mode: 'lines+markers',
            name: 'BMI',
            line: { color: 'rgb(153, 102, 255)' }
        };

        const bmiLayout = {
            title: '<?php echo xlt("Body Mass Index"); ?>',
            xaxis: { title: '<?php echo xlt("Date"); ?>' },
            yaxis: { title: 'kg/m²' },
            shapes: [
                {
                    type: 'line',
                    x0: dates[0],
                    x1: dates[dates.length-1],
                    y0: 18.5,
                    y1: 18.5,
                    line: { color: 'blue', dash: 'dash' }
                },
                {
                    type: 'line',
                    x0: dates[0],
                    x1: dates[dates.length-1],
                    y0: 25,
                    y1: 25,
                    line: { color: 'orange', dash: 'dash' }
                },
                {
                    type: 'line',
                    x0: dates[0],
                    x1: dates[dates.length-1],
                    y0: 30,
                    y1: 30,
                    line: { color: 'red', dash: 'dash' }
                }
            ]
        };

        Plotly.newPlot('bmiChart', [bmiTrace], bmiLayout);

        // Respiration Chart
        const respirationTrace = {
            x: dates,
            y: vitalsData.respiration,
            type: 'scatter',
            mode: 'lines+markers',
            name: 'Respiration',
            line: { color: 'rgb(255, 159, 64)' }
        };

        const respirationLayout = {
            title: '<?php echo xlt("Respiration Rate"); ?>',
            xaxis: { title: '<?php echo xlt("Date"); ?>' },
            yaxis: { title: 'breaths/min' }
        };

        Plotly.newPlot('respirationChart', [respirationTrace], respirationLayout);
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
