<?php
class vocabPdf extends FPDF {

    public function  __construct($orientation='P', $unit='mm', $format='A4') {

        parent::FPDF($orientation, $unit, $format);
    }
		function printVocab ($data = array ()) {

		$this->AddPage();
		$this->Image('header.png', 15, 14, 180);

		$this->SetFont('Arial','B',12);
		$this->Ln(16);
		$this->Cell(160,6,$data['unit'],0,1);
		$this->Cell(160,6,$data['section'],0,1);
		$this->Ln(5);
		//'vocab'=>$vocab
		// add headers
		$this->Cell(95,4,$data['language'],0,0,'C');
		$this->Cell(95,4,'English',0,1,'C');
		$this->Ln(1);
		// build table
		foreach($data['vocab'] as $key=>$val) {
			$this->SetFont('Arial','','12');
			$this->Cell(95,8.5,$val[$data['language']],1,0,'C');
			$this->Cell(95,8.5,$val['English'],1,1,'C');
		}
		$this->Image('cut.png', 15, 147, 180);


		$this->Image('header.png', 15, 159, 180);
		$this->SetFont('Arial','B',12);
		$this->Ln(36.9);
		$this->Cell(160,6,$data['unit'],0,1);
		$this->Cell(160,6,$data['section'],0,1);
		$this->Ln(5);
		//'vocab'=>$vocab
		// add headers
		$this->Cell(95,4,$data['language'],0,0,'C');
		$this->Cell(95,4,'English',0,1,'C');
		$this->Ln(1);
		// build table
		foreach($data['vocab'] as $key=>$val) {
			$this->SetFont('Arial','','12');
			$this->Cell(95,8.5,'',1,0,'C');
			$this->Cell(95,8.5,'',1,1,'C');
		}
	}
	function printFlashCards ($data = array ()) {
		$state = 0;
		foreach($data['vocab'] as $key=>$val) {
			$this->SetFont('Arial','B',12);
			if($state==0) {
				$this->AddPage();
				$this->SetAutoPageBreak(false,1);
				$this->Image('cards_header.jpg', 13, 13, 111);		$this->Image('cards_box2.jpg', 14, 40, 111);
				$this->Image('cards_header.jpg', 172, 13, 111);		$this->Image('cards_foldsymbol.jpg',147,63,5);	$this->Image('cards_box2.jpg', 173, 40, 111);
				$this->Image('cards_cutline.jpg', 13, 103, 270);
				$this->Image('cards_header.jpg', 13, 115, 111);		$this->Image('cards_box2.jpg', 14, 143, 111);
				$this->Image('cards_header.jpg', 172, 115, 111);	$this->Image('cards_foldsymbol.jpg',147,165,5);	$this->Image('cards_box2.jpg', 173, 143, 111);
				$state++;
			}
			if($state==1) {
				$u			= $data['unit'];
				$s			= $data['section'];
				$l			= $data['language'];
				$e			= 'English';
				$w			= $val[$data['language']];
				$h			= $val['English'];
				$c			= 'Copyright '.iconv("UTF-8", "ISO-8859-1", "©").' Languagenut 2010 - Photocopying permitted';
				// add spacer down from the top
				$this->SetY(20);
				$this->Cell(5,6,'',0,0);	$this->Cell(105,6,$u,0,0);		$this->Cell(54,6,'',0,0);	$this->Cell(105,6,$u,0,1);
				$this->Cell(5,6,'',0,0);	$this->Cell(105,6,$s,0,0);		$this->Cell(54,6,'',0,0);	$this->Cell(105,6,$s,0,1);
				// add spacer down from this row
				$this->SetFont('Arial','B',14);
				$this->Cell(5,8,'',0,0);	$this->Cell(105,8,$l,0,0,'C');	$this->Cell(53,8,'',0,0);	$this->Cell(105,8,$e,0,1,'C');
				// add in the translation data
				$this->SetY(57);
				$this->SetFont('Arial','B','18');
				$this->Cell(5,12,'',0,0);	$this->Cell(105,12,$w,0,0,'C');	$this->Cell(53,12,'',0,0);	$this->Cell(105,12,$h,0,1,'C');
				// add in copyright notice
				$this->SetY(90);
				$this->SetFont('Arial','','8');
				$this->Cell(5,6,'',0,0);	$this->Cell(105,6,$c,0,0,'C');	$this->Cell(53,6,'',0,0);	$this->Cell(105,6,$c,0,0,'C');
				$state++;
			} else {
				$u			= $data['unit'];
				$s			= $data['section'];
				$l			= $data['language'];
				$e			= 'English';
				$w			= $val[$data['language']];
				$h			= $val['English'];
				$c			= 'Copyright '.iconv("UTF-8", "ISO-8859-1", "©").' Languagenut 2010 - Photocopying permitted';
				// add spacer down from the top
				$this->SetY(122);
				$this->Cell(5,6,'',0,0);	$this->Cell(105,6,$u,0,0);		$this->Cell(54,6,'',0,0);	$this->Cell(105,6,$u,0,1);
				$this->Cell(5,6,'',0,0);	$this->Cell(105,6,$s,0,0);		$this->Cell(54,6,'',0,0);	$this->Cell(105,6,$s,0,1);
				// add spacer down from this row
				$this->SetFont('Arial','B',14);
				$this->Cell(5,8,'',0,0);	$this->Cell(105,8,$l,0,0,'C');	$this->Cell(53,8,'',0,0);	$this->Cell(105,8,$e,0,1,'C');
				// add in the translation data
				$this->SetY(162);
				$this->SetFont('Arial','B','18');
				$this->Cell(5,12,'',0,0);	$this->Cell(105,12,$w,0,0,'C');	$this->Cell(53,12,'',0,0);	$this->Cell(105,12,$h,0,1,'C');
				// add in copyright notice
				$this->SetY(193);//3
				$this->SetFont('Arial','','8');
				$this->Cell(5,6,'',0,0);	$this->Cell(105,6,$c,0,0,'C');	$this->Cell(53,6,'',0,0);	$this->Cell(105,6,$c,0,0,'C');
				$state = 0;
			}
		}
	}
	//Basic Format
	function BasicLinear($nvpArray) {
		$this->AddPage();
		$this->Image('../images/shared/menu/nowhere.gif',10,8,15);
		$this->Image('../images/shared/menu/ccm-word.gif',28,8,10);
		$this->Image('../images/shared/menu/ecl-word.gif',42,8,10);
		$this->Image('../images/shared/menu/eqs-word.gif',56,8,10);
		$this->Ln(16);
		foreach($nvpArray as $order_id=>$nvp) {
			$this->SetFont('Arial','B',12);
			$this->Cell(160,4,'Invoice: '.$order_id.' [Date: '.$nvp['order_date'].']',0);
			$this->Ln(8);
			/**
			 * Capture Fields
			 */
			$customer_name		= $nvp['customer_name'];
			$shipping_address	= $nvp['shipping_address'];
			$billing_address	= $nvp['billing_address'];
			$email				= $nvp['email'];
			$work_phone			= $nvp['work_phone'];
			$home_phone			= $nvp['home_phone'];
			$mobile_phone		= $nvp['mobile_phone'];
			$organisation		= $nvp['organisation'];
			$origin				= $nvp['origin'];
			$order_note			= $nvp['order_note'];
			$total				= $this->addMoney($nvp['total']);
			$payment_completed	= $nvp['payment_completed'];
			$promocode			= $nvp['promocode'];
			$items_purchased	= $nvp['items_purchased'];
			/**
			 * Build PDF
			 */
			$this->SetFont('Arial','B',10);			$this->Cell(30,3,'Customer Name',0);			$this->Ln(4);
			$this->SetFont('Arial','',10);			$this->Cell(60,3,$customer_name,0);				$this->Ln(5);
			$format=2;
			if($format==1) {
				$this->SetFont('Arial','B',10);			$this->Cell(60,4,'Shipping Address',0);			$this->Ln(4);
				$this->SetFont('Arial','',10);
				$shipping_address = explode("\n",$shipping_address);
				foreach($shipping_address as $address_line) {
					$this->Cell(60,3,$address_line,0);
					$this->Ln();
				}
				$this->Ln(4);
				$this->SetFont('Arial','B',10);			$this->Cell(60,4,'Billing Address',0);			$this->Ln(4);
				$this->SetFont('Arial','',10);
				$billing_address = explode("\n",$billing_address);
				foreach($billing_address as $address_line) {
					$this->Cell(60,3,$address_line,0);
					$this->Ln();
				}
				$this->Ln(4);
			} else {
				$this->SetFont('Arial','B',10);
				$this->Cell(80,4,'Shipping Address',0);
				$this->Cell(80,4,'Billing Address',0);
				$this->SetFont('Arial','',10);
				$this->Ln(4);
				$shipping_address = explode("\n",$shipping_address);
				$billing_address = explode("\n",$billing_address);
				$parts = ((count($shipping_address) > count($billing_address)) ? count($shipping_address) : count($billing_address));
				for($i=0; $i<$parts; $i++) {
					if(isset($shipping_address[$i])) {
						$this->Cell(80,4,$shipping_address[$i],0);
					} else {
						$this->Cell(80,4,'',0);
					}
					if(isset($billing_address[$i])) {
						$this->Cell(80,4,$billing_address[$i],0);
					} else {
						$this->Cell(80,4,'',0);
					}
					$this->Ln(4);
				}
				$this->Ln(4);
			}
			$this->SetFont('Arial','B',10);		$this->Cell(60,3,'Email',0);				$this->Ln(4);
			$this->SetFont('Arial','',10);		$this->Cell(60,3,$email,0);					$this->Ln(5);
			$this->SetFont('Arial','B',10);		$this->Cell(60,3,'Work Phone',0);			$this->Ln(4);
			$this->SetFont('Arial','',10);		$this->Cell(60,3,$work_phone,0);			$this->Ln(5);
			$this->SetFont('Arial','B',10);		$this->Cell(60,3,'Home Phone',0);			$this->Ln(4);
			$this->SetFont('Arial','',10);		$this->Cell(60,3,$home_phone,0);			$this->Ln(5);
			$this->SetFont('Arial','B',10);		$this->Cell(60,3,'Mobile Phone',0);			$this->Ln(4);
			$this->SetFont('Arial','',10);		$this->Cell(60,3,$mobile_phone,0);			$this->Ln(5);
			$this->SetFont('Arial','B',10);		$this->Cell(60,3,'Organisation',0);			$this->Ln(4);
			$this->SetFont('Arial','',10);		$this->Cell(60,3,$organisation,0);			$this->Ln(5);
			$this->SetFont('Arial','B',10);		$this->Cell(60,3,'Origin',0);				$this->Ln(4);
			$this->SetFont('Arial','',10);		$this->Cell(60,3,$origin,0);				$this->Ln(5);
			$this->SetFont('Arial','B',10);		$this->Cell(60,3,'Order Note',0);			$this->Ln(4);
			$this->SetFont('Arial','',10);		$this->Cell(60,3,$order_note,0);			$this->Ln(5);
			$this->SetFont('Arial','B',10);		$this->Cell(60,3,'Payment Completed',0);	$this->Ln(4);
			$this->SetFont('Arial','',10);		$this->Cell(60,3,$payment_completed,0);		$this->Ln(5);
			$this->SetFont('Arial','B',10);		$this->Cell(60,3,'Basket',0);				$this->Ln(4);
			$items_listed = 0;
			foreach($items_purchased as $index=>$nvpProducts) {
				$product_name	= $nvpProducts['product_name'];
				$product_price	= $this->addMoney($nvpProducts['product_price']);
				$product_token	= $nvpProducts['product_token'];
				$subscription	= $nvpProducts['subscription'];
				$attribute_1	= $nvpProducts['attribute_1'];
				$attribute_2	= $nvpProducts['attribute_2'];
				$value_1		= $nvpProducts['value_1'];
				$value_2		= $nvpProducts['value_2'];
				$quantity		= $nvpProducts['quantity'];
				/**
				 * Indicate if the product is a subscription
				 */
				$product_name.= ($subscription != 0) ? ' (Subscription)' : '';
				/**
				 * Don't show empty attribute information
				 */
				if($attribute_1 != '') {
					$attribute_1 = $attribute_1.': '.$value_1;
				}
				if($attribute_2 != '') {
					$attribute_2 = $attribute_2.': '.$value_2;
				}
				$attributes = $attribute_1;
				if($attribute_2 != '') {
					$attributes = $attribute_1.'; '.$attribute_2;
				}
				/**
				 * Build Table Headers if not already present
				 */
				if($items_listed == 0) {
					$this->SetFont('Arial','B',10);
					$this->Cell(30,3,'Token',0);
					$this->Cell(80,3,'Description',0);
					$this->Cell(15,3,'Quantity',0);
					$this->Cell(10,3,'Price',0);
					$this->Ln(5);
				}
				/**
				 * Build Products Data
				 */
				$this->SetFont('Arial','',10);
				$this->Cell(30,3,$product_token,0);
				$this->Cell(80,3,$product_name,0);
				$this->Cell(15,3,$quantity,0);
				$this->Cell(10,3,$product_price,0);
				$this->Ln(5);
				$this->Cell(30,3,'',0);
				$this->Cell(80,3,$attributes,0);
				$this->Cell(15,3,'',0);
				$this->Cell(10,3,'',0);
				$this->Ln(5);
				$items_listed++;
			}
			$this->SetFont('Arial','B',10);
			$this->Cell(30,3,'',0);
			$this->Cell(80,3,(($promocode != '') ? 'Promocode: '.((substr($promocode,0,1)=='£') ? $this->addMoney(substr($promocode,1)) : $promocode) : ''),0);
			$this->Cell(15,3,'Total:',0);
			$this->Cell(10,3,$total,0);
			$this->Ln(5);
		}
	}
}
define('FPDF_FONTPATH','font/');

?>