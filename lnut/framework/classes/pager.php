<?php
/**
 * firepages.com.au - basic pagination class - see
 * http://www.firepages.com.au/pagination.htm for more information
 */

/* example usage
 ***************
//find out the size of our recordset , only fetch a count, in this query nothing else
$max = mysql_result( mysql_query("SELECT COUNT(id) FROM table WHERE id > 0"), 0 ) ;
$pager = new pager(
    $max ,                     //see above
    10 ,                         //how many records to display at one time
    @$_GET['_p']  //this is the current page no carried via _GET
    ) ;

//optionally set a maximum number of pages to display
//so you dont end up with 100's of links

$pager->set_range(10);

//the main query note the same WHERE conditions
$q = mysql_query("SELECT id,name FROM table WHERE id > 0 LIMIT ".$pager->get_limit());

//show something for your troubles
echo $pager->get_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}'). ' <br />';
echo $pager->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').'<br /><br />';

//+ of course the actual data
while($r = mysql_fetch_assoc($q)){
    echo "{$r['id']},{$r['name']}<br />";
}
*/

class pager {
	var $p_range	= 0;	# range to show if you dont want to show ALL pages returned
	var $curr		= 1;	# current page number
	var $_pages		= '';	# no of pages in a recordset
	var $_ctl		= '_p';	# default control variable name
	var $_req_url	='';	# url to build links with
	var $_req_qs	='';	# query string to build links with

	# allowed replacements for titles and links
	var $_t_tpls	= array('{CURRENT}','{FROM}','{TO}','{MAX}','{TOTAL}');
	var $_l_tpls	= array('{LINK_HREF}','{LINK_LINK}');

	#when set_range() is in use
	var $set_from	= ''; #min page number of returned set
	var $set_to		= '';     #max number of returned set

	function pager($max, $pp, $curr, $extra='') {
		if(!is_array($extra)) {
			$extra=array();
		}
		$this->_pp		= $pp;
		$this->curr		= (int)$curr > 0 ? $curr  : 1 ;
		$this->set_from	= 1;//may be overriden by a set_range() paged set
		$this->_pages	= $this->set_to = $this->p_range = ceil( $max/$pp );
		$this->_ctl.= empty($extra['suffix']) ? '' : $extra['suffix'] ;
		$this->_req_qs	= isset($extra['query_string']) ? $extra['query_string'] : $_SERVER['QUERY_STRING'];
		$this->_req_url = isset($extra['php_self']) ? $extra['php_self'] : $_SERVER['PHP_SELF'];

		#check for and remove control variables from query string#
		if(strpos($this->_req_qs,$this->_ctl)!==false) {
			parse_str($this->_req_qs,$arr);
			$tmp=array();
			unset($arr[$this->_ctl]);
			foreach($arr as $k=>$v) {
				$tmp[]="$k=$v";
			}
			$this->_req_qs = implode('&', $tmp);
			unset($tmp);
		}
		#vars for eye_candy not declared ~#
		$this->_from	= (($this->curr * $this->_pp) - $this->_pp) + 1;
		$to				= ($this->_from +  $this->_pp) -1 ;
		$this->_to		= ($to > $max ) ? $max : $to ;
		$this->_total	= $max ;
	}

	function set_range($p_range) {
		$this->p_range = $p_range;
	}
	function srt_req_url($url) {
		$this->req_url = $url;
	}

	function get_limit() {
		return ($this->curr * $this->_pp) - $this->_pp. ' , '.$this->_pp;
	}

	function get_limit_offset() {
		return ($this->curr * $this->_pp) - $this->_pp;
	}

	function get_page_title($format) {
		return str_replace($this->_t_tpls,
			array($this->curr, $this->_from, $this->_to, $this->_pages, $this->_total),
			$format
		);
	}

    function _get_qurl() {
        $q = empty($this->_req_qs) ? '' : '?'.$this->_req_qs ;
        $s = (substr($q, 0, 1) == '?') ? '&amp;' : '?' ;
		if(!empty($q)) {
			if(strpos($q,"/p-") !== false) {
				$q = substr($q,0,strpos($q,"/p-"));
			}
			/*
			if(@strstr($q, '/p-', true)) {
				$q = @strstr($q, '/p-', true);
			}*/
			if($this->_req_url == '&p=') {
				if(strpos($q,"&p=") !== false) {
					$q = substr($q,0,strpos($q,"&p="));
				}
				return $q.''.$this->_req_url;
			} else {
				return $q.'/'.$this->_req_url;
			}
		}
        return $this->_req_url;// . $q . $s . $this->_ctl . '=';
    }

    function get_prev($format) {
        return $this->curr > 1 ?
                str_replace($this->_l_tpls,array($this->_get_qurl().($this->curr -1),"<<"),$format) : '' ;
    }

    function get_next($format) {
        return ($this->curr < $this->_pages) ?
                str_replace($this->_l_tpls,array($this->_get_qurl().($this->curr +1),">>"),$format) : '' ;
    }

    function get_range($format, $sep,$first='',$last='') {
        if($this->_pages < 2) {
            return ;
        }
        $pre_url = $this->_get_qurl();
        $lfirst = $llast = '' ;
        $min  = 1 ;
        $to = $this->_pages ;

        if($this->_pages > $this->p_range) {
            $mid = ceil(($this->p_range / 2));
            if(($this->curr - $mid) >= 1) {
                $min = $this->curr - $mid;
            }
            $to = $min + ($this->p_range-1);
            if($this->_pages > $to) {
                $llast = (!empty($last)) ?
                        $sep.str_replace($this->_l_tpls,array($pre_url.$this->_pages,$last),$format) : '' ;
            }
            if($min > 1) {
                $lfirst = (!empty($first) && $this->curr >1 ) ?
                        str_replace($this->_l_tpls,array($pre_url.'1',$first),$format) .$sep : '' ;
            }
            if($to > $this->_pages) {
                $to = $this->_pages ;
            }
            //NEW readjust to show at least p_range links is set_range in use//
            if(($to - $min) <  $this->p_range) {
                $min= ($to-$this->p_range)+1;
            }
            //NEW external parsers may need the correct from and to values from a set_range() limited set
            $this->set_from=$min;
            $this->set_to=$to;
        }
        for($x=$min; $x<=$to; ++$x) {
            $rets[]=($this->curr!=$x)?str_replace($this->_l_tpls, array($pre_url.$x, $x) , $format):$x;
        }
        return $lfirst.implode($sep, $rets).$llast;
    }
}
?>