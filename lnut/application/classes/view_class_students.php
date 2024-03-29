<?php
class view_class_students extends FPDF {
	private $topoffset				= 126.5;
	private $max_offset				= 151.2;
	private $lineheight				= 3.5;
	private $fontsize				= 6;
	private $fontfamily				= 'Courier';
	private $width_left_margin		= 1.8;
	private $width_token			= 18.5;
	private $width_description		= 33.6;
	private $width_quantity			= 9.4;
	private $width_unit_price		= 10.6;
	private $width_discount_price	= 18.5;
	private $width_total_price		= 14;
	private $subtotal				= 0;
	private $pagedata				= array ();
	private $currentpage			= 0;
	private $headerToken			= '';

	function addMoney ($money){
		//return iconv("UTF-8", "ISO-8859-1", ""). $money;
		//return "". $money;
		return $money;
	}

	//Basic Format
	function generate($data) {
		$this->AddPage();
		if($_SERVER['HTTP_HOST'] == '127.0.0.1') {
			$this->Image($_SERVER['DOCUMENT_ROOT'].'languagenut/logo-small.png',20,12,60.6);
		} else {
			$this->Image('/home/language/admin_html/logo-small.png',20,12,60.6);
		}

		$this->SetFont('Arial','B',12);
		$this->SetY(50);
		$this->Cell(180,$this->lineheight,'STUDENTS',0,1,'C');

		// spacers
		$this->Cell(180,$this->lineheight,'',0,1,'L');

		// LANGNUT Address
		$this->SetFont('Arial','',10);
		$this->Cell(170,$this->lineheight,'Languagenut Ltd',0,1,'R');
		$this->Cell(170,$this->lineheight,'19 The Twitten',0,1,'R');
		$this->Cell(170,$this->lineheight,'Ditchling',0,1,'R');
		$this->Cell(170,$this->lineheight,'Hassocks',0,1,'R');
		$this->Cell(170,$this->lineheight,'BN6 8UJ',0,1,'R');
		$this->Cell(170,$this->lineheight,'Tel : 07944 151674',0,1,'R');
		$this->Cell(170,$this->lineheight,'Email: subs@languagenut.com',0,1,'R');

		// spacers
		$this->Cell(180,$this->lineheight,'',0,1,'L');

		// add address


		$this->SetFont('Arial','',10);
		if(isset($data['address']) && count($data['address']) > 0) {
			$this->Cell(10,$this->lineheight,'',0,0,'L');
			$this->SetFont('Arial','B',10);
			$this->Cell(13,$this->lineheight,'School: ',0,0,'L');
			$this->SetFont('Arial','',10);
			$this->Cell(170,$this->lineheight,$data['to'],0,1,'L');

			foreach($data['address'] as $address_line) {
				$this->Cell(23,$this->lineheight,'',0,0,'L');
				$this->Cell(170,$this->lineheight,$address_line,0,1,'L');
			}
			$this->Cell(23,$this->lineheight,'',0,0,'L');
			$this->Cell(170,$this->lineheight,$data['school_postcode'],0,1,'L');

			// leaves a linke
			$this->Cell(180,$this->lineheight,'',0,1,'L');
			$this->Cell(10,$this->lineheight,'',0,0,'L');
			$this->SetFont('Arial','B',10);
			$this->Cell(13,$this->lineheight,'Class: ',0,0,'L');
			$this->SetFont('Arial','',10);
			$this->Cell(170,$this->lineheight,$data['class'],0,1,'L');

		}
		/*
		// spacers
		$this->Cell(180,$this->lineheight,'',0,1,'L');
		$this->Cell(180,$this->lineheight,'',0,1,'L');
		$this->Cell(180,$this->lineheight,'',0,1,'L');
		$this->Cell(180,$this->lineheight,'',0,1,'L');

		// user details
		$this->Cell(10,$this->lineheight,'',0,0,'L');
		$this->Cell(170,$this->lineheight,'Invoice No: '.$data['invoice_number'],0,1,'L');
		$this->Cell(10,$this->lineheight,'',0,0,'L');
		$this->Cell(170,$this->lineheight,'Date: '.$data['date'],0,1,'L');
		$this->Cell(10,$this->lineheight,'',0,0,'L');
		$this->Cell(170,$this->lineheight,'Due Date: '.$data['due_date'],0,1,'L');
		$this->Cell(10,$this->lineheight,'',0,0,'L');
		$this->Cell(170,$this->lineheight,'Reference: '.$data['reference'],0,1,'L');
		$this->Cell(10,$this->lineheight,'',0,0,'L');
		$this->Cell(170,$this->lineheight,'To: '.$data['to'],0,1,'L');
		*/
		// spacers
		$this->Cell(180,$this->lineheight,'',0,1,'L');
		$this->Cell(180,$this->lineheight,'',0,1,'L');
		$this->Cell(180,$this->lineheight,'',0,1,'L');
		$this->Cell(180,$this->lineheight,'',0,1,'L');


		// list headers
		$this->Cell(10,$this->lineheight,'',0,0,'L');
		$this->Cell(12,$this->lineheight,'#',0,0,'L');
		$this->Cell(55,$this->lineheight,'Name',0,0,'L');
		$this->Cell(65,$this->lineheight,'Username',0,0,'L');
		$this->Cell(100,$this->lineheight,'Password',0,0,'L');

		// spacer
		$this->Cell(180,$this->lineheight,'',0,1,'L');
		$this->Cell(10,0.5,'',0,0,'L');
		$this->Cell(170,0.5,'','B',1,'L');
		$this->Cell(10,0.5,'',0,0,'L');
		$this->Cell(170,0.5,'','B',1,'L');
		$this->Cell(180,$this->lineheight,'',0,1,'L');

		if(is_array($data['students'])) {

			// list student's name with their username and password
			for($i = 0; $i < count($data['students']); $i++ ) {
				// list rows
				$this->Cell(10,$this->lineheight,'',0,0,'L');
				$this->Cell(12,$this->lineheight,($i+1),0,0,'L');
				$this->Cell(55,$this->lineheight,$data['students'][$i]['Name'],0,0,'L');
				$this->Cell(65,$this->lineheight,$data['students'][$i]['email'],0,0,'L');
				$this->Cell(100,$this->lineheight,$data['students'][$i]['wordbank_word'],0,0,'L');

				$this->Cell(180,10,'',0,1,'L');
				/*
				$this->Cell(10,0.5,'',0,0,'L');
				$this->Cell(170,0.5,'','B',1,'L');
				$this->Cell(180,$this->lineheight,'',0,1,'L');
				*/

			}

		}

		// spacers
		$this->Cell(180,$this->lineheight,'',0,1,'L');
		$this->Cell(180,$this->lineheight,'',0,1,'L');
		$this->Cell(180,$this->lineheight,'',0,1,'L');
		$this->Cell(180,$this->lineheight,'',0,1,'L');

		/*

		// spacers
		$this->Cell(180,$this->lineheight,'',0,1,'L');
		$this->Cell(180,$this->lineheight,'',0,1,'L');
		$this->Cell(180,$this->lineheight,'',0,1,'L');
		$this->Cell(180,$this->lineheight,'',0,1,'L');
		$this->Cell(180,$this->lineheight,'',0,1,'L');
		$this->Cell(180,$this->lineheight,'',0,1,'L');
		$this->Cell(180,$this->lineheight,'',0,1,'L');

		// ending text
		$this->SetFont('Arial','I',10);
		$this->Cell(10,$this->lineheight,'',0,0,'L');
		$this->Cell(170,$this->lineheight,'Payable within 14 days  cheques made payable to Language Nut.',0,1,'L');

		$this->SetFont('Arial','',10);
		$this->Cell(10,$this->lineheight,'',0,0,'L');
		$this->Cell(170,$this->lineheight,'BACS: account number 33096882',0,1,'L');
		$this->Cell(10,$this->lineheight,'',0,0,'L');
		$this->Cell(170,$this->lineheight,'Sort Code: 20-49-76',0,1,'L');
		$this->Cell(10,$this->lineheight,'',0,0,'L');
		$this->Cell(170,$this->lineheight,'Please quote invoice number '.$data['invoice_number'],0,1,'L');
		$this->Cell(10,$this->lineheight,'',0,0,'L');
		$this->Cell(170,$this->lineheight,'VAT registration number: 983 9715 59',0,1,'L');

		// spacers
		$this->Cell(10,$this->lineheight,'',0,0,'L');
		$this->Cell(170,$this->lineheight,'',0,1,'L');

		// thank you
		$this->SetFont('Arial','B',10);
		$this->Cell(10,$this->lineheight,'',0,0,'L');
		$this->Cell(170,$this->lineheight,'Thank you for your payment.',0,1,'L');
*/
		$this->Output();
	}

	function generate_password_pdf($data, $msgs) {
		parent::FPDF('p', 'mm', 'passwordpdf');
		$this->AddPage();
		
		$LH = 7; // LINE HEIGHT
		$B  = 0; // BORDER 1 MEANS YES AND 0 MEANS NO
		$Y  = 10; // used to set Y position to name,username and passwords
		$YI  = 8; // used to set Y position to Image
		$YPLUS = 40;

		$X = array(202,10,105); // userd to set X position for name,usrname and passwords
		$XL = array(215,25,120); // userd to set X position for links
		$XI = array(270,78,173); // userd to set X position for image


		$xindex = 0;
		
		for($i = 0; $i < count($data); $i++) {

			$xindex = ($i+1)%3;

			$this->SetY($Y);
			$this->SetFont('Arial','',12);

			$this->SetX($X[$xindex]);		
			$this->Cell(13.5,$LH, $msgs['tag.name'] ,$B,0,'L');
			$this->Cell(50,$LH, $data[$i]['Name'],$B,0,'L');

			// To leave a line
			$this->Cell(180,$LH,'',0,1,'L');

			$this->SetX($X[$xindex]);
			$this->Cell(21.5,$LH, $msgs['tag.username'] ,$B,0,'L');
			$this->Cell(40,$LH,  $data[$i]['email'] ,$B,0,'L');

			$this->Cell(180,$LH,'',0,1,'L');

			$this->SetX($X[$xindex]);
			$this->Cell(21.5,$LH, $msgs['tag.password'] ,$B,0,'L');
			$this->Cell(40,$LH,  $data[$i]['wordbank_word'] ,$B,0,'L');

			// To leave a line
			$this->Cell(180,$LH,'',0,1,'L');

			$this->SetFont('Arial','',14);
			$this->SetX($XL[$xindex]);
			//$this->Cell(62,$LH,'www.languagenut.com' ,$B,0,'L','','http://www.languagenut.com/'.$data[$i]['locale']);
			$this->Cell(62,$LH,'languagenut' ,$B,0,'L','','');
			
			$this->Image(config::images_common('nut_logo.jpg'), $XI[$xindex] ,$YI,25,25);

			if( ($i+1)%3 == 0 ) {
				$Y += $YPLUS;
				$YI += $YPLUS;
			}

			if( ($i+1)%27 == 0 && ($i+1) < count($data)) {
				$this->AddPage();
				// Re-Initialize				
				$Y  = 10; // used to set Y position to name,username and passwords
				$YI  = 8; // used to set Y position to Image
			}
		}


		$this->Output();
	}

}

?>