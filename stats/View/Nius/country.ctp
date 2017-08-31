<?php
echo $this->extend('/Common/fluid');
?>
<div class="box">
    <div class="box-body">
		<div>
			<?php
			echo $this->Form->create('LogAccountsCountryByDay', array('inputDefaults' => array('div' => false, 'label' => false), 'class' => 'form-inline'));
			echo '<div class="form-group">';

			echo $this->Form->input('game_id', array(
				'id' => 'games-select',
				'empty' => '--All Games--', 'data-placeholder' => '--All Games--',
				'value' => empty($this->request->params['named']['game_id']) ? '': $this->request->params['named']['game_id']
			));

			echo $this->element('date_ranger_picker');
			echo $this->Form->submit('Submit', array('class' => 'btn btn-default', 'div' => false));

			echo '</div>';
			echo $this->Form->end()
			?>
		</div>
		* This chart isn't realtime, it is a <strong>half day</strong> late.
	</div>
</div>

<?php if (empty($data) || empty($this->request->params['named']['game_id'])) { ?>
	<script type="text/javascript">
	$(function() {
		$('#games_select_chzn').trigger('mousedown');
	})
	</script>
<?php goto a; } ?>

<div class="box">
    <div class="box-body">
        <div id='chart'></div>
    </div>
</div>

<?php
$pointInterval = 3600 * 1000 * 24;
$m = (int) date('m', $fromTime) - 1;
$pointStart = '____Date.UTC(' . date('Y', $fromTime) . ', ' . $m . ', ' . date('d', $fromTime) . ')____';

$this->Highchart->render(array(
	'chart' => array('type' => 'area'),
	'title' => array('text' => 'NRU by Countries - ' . $games[$this->request->params['named']['game_id']]),
	'xAxis' => array('title' => array('text' => 'Dates')),
	'yAxis' => array('title' => array('text' => 'New registers')),
	'tooltip' => array('shared' => true, 'formatter' => ""),
	'plotOptions' => array(
		'area' => array(
			'stacking' => 'normal',
		),
		'series' => array(
			'pointStart' => $pointStart,
			'pointInterval' => $pointInterval
		)
	)), $dataHighchart);
?>
<div class="box">
    <div class="box-body">
		<div class="table-responsive">
<table  class='table table-striped table-bordered responsive '>
	<thead>
		<tr>
			<th>Countries</th>
			<?php
			for($i=0 ;$i < count($rangeDates); $i++){
				echo "<th class='int'>" . date('d/m', strtotime($rangeDates[$i])) . "</th>";
			}
			?>
			<th class="int">AVG</th>
			<th class='int'>Total</th>
		</tr>
	</thead>
	<tbody>
		<tr class="selected-total">
			<td>Selected Rows</td>
			<?php
			foreach($rangeDates as $val) {
				echo '<td></td>';
			}
			?>
			<td></td>
            <td></td>
		</tr>

		<?php
		# Calculate totals
		$totals = array();
		foreach($data as $v) {
			foreach($v['data'] as $kk => $count) {
				if (isset($totals[$kk])) {
						$totals[$kk] += $count;
				} else {
					$totals[$kk] = $count;
				}
			}
		}

		# print data to table
		echo '<tr>';
		echo '<td class="total">Total</td>';
		foreach($totals as $val) {
			echo '<td class="total int">' . n($val) . '</td>';
		}
		echo '<td class="total int">' . n(array_sum($totals) / count($rangeDates)) . '</td>';
		echo '<td class="total int">' . n(array_sum($totals)) . '</td>';
		echo '</tr>';

		foreach($data as $v) {
			$range = 0;
			echo '<tr>';
			echo '<td class="name">' . $v['name'] . '</td>';

			$t = 0;
			foreach($v['data'] as $kk => $count) {
				$t += $count;
				echo '<td class="int">' . n($count) . '</td>';
			}

			$class = '';
			$rate = '';
			if (isset($total_old) && !empty($total_old)) {
				foreach ($total_old as $value) {
					if ($value['country'] == $v['name']) {
						if ($t > $value['sum']) {
							$rate = round((abs($t - $value['sum']) / $t) * 100, 1) . '%';
							$class = 'glyphicon glyphicon-arrow-up';
						} else if ($t < $value['sum']) {
							$rate = round((abs($t - $value['sum']) / $t) * 100, 1) . '%';
							$class = 'glyphicon glyphicon-arrow-down';
						} else if ($t == $value['sum']) {
							$rate = '&nbsp;<span title="no change">0%</span>';
						}
						if ($rate > 10000) $rate = '&infin;';
					}
				}
			}

			$a = n($t / count($rangeDates));
			switch (strlen($a)) {
				case 1 :
					$a = $a . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ";
					break;
				case 2 :
					$a = $a . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ";
					break;
				case 3 :
					$a = $a . "&nbsp;&nbsp;&nbsp;&nbsp; ";
					break;
				case 4 :
					$a = $a . "&nbsp;&nbsp;&nbsp;  ";
					break;
				case 5 :
					$a = $a . "&nbsp; ";
					break;
			}
			?>
			<td class="int total"><?php echo $a;?><?php echo ($class != '') ? '<label class="' . $class . '"></label>' : '';?><?php echo ($rate != '') ? $rate : '&nbsp;<span title="no data">--</span>';?></td>
			<?php
			echo '<td class="int total">' . n($t) . '</td>';
			if (!empty($gameTotals)) {
				echo '<td class="int total">' . n($gameTotals[$v['app_key']]) . '</td>';
			}
			echo '</tr>';
		}
		?>
	</tbody>
</table>

</div>

</div>

</div>
		
<!--<script type="text/javascript" charset="utf-8">
	$(function() {
		var table = $('.table').DataTable( {
            "scrollX": "100%",
			"scrollCollapse": true,
			"paging": false,
			"search": false,
			"bSort": false,
			bFilter: false,
			bInfo: false
		} );
	} );
</script>-->
<?php
a: