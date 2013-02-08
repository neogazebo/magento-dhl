<?php

class Dhl_Dhlshipment_IndexController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}

	public function pdfbarcodeAction()
	{
		$pdf = new Zend_Pdf();
		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
		$pdf->pages[] = $page;
		$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 20);
		$page->drawText('Hello world!', 100, 510);

		$pdfData = $pdf->render();
		header("Content-Disposition: inline; filename=result.pdf");
		header('Content-type', 'application/pdf', true);
		echo $pdfData;
	}

	public function pdfAction()
	{
//		$this->_helper->layout->disableLayout();
//		$this->_helper->viewRenderer->setNoRender();

		$pdf = new Zend_Pdf();
//		$pdf->properties['Title'] = "TITLE";
//		$pdf->properties['Author'] = "AUTHOR";

		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
//		$width = $page->getWidth();				// A4 : 595
//		$height = $page->getHeight();			 // A4 : 842

//		$imagePath = WEB_DIR . '/images/logo.png';
//		$image = Zend_Pdf_Image::imageWithPath($imagePath);
//		$x = 15;
//		$y = $height - 15 - 106 / 2;
//		$page->drawImage($image, $x, $y, $x + 155 / 2, $y + 106 / 2);

		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$page->setFont($font, 36);

		$page->drawText('Hello world!', 72, 720, 'UTF-8');

		$pdf->pages[] = $page;

		$this->getResponse()->setHeader('Content-type', 'application/x-pdf', true);
		$this->getResponse()->setHeader('Content-disposition', 'inline; filename=my-file.pdf', true);
		$this->getResponse()->setBody($pdf->render());
	}

	public function halloAction()
	{
		echo "Hallo";
	}
}