<?php

class test_pdf extends FPDF {

	private $topoffset = 126.5;
	private $max_offset = 151.2;
	private $lineheight = 4;
	private $fontsize = 6;
	private $fontfamily = 'Courier';
	private $width_left_margin = 1.8;
	private $width_token = 18.5;
	private $width_description = 33.6;
	private $width_quantity = 9.4;
	private $width_unit_price = 10.6;
	private $width_discount_price = 18.5;
	private $width_total_price = 14;
	private $subtotal = 0;
	private $pagedata = array();
	private $currentpage = 0;
	private $headerToken = '';

	public function __construct($orientation='P', $unit='mm', $format='certificate') {
		parent::FPDF($orientation, $unit, $format);
	}

	//Basic Format
	function generate() {
		$this->AddPage();


		// http://www.languagenut.com/fpdf16/doc/image.htm
		if (isset($_GET['demo'])) {
			$this->Image(config::get('site') . '/certificateLayout.png', 0, 0);
			$this->Cell(1, 55, '', 0, 1, 'L');
		} else {


			$this->Image(config::get('site') . '/certificate-noalpha.png', 15, 15);
			$this->Image(config::get('site') . '/url.png', 326, 50.5);

			$this->SetY(115);
			$this->SetFont('Arial', '', 194);
			$this->Cell(842, 22, 'GOLD MEDAL', 0, 1, 'C');

			// To Leave a space between line
			$this->Cell(1, 55, '', 0, 1, 'L');

			$this->SetFont('Arial', '', 99);
			$this->Cell(65, $this->lineheight, '', 0, 0, 'L');
			$this->Cell(245, $this->lineheight, 'Unit 5', 0, 0, 'L');
			$this->Cell(345, $this->lineheight, 'Section 5', 0, 1, 'L');

			// To Leave a space between line
			$this->Cell(1, 30, '', 0, 1, 'L');

			$this->SetFont('Arial', '', 50);
			$this->Cell(65, $this->lineheight, '', 0, 0, 'L');
			$this->Cell(245, $this->lineheight, 'Unit name', 0, 0, 'L');
			$this->Cell(290, $this->lineheight, 'Section name', 0, 0, 'L');
			$this->Cell(180, $this->lineheight, 'Game name', 0, 1, 'L');


			// To Leave a space between line
			$this->Cell(1, 44, '', 0, 1, 'L');

			$this->SetFont('Arial', '', 69);
			$this->Cell(800, $this->lineheight, 'Congratulations, you have achieved a gold medal!', 0, 1, 'C');


			// To Leave a space between line
			$this->Cell(1, 45, '', 0, 1, 'L');

			$this->SetFont('Arial', '', 58);
			$this->Cell(58, $this->lineheight, '', 0, 0, 'L');
			$this->Cell(390, $this->lineheight, 'Awarded to:', 0, 0, 'L');
			$this->Cell(320, $this->lineheight, 'Class:', 0, 1, 'L');

			// spacer
			$this->Cell(1, 39, '', 0, 1, 'L');
			$this->Cell(58, 0.5, '', 0, 0, 'L');
			$this->Cell(340, 0.5, '', 'B', 0, 'L');
			$this->Cell(10, 0.5, '', 0, 0, 'L');

			$this->Cell(58, 0.5, '', 0, 0, 'L');
			$this->Cell(280, 0.5, '', 'B', 1, 'L');


			// To Leave a space between line
			$this->Cell(1, 30, '', 0, 1, 'L');

			$this->SetFont('Arial', '', 58);
			$this->Cell(113, $this->lineheight, '', 0, 0, 'L');
			$this->Cell(390, $this->lineheight, 'You learnt these words perfectly:', 0, 0, 'L');

			// spacer
			$this->Cell(1, 36, '', 0, 1, 'L');
			$this->Cell(113, 0.5, '', 0, 0, 'L');
			$this->Cell(633, 0.5, '', 'B', 1, 'L');




			// To Leave a space between line
			$this->Cell(1, 30, '', 0, 1, 'L');

			$this->SetFont('Arial', '', 58);
			$this->Cell(113, $this->lineheight, '', 0, 0, 'L');
			$this->Cell(390, $this->lineheight, 'Work on these words:', 0, 0, 'L');

			// spacer
			$this->Cell(1, 36, '', 0, 1, 'L');
			$this->Cell(113, 0.5, '', 0, 0, 'L');
			$this->Cell(633, 0.5, '', 'B', 0, 'L');
		}

		/*
		  $this->SetY(150);
		  $this->SetFont('Arial','B',58);
		  $this->Cell(25,$this->lineheight,'',0,0,'L');
		  $this->Cell(60,$this->lineheight,'Unit 5',0,0,'L');
		  $this->Cell(85,$this->lineheight,'Section 5',0,1,'L');

		  $this->SetFont('Arial','',12);
		  $this->Cell(180,$this->lineheight,'',0,1,'L');
		  $this->Cell(25,$this->lineheight,'',0,0,'L');
		  $this->Cell(60,$this->lineheight,'Unit Name',0,0,'L');
		  $this->Cell(85,$this->lineheight,'Section Name',0,0,'L');
		  $this->Cell(60,$this->lineheight,'Game Name',0,1,'L');

		  $this->SetFont('Arial','',16);
		  $this->Cell(180,$this->lineheight,'',0,1,'L');
		  $this->Cell(25,$this->lineheight,'',0,0,'L');
		  $this->Cell(100,$this->lineheight,'Congratulations, you have achieved a gold medal!',0,0,'L');
		 */

		/*
		  if($_SERVER['HTTP_HOST'] == '127.0.0.1')
		  $this->Image($_SERVER['DOCUMENT_ROOT'].'languagenut/img.png',0,0,210);
		  else
		  $this->Image('/home/language/admin_html/logo-small.png',20,12,60.6);
		  $this->SetFont('Arial','B',12);
		  $this->Cell(100,$this->lineheight,'STUDENTS',0,1,'C');
		 */
		/*
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








		  // spacers
		  $this->Cell(180,$this->lineheight,'',0,1,'L');
		  $this->Cell(180,$this->lineheight,'',0,1,'L');
		  $this->Cell(180,$this->lineheight,'',0,1,'L');
		  $this->Cell(180,$this->lineheight,'',0,1,'L');
		 */
		$this->Output();
	}

}

?>