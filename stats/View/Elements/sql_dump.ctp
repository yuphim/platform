<?php
/**
 * SQL Dump element. Dumps out SQL log information
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.View.Elements
 * @since         CakePHP(tm) v 1.3
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

if (!class_exists('ConnectionManager') || Configure::read('debug') < 2) {
	return false;
}
?>
<style type='text/css'>
	#sqldump pre {
		border:0;
	}
</style>
<div class="table-responsive">
<div id='sqldump'>
<?php
require_once ROOT. DS . 'vendors' . DS . 'SqlFormatter.php';
$noLogs = !isset($logs);
if ($noLogs):
	$sources = ConnectionManager::sourceList();

	$logs = array();
	foreach ($sources as $source):
		$db = ConnectionManager::getDataSource($source);
		if (!method_exists($db, 'getLog')):
			continue;
		endif;
		$logs[$source] = $db->getLog();
	endforeach;
endif;

if ($noLogs || isset($_forced_from_dbo_)):
	foreach ($logs as $source => $logInfo):
		$tableId = 'cakeSqlLog_'.preg_replace('/[^A-Za-z0-9_]/', '_', uniqid(time(), true));

		$text = $logInfo['count'] > 1 ? 'queries' : 'query';
		printf(
			'<table class="cake-sql-log table table-striped" id="%s" summary="Cake SQL Log" cellspacing="0">',
			$tableId

		);
		printf('<caption>(%s) %s %s took %s ms</caption>',$source, $logInfo['count'], $text, $logInfo['time']);
	?>
	<thead>
		<tr><th>Nr</th><th>Query</th><th>Error</th><th>Affected</th><th>Num. rows</th><th>Took (ms)</th></tr>
	</thead>
	<tbody>
	<?php
		foreach ($logInfo['log'] as $k => $i) :
			$i += array('error' => '');
			if (!empty($i['params']) && is_array($i['params'])) {
				$bindParam = $bindType = null;
				if (preg_match('/.+ :.+/', $i['query'])) {
					$bindType = true;
				}
				foreach ($i['params'] as $bindKey => $bindVal) {
					if ($bindType === true) {
						$bindParam .= h($bindKey) . " => " . h($bindVal) . ", ";
					} else {
						$bindParam .= h($bindVal) . ", ";
					}
				}
				$i['query'] .= " , params[ " . rtrim($bindParam, ', ') . " ]";
			}
			printf('<tr><td>%d</td><td>%s</td><td>%s</td><td style="text-align: right">%d</td><td style="text-align: right">%d</td><td style="text-align: right">%d</td></tr>%s',
				$k + 1,
				SqlFormatter::highlight($i['query']),
				$i['error'],
				$i['affected'],
				$i['numRows'],
				$i['took'],
				"\n"
			);
		endforeach;
	?>
	</tbody></table>
	<?php
	endforeach;
else:
	printf('<p>%s</p>', __d('cake_dev', 'Encountered unexpected %s. Cannot generate SQL log.', '$logs'));
endif;
?>
</div>
</div>