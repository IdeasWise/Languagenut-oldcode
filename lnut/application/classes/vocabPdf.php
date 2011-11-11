<?php

class vocabPdf extends FPDF {

	public function __construct($orientation='P', $unit='mm', $format='A4') {
		parent::FPDF($orientation, $unit, $format);
	}

	function printVocab($data = array()) {
		setlocale(LC_ALL, 'en_US.UTF8');
		$this->AddPage();
		$this->Image('header.png', 15, 14, 180);
		$this->SetFont('Arial', 'B', 12);
		$this->Ln(16);
		$this->Cell(160, 6, iconv("UTF-8", "ISO-8859-1", $data['unit']), 0, 1);
		$this->Cell(160, 6, iconv("UTF-8", "ISO-8859-1", $data['section']), 0, 1);
		$this->Ln(5);
		//'vocab'=>$vocab
		// add headers
		$this->Cell(95, 4, iconv("UTF-8", "ISO-8859-1", $data['language']), 0, 0, 'C');
		$this->Cell(95, 4, iconv("UTF-8", "ISO-8859-1", $data['support']), 0, 1, 'C');
		$this->Ln(1);
		// build table
		foreach ($data['vocab'] as $key => $val) {
			$this->SetFont('Arial', '', '12');
			#$this->Cell(95, 8.5, iconv("UTF-8", "ISO-8859-1", $val[$data['language']]), 1, 0, 'C');
			//$this->Cell(95, 8.5, $val[$data['language']], 1, 0, 'C');
			$this->Cell(95, 8.5, iconv("UTF-8", "ISO-8859-1//TRANSLIT", $val[$data['language']]), 1, 0, 'C');
			$this->Cell(95, 8.5, iconv("UTF-8", "cp1252", $val['support']), 1, 1, 'C');
		}
		$this->Image('cut.png', 15, 147, 180);
		$this->Image('header.png', 15, 159, 180);
		$this->SetFont('Arial', 'B', 12);
		$this->Ln(36.9);
		$this->Cell(160, 6, iconv("UTF-8", "ISO-8859-1", $data['unit']), 0, 1);
		$this->Cell(160, 6, iconv("UTF-8", "ISO-8859-1", $data['section']), 0, 1);
		$this->Ln(5);
		//'vocab'=>$vocab
		// add headers
		$this->Cell(95, 4, iconv("UTF-8", "ISO-8859-1", $data['language']), 0, 0, 'C');
		$this->Cell(95, 4, iconv("UTF-8", "ISO-8859-1", $data['support']), 0, 1, 'C');
		$this->Ln(1);
		// build table
		foreach ($data['vocab'] as $key => $val) {
			$this->SetFont('Arial', '', '12');
			$this->Cell(95, 8.5, '', 1, 0, 'C');
			$this->Cell(95, 8.5, '', 1, 1, 'C');
		}
	}

	function printFlashCards($data = array()) {
		setlocale(LC_ALL, 'en_US.UTF8');
		$state = 0;
		foreach ($data['vocab'] as $key => $val) {
			$this->SetFont('Arial', 'B', 12);
			if ($state == 0) {
				$this->AddPage();
				$this->SetAutoPageBreak(false, 1);
				$this->Image('cards_header.jpg', 13, 13, 111);
				$this->Image('cards_box2.jpg', 14, 40, 111);
				$this->Image('cards_header.jpg', 172, 13, 111);
				$this->Image('cards_foldsymbol.jpg', 147, 63, 5);
				$this->Image('cards_box2.jpg', 173, 40, 111);
				$this->Image('cards_cutline.jpg', 13, 103, 270);
				$this->Image('cards_header.jpg', 13, 115, 111);
				$this->Image('cards_box2.jpg', 14, 143, 111);
				$this->Image('cards_header.jpg', 172, 115, 111);
				$this->Image('cards_foldsymbol.jpg', 147, 165, 5);
				$this->Image('cards_box2.jpg', 173, 143, 111);
				$state++;
			}
			if ($state == 1) {
				$u = iconv("UTF-8", "ISO-8859-1", $data['unit']);
				$s = iconv("UTF-8", "ISO-8859-1", $data['section']);
				$l = iconv("UTF-8", "ISO-8859-1", $data['language']);
				$e = iconv("UTF-8", "ISO-8859-1", $data['support']);
				#$w = iconv("UTF-8", "ISO-8859-1", $val[$data['language']]);
				$w = iconv("UTF-8", "cp1252", $val[$data['language']]);
				#$h = iconv("UTF-8", "ISO-8859-1", $val['support']);
				$h = iconv("UTF-8", "cp1252", $val['support']);
				$c = 'Copyright ' . iconv("UTF-8", "ISO-8859-1", "©") . ' Languagenut 2010 - Photocopying permitted';
				// add spacer down from the top
				$this->SetY(20);
				$this->Cell(5, 6, '', 0, 0);
				$this->Cell(105, 6, $u, 0, 0);
				$this->Cell(54, 6, '', 0, 0);
				$this->Cell(105, 6, $u, 0, 1);
				$this->Cell(5, 6, '', 0, 0);
				$this->Cell(105, 6, $s, 0, 0);
				$this->Cell(54, 6, '', 0, 0);
				$this->Cell(105, 6, $s, 0, 1);
				// add spacer down from this row
				$this->SetFont('Arial', 'B', 14);
				$this->Cell(5, 8, '', 0, 0);
				$this->Cell(105, 8, $l, 0, 0, 'C');
				$this->Cell(53, 8, '', 0, 0);
				$this->Cell(105, 8, $e, 0, 1, 'C');
				// add in the translation data
				$this->SetY(57);
				$this->SetFont('Arial', 'B', '18');
				$this->Cell(5, 12, '', 0, 0);
				$this->Cell(105, 12, $w, 0, 0, 'C');
				$this->Cell(53, 12, '', 0, 0);
				$this->Cell(105, 12, $h, 0, 1, 'C');
				// add in copyright notice
				$this->SetY(90);
				$this->SetFont('Arial', '', '8');
				$this->Cell(5, 6, '', 0, 0);
				$this->Cell(105, 6, $c, 0, 0, 'C');
				$this->Cell(53, 6, '', 0, 0);
				$this->Cell(105, 6, $c, 0, 0, 'C');
				$state++;
			} else {
				$u = iconv("UTF-8", "ISO-8859-1", $data['unit']);
				$s = iconv("UTF-8", "ISO-8859-1", $data['section']);
				$l = iconv("UTF-8", "ISO-8859-1", $data['language']);
				$e = iconv("UTF-8", "ISO-8859-1", $data['support']);
				#$w = iconv("UTF-8", "ISO-8859-1", $val[$data['language']]);
				$w = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $val[$data['language']]);
				#$h = iconv("UTF-8", "ISO-8859-1", $val['support']);
				$h = iconv("UTF-8", "cp1252", $val['support']);
				$c = 'Copyright ' . iconv("UTF-8", "ISO-8859-1", "©") . ' Languagenut 2010 - Photocopying permitted';
				// add spacer down from the top
				$this->SetY(122);
				$this->Cell(5, 6, '', 0, 0);
				$this->Cell(105, 6, $u, 0, 0);
				$this->Cell(54, 6, '', 0, 0);
				$this->Cell(105, 6, $u, 0, 1);
				$this->Cell(5, 6, '', 0, 0);
				$this->Cell(105, 6, $s, 0, 0);
				$this->Cell(54, 6, '', 0, 0);
				$this->Cell(105, 6, $s, 0, 1);
				// add spacer down from this row
				$this->SetFont('Arial', 'B', 14);
				$this->Cell(5, 8, '', 0, 0);
				$this->Cell(105, 8, $l, 0, 0, 'C');
				$this->Cell(53, 8, '', 0, 0);
				$this->Cell(105, 8, $e, 0, 1, 'C');
				// add in the translation data
				$this->SetY(162);
				$this->SetFont('Arial', 'B', '18');
				$this->Cell(5, 12, '', 0, 0);
				$this->Cell(105, 12, $w, 0, 0, 'C');
				$this->Cell(53, 12, '', 0, 0);
				$this->Cell(105, 12, $h, 0, 1, 'C');
				// add in copyright notice
				$this->SetY(193); //3
				$this->SetFont('Arial', '', '8');
				$this->Cell(5, 6, '', 0, 0);
				$this->Cell(105, 6, $c, 0, 0, 'C');
				$this->Cell(53, 6, '', 0, 0);
				$this->Cell(105, 6, $c, 0, 0, 'C');
				$state = 0;
			}
		}
	}

}

define('FPDF_FONTPATH', 'font/');
?>