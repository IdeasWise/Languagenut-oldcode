<?php
header('Content-Type:text/html; charset=UTF-8');
/**
 * XML Reader/Writer
 */
$source = '/var/www/cache/xmltest';

$filelist = '';


if(count($_POST) > 0) {

	if(isset($_POST['filename']) && isset($_POST['newname']) && strlen($_POST['newname']) > 0) {
		$file = $source .'/'. $_POST['filename'];

		$xml = file_get_contents($file);
		$xml = simplexml_load_string($xml);


		if(count($xml->lines) > 0) {
			if(count($xml->lines->line) > 0) {

				foreach($xml->lines as $lines) {

					foreach($lines->line as $line) {

						foreach($line->subtitles as $subtitles) {

							foreach($subtitles->subtitle as $subtitle) {

								$arrAttributes = $subtitle->Attributes();

								$copyto = $_POST['copy'];
								$copyto = explode(',',$copyto);
								$arrCopy = array();
								if(count($copyto) > 1) {
									foreach($copyto as $copy) {
										$arrCopy[] = trim(strtolower($copy));
									}
								} else {
									$arrCopy[] = $_POST['copy'];
								}
								foreach($arrCopy as $copy) {

									if((string)$arrAttributes['lang']==$_POST['find']) {
										$newSubTitle = $subtitles->addChild('subtitle');

										$newSubTitle->addAttribute('lang',$copy);
										$newSubTitle->addAttribute('line',(string)$arrAttributes['line']);
									}
								}
							}
						}
					}
				}
				$xml->asXML($source.'/'.$_POST['newname'].'.xml');

				$str = file_get_contents($source.'/'.$_POST['newname'].'.xml');
				$str = preg_replace('/\/\>\<\/subtitles/',"/>\n\t\t\t</subtitles",$str);
				$str = str_replace("\n\t\t\t<subtitle ","\n\t\t\t\t<subtitle ",$str);

				$fh = fopen($source.'/'.$_POST['newname'].'.xml','w');
				fwrite($fh,$str);
				fclose($fh);
			} else {
				echo 'no line nodes<br />';
			}
		} else {
			echo 'no lines node<br />';
		}
	} else {
		echo 'not set';
	}
}

if(false !==($arrFiles = scandir($source))) {
	foreach($arrFiles as $index=>$file) {
		if($file != '.' && $file != '..' && $file != 'reader.php') {
			$filelist.= '<option value="'.$file.'">'.$file.'</option>';
		}
	}
}

?>
<html>
<head>
	<title>XML Reader/Writer</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
	<form action="http://www.languagenut.com/xmltest/reader.php" method="post">
		<h2>Select File to Update</h2>
		<p><select name="filename"><?php echo $filelist; ?></select></p>
		<h2>New File Name</h2>
		<p><input type="text" name="newname" value="" /> .xml (leave off the .xml part)</p>
		<h2>Locale to Find</h2>
		<p><input type="text" name="find" value="<?php echo isset($_POST['find']) ? $_POST['find'] : 'en'; ?>" /></p>
		<h2>Locale to Create</h2>
		<p><input type="text" name="copy" value="<?php echo isset($_POST['copy']) ? $_POST['copy'] : ''; ?>" /></p>
		<p><input type="submit" name="go" value="go" /></p>
	</form>
</body>
</html>