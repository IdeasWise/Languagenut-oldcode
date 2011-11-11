<?
class image_resize {

public function resize_image($photo_dest, $file, $file_maxwidth = "100", $file_maxheight = "100") { 
	  
	   // GET FILE VARS
	  $file_name = $_FILES[$file]['name'];
	  $file_type = strtolower($_FILES[$file]['type']);
	  $file_size = $_FILES[$file]['size'];
	  $file_tempname = $_FILES[$file]['tmp_name'];
	  $file_error = $_FILES[$file]['error'];
	  $file_ext = strtolower(str_replace(".", "", strrchr($file_name, "."))); 

	  $file_dimensions = @getimagesize($file_tempname);
	  $file_width = $file_dimensions[0];
	  $file_height = $file_dimensions[1];
	  if($file_maxwidth == "") { $file_maxwidth = $file_width; }
	  if($file_maxheight == "") { $file_maxheight = $file_height; }
	  $file_maxwidth = $file_maxwidth;
	  $file_maxheight = $file_maxheight;
		// BY ME
		$width = $file_maxwidth;
    		$height =$file_maxheight;	
		// BY ME
	  // RESIZE IMAGE AND PUT IN USER DIRECTORY
	  switch($file_ext) {
	    case "gif":
	      $file = imagecreatetruecolor($width, $height);
	      $new = imagecreatefromgif($file_tempname);
	      $kek=imagecolorallocate($file, 255, 255, 255);
	      imagefill($file,0,0,$kek);
	      imagecopyresampled($file, $new, 0, 0, 0, 0, $width, $height, $file_width, $file_height);
	      imagejpeg($file, $photo_dest, 100);
	      ImageDestroy($new);
	      ImageDestroy($file);
	      break;
	    case "bmp":
	      $file = imagecreatetruecolor($width, $height);
	      $new = $imagecreatefrombmp($file_tempname);
	      for($i=0; $i<256; $i++) { imagecolorallocate($file, $i, $i, $i); }
	      imagecopyresampled($file, $new, 0, 0, 0, 0, $width, $height, $file_width, $file_height); 
	      imagejpeg($file, $photo_dest, 100);
	      ImageDestroy($new);
	      ImageDestroy($file);
	      break;
	    case "jpeg":
	    case "jpg":
	      $file = imagecreatetruecolor($width, $height);
	      $new = imagecreatefromjpeg($file_tempname);
	      for($i=0; $i<256; $i++) { imagecolorallocate($file, $i, $i, $i); }
	      imagecopyresampled($file, $new, 0, 0, 0, 0, $width, $height, $file_width, $file_height);
	      imagejpeg($file, $photo_dest, 100);
	      ImageDestroy($new);
	      ImageDestroy($file);
	      break;
	    case "png":
	      $file = imagecreatetruecolor($width, $height);
	      $new = imagecreatefrompng($file_tempname);
	      for($i=0; $i<256; $i++) { imagecolorallocate($file, $i, $i, $i); }
	      imagecopyresampled($file, $new, 0, 0, 0, 0, $width, $height, $file_width, $file_height); 
	      imagejpeg($file, $photo_dest, 100);
	      ImageDestroy($new);
	      ImageDestroy($file);
	      break;
	  } 

	  chmod($photo_dest, 0777);

	  return true;

	} // END upload_photo() METHOD
}	
?>