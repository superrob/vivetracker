<?php
include ("header.php");
// Data gathering
$weekdays = array('Monday' => 0, 'Tuesday' => 0, 'Wednesday' => 0, 'Thursday' => 0, 'Friday' => 0, 'Saturday' => 0, 'Sunday' => 0);
$countries = array();
$time = 1461204000;
$hour = array();
$total_shipped = 0;
$first = true;
for ($i=0;$i<24;$i++)
	$hour[$i] = 0;
while ($time < time()) {
	$data = mysql_query("SELECT id, description, destination FROM `vive` WHERE firstdate='".date("Y-m-d", $time)."' AND `origin` LIKE  '%Ricany-Jazlovice%'");
	$total = mysql_num_rows($data);
	$total_shipped += $total;	
	$weekdays[date("l", $time)] += $total;
	while ($dat = mysql_fetch_array($data)) {
		$ex = explode(" - ", $dat['destination']);
		$last = count($ex)-1;
		if (isset($countries[$ex[$last]]))
			$countries[$ex[$last]]++; 
		else
			$countries[$ex[$last]] = 1;
		
			if ($first)
				continue;
				
			$pos = strrpos(":",$dat['description']);
			$h = intval(substr($dat['description'], $pos-5, 2));
			$hour[$h]++;
	}
	if ($first)
		$firstday = $total;			
		
	$first = false;
	$days[date("jS F", $time)] = $total;
	$time += 60 * 60 * 24;
}
arsort($countries);
for ($i=0;$i<24;$i++)
	if ($hour[$i] > 0)
		$hour[$i] = round(($hour[$i] / ($total_shipped-$firstday)) * 100,2);
	
?>
	
      <div class="page-header">
        <h1>Shipments per day since 21. April</h1>
      </div>
      <div class="row">
		<canvas id="myChart" width="400" height="125"></canvas>
		<script>
		var ctx = document.getElementById("myChart");
		var myChart = new Chart(ctx, {
			type: 'bar',
			responsive: false,
			data: {
				labels: [<? foreach (array_keys($days) as $key) echo "'$key',"; ?>],
				datasets: [{
					label: 'Estimated amount of vives shipped this day',
					data: [<? foreach (array_values($days) as $val) echo "'$val',"; ?>],
					backgroundColor: "rgba(19,18,228,0.2)",
					borderColor: "rgba(19,18,228,1)",
					borderWidth: 2,
					hoverBackgroundColor: "rgba(38,38,228,0.4)",
					hoverBorderColor: "rgba(38,38,228,1)"
				}]
			},
			options: {
				scales: {
					yAxes: [{
						ticks: {
							beginAtZero:true
						}
					}]
				}
			}
		});
		</script>		
		<div class="alert alert-info" role="alert">Total estimated units shipped since 21. April: <strong><?=$total_shipped?></strong></div>
      </div>
	  
	  <div class="page-header">
        <h1>Country distribution since 21. April</h1>
      </div>
      <div class="row">
		<canvas id="distChart" width="400" height="200"></canvas>
		<script>
		var randomColorGenerator = function () { 
			return '#' + (Math.random().toString(16) + '0000000').slice(2, 8); 
		};
		var ctx = document.getElementById("distChart");
		var myChart = new Chart(ctx, {
			type: 'bar',
			responsive: false,
			data: {
				labels: [<? foreach (array_keys($countries) as $key) echo "'$key',"; ?>],
				datasets: [{
					label: 'Estimated amount of vives this country has received',
					data: [<? foreach (array_values($countries) as $val) echo "'$val',"; ?>],
					borderWidth: 2,
					backgroundColor: [<? for ($i = 0; $i <count($countries);$i++) echo 'randomColorGenerator(),'; ?>, randomColorGenerator()],
					hoverBackgroundColor: "rgba(255,99,132,0.4)",
					hoverBorderColor: "rgba(255,99,132,1)"
				}]
			},
			options: {
				scales: {
					yAxes: [{
						ticks: {
							beginAtZero:true
						}
					}]
				}
			}
		});
		</script>		
      </div>
	  
	  <div class="page-header">
        <h1>Weekday ditribution of Vives shipped since 21. April</h1>
      </div>
      <div class="row">
		<canvas id="dayChart" width="400" height="125"></canvas>
		<script>
		var ctx = document.getElementById("dayChart");
		var myChart = new Chart(ctx, {
			type: 'bar',
			responsive: false,
			data: {
				labels: [<? foreach (array_keys($weekdays) as $h) echo "'$h',"; ?>],
				datasets: [{
					label: 'Vives shipped during this weekday',
					data: [<? foreach (array_values($weekdays) as $val) echo "'$val',"; ?>],
					backgroundColor: "rgba(255,99,132,0.2)",
					borderColor: "rgba(255,99,132,1)",
					borderWidth: 2,
					hoverBackgroundColor: "rgba(38,38,228,0.4)",
					hoverBorderColor: "rgba(38,38,228,1)"
				}]
			},
			options: {
				scales: {
					yAxes: [{
						ticks: {
							beginAtZero:true
						}
					}]
				}
			}
		});
		</script>
      </div>
	  
	  <div class="page-header">
        <h1>Percent of total vives shipped since 22. April split by hour</h1>
      </div>
      <div class="row">
		<canvas id="percentChart" width="400" height="125"></canvas>
		<script>
		var ctx = document.getElementById("percentChart");
		var myChart = new Chart(ctx, {
			type: 'bar',
			responsive: false,
			data: {
				labels: [<? foreach (array_keys($hour) as $h) echo "'$h',"; ?>],
				datasets: [{
					label: 'Percentage of Vives shipped during this hour.',
					data: [<? foreach (array_values($hour) as $val) echo "'$val',"; ?>],
					backgroundColor: "rgba(255,99,132,0.2)",
					borderColor: "rgba(255,99,132,1)",
					borderWidth: 2,
					hoverBackgroundColor: "rgba(38,38,228,0.4)",
					hoverBorderColor: "rgba(38,38,228,1)"
				}]
			},
			options: {
				scales: {
					yAxes: [{
						ticks: {
							beginAtZero:true
						}
					}]
				}
			}
		});
		</script>
      </div>
<?php include("footer.php"); ?>