<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/wordpress/wp-config.php');
require('fpdf.php');
$pdf=new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(255, 248, 220);
$pdf->SetAutoPageBreak(true);
$pdf->Ln();                    
$pdf->SetFont('Arial','B',10);
        $subcatIdArray = $wpdb->get_results("SELECT * FROM wp_product_list where product_id=".$_GET['id']);
		$subcatId = $subcatIdArray[0]->psc_id;
		$primaryCatId = $subcatIdArray[0]->ppc_id;
		if($subcatId != 0) {
			$results = $wpdb->get_results("SELECT * FROM wp_product_list where psc_id=".$subcatId);
		}
		else {
			$results = $wpdb->get_results("SELECT * FROM wp_product_list where ppc_id=".$primaryCatId);
		}

		foreach($results as $row)	{
				$bId= $row->product_id;
				$bTitle = $row->product_name;
				$bDesc = $row->product_description;
				$bImage = $row->product_image;
				$pdf->Cell(0,15,$bTitle,0,1,'L');
				$pdf->Cell(0,5, $pdf->Image(plugins_url($bImage, __FILE__ )),10,6,30);
				$pdf->MultiCell(0,5,$bDesc,'0');
				$pdf->Ln();
				$pdf->Cell(0,15,'--------------------------------------------------------------------------------------------------------------------------------------------------',0,1,'L');
        }
		ob_start();
		$pdf->Output('123.pdf','D');
		ob_end_flush(); 
?>