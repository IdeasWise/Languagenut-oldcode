<?php

/**
 *
 */

class format {
	public function __construct () {
	}
	public static $stringcompare = array('is', 'regex', 'like', 'before', 'before_to', 'after', 'from_after');

	public static function string_compare ($table='',$field='',$match=array(),$type='is') {
		switch($type) {
			case 'is':			$condition = "= '".$match[0]."'";							break;
			case 'regex':		$condition = "REGEXP '[[:<:]]".$match[0]."[[:>:]]'";		break;
			case 'like':		$condition = "LIKE '%".$match[0]."%'";						break;
			case 'before':		$condition = "< '".$match[0]."'";							break;
			case 'before_to':	$condition = "<= '".$match[0]."'";							break;
			case 'after':		$condition = "> '".$match[0]."'";							break;
			case 'from_after':	$condition = ">= '".$match[0]."'";							break;
			case 'between':		$condition = "BETWEEN '".$match[0]."' AND '".$match[1]."'";	break;
			default:			$condition = "= '".$match[0]."'";							break;
		}
		return (($table != '') ? "`$table`." : "") . "`$field` $condition";
	}
	public static function to_flag ($code='') {
		if(strlen($code)==2) {
			return '<img src="'.core::site().'images/'.$code.'.gif" />';
		}
		return '';
	}
	public static function to_phone ($region='uk',$phonenumber='') {
		$num = preg_replace('/[^\d]/','',$phonenumber);
		switch($region) {
			case 'gb':
			// 5 6
				$num = substr($num,0,5).' '.substr($num,5,6);
			break;
			case 'us':
			// 3 3 4
				if(strlen($num) > 7) {
					$num = substr($num,0,3).'-'.substr($num,3,3).'-'.substr($num,6,(strlen($num)-6));
				}
				if(strlen($num) > 13) {
					$num = substr($num,0,12).' '.substr($num,10,(strlen($num)-13));
				}
			break;
			case 'fr':
			// 4 6
				if(substr($num, 0, 2) == '07') {
					$num = substr($num,0,5).' '.substr($num,5,6);
				} else if(substr($num, 0, 2) == '00') {
					$num = '00 '.substr($num,2,2).' '.substr($num,4,4).'-'.substr($num,10,6).' '.substr($num,14);
				} else {
					if(strlen($num) > 7) {
						$num = substr($num,0,4).'-'.substr($num,4,6).' '.substr($num,10);
					}
				}
			break;
		}
		return $num;
	}
	public static function to_select ($attributes = array (), $options = array (), $selected_value = null) {
		$attribute_string = array ();
		if(is_array($attributes) && count($attributes) > 0) {
			foreach($attributes as $attribute=>$value) {
				$attribute_string[] = $attribute.'="'.$value.'"';
			}
		}
		$attribute_string = implode(' ',$attribute_string);
		$html = '{options}';
		if($attributes['options_only'] != true) {
			$html = '<select'.((strlen($attribute_string) > 0) ? ' '.$attribute_string : '').'>{options}</select>';
		}
		$optionstring = '';
		foreach($options as $key=>$value) {
			if(is_array($value)) {
				$optionstring.= '<optgroup label="'.$value['label'].'">';
				foreach($value['options'] as $option_key => $option_value) {
					$optionstring.= '<option value="'.$option_key.'"';
					$optionstring.= ((!is_null($selected_value) && (!is_array($selected_value) && $selected_value==$option_key)||(is_array($selected_value) && in_array($value,$selected_value))) ? ' selected="selected"' : '');
					$optionstring.= ' title="'.$value.'">'.$option_value.'</option>';
				}
				$optionstring.= '</optgroup>';
			} else {
				$optionstring.= '<option value="'.$key.'"';
				if(is_array($selected_value)) {
					if(in_array($key, $selected_value)) {
						$optionstring .= ' selected="selected"';
					}
				} else {
					if($key == $selected_value) {
						$optionstring .= ' selected="selected"';
					}
				}
				$optionstring .= ' title="'.$value.'">'.$value.'</option>';
			}
		}
		return str_replace('{options}',$optionstring,$html);
	}
	public static function to_textbox($attributes = array (),$default_value = "") {

		$attribute_string = array ();
		if(is_array($attributes) && count($attributes) > 0) {
			foreach($attributes as $attribute=>$value) {
				$attribute_string[] = $attribute.'="'.$value.'"';
			}
		}
		$attribute_string = implode(' ',$attribute_string);
		$html = '<input type="text" '.((strlen($attribute_string) > 0) ? ' '.$attribute_string : '').' value="'.$default_value.'" />';
		return $html;
	}
	public static function to_radio($attributes = array (),$checked = false) {

		$attribute_string = array ();
		if(is_array($attributes) && count($attributes) > 0) {
			foreach($attributes as $attribute=>$value) {
				$attribute_string[] = $attribute.'="'.$value.'"';
			}
		}
		$attribute_string = implode(' ',$attribute_string);

		$check_str = ($checked)?'checked="checked"':'';

		$html = '<input type="radio" '.((strlen($attribute_string) > 0) ? ' '.$attribute_string : '').' '.$check_str.' />';
		return $html;
	}
	public static function to_checked ($value = 0, $compare = 0) {
		return ((int)$value === (int)$compare && preg_replace('/[^\d]/','',$compare) > 0 && preg_replace('/[^\d]/','',$value) > 0) ? ' checked="checked"' : '';
	}
	public static function to_selected ($value = 0, $compare = 0) {
		return ((int)$value === (int)$compare && preg_replace('/[^\d]/','',$compare) > 0 && preg_replace('/[^\d]/','',$value) > 0) ? ' selected="selected"' : '';
	}
	public static function to_message ($content = '') {
		return '<div class="message-body">'.$content.'</div>';
	}
	public static function to_error ($content = '') {
		return '<div class="error-body">'.$content.'</div>';
	}

	public static function to_update_message ($content = '') {
		$content = '<div class="update_successful">'.$content.'</div>';
		return $content;
	}
	public static function mysql_prepare ($array=array()) {
		database::connect();
		if(is_array($array)) {
			switch($array['type']) {
				case 'TINYINT':
				case 'SMALLINT':
				case 'MEDIUMINT':
				case 'INT':
				case 'BIGINT':
					return (int)preg_replace('/[^\d]/','',$array['value']);
				break;

				case 'DECIMEL':
				case 'FLOAT':
				case 'DOUBLE':
				case 'REAL':
				case 'BIT':
				case 'BOOL':
				case 'SERIAL':
					return "'".(float)preg_replace('/[^\d\.]/','',$array['value'])."'";
				break;

				case 'DATE':
				case 'DATETIME':
				case 'TIMESTAMP':
				case 'TIME':
				case 'YEAR':
					return "'".mysql_real_escape_string($array['value'])."'";
				break;

				case 'CHAR':
				case 'VARCHAR':
				case 'TINYTEXT':
				case 'TEXT':
				case 'MEDIUMTEXT':
				case 'LONGTEXT':
				case 'BINARY':
				case 'VARBINARY':
				case 'TINYBLOB':
				case 'MEDIUMBLOB':
				case 'BLOB':
				case 'LONGBLOB':
				case 'ENUM':
				case 'SET':
					return "'".mysql_real_escape_string($array['value'])."'";
				break;

				case 'GEOMETRY':
				case 'POINT':
				case 'LINESTRING':
				case 'POLYGON':
				case 'MULTIPOINT':
				case 'MULTILINESTRING':
				case 'MULTIPOLYGON':
				case 'GEOMETRYCOLLECTION':
					return "'".mysql_real_escape_string($array['value'])."'";
				break;

                                case strstr($array['type'], 'ENUM'):
                                        return "'".mysql_real_escape_string($array['value'])."'";
                                break;
			}
		}
	}

	public static function toTableName($string='') {
		return preg_replace('/[^a-zA-z_\d]/','',$string);
	}

	public static function to_sql_condition ($array = array()) {
		$table		= mysql_real_escape_string($array['table']);
		$field		= mysql_real_escape_string($array['field']);
		$condition	= $array['condition'];
		$params		= $array['params'];
		$value		= mysql_real_escape_string($array['value']);

		$sql1 = "`$table`.`$field` ";

		switch($condition) {
			case 'is':			$sql2= "= '$condition'";						break;
			case 'regexp':		$sql2= "REGEXP '[[:<:]]".$condition."[[:>:]]'";	break;
			case 'like':		$sql2= "LIKE '%".$condition."%'";				break;
			case 'in':			$sql2= "IN ('".implode("','",$condition)."')";	break;
			case 'starts':		$sql2= "LIKE '".$condition."%'";				break;
			case 'ends':		$sql2= "LIKE '%".$condition."'";				break;
			case '<':			$sql2= "< '".$condition."'";					break;
			case '<=':			$sql2= "<= '".$condition."'";					break;
			case '>=':			$sql2= ">= '".$condition."'";					break;
			case '>':			$sql2= "> '".$condition."'";					break;
			case 'between':		$sql2= "BETWEEN '".$condition[0]."' AND '".$condition[1]."'";				break;
			case 'sounds like':
				$sql2= "SOUNDEX(".$sql1.") = SOUNDEX('".$condition."')";
				$sql1 = '';
			break;
		}
		return $sql1 . $sql2;
	}
	public static function to_day ($daynumber) {
		$day = 'N/A';
		switch($daynumber) {
			case 1:	$day = 'SUNDAY';				break;
			case 2: $day = 'MONDAY';				break;
			case 3: $day = 'TUESDAY';				break;
			case 4:	$day = 'WEDNESDAY';				break;
			case 5:	$day = 'THURSDAY';				break;
			case 6:	$day = 'FRIDAY';				break;
			case 7:	$day = 'SATURDAY';				break;
			case 8:	$day = 'NOT_APPLICABLE_SHORT';	break;
		}
		return $day;
	}
	public static function to_yesno ($bool) {
		return ($bool == 1) ? 'Yes' : 'No';
	}
	public static function to_yesno_graphic ($bool) {
		return '<img src="'.core::site().'images/'.(($bool == 1) ? 'greenball' : 'redball').'.gif" alt="'.(($bool == 1) ? 'Yes' : 'No').'" title="'.(($bool == 1) ? 'Yes' : 'No').'" />';
	}
	public static function to_previous_icon ($url='') {
		if($url && strlen($url) > 0) {
			return '<a href="'.core::site().$url.'" class="previous" title="Previous"></a>';
		} else {
			return '<span class="previous"></span>';
		}
	}
	public static function to_next_icon ($url='') {
		if($url && strlen($url) > 0) {
			return '<a href="'.core::site().$url.'" class="next" title="Next"></a>';
		} else {
			return '<span class="next"></span>';
		}
	}
	public static function to_edit_icon ($url='') {
		if($url && strlen($url) > 0) {
			return '<a href="'.core::site().$url.'" class="edit" title="Edit"></a>';
		} else {
			return '<span class="edit"></span>';
		}
	}
	public static function to_delete_icon ($url='') {
		if($url && strlen($url) > 0) {
			return '<a href="'.core::site().$url.'" class="delete" title="Delete"></a>';
		} else {
			return '<span class="delete"></span>';
		}
	}
	public static function active_yesno ($int) {
		return ($int == 1) ? 'yes' : 'no';
	}
	public static function to_anchor($url='',$text='',$class='') {
		return '<a href="'.$url.'"'.(($class!='')?' class="'.$class.'"' : '').'>'.$text.'</a>';
	}
	// new
	public static function to_friendly_url ($text = '', $id = null) {
		return strtolower(preg_replace('/(_){2,}/','_',preg_replace('/[^a-zA-Z0-9\'\-]/','_',stripslashes($text)))) . ($id != null ? '-'.$id : '');
	}
	// old
	public static function make_friendly_url ($text = null, $id = null) {
		if($text) {
			$text = html_entity_decode(trim($text));
			$text = str_replace(' ', '-', $text);
			$text = str_replace('_', '-', $text);
			$text = preg_replace('/[^a-zA-Z0-9_\-]+/', '', $text);
			$text = preg_replace('/[\-]+/', '-', $text);
			$text = preg_replace('/(_)+/', '_', $text);
			return strtolower($text.(($id) ? '_'.$id : ''));
		} else {
			return null;
		}
	}
	public static function to_url ($text = '', $linkname = '') {
		$parsed = parse_url($text);

		if (!is_array($parsed) || count($parsed) <= 1) {
			return format::output($linkname);
		}
		$uri = isset($parsed['scheme']) ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '' : '//') : '';
		$uri .= isset($parsed['user']) ? $parsed['user'].(isset($parsed['pass']) ? ':'.$parsed['pass'] : '').'@' : '';
		$uri .= isset($parsed['host']) ? $parsed['host'] : '';
		$uri .= isset($parsed['port']) ? ':'.$parsed['port'] : '';

		if(isset($parsed['path'])) {
			$uri .= (substr($parsed['path'], 0, 1) == '/') ? $parsed['path'] : ('/'.$parsed['path']);
		}
		$uri .= isset($parsed['query']) ? '?'.$parsed['query'] : '';
		$uri .= isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';

		return '<a href="'.$uri.'" title="'.$uri.'">'.(($linkname == '') ? $uri : self::output($linkname)).'</a>';
	}
	public static function to_email ($text = '') {
		return '<a href="mailto:'.$text.'">'.$text.'</a>';
	}
	/**
	 * Converts high-character symbols into their respective html entities.
	 *
	 * @return string
	 * @param  string $s
	 */
	public static function to_html_entities ($s = '') {
		static $symbols = array (
		'‚', 'ƒ', '„', '…', '†', '‡', 'ˆ', '‰', 'Š', '‹', 'Œ', '‘', '’', '“', '”',
		'•', '–', '—', '˜', '™', 'š', '›', 'œ', 'Ÿ', '€', 'Æ', 'Á', 'Â', 'À', 'Å',
		'Ã', 'Ä', 'Ç', 'Ð', 'É', 'Ê', 'È', 'Ë', 'Í', 'Î', 'Ì', 'Ï', 'Ñ', 'Ó', 'Ô',
		'Ò', 'Ø', 'Õ', 'Ö', 'Þ', 'Ú', 'Û', 'Ù', 'Ü', 'Ý', 'á', 'â', 'æ', 'à', 'å',
		'ã', 'ä', 'ç', 'é', 'ê', 'è', 'ð', 'ë', 'í', 'î', 'ì', 'ï', 'ñ', 'ó', 'ô',
		'ò', 'ø', 'õ', 'ö', 'ß', 'þ', 'ú', 'û', 'ù', 'ü', 'ý', 'ÿ', '¡', '£', '¤',
		'¥', '¦', '§', '¨', '©', 'ª', '«', '¬', '­', '®', '¯', '°', '±', '²', '³',
		'´', 'µ', '¶', '·', '¸', '¹', 'º', '»', '¼', '½', '¾', '¿', '×', '÷', '¢',
		'…', 'µ'
		);
		static $entities = array (
		'&#8218;',  '&#402;',   '&#8222;',  '&#8230;',  '&#8224;',  '&#8225;',  '&#710;',
		'&#8240;',  '&#352;',   '&#8249;',  '&#338;',   '&#8216;',  '&#8217;',  '&#8220;',
		'&#8221;',  '&#8226;',  '&#8211;',  '&#8212;',  '&#732;',   '&#8482;',  '&#353;',
		'&#8250;',  '&#339;',   '&#376;',   '&#8364;',  '&aelig;',  '&aacute;', '&acirc;',
		'&agrave;', '&aring;',  '&atilde;', '&auml;',   '&ccedil;', '&eth;',    '&eacute;',
		'&ecirc;',  '&egrave;', '&euml;',   '&iacute;', '&icirc;',  '&igrave;', '&iuml;',
		'&ntilde;', '&oacute;', '&ocirc;',  '&ograve;', '&oslash;', '&otilde;', '&ouml;',
		'&thorn;',  '&uacute;', '&ucirc;',  '&ugrave;', '&uuml;',   '&yacute;', '&aacute;',
		'&acirc;',  '&aelig;',  '&agrave;', '&aring;',  '&atilde;', '&auml;',   '&ccedil;',
		'&eacute;', '&ecirc;',  '&egrave;', '&eth;',    '&euml;',   '&iacute;', '&icirc;',
		'&igrave;', '&iuml;',   '&ntilde;', '&oacute;', '&ocirc;',  '&ograve;', '&oslash;',
		'&otilde;', '&ouml;',   '&szlig;',  '&thorn;',  '&uacute;', '&ucirc;',  '&ugrave;',
		'&uuml;',   '&yacute;', '&yuml;',   '&iexcl;',  '&pound;',  '&curren;', '&yen;',
		'&brvbar;', '&sect;',   '&uml;',    '&copy;',   '&ordf;',   '&laquo;',  '&not;',
		'&shy;',    '&reg;',    '&macr;',   '&deg;',    '&plusmn;', '&sup2;',   '&sup3;',
		'&acute;',  '&micro;',  '&para;',   '&middot;', '&cedil;',  '&sup1;',   '&ordm;',
		'&raquo;',  '&frac14;', '&frac12;', '&frac34;', '&iquest;', '&times;',  '&divide;',
		'&cent;',   '...',      '&micro;'
		);

		return ((validate::valid_string ($s, false)) ? str_replace ($symbols, $entities, $s) : $s);
	}
	public static function output ($string='') {
		return str_replace(',',', ',str_replace(', ',', ',strip_tags(stripslashes($string))));
	}
	public static function strip_html_tags ($text) {
		$text = preg_replace(
				array(
				// Remove invisible content
				'@<head[^>]*?>.*?</head>@siu',
				'@<style[^>]*?>.*?</style>@siu',
				'@<script[^>]*?.*?</script>@siu',
				'@<object[^>]*?.*?</object>@siu',
				'@<embed[^>]*?.*?</embed>@siu',
				'@<applet[^>]*?.*?</applet>@siu',
				'@<noframes[^>]*?.*?</noframes>@siu',
				'@<noscript[^>]*?.*?</noscript>@siu',
				'@<noembed[^>]*?.*?</noembed>@siu',

				// Add line breaks before and after blocks
				'@</?((address)|(blockquote)|(center)|(del))@iu',
				'@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
				'@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
				'@</?((table)|(th)|(td)|(caption))@iu',
				'@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
				'@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
				'@</?((frameset)|(frame)|(iframe))@iu',
				),
				array(' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0"),
				$text
		);
		return strip_tags( $text );
	}

	/**
	 // Read an HTML file
	 //$raw_text = file_get_contents( $filename );
	 // Get the file's character encoding from a <meta> tag
	 //preg_match( '@<meta\s+http-equiv="Content-Type"\s+content="([\w/]+)(;\s+charset=([^\s"]+))?@i',
	 //$raw_Text, $matches );
	 //$encoding = $matches[3];
	 // Convert to UTF-8 before doing anything else
	 //$utf8_text = iconv( $encoding, "utf-8", $raw_text );
	 // Strip HTML tags and invisible text
	 //$utf8_text = strip_html_tags( $utf8_text );
	 // Decode HTML entities
	 //$utf8_text = html_entity_decode( $utf8_text, ENT_QUOTES, "UTF-8" );
	 */
	/**
	 * Get the ordinal value of a number (1st, 2nd, 3rd, 4/5/6/7/8/9th).
	 *
	 * @return string
	 * @param  int    $n	integer to format
	 * @param  bool	  $f	return full format (true) or ordinal only (false)
	 */
	public static function integer_ordinal($n = 0, $f = false) {
		if(validate::integer($n)) {
			static $ords = array('th', 'st', 'nd', 'rd');
			if(substr($n,-1) > 3 || substr($n,-1)==0) {
				$value = 0;
			}
			return (($f == true) ? $n : '') . $ords[$value];
		} else {
			return '';
		}
	}

	/**
	 * Returns the plural appendage, handy for instances like: 1 file,
	 * 5 files, 1 box, 3 boxes.
	 *
	 * @return string
	 * @param  int    $n
	 * @param  string $s what value to append as suffix to the string
	 */
	public static function integer_plural($n = 0, $s = 's') {
		return ($n == 1 ? '' : $s);
	}

	public static function sentence_case($string) {
		$sentences = preg_split('/([.?!]+)/', $string, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
		$new_string = '';
		foreach ($sentences as $key => $sentence) {
			$new_string .= ($key & 1) == 0?
					ucfirst(strtolower(trim($sentence))) :
					$sentence.' ';
		}
		$new_string = str_replace(' i ',' I ',$new_string);
		$new_string = str_replace(' i\'', ' I\'',$new_string);
		return trim($new_string);
	}

	public static function to_integer($value) {
		return $value;
	}

	public static function to_string($value) {
		return $value;
	}

	public static function to_filename($value) {
		return $value;
	}

	public static function is_url($value) {
		return $value;
	}

	public static function generatePassword($length=9) {
		$vowels = 'aeuyAEUY';
		$consonants = 'bdghjmnpqrstvzBDGHJLMNPQRSTVWXZ23456789@#$%';
		$password = '';
		$alt = time() % 2;
		for ($i = 0; $i < $length; $i++) {
			if ($alt == 1) {
				$password .= $consonants[(rand() % strlen($consonants))];
				$alt = 0;
			} else {
				$password .= $vowels[(rand() % strlen($vowels))];
				$alt = 1;
			}
		}
		return $password;
	}

}
?>