<?php
include("header.php");
?>
	
      <div class="page-header">
        <h1>Estimated european country distribution (<?=date("jS F", $timestamp)?> <? if ($currentday) echo "@ ".date("G:i")." CEST"?>)</h1>
      </div>
      <div class="row">
          <table class="table">
            <thead>
              <tr>
                <th>Country</th>
                <th>Estimated units shipped</th>
              </tr>
            </thead>
            <tbody>
			<?
			$countries = array();
			$cities = array();
			$data = mysql_query("SELECT destination FROM `vive` WHERE firstdate='".$currentdate."' AND `origin` LIKE  '%Ricany-Jazlovice%'");
			$total = mysql_num_rows($data);
			while ($dat = mysql_fetch_array($data)) {
				$ex = explode(" - ", $dat['destination']);
				$last = count($ex)-1;
				if (isset($countries[$ex[$last]]))
					$countries[$ex[$last]]++; 
				else
					$countries[$ex[$last]] = 1;
				
				$city = ucfirst(strtolower($ex[$last-1]));
				if (isset($cities[$ex[$last]][$city]))
					$cities[$ex[$last]][$city]++;
				else
					$cities[$ex[$last]][$city] = 1;
			}
			arsort($countries);
			foreach($countries as $key => $am) {
				echo "<tr><td>$key</td><td>$am</td></tr>";
			}
			?>
            </tbody>
          </table>
		  <?
		  if ($total == 0) {
			  $nonvive = mysql_fetch_array(mysql_query("select scandate from vive order by scandate desc limit 1"));
			  echo '<div class="alert alert-danger" role="alert"><strong>Aww!</strong> no shipments this day :(... yet?<br>Last non-vive tracking number found: ' . $nonvive['scandate'] . '</div>';
		  } else {	  
			  echo '<div class="alert alert-info" role="alert"><strong>Woh!</strong> Looks like '.$total.' are estimated to be shipped this day!';
			  if ($currentday) {
				  $vive = mysql_fetch_array(mysql_query("select scandate from vive WHERE `origin` LIKE  '%Ricany-Jazlovice%' order by scandate desc limit 1"));
				  $nonvive = mysql_fetch_array(mysql_query("select scandate from vive order by scandate desc limit 1"));
			      echo "<br>Last Vive tracking number found: " . $vive['scandate'];
				  echo "<br>Last non-vive tracking number found: " . $nonvive['scandate'];
			  }
			  echo '</div>';
		  } ?>
      </div>

      <div class="page-header">
        <h1>Destination cities split by country</h1>
      </div>
      <div class="row">
		<?
		foreach($countries as $key => $am) {
			ksort($cities[$key]);
			echo "<div class='panel panel-default'>
            <div class='panel-heading'>
              <h3 class='panel-title'>$key</h3>			  
            </div>
            <div class='panel-body'>";
			echo "<table class=\"table\">
            <thead>
              <tr>
                <th>City</th>
                <th>Estimated units shipped</th>
              </tr>
            </thead>
            <tbody>";
			foreach ($cities[$key] as $city => $amount) {
				echo "<tr><td>$city</td><td>$amount</td></tr>";
			}
			echo '</tbody>
          </table></div></div>';
		}
		?>
      </div>
<?php
include("footer.php");
?>