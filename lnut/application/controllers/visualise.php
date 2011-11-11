<?php

/**
 * visualise.php
 */

class Visualise extends Controller {

	public function __construct () {
		parent::__construct();
		$this->page();

	}

	protected function page () {
		/**
		 * Fetch the translated terms
		 */
		$language_uid = 16;
		$prefix = '';

		$query ="SELECT `prefix` FROM `language` WHERE `uid`='".$language_uid."'";
		$result = database::query($query);
		if(mysql_error()=='' && mysql_num_rows($result)) {
			$arrRow = mysql_fetch_array($result);
			$prefix = $arrRow['prefix'];
		}

		$query = "SELECT
`sections_vocabulary_translations`.`uid` AS `uid`,
`sections_vocabulary`.`section_uid`
FROM
`units`,
`sections`,
`sections_vocabulary`,
`sections_vocabulary_translations`,
`years`
WHERE
`sections_vocabulary_translations`.`language_id`='".$language_uid."'
AND `sections_vocabulary_translations`.`term_uid`=`sections_vocabulary`.`uid`
AND `sections_vocabulary`.`section_uid`=`sections`.`uid`

AND `sections`.`unit_uid`=`units`.`uid`
AND `units`.`year_uid`=`years`.`uid`
ORDER BY `sections_vocabulary_translations`.`uid` ASC";

		$result = database::query($query);

		$arrSectionVocab = array();

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$arrSectionVocab[$row['section_uid']][] = $row['uid'];
			}
		}
/*
		echo '<table>';
		foreach($arrSectionVocab as $section_number=>$arrData) {
			echo '<tr><td>'.$section_number.'</td><td></td></tr>';
			foreach($arrData as $index=>$uid) {
				echo '<tr><td>'.$index.'</td><td>'.$uid.'</td></tr>';
			}
		}
		echo '</table>';
		echo '<hr />';
echo '<pre>';
print_r($arrSectionVocab);
echo '</pre>';
*/
		$query = "SELECT
`units`.`name` AS `unit`,
`sections_vocabulary`.`section_uid`,
`sections`.`name` AS `section_name`,
`sections_vocabulary_translations`.`uid` AS `term_uid`,
`sections_vocabulary_translations`.`name` AS `translation`,
`sections_vocabulary`.`name` AS `section_term_name`,
`years`.`year_number`,
`units`.`unit_number`,
`sections`.`section_number`,
`units`.`uid` AS `unit_uid`
FROM
`units`,
`sections`,
`sections_vocabulary`,
`sections_vocabulary_translations`,
`years`
WHERE
`sections_vocabulary_translations`.`language_id`='".$language_uid."'
AND `sections_vocabulary_translations`.`term_uid`=`sections_vocabulary`.`uid`
AND `sections_vocabulary`.`section_uid`=`sections`.`uid`
AND `sections`.`unit_uid`=`units`.`uid`
AND `units`.`year_uid`=`years`.`uid`
ORDER BY `unit_uid` ASC,`sections`.`section_position` ASC, `section_term_name` ASC";

		$result = database::query($query);
		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {

			echo '<table>';
			echo '<tr><th>Unit</th><th>Unit #</th><th>Section</th><th>Translation</th><th>Term</th><th>Existing File Name</th><th>Correct File Name</th></tr>';
			$section_uid = 0;
			while($row = mysql_fetch_assoc($result)) {
				if($section_uid != $row['section_uid']) {
					$section_uid = $row['section_uid'];
					$count = 0;
				}
				
				$unit_number = $row['unit_number'];
				if($unit_number < 10) {
					$unit_number = '0'.$unit_number;
				}
				$section_number = $row['section_number'];
				if($section_number < 10) {
					$section_number = '0'.$section_number;
				}

				$file = $prefix;
				$file.='_y'.$row['year_number'];
				$file.='_u'.$unit_number;
				$file.='_s'.$section_number;

				//foreach($arrSectionVocab[$section_number] as $index=>$uid) {
				foreach($arrSectionVocab[$row['section_uid']] as $index=>$uid) {
					if($uid == $row['term_uid']) {
						//$file_num = ((strlen($index) < 2) ? '0' : '') . $index;
						$file_num = $index+1;
						if($file_num<10) {
							$file_num = '0'.$file_num;
						}
					}
				}
				$actual_file = $file.'_'.$file_num.'.mp3';
				
				$count++;
				if($count < 10) {
					$file_num = '0'.$count;
				} else {
					$file_num = $count;
				}
				$file.='_'.$file_num.'.mp3';
				echo '<tr><td>'.$row['unit'].'</td><td>'.$row['unit_number'].'</td><td>'.$row['section_name'].'</td><td>'.$row['translation'].'</td><td>'.$row['section_term_name'].'</td><td>'.$file.'</td><td>'.$actual_file.'</td></tr>';
			}
			echo '</table>';
		}
		/*
echo '<pre>';
print_r($arrSectionVocab);
echo '</pre>';
*/
	}
}

?>