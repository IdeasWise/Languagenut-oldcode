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
		$query = "SELECT
`units`.`name` AS `unit`,
`units`.`unit_number` AS `unit_number`,
`sections_vocabulary`.`section_uid`,
`sections`.`name` AS `section_name`,
`sections_vocabulary_translations`.`name` AS `translation`,
`sections_vocabulary`.`name` AS `section_term_name`
FROM
`units`,
`sections`,
`sections_vocabulary`,
`sections_vocabulary_translations`
WHERE
`sections_vocabulary_translations`.`language_id`=16
AND `sections_vocabulary_translations`.`term_uid`=`sections_vocabulary`.`uid`
AND `sections_vocabulary`.`section_uid`=`sections`.`uid`
AND `sections`.`unit_uid`=`units`.`uid`
ORDER BY `unit_uid` ASC,`sections`.`section_position` ASC, `section_term_name` ASC";

		$result = database::query($query);
		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			echo '<table>';
			echo '<tr><th>Unit</th><th>Unit #</th><th>Section</th><th>Translation</th><th>Term</th></tr>';
			while($row = mysql_fetch_assoc($result)) {
				echo '<tr><td>'.$row['unit'].'</td><td>'.$row['unit_number'].'</td><td>'.$row['section_name'].'</td><td>'.$row['translation'].'</td><td>'.$row['section_term_name'].'</td></tr>';
			}
			echo '</table>';
		}
	}
}

?>