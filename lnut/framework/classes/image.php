<?php

/**
 * Image class.
 * Basic Image Reader
 *
 * @access public
 * @author Andrew Whitfield, <andrew.whitfield@yahoo.co.uk>
 * @company Hyperdesk Limited, <http://www.hyperdesk.biz/>
 */

class image {

	private $source		= '';
	private $content	= '';
	private $suffix		= '';

	public function __construct ($image_file = '') {
		if ('' != $image_file && null != $image_file && 0 < strlen($image_file)) {
			$image_location = core::$root.config::$application.'/images/' . $image_file;
			if (file_exists ($image_location)) {
				$this->source = $image_location;
				$this->content = file_get_contents($image_location);
				$parts = explode('.',$image_file);
				$this->suffix = $parts[sizeof($parts)-1];
			} else {
				if(debug::$logging) {
					debug::message(
						debug::$log_type['informative'],
						null,
						'Image Not Found: '.$image_file
					);
				}
			}
		} else {
			if(debug::$logging) {
				debug::message(
					debug::$log_type['error'],
					null,
					'Cannot Load Blank Image'
				);
			}
		}
	}
	public function get_source () {
		return $this->source;
	}
	public function get_suffix () {
		return $this->suffix;
	}
	public function get_content () {
		return $this->content;
	}
	function imagecreatefromext () {
		switch ($this->suffix) {
			case 'jpg':
			case 'jpeg':	$src = imagecreatefromjpeg($this->source);	break;
			case 'gif':		$src = imagecreatefromgif($this->source);	break;
			case 'png':		$src = imagecreatefrompng($this->source);	break;
		}
		return $src;
	}

	function imageext (&$context, $destination, $quality) {
		switch($this->suffix) {
			case 'jpg':
			case 'jpeg':	imagejpeg($context, $destination, $quality);	break;
			case 'gif':		imagegif($context, $destination);				break;
			case 'png':		imagepng($context, $destination, $quality);		break;
		}
	}

	function scale_by_ratio ($ratio=100, $replace_original=false, $replace_new=false, $save_path='', $save_as='') {
		if(!is_numeric($ratio) && $ratio > 0) {						$this->errors[] = INVALID_SCALE_PERCENTAGE;		}
		if(!file_exists($this->path)) {								$this->errors[] = FILE_NOT_FOUND;		}
		if(!$replace_original) {
			if($save_path=='') {
				$this->errors[] = INVALID_SAVE_PATH;
			} else {
				if(!is_dir($save_path)) {							$this->errors[] = DIRECTORY_NOT_FOUND;		}
				$destination = $save_path.$save_as;
				if(file_exists($destination) && !$replace_new) {	$this->errors[] = CANNOT_OVERWRITE_EXISTING_FILE;	}
			}
		} else {
			$destination = $filename;
		}
		if(sizeof($this->errors) < 1) {
			$ext = substr($this->path, strrpos(',',$this->path));
			$width = 0;
			$height = 0;
			list($width, $height) = @getimagesize($this->path);
			if($width > 0 && $height > 0) {
				$scale_w = $width * $ratio / 100;
				$scale_h = $height * $ratio / 100;

				$context = imagecreatetruecolor($scale_w, $scale_h);
				$source = $this->imagecreatefromext($this->path);
				imagecopyresampled($context, $source, 0, 0, 0, 0, $scale_w, $scale_h, $width, $height);
				$this->imageext ($context, $destination);
				imagedestroy($source);
				imagedestroy($context);
				return array(true,$destination);
			} else {
				$this->errors[] = INVALID_IMAGE_DIMENSIONS;
				return array(false,$this->errors);
			}
		} else {
			return array(false,$this->errors);
		}
	}
	function scale_by_dimension ($newwidth=null, $newheight=null, $replace_original=false, $replace_new=false, $save_path='', $save_as='') {
		if(!file_exists($this->path)) {								$this->errors[] = FILE_NOT_FOUND;		}
		if(!$replace_original) {
			if($save_path=='') {
				$this->errors[] = INVALID_SAVE_PATH;
			} else {
				if(!is_dir($save_path)) {							$this->errors[] = DIRECTORY_NOT_FOUND;		}
				$destination = $save_path.$save_as;
				if(file_exists($destination) && !$replace_new) {	$this->errors[] = CANNOT_OVERWRITE_EXISTING_FILE;	}
			}
		} else {
			$destination = $filename;
		}
		if(sizeof($this->errors) < 1) {
			$ext = substr($this->path, strrpos(',',$this->path));
			$width = 0;
			$height = 0;
			list($width, $height) = @getimagesize($this->path);
			if($width > 0 && $height > 0) {
				$ratio = $width / $height;
				if ($newwidth != null && $width > $newwidth)  {
					$scale_w = $width;
					$scale_h = $height / $ratio ;
				} else if ($height > $newheight) {
					$scale_w = $width / $ratio;
					$scale_h = $newheight;
				}
				$context = imagecreatetruecolor($scale_w, $scale_h);
				$source = $this->imagecreatefromext($this->path);
				imagecopyresampled($context, $source, 0, 0, 0, 0, $scale_w, $scale_h, $width, $height);
				$this->imageext ($context, $destination);
				imagedestroy($source);
				imagedestroy($context);
				return array(true,$destination);
			} else {
				$this->errors[] = INVALID_IMAGE_DIMENSIONS;
				return array(false,$this->errors);
			}
		} else {
			return array(false,$this->errors);
		}
	}


	public function add_watermark ($watermark_file = '', $save_as = '') {
		if ('' != $watermark_file && null != $watermark_file && 0 < strlen($watermark_file) && '' != $save_as && null != $save_as && 0 < strlen($save_as)) {
			$image_location = core::$sys_framework .  core::$dir_framework['images'] . $watermark_file;
			if (file_exists ($image_location)) {
				$watermark = imagecreatefrompng($image_location);
				$watermark_width = imagesx($watermark);
				$watermark_height = imagesy($watermark);
				$image = imagecreatetruecolor($watermark_width, $watermark_height);
				$image = $this->imagecreatefromext();
				$size = getimagesize($this->source);
				$dest_x = ceil($size[0]/2 - $watermark_width/2);
				$dest_y = ceil($size[1]/2 - $watermark_height/2);
			#	imagecopymerge($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, 85);
			#	imagecopymerge($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, 100);
				imagecopy($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height);
				$this->imageext($image, $save_as, ($this->suffix=='png') ? 0 : 100);
				imagedestroy($image);
				imagedestroy($watermark);
			} else {
				if(core::$logging) { core::message( core::$log_type['informative'], null, 'Image Not Found: '.$image_location ); }
			}
		}
	}

	public function save_as_size ($newsize_width = 85, $newsize_height = 84, $suffix = '', $name = '') {
		$width=0;
		$height=0;
		list($width, $height) = getimagesize($this->source);
		if(is_numeric($width) && $width > 0 && is_numeric($height) && $height > 0) {
			if($height > $width) {
				$percent = $newsize_height / $height;
				$newheight = $newsize_height;
				$newwidth = $width * $percent;
			} else {
				$percent = $newsize_width / $width;
				$newwidth = $newsize_width;
				$newheight = $height * $percent;
			}
			$thumb1 = imagecreatetruecolor($newwidth, $newheight);
			$thumb1 = $this->imagecreatefromext();
			imagecopyresampled($thumb1, $source1, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
			if($suffix == '') {
				$suffix = $newsize_width.'.'.$newsize_height;
			}
			$destination = str_replace('.'.$this->suffix,'',$this->source).'-'.$suffix.'.'.$this->suffix;
				echo $destination. '[ before ]<br />';
			if(strlen($name) > 0) {
				// find the last '/' character and trim off the rest other than '-'.suffix.'.'.$this->suffix
				$slash = strrpos($destination, '/');
				if($slash > 0) {
					$hyphen = strrpos($destination, '-');
					if($hyphen > 0) {
						$destination = substr($destination,0,$slash) .'/'. $name . substr($destination,$hyphen);
						echo $destination.'[ after ]<br />';
					}
				}
			}
			$this->imageext($image, $save_as, ($this->suffix=='png') ? 0 : 100);
			imagedestroy($source1);
			imagedestroy($thumb1);
		} else {
			echo $this->source.' failed: '.$width.', '.$height.'<br />';
		}
	}

	public function make_standard ($name='') {
		$this->save_as_size(520,580,'lrg',$name);
	}

	public function make_thumb ($name='') {
		$this->save_as_size(85,84,'thumb',$name);
	}

	public function make_cropped_thumb ($resize_width = 170, $resize_height = 170, $crop_width = 85, $crop_height = 85, $suffix = 'thumb', $name ='') {
		/**
		 * RESCALE THE IMAGE
		 * - GET OLD AND NEW DIMENSIONS - KEEPING ASPECT RATIO
		 */
		$width=0;
		$height=0;
		list($width, $height) = getimagesize($this->source);
		if(is_numeric($width) && $width > 0 && is_numeric($height) && $height > 0) {
			if($height > $width) {
				$percent = $resize_height / $height;
				$newheight = $resize_height;
				$newwidth = $width * $percent;
			} else {
				$percent = $resize_width / $width;
				$newwidth = $resize_width;
				$newheight = $height * $percent;
			}
			/**
			 * GENERATE EMPTY IMAGE IN WHICH TO STORE PROCESSED IMAGE DATA
			 * - AND GENERATE THE DATA FROM THE SOURCE PATH
			 */
			$thumb1 = imagecreatetruecolor($newwidth, $newheight);
			switch($this->suffix) {
				case 'jpg':		$source1 = imagecreatefromjpeg($this->source);	break;
				case 'jpeg':	$source1 = imagecreatefromjpeg($this->source);	break;
				case 'gif':		$source1 = imagecreatefromgif($this->source);	break;
				case 'png':		$source1 = imagecreatefrompng($this->source);	break;
			}
			/**
			 * STORE THE DATA IN THE PREPARED EMPTY IMAGE
			 */
			imagecopyresampled($thumb1, $source1, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
			imagedestroy($source1);
			$destination1 = str_replace('.'.$this->suffix,'',$this->source).'-tmp.'.$this->suffix;
			switch($this->suffix) {
				case 'jpg':		$success = imagejpeg($thumb1, $destination1);	break;
				case 'jpeg':	$success = imagejpeg($thumb1, $destination1);	break;
				case 'gif':		$success = imagegif($thumb1, $destination1);	break;
				case 'png':		$success = imagepng($thumb1, $destination1);	break;
			}
			imagedestroy($thumb1);

			/**
			 * CREATE A CROPPED IMAGE FROM THE RESIZED ONE
			 */
			list($width, $height) = getimagesize($destination1);
			$thumb2 = imagecreatetruecolor($crop_width, $crop_height);
			switch($this->suffix) {
				case 'jpg':		$source2 = imagecreatefromjpeg($destination1);	break;
				case 'jpeg':	$source2 = imagecreatefromjpeg($destination1);	break;
				case 'gif':		$source2 = imagecreatefromgif($destination1);	break;
				case 'png':		$source2 = imagecreatefrompng($destination1);	break;
			}
			/**
			 * STORE THE DATA IN THE PREPARED EMPTY IMAGE
			 */
			$source_x_position = floor(($width-$crop_width)/2);
			$source_y_position = floor(($height-$crop_height)/2);
			imagecopyresampled($thumb2, $source2, 0, 0, $source_x_position, $source_y_position, $crop_width, $crop_height, $crop_width, $crop_height);
			imagedestroy($source2);
			if($suffix == '') {
				$suffix = $crop_width.'.'.$crop_height;
			}
			$destination2 = str_replace('.'.$this->suffix,'',$this->source).'-'.$suffix.'.'.$this->suffix;
			if(strlen($name) > 0) {
				// find the last '/' character and trim off the rest other than '-'.suffix.'.'.$this->suffix
				$slash = strrpos($destination2, '/');
				if($slash > 0) {
					$hyphen = strrpos($destination2, '-');
					if($hyphen > 0) {
						$destination2 = substr($destination2,0,$slash) .'/'. $name . substr($destination2,$hyphen);
					}
				}
			}
			switch($this->suffix) {
				case 'jpg':		$success = imagejpeg($thumb2, $destination2);	break;
				case 'jpeg':	$success = imagejpeg($thumb2, $destination2);	break;
				case 'gif':		$success = imagegif($thumb2, $destination2);	break;
				case 'png':		$success = imagepng($thumb2, $destination2);	break;
			}
			imagedestroy($thumb2);
			unlink($destination1);
		} else {
			echo $this->source.' failed: '.$width.', '.$height.'<br />';
		}
	}
}

?>