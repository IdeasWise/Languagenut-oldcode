<?php

/**
 *  class output.php
 */

class output {

	public static function redirect ($url) {
		if(!headers_sent($filename, $linenum)) {
			header('Location: '.$url);
			exit();
		} else {
			echo "Headers already sent in $filename on line $linenum\n";
		}
	}

	public static function redirectTo ($url) {
		if(!headers_sent($filename, $linenum)) {
			header('Location: '.config::url($url));
			exit();
		} else {
			echo "Headers already sent in $filename on line $linenum\n";
		}
	}

    public static function send ($usecached, $content, $mime, $size, $file_modified) {
        if($usecached) {
            if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                $modified_since = $_SERVER['HTTP_IF_MODIFIED_SINCE']; // e.g. If-Modified-Since: Thu, 13 Nov 2008 00:40:58 GMT
                $parts = explode(' ',$modified_since);
                $time = explode(':',$parts[4]);
                $modified_since = mktime($time[0], $time[1], $time[2], format::to_month($parts[2]), $parts[1], $parts[3]);
            } else {
                $modified_since = false;
            }

            if($modified_since && ($file_modified <= $modified_since)) {
                // if the browser has a cached version of this image, send 304
                header('HTTP/1.1 304 Not Modified');
            } else {
                // set up the last modified time based upon the file time
                header("Pragma: private");
                header("Cache-Control: public, max-age=86400, pre-check=86400");
                header("Expires: ".gmdate('D, d M Y H:i:s', time()+86400)."GMT");
                header("Etag: ".'"'.md5($file_modified).'"');
                header("Last-Modified: ".gmdate('D, d M Y H:i:s', $file_modified) . " GMT");
                header("Content-Type: ".$mime);
                echo $content;
            }
        } else {
            header("Content-Type: ".$mime);
            echo $content;
        }
    }

    public static function as_html ($object = null, $cache = false, $finalise = true) {
        if(is_object($object)) {
            $file_content = $object->get_content($finalise);
        } else {
            $file_content = $object;
        }
        $usecached = false;
        $file_mime = 'text/html; charset=utf-8';
        self::send($usecached, $file_content, $file_mime, 0, 0);
    }

    public static function as_xml ($object = null, $cache = false) {
        if(is_object($object)) {
            $file_content = $object->get_content();
        } else {
            $file_content = $object;
        }
        $usecached = false;
        $file_mime = 'text/xml';
        self::send($usecached, $file_content, $file_mime, 0, 0);
    }

    public static function as_json ($object = null, $cache = false) {
        if(is_object($object)) {
            $file_content = $object->get_content();
        } else {
            $file_content = $object;
        }
        $usecached = false;
        $file_mime = 'application/json';
        self::send($usecached, $file_content, $file_mime, 0, 0);
    }

    public static function as_pdf ($content = '') {
    }

    public static function as_javascript ($object=null) {
        $usecached=false;
        $file_modified = filemtime($object->get_source());
        $file_size = filesize($object->get_source());
        $file_mime = 'text/javascript';
        $file_content = $object->get_content(false);

        self::send($usecached, $file_content, $file_mime, $file_size, $file_modified);
    }

    public static function as_stylesheet ($object=null, $usecached=false) {
        $file_modified = filemtime($object->get_source());
        $file_size = filesize($object->get_source());
        $file_mime = 'text/css';
        $file_content = $object->get_content(false);

        self::send($usecached, $file_content, $file_mime, $file_size, $file_modified);
    }

    public static function as_image ($object=null, $usecached=false) {
        $file_modified = filemtime($object->get_source());
        $file_size = filesize($object->get_source());
        $file_mime = 'image/'.$object->get_suffix();
        $file_content = $object->get_content();

        self::send($usecached, $file_content, $file_mime, $file_size, $file_modified);
    }
}
?>