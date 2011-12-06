<?php

/**
 * links.php
 */

class Links extends Controller {

	public function __construct () {
		parent::__construct();
		$this->page();
	}

	protected function page () {
	$html ='<table width="80%" border="1">
			<tr>
				<th>ID</th>
				<th>Site</th>
				<th>Available</th>
				<th>Public</th>
				<th>Learnable</th>
				<th>Support</th>
			</tr>';


		$query = "SELECT `uid`,`prefix`,`active`,`available`,`is_learnable`,`is_support` FROM `language` ORDER BY `prefix` ";
		$result = database::query($query);
		while($arrRow=mysql_fetch_assoc($result)) {
			$html .='<tr>
					<td>'.$arrRow['uid'].'</th>
					<td><a href="'.config::base($arrRow['prefix']).'">'.config::base($arrRow['prefix']).'</a></th>
					<td>'.(($arrRow['active']==1)?'Yes':'No').'</th>
					<td>'.(($arrRow['available']==1)?'Yes':'No').'</th>
					<td>'.(($arrRow['is_learnable']==1)?'Yes':'No').'</th>
					<td>'.(($arrRow['is_support']==1)?'Yes':'No').'</th>
				</tr>';

		}
		$html .='</table>';
		echo $html;
	}
	
}

?>