<?php

/**
 * static.debug.php
 */

class debug {

	public static $logging			= true;

	public static $log_type			= array (
		'informative'				=>'Informative',
		'notice'					=>'Notice',
		'message'					=>'Message',
		'warning'					=>'Warning',
		'error'						=>'Error',
		'fatal'						=>'Fatal',
		'debugging'					=>'Debugging'
	);

	public static $log_msgs			= array (
	);

	public static function message ($type = null, $dbug = null, $mess = '') {
		list($usec, $sec) = explode(" ", microtime());

		self::$log_msgs[] = array (
			'type'		=> $type,
			'time'		=> $usec,
			'dbug'		=> $dbug,
			'mess'		=> $mess
		);
	}

	public static function pre_array ($array = array()) {
		echo '<pre style="padding:3px;border:1px dotted yellow;">';
		print_r($array);
		echo '</pre>';
	}

	public static function output () {
		if (self::$logging && count(self::$log_msgs) > 0) {
			echo '<div style="margin:10px; height:300px;overflow:auto;clear:both;">';
			echo '<table style="border-collapse:collapse;font-size:0.8em;font-family:Arial;border:1px solid gold;">';
			echo '<tr><th style="text-align:left;">Framework Debug Messages</th><td></td></tr>';
			array_reverse(self::$log_msgs);
			foreach(self::$log_msgs as $index=>$msg) {
				echo '<tr><th style="text-align:left;">'.$msg['time'].'</th><td>'.$msg['type'].'</td></tr>';
				echo '<tr><th style="text-align:left;vertical-align:top;">Debug Trace</th>';
				echo '<td>';
				echo '<table style="font-size:1em;border-collapse:collapse;">';
				if(is_array($msg['dbug']) && count($msg['dbug']) > 0) {
					$x = array ();
					foreach($msg['dbug'] as $in_1=>$ar_1) {
						$y = '';
						if(isset($ar_1['file'])) {
							$y.= '<tr style="background:#424242;color:white;text-align:left;"><td>Line: '.$ar_1['line'].' of File: '.$ar_1['file'].'</td></tr>';
						}
						if(isset($ar_1['class'])) {
							$y.= '<tr><td>Class: '.$ar_1['class'];
							if(isset($ar_1['type'])){
								$y.= $ar_1['type'];
							}
							if(isset($ar_1['function'])) {
								$array_values = array_values($ar_1['args']);
								$data = array ();
								foreach($array_values as $value) {
									if(is_array($value)) {
										$value_data = array ();
										foreach($value as $value_key => $value_value) {
											$value_data[] = "'".$value_key."' = ".((is_string($value_value)) ? "'".$value_value."'" : $value_value);
										}
										$data[] = 'array(<br />'.implode(',<br />',$value_data).'<br />)';
									} else {
										$data[] = $value;
									}
								}
								$y.= $ar_1['function'].'(<br />'.implode(',<br />',$data).'<br />)';
							}
							$y.= '</td></tr>';
						} else {
							if(isset($ar_1['function'])) {
								$array_values = array_values($ar_1['args']);
								$data = array ();
								foreach($array_values as $value) {
									if(is_array($value)) {
										$data[] = $data[] = 'array(<br />'.implode(',<br />',$value).'<br />)';
									} else {
										$data[] = $value;
									}
								}
								$y.= '<tr><td>'.$ar_1['function'].'(<br />'.implode(',<br />',$data).'<br />)</td></tr>';
							}
						}
						$x[] = $y;
					}
					echo implode('',array_reverse($x));
				} else {
					echo '<tr><td style="vertical-align:top;">Message</td><td>'.$msg['dbug'].'</td></tr>';
				}
				echo '</table>';
				echo '</td></tr>';
				echo '<tr><th>Message</th><td>';
				echo '<table style="font-size:1em;font-family:Arial;">';
				if(is_array($msg['mess']) && sizeof($msg['mess']) > 0) {
					foreach($msg['mess'] as $msg_id=>$message) {
						echo '<tr><td>'.$message.'</td></tr>';
					}
				} else {
					echo '<tr><td>'.$msg['mess'].'</td></tr>';
				}
				echo '</table>';
				echo '</td></tr>';
			}
			echo '</table>';
			echo '</div>';
		}
	}
}

?>