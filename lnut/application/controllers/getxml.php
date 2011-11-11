<?php
class classXML extends Controller {

	public $newXml		= null;
	public $dirLocale	= null;
	public function __construct () {

		parent::__construct();
		//$this->read_directories();
		$this->frStory();
	}

	private function frStory() {
		$this->dirLocale = 'fr';
		$path = '/swf/story/'.$this->dirLocale;
		$this->UploadDir2($path);
	}
	private function read_directories() {
		$this->dirLocale = 'sp';
		$path = '/swf/karaoke/'.$this->dirLocale;
		$this->UploadDir($path);
	}

	public function UploadDir2( $path ) {
		$root = config::get('root');
		$str = "";
		if (false !== ($handle = opendir($root.$path))) {
			/* This is the correct way to loop over the directory. */
			while (false !== ($file = readdir($handle))) {
				if($file == "." || $file == ".." || $file == "Thumbs.db") {
					continue;
				}
				if ( is_file($root.$path.'/'.$file) && strstr($file,'xml') && !strstr($file,'backup') && !strstr($file,'-new') && !strstr($file,'01.xml'))
				{

					
					echo '<br>';
					echo '<b>dir :</b>'.$root.$path.'/'.$file;
					echo '<br>';
					echo '<b>url :</b>'.config::base($path.'/'.$file);
					
					//echo $root.$path.'/'.$file;
					//echo '<br>'.strstr($file,'.xml',true).'-new.xml';
					//$this->writeFile(config::base($path.'/'.$file) , $root.$path.'/'.str_replace('.xml','-new.xml',$file));
				}
			}
		}

		if (false !== ($handle = opendir($root.$path))) {
			while (false !== ($file = readdir($handle))) {
				if($file == "." || $file == ".." || $file == "Thumbs.db") {
					continue;
				}
				if ( is_dir($root.$path.'/'.$file) ) {
					$NewSOURCE = $path.'/'.$file;
					$this->UploadDir( $NewSOURCE );
				}
			}

		}
	}
	
	public function UploadDir( $path ) {
		$root = config::get('root');
		$str = "";
		if (false !== ($handle = opendir($root.$path))) {
			/* This is the correct way to loop over the directory. */
			while (false !== ($file = readdir($handle))) {
				if($file == "." || $file == ".." || $file == "Thumbs.db") {
					continue;
				}
				if ( is_file($root.$path.'/'.$file) && strstr($file,'xml') && !strstr($file,'backup') && !strstr($file,'-new') && !strstr($file,'01.xml'))
				{

					
					echo '<br>';
					echo '<b>dir :</b>'.$root.$path.'/'.$file;
					echo '<br>';
					echo '<b>url :</b>'.config::base($path.'/'.$file);
					
					//echo $root.$path.'/'.$file;
					//echo '<br>'.strstr($file,'.xml',true).'-new.xml';
					$this->writeFile(config::base($path.'/'.$file) , $root.$path.'/'.str_replace('.xml','-new.xml',$file));
				}
			}
		}

		if (false !== ($handle = opendir($root.$path))) {
			while (false !== ($file = readdir($handle))) {
				if($file == "." || $file == ".." || $file == "Thumbs.db") {
					continue;
				}
				if ( is_dir($root.$path.'/'.$file) ) {
					$NewSOURCE = $path.'/'.$file;
					$this->UploadDir( $NewSOURCE );
				}
			}

		}
	}
	
	private function read($xml_file=null) {
		$xml = simplexml_load_string(file_get_contents($xml_file));
/*		echo '<pre>';
		print_r($xml);
		echo '</pre>';
*/
		$this->newXml = '<?xml version="1.0" encoding="utf-8" ?>';
		$this->newXml.= '<karaoke length="'.$xml->Attributes()->length.'">';
		if($xml->lines) {
			$this->newXml.= '<lines>';
				foreach($xml->lines->line as $line) {
					$this->setLineTag($line);
				}
			$this->newXml.= '</lines>';
		}
		$this->newXml.= '</karaoke>';
		header ("Content-Type:text/xml");
		echo $this->newXml;
	}


	private function writeFile($xml_file=null,$newFile=null) {
		$xml = simplexml_load_string(file_get_contents($xml_file));
/*		echo '<pre>';
		print_r($xml);
		echo '</pre>';
*/
		$this->newXml = '<?xml version="1.0" encoding="utf-8" ?>';
		$this->newXml.= '<karaoke length="'.$xml->Attributes()->length.'">';
		if($xml->lines) {
			$this->newXml.= '<lines>';
				foreach($xml->lines->line as $line) {
					$this->setLineTag($line);
				}
			$this->newXml.= '</lines>';
		}
		$this->newXml.= '</karaoke>';
		//header ("Content-Type:text/xml");

		$handle = fopen($newFile, "w");
		if (fwrite($handle, $this->newXml) === false) {
			echo "Cannot write to text file. <br />";
		}
		fclose($handle);
		//exit;
		//echo $this->newXml;
	}


	private function setLineTag($objLine=null) {
		if($objLine != null) {
			
			$this->writeTagWithAttribute('line',$objLine);
			// check if there is highlight tags if yes then set them to xml
			if($objLine->highlight) {
				$this->setHighlight($objLine->highlight);
			}
			// check if there is subtitles if yes then set them to xml
			if($objLine->subtitles) {
				$this->newXml.= '<subtitles>';
				$this->setSubTitles($objLine->subtitles);
				if($this->dirLocale == 'en') {
					$this->addENCopies($objLine->Attributes()->words);
				} else if($this->dirLocale == 'fr') {
					$this->addFRCopies($objLine->Attributes()->words);
				} if($this->dirLocale == 'mx') {
					$this->addMXCopies($objLine->Attributes()->words);
				} 
				$this->newXml.= '</subtitles>';
			}
			$this->newXml.= '</line>';
		}
	}
	
	private function setSubTitles($objSubTitles=null) {
		if($objSubTitles!=null) {
			foreach($objSubTitles->subtitle as $subtitle ) {
				$this->writeTagWithAttribute('subtitle ',$subtitle ,true);
				if($subtitle->Attributes()->lang == 'mx') {
					$this->addMXCopies($subtitle->Attributes()->line);
				} else if($subtitle->Attributes()->lang == 'fr') {
					$this->addFRCopies($subtitle->Attributes()->line);
				} else if($subtitle->Attributes()->lang == 'en') {
					$this->addENCopies($subtitle->Attributes()->line);
				}
			}
		}
	}
	
	private function setHighlight($objLineHighlight=null) {
		if($objLineHighlight != null) {
			foreach($objLineHighlight as $highlight) {
				$this->writeTagWithAttribute('highlight',$highlight,true);
			}
		}
	}
	
	private function writeTagWithAttribute($tagName=null,$tagObject=null,$autoComplete=false) {
		if($tagName!=null && $tagObject!=null) {
			$this->newXml.= '<'.$tagName;
			foreach($tagObject->Attributes() as $index => $value) {
				$this->newXml.=' '.$index.'="'.$value.'"';
			}
			$this->newXml.= '>';
			if($autoComplete) {
				$this->newXml.= '</'.$tagName.'>';
			}
		}
	}
	
	private function addMXCopies($line=null) {
		$arrMx = array(
			'cl',
			'es',
			'co',
			'gt',
			'pe',
			'ni',
			'do',
			'bo',
			've',
			'ar',
			'cu',
			'pr',
			'py',
			'ec',
			'hn',
			'sv',
			'cr',
			'pa'
		);
		if($line!=null) {
				foreach($arrMx as $lang) {
				$this->newXml.= '<subtitle lang="'.$lang.'" line="'.$line.'">';
				$this->newXml.= '</subtitle>';
			}
		}
	}
	
	private function addFRCopies($line=null) {
		$arrFR = array(
			'cd',
			'fr',
			'mg',
			'cm',
			'ci',
			'bf',
			'ne',
			'sn',
			'ml',
			'bi',
			'bj',
			'tg',
			'ga',
			'dj'
		);
		if($line != null) {
				foreach($arrFR as $lang) {
				$this->newXml.= '<subtitle lang="'.$lang.'" line="'.$line.'">';
				$this->newXml.= '</subtitle>';
			}
		}
	}
	
	private function addENCopies($line=null) {
		$arrEN = array(
			'us',
			'nz',
			'au',
			'bz',
			'jm',
			'tt',
			'gy',
			'ag',
			'dm',
			'vc',
			'bs',
			'ca',
			'bd',
			'bw',
			'fj',
			'gm',
			'gh',
			'gy',
			'ke',
			'mt',
			'mu',
			'na',
			'ng',
			'pk',
			'rw',
			'ws',
			'sl',
			'sg',
			'sb',
			'za',
			'tz',
			'to',
			'ug',
			'vu',
			'zm',
			'zw'
		);
		if($line != null) {
				foreach($arrEN as $lang) {
				$this->newXml.= '<subtitle lang="'.$lang.'" line="'.$line.'">';
				$this->newXml.= '</subtitle>';
			}
		}
	}
}
?>