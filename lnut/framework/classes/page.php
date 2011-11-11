<?php
/**
* page.php
*/
class page extends database {

private $title              = '';
private $keywords		= '';
private $description        = '';

public function __construct ($tag) {
	$this->tag($tag);
}

	public function tag ($tag='') {
		/**
		 * Select only one page with
		 * a matching tag name
		 */
		$query = "SELECT ";
		$query.= "* ";
		$query.= "FROM ";
		$query.= "`pages` ";
		$query.= "WHERE ";
		$query.= "`tag`='".mysql_real_escape_string($tag)."' ";
		$query.= "AND ";
		$query.= "`locale`='".mysql_real_escape_string(config::get('locale'))."' ";
		$query.= "LIMIT 1";
		$result = $this->query($query);

		if($result && '' == mysql_error() && 1 == mysql_num_rows($result)) {
			$array                  = mysql_fetch_assoc($result);
			$this->title		= $array['title'];
			$this->keywords		= $array['keywords'];
			$this->description	= $array['description'];
		} else {
			$query = "SELECT ";
			$query.= "* ";
			$query.= "FROM ";
			$query.= "`pages` ";
			$query.= "WHERE ";
			$query.= "`tag`='".mysql_real_escape_string($tag)."' ";
			$query.= "AND ";
			$query.= "`locale`='default' ";
			$query.= "LIMIT 1";
			$result = $this->query($query);

			if($result && '' == mysql_error() && 0 < mysql_num_rows($result)) {
				$array                  = mysql_fetch_assoc($result);
				$this->title		= $array['title'];
				$this->keywords		= $array['keywords'];
				$this->description	= $array['description'];
			}
		}
	}

	public function title () {
		return htmlentities($this->title);
	}

	public function keywords () {
		return htmlentities($this->keywords);
	}

	public function description () {
		return htmlentities($this->description);
	}
}
?>