<?php

class Dhl_Dhlshipment_Adminhtml_DhlshipmentController extends Mage_Adminhtml_Controller_Action
{
	protected $globalproductcode = "P";
	protected $localproductcode = "P";
	public $remoteAreaService = false;
	public $arrayResponse = array();
	public $paymentCountryCode = 'SG';
	protected $siteId;
	protected $password;
	protected $timeNow;
	protected $mref;
	protected $time;
	protected $paymentAccountNumber;
	protected $conFullname;
	protected $conAddressLine1;
	protected $conAddressLine2;
	protected $conCity;
	protected $conRegion;
	protected $conPostcode;
	protected $conCountryId;
	protected $conCountry;
	protected $conPersonName;
	protected $conTelp;
	protected $conEmailFrom;
	protected $conEmailTo;
	protected $pieceWeight;
	protected $totalWeight;
	protected $oriStoreName;
	protected $oriStreetLine1;
	protected $oriStreetLine2;
	protected $oriCity;
	protected $oriRegionId;
	protected $oriPostcode;
	protected $oriCountryId;
	protected $oriCountry;
	protected $oriOwnerName;
	protected $oriPhone;
	protected $oriEmailFrom;
	protected $oriEmailTo;
	protected $xml;

	protected function _initAction()
	{
		$this->loadLayout()
				->_setActiveMenu('dhlshipment/items')
				->_addBreadcrumb(Mage::helper('adminhtml')->__('Dhlshipment Manager'), Mage::helper('adminhtml')->__('Banner Manager'));

		return $this;
	}

	public function indexAction()
	{
		$this->_title($this->__('Dhlshipment'))
				->_title($this->__('Manage Tracking AWB'));
		$this->_initAction()
				->renderLayout();
	}
	/*
	 * Create Awb
	 */

	protected function createawb($id)
	{
		$model = Mage::getModel('dhlshipment/dhlshipment')->load($id);
		if ($model->getTrackingAwb() == 0)
		{
			$xmlObj = Mage::helper('dhlshipment')->xmlRequest($model->getOrderId(), 'shipmentvalidation');
			$response = $xmlObj->getResponse();
			$xmlResponse = simplexml_load_string($response);
			$model->setStatusAwb($response);
			$model->setTrackingAwb($xmlResponse->AirwayBillNumber);
			if ($model->save())
				return true;
		}
		else
			return false;
	}
	/*
	 * create shipmenet validation 
	 */

	public function createawbAction()
	{
		$id = $this->getRequest()->getParam('id');
		if ($this->createawb($id))
			$this->_redirect('*/*/');
	}
	/*
	 *  Create AWB for selected
	 */

	public function masscreateawbAction()
	{
		$orderIds = $this->getRequest()->getPost('order_ids');
		foreach ($orderIds as $orderId)
		{
			$model = Mage::getModel('dhlshipment/dhlshipment')->load($orderId);
			if ($this->createawb($orderId))
				$this->_getSession()->addSuccess($this->__('Success.' . $model->getOrderId()));
			else
				$this->_getSession()->addError($this->__('Error.' . $model->getOrderId()));
		}
		$this->_redirect('*/*/');
	}
	/*
	 * Create Awb
	 */

	protected function pickup($id)
	{
		$model = Mage::getModel('dhlshipment/dhlshipment')->load($id);
		if ($model->getPickup() == '' || $model->getPickup() == NULL)
		{
			if ($model->getTrackingAwb() == 0)
				return false;
			$xmlObj = Mage::helper('dhlshipment')->xmlRequest($model->getOrderId(), 'bookingpickup');
			$response = $xmlObj->getResponse();
			$xmlResponse = simplexml_load_string($response);
			$model->setStatusPickup($response);
			$model->setPickup($xmlResponse->ConfirmationNumber);
			if ($model->save())
				return true;
		}
		else
			return false;
	}
	/*
	 * create pickup 
	 */

	public function pickupAction()
	{
		$id = $this->getRequest()->getParam('id');
		if ($this->pickup($id))
			$this->_redirect('*/*/');
	}
	/*
	 * mass pickup
	 */

	public function masspickupAction()
	{
		$orderIds = $this->getRequest()->getPost('order_ids');
		foreach ($orderIds as $orderId)
		{
			$model = Mage::getModel('dhlshipment/dhlshipment')->load($orderId);
			if ($this->pickup($orderId))
				$this->_getSession()->addSuccess($this->__('Success.' . $model->getOrderId()));
			else
				$this->_getSession()->addError($this->__('Error.' . $model->getOrderId()));
		}
		$this->_redirect('*/*/');
	}

	public function returnawb($id)
	{
		$model = Mage::getModel('dhlshipment/dhlshipment')->load($id);
		if ($model->getReturnAwb() == '' || $model->getReturnAwb() == NULL)
		{
			if ($model->getTrackingAwb() == 0)
				return false;
			$xmlObj = Mage::helper('dhlshipment')->xmlRequest($model->getOrderId(), 'shipmentvalidation', 'return');
			$response = $xmlObj->getResponse();
			$xmlResponse = simplexml_load_string($response);
			$model->setStatusReturn($response);
			$model->setReturnAwb($xmlResponse->AirwayBillNumber);
			if ($model->save())
				return true;
		}
		return false;
	}
	/*
	 * create return order 
	 */

	public function returnawbAction()
	{
		$id = $this->getRequest()->getParam('id');
		if ($this->returnawb($id))
			$this->_redirect('*/*/');
	}
	/*
	 * mass pickup
	 */

	public function massreturnawbAction()
	{
		$orderIds = $this->getRequest()->getPost('order_ids');
		foreach ($orderIds as $orderId)
		{
			$model = Mage::getModel('dhlshipment/dhlshipment')->load($orderId);
			if ($this->returnawb($orderId))
				$this->_getSession()->addSuccess($this->__('Success.' . $model->getOrderId()));
			else
				$this->_getSession()->addError($this->__('Error.' . $model->getOrderId()));
		}
		$this->_redirect('*/*/');
	}
	/*
	 * create shipmenet validation 
	 */

	public function mailAction()
	{
		$id = $this->getRequest()->getParam('id');
		$typeXml = $this->getRequest()->getParam('type_xml');
		$model = Mage::getModel('dhlshipment/dhlshipment')->load($id);

		$orderId = $model->getOrderId();
		$order = Mage::getSingleton('sales/order')->loadByIncrementId($orderId);
		$address = Mage::getModel('sales/order_address')->load($order->shipping_address_id);

		$xmlConfig = new Dhl_Dhlshipment_Model_Carrier_Dhlshipment();

		$subject = str_replace('{order_id}', $model->getOrderId(), $xmlConfig->getConfigXml('pdf_email_subject'));

		$mail = new Zend_Mail();
		$mail->setBodyText($xmlConfig->getConfigXml('pdf_email_body'));
		$mail->setFrom($xmlConfig->getConfigXml('pdf_email_from'), Mage::app()->getStore()->getName());
		$mail->addTo($address->email, 'Recipient');
		$mail->setSubject($subject);

		$pdf = $this->getPdf($id, $typeXml);

		$at = $mail->createAttachment($pdf);
		$at->type = 'application/pdf';
		$at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
		$at->encoding = Zend_Mime::ENCODING_BASE64;
		$at->filename = $model->getTrackingAwb() . '.pdf';

		if ($mail->send())
		{
			echo "<script>alert('Email Hasbeen Send');window.history.back();</script>";
			die();
		}
//			$this->_redirect('*/*/');
		else
			die('send mail error');
	}
	/*
	 * create pdf for invoice
	 */

	public function pdfAction()
	{
		$id = $this->getRequest()->getParam('id');
		$typeXml = $this->getRequest()->getParam('type_xml');
		$this->getPdf($id, $typeXml);
	}

	public function getPdf($id, $typeXml = 'tracking')
	{
		$model = Mage::getModel('dhlshipment/dhlshipment')->load($id);
		if ($typeXml == 'return')
			$xml = $model->getStatusReturn();
		else
			$xml = $model->getStatusAwb();

//		var_dump($xml);exit;
		$xmlResponse = simplexml_load_string($xml);
//		var_dump($xmlResponse);exit;
//		$this->_helper->layout->disableLayout();
//		$this->_helper->viewRenderer->setNoRender();

		$fileName = dirname(__FILE__) . '/ambikuk.pdf';
		$pdf = new Zend_Pdf();
//		$pdf->properties['Title'] = "TITLE";
//		$pdf->properties['Author'] = "AUTHOR";

		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
		$width = $page->getWidth(); // A4 : 595
		$height = $page->getHeight(); // A4 : 842
		$imagePath = dirname(__FILE__) . '/invoice.png';
		$image = Zend_Pdf_Image::imageWithPath($imagePath);
		$page->drawImage($image, 300, 150, 550, 835);

//		$logoPath = dirname(__FILE__) . '/logo.jpg';
//		$logo = Zend_Pdf_Image::imageWithPath($logoPath);
//		$page->drawImage($logo, 463, 795, 540, 822);

		$page = $this->contentPdf($page, $model, $xmlResponse);

		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
		$page->setFont($font, 8);
		$page->drawText($xmlResponse->ProductShortName, 314, 805, 'UTF-8');
		$page->setFont($font, 6);
		$page->drawText('XML PI v4.0', 314, 799, 'UTF-8');

		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$page->setFont($font, 5);
		$page->drawText('Order #' . $model->getOrderId(), 314, 597, 'UTF-8');
		$page->drawText('Piece Weight:', 420, 597, 'UTF-8');

		$barcode_binary = base64_decode($xmlResponse->Barcodes->AWBBarCode);
		$tmp_dir = Mage::getBaseDir('tmp');
		$png_file = tempnam($tmp_dir) . '.png';
//		$tmp_file = array_search('uri', @array_flip(stream_get_meta_data($GLOBALS[mt_rand()] = tmpfile())));
		file_put_contents($png_file, $barcode_binary);
//		$png_file = $tmp_file . '.png';
//		rename($tmp_file, $png_file);
		$image = Zend_Pdf_Image::imageWithPath($png_file);
		$page->drawImage($image, 320, 500, 480, 550);

		$barcode_binary = base64_decode($xmlResponse->Barcodes->OriginDestnBarcode);
		// Temporary dir
		$tmp_dir = Mage::getBaseDir('tmp');
		$png_file = tempnam($tmp_dir) . '.png';
//		$tmp_file = array_search('uri', @array_flip(stream_get_meta_data($GLOBALS[mt_rand()] = tmpfile())));
		file_put_contents($png_file, $barcode_binary);
//		$png_file = $tmp_file . '.png';
//		rename($tmp_file, $png_file);
		$image = Zend_Pdf_Image::imageWithPath($png_file);
		$page->drawImage($image, 320, 440, 520, 490);


		$barcode_binary = base64_decode($xmlResponse->Barcodes->DHLRoutingBarCode);
		$tmp_dir = Mage::getBaseDir('tmp');
		$png_file = tempnam($tmp_dir) . '.png';
//		$tmp_file = array_search('uri', @array_flip(stream_get_meta_data($GLOBALS[mt_rand()] = tmpfile())));
		file_put_contents($png_file, $barcode_binary);
//		$png_file = $tmp_file . '.png';
//		rename($tmp_file, $png_file);
		$image = Zend_Pdf_Image::imageWithPath($png_file);
		$page->drawImage($image, 320, 380, 520, 430);

		$page->setFont($font, 8);
		$page->drawText('WAYBILL ' . $xmlResponse->AirwayBillNumber, 380, 493, 'UTF-8');
		$page->drawText('(2L) ' . $xmlResponse->DHLRoutingCode, 380, 433, 'UTF-8');
		$page->drawText('(J) ' . $xmlResponse->Pieces->Piece->LicensePlate, 380, 370, 'UTF-8');

		//colomn 7
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$page->setFont($font, 5);
		$page->drawText('Content: ' . $xmlResponse->Contents, 314, 570, 'UTF-8');

		$imagePath = dirname(__FILE__) . '/label1.png';
		$image = Zend_Pdf_Image::imageWithPath($imagePath);
		$page->drawImage($image, 0, 500, 23, 750);
		$pdf->pages[] = $page;

		// ARCIVE
		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
		$width = $page->getWidth(); // A4 : 595
		$height = $page->getHeight(); // A4 : 842
		$imagePath = dirname(__FILE__) . '/invoice2.png';
		$image = Zend_Pdf_Image::imageWithPath($imagePath);
		$page->drawImage($image, 300, 150, 550, 835);

//		$logoPath = dirname(__FILE__) . '/logo.jpg';
//		$logo = Zend_Pdf_Image::imageWithPath($logoPath);
//		$page->drawImage($logo, 463, 795, 540, 822);

		$page = $this->contentPdf($page, $model, $xmlResponse);
		$page = $this->contentPdf($page, $model, $xmlResponse);
		$imagePath = dirname(__FILE__) . '/label2.png';
		$image = Zend_Pdf_Image::imageWithPath($imagePath);
		$page->drawImage($image, 0, 500, 23, 750);

		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
		$page->setFont($font, 8);
		$page->drawText('*ARCHIVE DOC*', 314, 805, 'UTF-8');
		$page->setFont($font, 6);
		$page->drawText('Do not attach to package', 314, 799, 'UTF-8');

		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$page->setFont($font, 5);
//		$page->drawText('Account No: ' . $xmlResponse->Billing->BillingAccountNumber, 314, 597, 'UTF-8');
		$page->drawText('Order #' . $model->getOrderId(), 314, 597, 'UTF-8');
		$page->drawText('Shipment Weight:', 411, 597, 'UTF-8');

		$barcode_binary = base64_decode($xmlResponse->Barcodes->AWBBarCode);
		$tmp_dir = Mage::getBaseDir('tmp');
		$png_file = tempnam($tmp_dir) . '.png';
//		$tmp_file = array_search('uri', @array_flip(stream_get_meta_data($GLOBALS[mt_rand()] = tmpfile())));
		file_put_contents($png_file, $barcode_binary);
//		$png_file = $tmp_file . '.png';
//		rename($tmp_file, $png_file);
		$image = Zend_Pdf_Image::imageWithPath($png_file);
		$page->drawImage($image, 320, 500, 480, 550);

		$page->setFont($font, 8);
		$page->drawText('WAYBILL ' . $xmlResponse->AirwayBillNumber, 380, 493, 'UTF-8');

		//colomn 7
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$page->setFont($font, 5);
		$page->drawText('DHL standard Terms and Conditions apply. Warsaw convention may olso apply.', 314, 570, 'UTF-8');
		$page->drawText('Shipment may be carried via intermediated stopping places DHL deems appropriate.', 314, 565, 'UTF-8');
		$page->drawText('Content: ' . $xmlResponse->Contents, 314, 560, 'UTF-8');

		$page->setFont($font, 6);
		$page->drawText('Product                   : ' . $xmlResponse->GlobalProductCode . ' ' . $xmlResponse->ProductShortName, 314, 462, 'UTF-8');
		$page->drawText('Service                   : ', 314, 456, 'UTF-8');
//		$page->drawText('Billing Account No  : ' . $xmlResponse->Billing->BillingAccountNumber, 314, 450, 'UTF-8');
		$page->drawText('DTP Account No     : ', 314, 450, 'UTF-8');
		$page->drawText('Insurance value      : ' . $xmlResponse->InsuredAmount, 314, 444, 'UTF-8');
		$page->drawText('Declared Value       : ' . $xmlResponse->Dutiable->DeclaredValue . ' ' . $xmlResponse->Dutiable->DeclaredCurrency, 314, 438, 'UTF-8');
		$page->drawText('Terms of Trade       : ' . $xmlResponse->Dutiable->TermsofTrade, 314, 432, 'UTF-8');

		$page->drawText('Licence plates of Pieces in Shipment : ', 314, 410, 'UTF-8');
		$page->drawText('-(' . $xmlResponse->Pieces->Piece->DataIdentifier . ')' . $this->spacepablic($xmlResponse->Pieces->Piece->LicensePlate), 314, 403, 'UTF-8');

		$pdf->pages[] = $page;
		$fileName = $xmlResponse->AirwayBillNumber . '.pdf';

		$this->getResponse()->setHeader('Content-type', 'application/x-pdf', true);
		$this->getResponse()->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '"', true);
		$this->getResponse()->setBody($pdf->render());

		return $pdf->render();
	}

	public function spacepablic($str)
	{
		$read = str_split($str, 4);
		foreach ($read as $r)
		{
			$n .= $r . ' ';
		}
		return $n;
	}

	public function contentPdf($page, $model, $xmlResponse)
	{
		//header

		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
		$page->setFillColor(Zend_Pdf_Color_Html::color('#FFFFFF'));
		$page->setFont($font, 15);
		$page->drawText($xmlResponse->ProductContentCode, 425, 805, 'UTF-8');

		//From
		$page->setFillColor(Zend_Pdf_Color_Html::color('#000000'));
		$page->setFont($font, 7);
		$page->drawText('From :', 314, 784, 'UTF-8');
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$page->setFont($font, 6);
		$page->drawText($xmlResponse->Shipper->CompanyName, 340, 784, 'UTF-8');
		$page->drawText($xmlResponse->Shipper->Contact->PersonName, 340, 778, 'UTF-8');
		$page->drawText($xmlResponse->Shipper->AddressLine[0], 340, 772, 'UTF-8');
		$page->drawText($xmlResponse->Shipper->AddressLine[1], 340, 766, 'UTF-8');
		$page->drawText('Ph:' . $xmlResponse->Shipper->Contact->PhoneNumber, 440, 766, 'UTF-8');
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
		$page->setFont($font, 7);
		$page->drawText($xmlResponse->Shipper->City . ' ' . $xmlResponse->Shipper->DivisionCode . ' ' . $xmlResponse->Shipper->PostalCode, 340, 754, 'UTF-8');
		$page->drawText($xmlResponse->Shipper->CountryName, 340, 746, 'UTF-8');
		$page->drawText($xmlResponse->OriginServiceArea->ServiceAreaCode, 510, 770, 'UTF-8');
		$page->drawText('Origin :', 510, 780, 'UTF-8');

		//To
		$page->setFont($font, 9);
		$page->drawText('To :', 314, 728, 'UTF-8');
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$page->setFont($font, 8);
		$page->drawText($xmlResponse->Consignee->CompanyName, 337, 728, 'UTF-8');
		$page->drawText($xmlResponse->Consignee->Contact->PersonName, 337, 718, 'UTF-8');
		$page->drawText($xmlResponse->Consignee->AddressLine[0], 337, 708, 'UTF-8');
		$page->drawText($xmlResponse->Consignee->AddressLine[1], 337, 698, 'UTF-8');
		$page->drawText('Ph:' . $xmlResponse->Consignee->Contact->PhoneNumber, 440, 728, 'UTF-8');
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
		$page->setFont($font, 10);
		$page->drawText($xmlResponse->Consignee->City . ' ' . $xmlResponse->Consignee->DivisionCode . ' ' . $xmlResponse->Consignee->PostalCode, 337, 680, 'UTF-8');
		$page->drawText($xmlResponse->Consignee->CountryName, 337, 670, 'UTF-8');

		//colomn 4
		$page->drawText($xmlResponse->OriginServiceArea->OutboundSortCode, 314, 640, 'UTF-8');
		$page->setFont($font, 13);
		$page->drawText($xmlResponse->Consignee->CountryCode . '-' . $xmlResponse->DestinationServiceArea->ServiceAreaCode . '-' . $xmlResponse->DestinationServiceArea->FacilityCode, 380, 640, 'UTF-8');
		$page->setFont($font, 10);
		$page->drawText($xmlResponse->DestinationServiceArea->InboundSortCode, 520, 640, 'UTF-8');

		//colomn 5
		$page->setFont($font, 18);
		$page->setFillColor(Zend_Pdf_Color_Html::color('#FFFFFF'));
		$page->drawText($xmlResponse->InternalServiceCode, 314, 611, 'UTF-8');
		$page->setFillColor(Zend_Pdf_Color_Html::color('#000000'));
		$page->setFont($font, 8);
		$page->drawText($xmlResponse->DeliveryDateCode, 497, 608, 'UTF-8');
		$page->drawText($xmlResponse->DeliveryTimeCode, 520, 608, 'UTF-8');
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$page->setFont($font, 8);
		$page->drawText('Day', 497, 618, 'UTF-8');
		$page->drawText('Time', 515, 618, 'UTF-8');

		//colomn 6
		$page->setFont($font, 5);
		$page->drawText('Date', 420, 584, 'UTF-8');
		$page->drawText('Piece:', 507, 597, 'UTF-8');
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
		$page->setFont($font, 6);
		$page->drawText($xmlResponse->Pieces->Piece->Weight . ' Kg', 453, 597, 'UTF-8');
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$page->setFont($font, 6);
		$page->drawText($xmlResponse->ShipmentDate, 434, 584, 'UTF-8');
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
		$page->setFont($font, 12);
		$page->drawText('1/1', 507, 585, 'UTF-8');


		return $page;
	}

	public function xmlAction()
	{
		header('content-type: text/plain');
		$id = $this->getRequest()->getParam('id');
		$type = $this->getRequest()->getParam('type');
		$model = Mage::getModel('dhlshipment/dhlshipment')->load($id);
		var_dump($type);exit;
		if ($type == 'shipmentvalidation')
			echo $model->getStatusAwb();
		elseif ($type == 'return')
			echo $model->getStatusReturn();
		elseif ($type == 'pickup')
			echo $model->getStatusPickup();
		exit;
	}

	public function checkawbAction()
	{
		$data = new Dhl_Dhlshipment_Model_Carrier_Dhlshipment();
		$awb = $this->getRequest()->getParam('id');
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<req:KnownTrackingRequest xmlns:req="http://www.dhl.com" 
						xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
						xsi:schemaLocation="http://www.dhl.com
						TrackingRequestKnown.xsd">
	<Request>
		<ServiceHeader>
			<MessageTime>' . $data->time . '</MessageTime>
			<MessageReference>' . $data->mref . '</MessageReference>
        <SiteID>' . $data->getConfigData("id") . '</SiteID>
        <Password>' . $data->getConfigData("password") . '</Password>
		</ServiceHeader>
	</Request>
	<LanguageCode>en</LanguageCode>
	<AWBNumber>' . $awb . '</AWBNumber>
	<LevelOfDetails>ALL_CHECK_POINTS</LevelOfDetails>
</req:KnownTrackingRequest>
			 ';
//			var_dump($xml);exit;
		$results = $data->sendXmlOverPost($xml);
		$xml = simplexml_load_string($results);
//			var_dump($xml);exit;
		echo "Number : " . $xml->AWBInfo->AWBNumber . '<br />';
		echo "Origin Service Area : " . $xml->AWBInfo->ShipmentInfo->OriginServiceArea->ServiceAreaCode . '<br />';
		echo "Description : " . $xml->AWBInfo->ShipmentInfo->OriginServiceArea->Description . '<br />';
		echo "Destination Service Area : " . $xml->AWBInfo->ShipmentInfo->DestinationServiceArea->ServiceAreaCode . '<br />';
		echo "Description : " . $xml->AWBInfo->ShipmentInfo->DestinationServiceArea->Description . '<br />';
		echo "Shipper Name : " . $xml->AWBInfo->ShipmentInfo->ShipperName . '<br />';
		echo "Consignee Name : " . $xml->AWBInfo->ShipmentInfo->ConsigneeName . '<br />';
		echo "Shipment Date : " . $xml->AWBInfo->ShipmentInfo->ShipmentDate . '<br />';
		echo "Pieces : " . $xml->AWBInfo->ShipmentInfo->Pieces . '<br />';
		echo "Weight : " . $xml->AWBInfo->ShipmentInfo->Weight . '<br />';
		echo "Weight Unit : " . $xml->AWBInfo->ShipmentInfo->WeightUnit . '<br />';
		echo "Global Product Code : " . $xml->AWBInfo->ShipmentInfo->GlobalProductCode . '<br />';
		echo "Shipment Desc : " . $xml->AWBInfo->ShipmentInfo->ShipmentDesc . '<br />';
		echo "Notifucation Flag : " . $xml->AWBInfo->ShipmentInfo->DlvyNotificationFlag . '<br />';
		echo "From : " . $xml->AWBInfo->ShipmentInfo->Shipper->City . ' ' . $xml->ShipmentInfo->Shipper->Postalcode . ' ' . $xml->ShipmentInfo->Shipper->CountryCode . '<br />';
		echo "To : " . $xml->AWBInfo->ShipmentInfo->Consignee->City . ' ' . $xml->ShipmentInfo->Consignee->Postalcode . ' ' . $xml->ShipmentInfo->Consignee->CountryCode . '<br />';
		foreach ($xml->AWBInfo->Status as $stat)
		{
			echo "<br />ActionStatus : " . $stat->ActionStatus;
			foreach ($stat->Condition as $con)
			{
				echo "<br />ConditionCode : " . $con->ConditionCode;
				echo "<br />ConditionData : " . $con->ConditionData;
			}
		}
		echo '<table cellspacing="5" cellpadding="5" class="tbltrackingawb">';
		echo '
							<tr>
								<th>Date</th>
								<th>Time</th>
								<th>Event Code</th>
								<th>Service Event</th>
							</tr>
					   ';
		foreach ($xml->AWBInfo->ShipmentInfo->ShipmentEvent as $event)
		{
			echo '
							<tr>
								<td>' . $event->Date . '</td>
								<td>' . $event->Time . '</td>
								<td>' . $event->ServiceEvent->EventCode . '</td>
								<td>' . $event->ServiceEvent->Description . '</td>
							</tr>
					   ';

			$event->Date;
		}
		echo '</table>';
	}

	public function newAction()
	{
		$this->_forward('edit');
	}

	protected function _initItem()
	{
		if (!Mage::registry('dhlshipment_categories'))
		{
			if ($this->getRequest()->getParam('id'))
			{
				Mage::register('dhlshipment_categories', Mage::getModel('dhlshipment/dhlshipment')
								->load($this->getRequest()->getParam('id'))->getCategories());
			}
		}
	}

	public function categoriesAction()
	{
		$this->_initItem();
		$this->getResponse()->setBody(
				$this->getLayout()->createBlock('dhlshipment/adminhtml_dhlshipment_edit_tab_categories')->toHtml()
		);
	}

	public function categoriesJsonAction()
	{
		$this->_initItem();
		$this->getResponse()->setBody(
				$this->getLayout()->createBlock('dhlshipment/adminhtml_dhlshipment_edit_tab_categories')
						->getCategoryChildrenJson($this->getRequest()->getParam('category'))
		);
	}

	public function exportCsvAction()
	{
		$fileName = 'dhlshipment.csv';
		$content = $this->getLayout()->createBlock('dhlshipment/adminhtml_dhlshipment_grid')
				->getCsv();

		$this->_sendUploadResponse($fileName, $content);
	}

	public function exportXmlAction()
	{
		$fileName = 'dhlshipment.xml';
		$content = $this->getLayout()->createBlock('dhlshipment/adminhtml_dhlshipment_grid')
				->getXml();

		$this->_sendUploadResponse($fileName, $content);
	}

	protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream')
	{
		$response = $this->getResponse();
		$response->setHeader('HTTP/1.1 200 OK', '');
		$response->setHeader('Pragma', 'public', true);
		$response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
		$response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
		$response->setHeader('Last-Modified', date('r'));
		$response->setHeader('Accept-Ranges', 'bytes');
		$response->setHeader('Content-Length', strlen($content));
		$response->setHeader('Content-type', $contentType);
		$response->setBody($content);
		$response->sendResponse();
		die;
	}

	public function saveAction()
	{
		if ($data = $this->getRequest()->getPost())
		{
			if ($data['filename']['delete'] == 1)
			{
				$data['filename'] = '';
			}
			elseif (is_array($data['filename']))
			{
				$data['filename'] = $data['filename']['value'];
			}

			$model = Mage::getModel('dhlshipment/dhlshipment');
			$model->setData($data)
					->setId($this->getRequest()->getParam('id'));

			try
			{
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL)
				{
					$model->setCreatedTime(now())
							->setUpdateTime(now());
				}
				else
				{
					$model->setUpdateTime(now());
				}

				$model->setStores(implode(',', $data['stores']));
				if (isset($data['category_ids']))
				{
					$model->setCategories(implode(',', array_unique(explode(',', $data['category_ids']))));
				}

				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('dhlshipment')->__('Item was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back'))
				{
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
			}
			catch (Exception $e)
			{
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('dhlshipment')->__('Unable to find item to save'));
		$this->_redirect('*/*/');
	}
}