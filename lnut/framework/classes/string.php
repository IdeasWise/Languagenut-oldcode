<?php

/**
 *	class.string.php
 */

class string {
	/**
	* Regex for matching a consonant
	* @var string
	*/
	private static $regex_consonant_uk = '(?:[bcdfghjklmnpqrstvwyxz]|(?<=[aeiou]))';
	private static $regex_consonant_us = '(?:[bcdfghjklmnpqrstvwxz]|(?<=[aeiou])y|^y)';

	/**
	* Regex for matching a vowel
	* @var string
	*/
	private static $regex_vowel_uk = '(?:[aeiou])';
	private static $regex_vowel_us = '(?:[aeiou]|(?<![aeiou])y)';

	public static function trim_safe ($data=null) {
		return mysql_real_escape_string (trim($data));
	}
	public static function trim_safe_tags ($data=null) {
		return mysql_real_escape_string (trim(addslashes(strip_tags($data))));
	}
	public static function get_stop_words () {
		return array ("a", "able", "about", "above", "abroad", "according", "accordingly", "across", "actually", "adj", "after", "afterwards", "again", "against", "ago", "ahead", "ain't", "all", "allow", "allows", "almost", "alone", "along", "alongside", "already", "also", "although", "always", "am", "amid", "amidst", "among", "amongst", "an", "and", "another", "any", "anybody", "anyhow", "anyone", "anything", "anyway", "anyways", "anywhere", "apart", "appear", "appreciate", "appropriate", "are", "aren't", "around", "as", "a's", "aside", "ask", "asking", "associated", "at", "available", "away", "awfully", "b", "back", "backward", "backwards", "be", "became", "because", "become", "becomes", "becoming", "been", "before", "beforehand", "begin", "behind", "being", "believe", "below", "beside", "besides", "best", "better", "between", "beyond", "both", "brief", "but", "by", "c", "came", "can", "cannot", "cant", "can't", "caption", "cause", "causes", "certain", "certainly", "changes", "clearly", "c'mon", "co", "co.", "com", "come", "comes", "concerning", "consequently", "consider", "considering", "contain", "containing", "contains", "corresponding", "could", "couldn't", "course", "c's", "currently", "d", "dare", "daren't", "definitely", "described", "despite", "did", "didn't", "different", "directly", "do", "does", "doesn't", "doing", "done", "don't", "down", "downwards", "during", "e", "each", "edu", "eg", "eight", "eighty", "either", "else", "elsewhere", "end", "ending", "enough", "entirely", "especially", "et", "etc", "even", "ever", "evermore", "every", "everybody", "everyone", "everything", "everywhere", "ex", "exactly", "example", "except", "f", "fairly", "far", "farther", "few", "fewer", "fifth", "first", "five", "followed", "following", "follows", "for", "forever", "former", "formerly", "forth", "forward", "found", "four", "from", "further", "furthermore", "g", "get", "gets", "getting", "given", "gives", "go", "goes", "going", "gone", "got", "gotten", "greetings", "h", "had", "hadn't", "half", "happens", "hardly", "has", "hasn't", "have", "haven't", "having", "he", "he'd", "he'll", "hello", "help", "hence", "her", "here", "hereafter", "hereby", "herein", "here's", "hereupon", "hers", "herself", "he's", "hi", "him", "himself", "his", "hither", "hopefully", "how", "howbeit", "however", "hundred", "i", "i'd", "ie", "if", "ignored", "i'll", "i'm", "immediate", "in", "inasmuch", "inc", "inc.", "indeed", "indicate", "indicated", "indicates", "inner", "inside", "insofar", "instead", "into", "inward", "is", "isn't", "it", "it'd", "it'll", "its", "it's", "itself", "i've", "j", "just", "k", "keep", "keeps", "kept", "know", "known", "knows", "l", "last", "lately", "later", "latter", "latterly", "least", "less", "lest", "let", "let's", "like", "liked", "likely", "likewise", "little", "look", "looking", "looks", "low", "lower", "ltd", "m", "made", "mainly", "make", "makes", "many", "may", "maybe", "mayn't", "me", "mean", "meantime", "meanwhile", "merely", "might", "mightn't", "mine", "minus", "miss", "more", "moreover", "most", "mostly", "mr", "mrs", "much", "must", "mustn't", "my", "myself", "n", "name", "namely", "nd", "near", "nearly", "necessary", "need", "needn't", "needs", "neither", "never", "neverf", "neverless", "nevertheless", "new", "next", "nine", "ninety", "no", "nobody", "non", "none", "nonetheless", "noone", "no-one", "nor", "normally", "not", "nothing", "notwithstanding", "novel", "now", "nowhere", "o", "obviously", "of", "off", "often", "oh", "ok", "okay", "old", "on", "once", "one", "ones", "one's", "only", "onto", "opposite", "or", "other", "others", "otherwise", "ought", "oughtn't", "our", "ours", "ourselves", "out", "outside", "over", "overall", "own", "p", "particular", "particularly", "past", "per", "perhaps", "placed", "please", "plus", "possible", "presumably", "probably", "provided", "provides", "q", "que", "quite", "qv", "r", "rather", "rd", "re", "really", "reasonably", "recent", "recently", "regarding", "regardless", "regards", "relatively", "respectively", "right", "round", "s", "said", "same", "saw", "say", "saying", "says", "second", "secondly", "see", "seeing", "seem", "seemed", "seeming", "seems", "seen", "self", "selves", "sensible", "sent", "serious", "seriously", "seven", "several", "shall", "shan't", "she", "she'd", "she'll", "she's", "should", "shouldn't", "since", "six", "so", "some", "somebody", "someday", "somehow", "someone", "something", "sometime", "sometimes", "somewhat", "somewhere", "soon", "sorry", "specified", "specify", "specifying", "still", "sub", "such", "sup", "sure", "t", "take", "taken", "taking", "tell", "tends", "th", "than", "thank", "thanks", "thanx", "that", "that'll", "thats", "that's", "that've", "the", "their", "theirs", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "there'd", "therefore", "therein", "there'll", "there're", "theres", "there's", "thereupon", "there've", "these", "they", "they'd", "they'll", "they're", "they've", "thing", "things", "think", "third", "thirty", "this", "thorough", "thoroughly", "those", "though", "three", "through", "throughout", "thru", "thus", "till", "to", "together", "too", "took", "toward", "towards", "tried", "tries", "truly", "try", "trying", "t's", "twice", "two", "u", "un", "under", "underneath", "undoing", "unfortunately", "unless", "unlike", "unlikely", "until", "unto", "up", "upon", "upwards", "us", "use", "used", "useful", "uses", "using", "usually", "v", "value", "various", "versus", "very", "via", "viz", "vs", "w", "want", "wants", "was", "wasn't", "way", "we", "we'd", "welcome", "well", "we'll", "went", "were", "we're", "weren't", "we've", "what", "whatever", "what'll", "what's", "what've", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "where's", "whereupon", "wherever", "whether", "which", "whichever", "while", "whilst", "whither", "who", "who'd", "whoever", "whole", "who'll", "whom", "whomever", "who's", "whose", "why", "will", "willing", "wish", "with", "within", "without", "wonder", "won't", "would", "wouldn't", "x", "y", "yes", "yet", "you", "you'd", "you'll", "your", "you're", "yours", "yourself", "yourselves", "you've", "z", "zero");
	}
	public static function summarise ($data=null, $limit=0) {
		if(null != $data) {
			$tok = strtok($data, " ");
			$text="";
			$words='0';
			while($tok) {
				$text .= " ".$tok;
				$words++;
				if(($words >= $limit) && ((substr($tok, -1) == "!")||(substr($tok, -1) == "."))) {
					break;
				}
				$tok = strtok(" ");
			}
			return ltrim($text);
		} else {
			return null;
		}
	}
	public static function to_word_phrase_array ($string=null) {
		if(null != $string) {
			$state		= 'space';
			$previous	= '';		// stores current state when encountering a backslash (which changes $state to 'escaped', but has to fall back into the previous $state afterwards)
			$out		= array();	// the return value
			$word		= '';
			$type		= '';		// type of character
			$length		= strlen($string);

			// array[states][chartypes] => actions
			$chart = array(
				'space'        => array('space'=>'',   'quote'=>'q',  'doublequote'=>'d',  'backtick'=>'b',  'backslash'=>'ue', 'other'=>'ua'),
				'unquoted'     => array('space'=>'w ', 'quote'=>'a',  'doublequote'=>'a',  'backtick'=>'a',  'backslash'=>'e',  'other'=>'a'),
				'quoted'       => array('space'=>'a',  'quote'=>'w ', 'doublequote'=>'a',  'backtick'=>'a',  'backslash'=>'e',  'other'=>'a'),
				'doublequoted' => array('space'=>'a',  'quote'=>'a',  'doublequote'=>'w ', 'backtick'=>'a',  'backslash'=>'e',  'other'=>'a'),
				'backticked'   => array('space'=>'a',  'quote'=>'a',  'doublequote'=>'a',  'backtick'=>'w ', 'backslash'=>'e',  'other'=>'a'),
				'escaped'      => array('space'=>'ap', 'quote'=>'ap', 'doublequote'=>'ap', 'backtick'=>'ap', 'backslash'=>'ap', 'other'=>'ap'));

			for ($i=0; $i<=$length; $i++) {
				$char = substr($string, $i, 1);
				$type = array_search($char, array('space'=>' ', 'quote'=>'\'', 'doublequote'=>'"', 'backtick'=>'`', 'backslash'=>'\\'));
				if (! $type) {
					$type = 'other';
				}
				if ($type == 'other') {
					// grabs all characters that are also 'other' following the current one in one go
					preg_match("/[ \'\"\`\\\]/", $string, $matches, PREG_OFFSET_CAPTURE, $i);
					if ($matches) {
						$matches = $matches[0];
						$char = substr($string, $i, $matches[1]-$i); // yep, $char length can be > 1
						$i = $matches[1] - 1;
					} else {
						// no more match on special characters, that must mean this is the last word!
						// the .= hereunder is because we *might* be in the middle of a word that just contained special chars
						$word .= substr($string, $i);
						break; // jumps out of the for() loop
					}
				}
				$actions = $chart[$state][$type];
				for($j=0; $j<strlen($actions); $j++) {
					$act = substr($actions, $j, 1);
					if ($act == ' ') {$state = 'space';}
					if ($act == 'u') {$state = 'unquoted';}
					if ($act == 'q') {$state = 'quoted';}
					if ($act == 'd') {$state = 'doublequoted';}
					if ($act == 'b') {$state = 'backticked';}
					if ($act == 'e') { $previous = $state; $state = 'escaped'; }
					if ($act == 'a') {$word .= $char;}
					if ($act == 'w') { $out[] = $word; $word = ''; }
					if ($act == 'p') {$state = $previous;}
				}
			}
			if (strlen($word)) { $out[] = $word; }
			return $out;
		} else {
			return null;
		}
	}
	function html_entity_decode_full($quotes = ENT_COMPAT, $charset = 'ISO-8859-1') {
		return html_entity_decode(preg_replace_callback('/&([a-zA-Z][a-zA-Z0-9]+);/', array( &$this, 'convert_entity'), $this->text), $quotes, $charset);
	}
	public function convert_entity($matches, $destroy = true) {
		$table = array(
			'quot' => '&#34;',
			'amp' => '&#38;',
			'lt' => '&#60;',
			'gt' => '&#62;',
			'OElig' => '&#338;',
			'oelig' => '&#339;',
			'Scaron' => '&#352;',
			'scaron' => '&#353;',
			'Yuml' => '&#376;',
			'circ' => '&#710;',
			'tilde' => '&#732;',
			'ensp' => '&#8194;',
			'emsp' => '&#8195;',
			'thinsp' => '&#8201;',
			'zwnj' => '&#8204;',
			'zwj' => '&#8205;',
			'lrm' => '&#8206;',
			'rlm' => '&#8207;',
			'ndash' => '&#8211;',
			'mdash' => '&#8212;',
			'lsquo' => '&#8216;',
			'rsquo' => '&#8217;',
			'sbquo' => '&#8218;',
			'ldquo' => '&#8220;',
			'rdquo' => '&#8221;',
			'bdquo' => '&#8222;',
			'dagger' => '&#8224;',
			'Dagger' => '&#8225;',
			'permil' => '&#8240;',
			'lsaquo' => '&#8249;',
			'rsaquo' => '&#8250;',
			'euro' => '&#8364;',
			'fnof' => '&#402;',
			'Alpha' => '&#913;',
			'Beta' => '&#914;',
			'Gamma' => '&#915;',
			'Delta' => '&#916;',
			'Epsilon' => '&#917;',
			'Zeta' => '&#918;',
			'Eta' => '&#919;',
			'Theta' => '&#920;',
			'Iota' => '&#921;',
			'Kappa' => '&#922;',
			'Lambda' => '&#923;',
			'Mu' => '&#924;',
			'Nu' => '&#925;',
			'Xi' => '&#926;',
			'Omicron' => '&#927;',
			'Pi' => '&#928;',
			'Rho' => '&#929;',
			'Sigma' => '&#931;',
			'Tau' => '&#932;',
			'Upsilon' => '&#933;',
			'Phi' => '&#934;',
			'Chi' => '&#935;',
			'Psi' => '&#936;',
			'Omega' => '&#937;',
			'alpha' => '&#945;',
			'beta' => '&#946;',
			'gamma' => '&#947;',
			'delta' => '&#948;',
			'epsilon' => '&#949;',
			'zeta' => '&#950;',
			'eta' => '&#951;',
			'theta' => '&#952;',
			'iota' => '&#953;',
			'kappa' => '&#954;',
			'lambda' => '&#955;',
			'mu' => '&#956;',
			'nu' => '&#957;',
			'xi' => '&#958;',
			'omicron' => '&#959;',
			'pi' => '&#960;',
			'rho' => '&#961;',
			'sigmaf' => '&#962;',
			'sigma' => '&#963;',
			'tau' => '&#964;',
			'upsilon' => '&#965;',
			'phi' => '&#966;',
			'chi' => '&#967;',
			'psi' => '&#968;',
			'omega' => '&#969;',
			'thetasym' => '&#977;',
			'upsih' => '&#978;',
			'piv' => '&#982;',
			'bull' => '&#8226;',
			'hellip' => '&#8230;',
			'prime' => '&#8242;',
			'Prime' => '&#8243;',
			'oline' => '&#8254;',
			'frasl' => '&#8260;',
			'weierp' => '&#8472;',
			'image' => '&#8465;',
			'real' => '&#8476;',
			'trade' => '&#8482;',
			'alefsym' => '&#8501;',
			'larr' => '&#8592;',
			'uarr' => '&#8593;',
			'rarr' => '&#8594;',
			'darr' => '&#8595;',
			'harr' => '&#8596;',
			'crarr' => '&#8629;',
			'lArr' => '&#8656;',
			'uArr' => '&#8657;',
			'rArr' => '&#8658;',
			'dArr' => '&#8659;',
			'hArr' => '&#8660;',
			'forall' => '&#8704;',
			'part' => '&#8706;',
			'exist' => '&#8707;',
			'empty' => '&#8709;',
			'nabla' => '&#8711;',
			'isin' => '&#8712;',
			'notin' => '&#8713;',
			'ni' => '&#8715;',
			'prod' => '&#8719;',
			'sum' => '&#8721;',
			'minus' => '&#8722;',
			'lowast' => '&#8727;',
			'radic' => '&#8730;',
			'prop' => '&#8733;',
			'infin' => '&#8734;',
			'ang' => '&#8736;',
			'and' => '&#8743;',
			'or' => '&#8744;',
			'cap' => '&#8745;',
			'cup' => '&#8746;',
			'int' => '&#8747;',
			'there4' => '&#8756;',
			'sim' => '&#8764;',
			'cong' => '&#8773;',
			'asymp' => '&#8776;',
			'ne' => '&#8800;',
			'equiv' => '&#8801;',
			'le' => '&#8804;',
			'ge' => '&#8805;',
			'sub' => '&#8834;',
			'sup' => '&#8835;',
			'nsub' => '&#8836;',
			'sube' => '&#8838;',
			'supe' => '&#8839;',
			'oplus' => '&#8853;',
			'otimes' => '&#8855;',
			'perp' => '&#8869;',
			'sdot' => '&#8901;',
			'lceil' => '&#8968;',
			'rceil' => '&#8969;',
			'lfloor' => '&#8970;',
			'rfloor' => '&#8971;',
			'lang' => '&#9001;',
			'rang' => '&#9002;',
			'loz' => '&#9674;',
			'spades' => '&#9824;',
			'clubs' => '&#9827;',
			'hearts' => '&#9829;',
			'diams' => '&#9830;',
			'nbsp' => '&#160;',
			'iexcl' => '&#161;',
			'cent' => '&#162;',
			'pound' => '&#163;',
			'curren' => '&#164;',
			'yen' => '&#165;',
			'brvbar' => '&#166;',
			'sect' => '&#167;',
			'uml' => '&#168;',
			'copy' => '&#169;',
			'ordf' => '&#170;',
			'laquo' => '&#171;',
			'not' => '&#172;',
			'shy' => '&#173;',
			'reg' => '&#174;',
			'macr' => '&#175;',
			'deg' => '&#176;',
			'plusmn' => '&#177;',
			'sup2' => '&#178;',
			'sup3' => '&#179;',
			'acute' => '&#180;',
			'micro' => '&#181;',
			'para' => '&#182;',
			'middot' => '&#183;',
			'cedil' => '&#184;',
			'sup1' => '&#185;',
			'ordm' => '&#186;',
			'raquo' => '&#187;',
			'frac14' => '&#188;',
			'frac12' => '&#189;',
			'frac34' => '&#190;',
			'iquest' => '&#191;',
			'Agrave' => '&#192;',
			'Aacute' => '&#193;',
			'Acirc' => '&#194;',
			'Atilde' => '&#195;',
			'Auml' => '&#196;',
			'Aring' => '&#197;',
			'AElig' => '&#198;',
			'Ccedil' => '&#199;',
			'Egrave' => '&#200;',
			'Eacute' => '&#201;',
			'Ecirc' => '&#202;',
			'Euml' => '&#203;',
			'Igrave' => '&#204;',
			'Iacute' => '&#205;',
			'Icirc' => '&#206;',
			'Iuml' => '&#207;',
			'ETH' => '&#208;',
			'Ntilde' => '&#209;',
			'Ograve' => '&#210;',
			'Oacute' => '&#211;',
			'Ocirc' => '&#212;',
			'Otilde' => '&#213;',
			'Ouml' => '&#214;',
			'times' => '&#215;',
			'Oslash' => '&#216;',
			'Ugrave' => '&#217;',
			'Uacute' => '&#218;',
			'Ucirc' => '&#219;',
			'Uuml' => '&#220;',
			'Yacute' => '&#221;',
			'THORN' => '&#222;',
			'szlig' => '&#223;',
			'agrave' => '&#224;',
			'aacute' => '&#225;',
			'acirc' => '&#226;',
			'atilde' => '&#227;',
			'auml' => '&#228;',
			'aring' => '&#229;',
			'aelig' => '&#230;',
			'ccedil' => '&#231;',
			'egrave' => '&#232;',
			'eacute' => '&#233;',
			'ecirc' => '&#234;',
			'euml' => '&#235;',
			'igrave' => '&#236;',
			'iacute' => '&#237;',
			'icirc' => '&#238;',
			'iuml' => '&#239;',
			'eth' => '&#240;',
			'ntilde' => '&#241;',
			'ograve' => '&#242;',
			'oacute' => '&#243;',
			'ocirc' => '&#244;',
			'otilde' => '&#245;',
			'ouml' => '&#246;',
			'divide' => '&#247;',
			'oslash' => '&#248;',
			'ugrave' => '&#249;',
			'uacute' => '&#250;',
			'ucirc' => '&#251;',
			'uuml' => '&#252;',
			'yacute' => '&#253;',
			'thorn' => '&#254;',
			'yuml' => '&#255;'
		);
		return (isset($table[$matches[1]])) ? $table[$matches[1]] : (($destroy) ? '' : $matches[0]);
	}
	public static function to_upper_entities($str=null){
		if(null != $string) {
			// convert to entities
			$subject = htmlentities($str,ENT_QUOTES);
			$pattern = '/&([a-z])(uml|acute|circ|tilde|ring|elig|grave|slash|horn|cedil|th);/e';
			$replace = "'&'.strtoupper('\\1').'\\2'.';'";
			$result = preg_replace($pattern, $replace, $subject);
			// convert from entities back to characters
			$htmltable = get_html_translation_table(HTML_ENTITIES);
			foreach($htmltable as $key => $value) {
				$result = ereg_replace(addslashes($value),$key,$result);
			}
			return (strtoupper($result));
		} else {
			return null;
		}
	}
	public static function to_upper ($string=null){
		if(null != $string) {
			// also accounting for any entity
			$new_string = "";
			while (eregi("^([^&]*)(&)(.)([a-z0-9]{2,9};|&)(.*)", $string, $regs)) {
				$entity = $regs[2].strtoupper($regs[3]).$regs[4];
				if (html_entity_decode($entity) == $entity) {
					$new_string .= strtoupper($regs[1]).$regs[2].$regs[3].$regs[4];
				} else {
					$new_string .= strtoupper($regs[1]).$entity;
				}
				$string = $regs[5];
			}
			$new_string .= strtoupper($string);
			return $new_string;
		} else {
			return null;
		}
	}
	/**
	* Stems a word.
	*
	* @param  string $word Word to stem
	* @return string       Stemmed word
	*/
	public static function get_stem($word='') {
		if (strlen($word) <= 2) {
			return $word;
		}

		$word = self::step1ab($word);
		$word = self::step1c($word);
		$word = self::step2($word);
		$word = self::step3($word);
		$word = self::step4($word);
		$word = self::step5($word);

		return $word;
	}
	/**
	* Step 1
	*/
	private static function step1ab($word='') {
		// Part a
		if (substr($word, -1) == 's') {
			   self::replace($word, 'sses', 'ss')
			OR self::replace($word, 'ies', 'i')
			OR self::replace($word, 'ss', 'ss')
			OR self::replace($word, 's', '');
		}

		// Part b
		if (substr($word, -2, 1) != 'e' OR !self::replace($word, 'eed', 'ee', 0)) { // First rule
			$v = self::$regex_vowel;

			// ing and ed
			if (   preg_match("#$v+#", substr($word, 0, -3)) && self::replace($word, 'ing', '')
				OR preg_match("#$v+#", substr($word, 0, -2)) && self::replace($word, 'ed', '')) { // Note use of && and OR, for precedence reasons

				// If one of above two test successful
				if (    !self::replace($word, 'at', 'ate')
					AND !self::replace($word, 'bl', 'ble')
					AND !self::replace($word, 'iz', 'ize')) {

					// Double consonant ending
					if (    self::doubleConsonant($word)
						AND substr($word, -2) != 'll'
						AND substr($word, -2) != 'ss'
						AND substr($word, -2) != 'zz') {

						$word = substr($word, 0, -1);

					} else if (self::m($word) == 1 AND self::cvc($word)) {
						$word .= 'e';
					}
				}
			}
		}

		return $word;
	}

	/**
	* Step 1c
	*
	* @param string $word Word to stem
	*/
	private static function step1c($word) {
		$v = self::$regex_vowel;

		if (substr($word, -1) == 'y' && preg_match("#$v+#", substr($word, 0, -1))) {
			self::replace($word, 'y', 'i');
		}

		return $word;
	}

	/**
	* Step 2
	*
	* @param string $word Word to stem
	*/
	private static function step2($word) {
		switch (substr($word, -2, 1)) {
			case 'a':
				   self::replace($word, 'ational', 'ate', 0)
				OR self::replace($word, 'tional', 'tion', 0);
			break;
			case 'c':
				   self::replace($word, 'enci', 'ence', 0)
				OR self::replace($word, 'anci', 'ance', 0);
			break;
			case 'e':
				self::replace($word, 'izer', 'ize', 0);
			break;
			case 'g':
				self::replace($word, 'logi', 'log', 0);
			break;
			case 'l':
				   self::replace($word, 'entli', 'ent', 0)
				OR self::replace($word, 'ousli', 'ous', 0)
				OR self::replace($word, 'alli', 'al', 0)
				OR self::replace($word, 'bli', 'ble', 0)
				OR self::replace($word, 'eli', 'e', 0);
			break;
			case 'o':
				   self::replace($word, 'ization', 'ize', 0)
				OR self::replace($word, 'ation', 'ate', 0)
				OR self::replace($word, 'ator', 'ate', 0);
			break;
			case 's':
				   self::replace($word, 'iveness', 'ive', 0)
				OR self::replace($word, 'fulness', 'ful', 0)
				OR self::replace($word, 'ousness', 'ous', 0)
				OR self::replace($word, 'alism', 'al', 0);
			break;
			case 't':
				   self::replace($word, 'biliti', 'ble', 0)
				OR self::replace($word, 'aliti', 'al', 0)
				OR self::replace($word, 'iviti', 'ive', 0);
			break;
		}
		return $word;
	}

	/**
	* Step 3
	*
	* @param string $word String to stem
	*/
	private static function step3($word) {
		switch (substr($word, -2, 1)) {
			case 'a':
				self::replace($word, 'ical', 'ic', 0);
			break;
			case 's':
				self::replace($word, 'ness', '', 0);
			break;
			case 't':
				   self::replace($word, 'icate', 'ic', 0)
				OR self::replace($word, 'iciti', 'ic', 0);
			break;
			case 'u':
				self::replace($word, 'ful', '', 0);
			break;
			case 'v':
				self::replace($word, 'ative', '', 0);
			break;
			case 'z':
				self::replace($word, 'alize', 'al', 0);
			break;
		}
		return $word;
	}

	/**
	* Step 4
	*
	* @param string $word Word to stem
	*/
	private static function step4($word) {
		switch (substr($word, -2, 1)) {
			case 'a':
				self::replace($word, 'al', '', 1);
			break;
			case 'c':
				   self::replace($word, 'ance', '', 1)
				OR self::replace($word, 'ence', '', 1);
			break;
			case 'e':
				self::replace($word, 'er', '', 1);
			break;
			case 'i':
				self::replace($word, 'ic', '', 1);
			break;
			case 'l':
				   self::replace($word, 'able', '', 1)
				OR self::replace($word, 'ible', '', 1);
			break;
			case 'n':
				   self::replace($word, 'ant', '', 1)
				OR self::replace($word, 'ement', '', 1)
				OR self::replace($word, 'ment', '', 1)
				OR self::replace($word, 'ent', '', 1);
			break;
			case 'o':
				if (substr($word, -4) == 'tion' OR substr($word, -4) == 'sion') {
				   self::replace($word, 'ion', '', 1);
				} else {
					self::replace($word, 'ou', '', 1);
				}
			break;
			case 's':
				self::replace($word, 'ism', '', 1);
			break;
			case 't':
				   self::replace($word, 'ate', '', 1)
				OR self::replace($word, 'iti', '', 1);
			break;
			case 'u':
				self::replace($word, 'ous', '', 1);
			break;
			case 'v':
				self::replace($word, 'ive', '', 1);
			break;
			case 'z':
				self::replace($word, 'ize', '', 1);
			break;
		}
		return $word;
	}

	/**
	* Step 5
	*
	* @param string $word Word to stem
	*/
	private static function step5($word) {
		// Part a
		if (substr($word, -1) == 'e') {
			if (self::m(substr($word, 0, -1)) > 1) {
				self::replace($word, 'e', '');

			} else if (self::m(substr($word, 0, -1)) == 1) {

				if (!self::cvc(substr($word, 0, -1))) {
					self::replace($word, 'e', '');
				}
			}
		}

		// Part b
		if (self::m($word) > 1 AND self::doubleConsonant($word) AND substr($word, -1) == 'l') {
			$word = substr($word, 0, -1);
		}

		return $word;
	}

	/**
	* Replaces the first string with the second, at the end of the string. If third
	* arg is given, then the preceding string must match that m count at least.
	*
	* @param  string $str   String to check
	* @param  string $check Ending to check for
	* @param  string $repl  Replacement string
	* @param  int    $m     Optional minimum number of m() to meet
	* @return bool          Whether the $check string was at the end
	*                       of the $str string. True does not necessarily mean
	*                       that it was replaced.
	*/
	private static function replace(&$str, $check, $repl, $m = null) {
		$len = 0 - strlen($check);

		if (substr($str, $len) == $check) {
			$substr = substr($str, 0, $len);
			if (is_null($m) OR self::m($substr) > $m) {
				$str = $substr . $repl;
			}

			return true;
		}

		return false;
	}

	/**
	* What, you mean it's not obvious from the name?
	*
	* m() measures the number of consonant sequences in $str. if c is
	* a consonant sequence and v a vowel sequence, and <..> indicates arbitrary
	* presence,
	*
	* <c><v>       gives 0
	* <c>vc<v>     gives 1
	* <c>vcvc<v>   gives 2
	* <c>vcvcvc<v> gives 3
	*
	* @param  string $str The string to return the m count for
	* @return int         The m count
	*/
	private static function m($str) {
		$c = self::$regex_consonant;
		$v = self::$regex_vowel;

		$str = preg_replace("#^$c+#", '', $str);
		$str = preg_replace("#$v+$#", '', $str);

		preg_match_all("#($v+$c+)#", $str, $matches);

		return count($matches[1]);
	}

	/**
	* Returns true/false as to whether the given string contains two
	* of the same consonant next to each other at the end of the string.
	*
	* @param  string $str String to check
	* @return bool        Result
	*/
	private static function doubleConsonant($str) {
		$c = self::$regex_consonant;

		return preg_match("#$c{2}$#", $str, $matches) AND $matches[0]{0} == $matches[0]{1};
	}

	/**
	* Checks for ending CVC sequence where second C is not W, X or Y
	*
	* @param  string $str String to check
	* @return bool        Result
	*/
	private static function cvc($str) {
		$c = self::$regex_consonant;
		$v = self::$regex_vowel;

		return     preg_match("#($c$v$c)$#", $str, $matches)
			   AND strlen($matches[1]) == 3
			   AND $matches[1]{2} != 'w'
			   AND $matches[1]{2} != 'x'
			   AND $matches[1]{2} != 'y';
	}
}

?>