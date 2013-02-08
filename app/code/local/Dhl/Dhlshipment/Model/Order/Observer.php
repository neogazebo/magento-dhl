<?php

class Dhl_Dhlshipment_Model_Order_Observer
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
	protected $shippingPaymentType = 'S';
	protected $dutyPaymentType = 'R';
	//property sambung menyambung
	protected $regionType;
	protected $xmlRequestType;
	protected $xmlRequest;
	protected $xmlResponse;
	protected $payer;
	protected $dutyAccountNumber;
	protected $orderId;

	public function _construct()
	{
		
	}

	public function export_new_order($observer)
	{
//		var_dump($observer->getEvent()->getOrder());exit;

		$incrementid = $observer->getEvent()->getOrder()->getIncrementId();
		$model = Mage::getModel("dhlshipment/dhlshipment");
		$model->setStatus("1");
		$model->setTrackingAwb("0");
		$model->setOrderId($incrementid);
		if ($model->save())
			return $this;
		else
			var_dump($model);exit;
		return $this;
	}

	public function return_order($observer)
	{
		$config = new Dhl_Dhlshipment_Model_Carrier_Dhlshipment();
		$configXml = $config->getConfigXml('credit_memo_return');

		if ($configXml == 'A')
		{
			$data = $observer->getEvent()->getData();
			$data = $data['creditmemo'];
			$incrementId = $data->getOrder()->getIncrementId();
			$id = $this->getId($incrementId);

			$model = Mage::getModel('dhlshipment/dhlshipment')->load($id);
			$xml = $this->shipmentValidation($model->getOrderId(), 'return');
			$response = $this->xmlRequest($xml);

			$xmlResponse = simplexml_load_string($response);

			$model->setStatusReturn($response);
			$model->setReturnAwb($xmlResponse->AirwayBillNumber);
			if ($model->save())
				return $this;
			else
				var_dump($model);exit;
			return $this;
		}
		elseif ($configXml == 'M')
		{
			
		}
	}

	public function getId($order_id)
	{
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$table = $resource->getTableName('dhlshipment/dhlshipment');
		$query = 'SELECT id FROM ' . $table . ' WHERE order_id = '
				. $order_id . ' LIMIT 1';
		$id = $readConnection->fetchOne($query);
		return $id;
	}

	public function getCountry($id)
	{
		$countries = Mage::getResourceModel('directory/country_collection')->loadByStore()->toOptionArray();

		foreach ($countries as $country)
		{
			if (in_array($id, $country))
			{
				return $country['label'];
			}
		}
	}

	public function getRegion($name, $country = 'US')
	{
		if ($country == 'US')
		{
			$regions = $this->regionUs();
			return $regions["$name"];
		}
		else
			return $name;
	}

	public function xmlRequest($xml)
	{
//		var_dump($xml);exit;
		$getUrl = new Dhl_Dhlshipment_Model_Carrier_Dhlshipment();
		$client = new Zend_Http_Client();
		$client->setUri($getUrl->getConfigXml('gateway_url'));
		$response = $client->setRawData($xml, 'text/xml')->request('POST');
		if ($response->isSuccessful())
		{
//			var_dump($response->getBody());
//			exit;
			return $response->getBody();
		}
	}

//	public function pickup($orderId, $trackingawb)
//	{
//		if ($trackingawb == '' || $trackingawb == NULL)
//			die('Tranking Awb not set');
//
//		$getXml = new Dhl_Dhlshipment_Model_Carrier_Dhlshipment();
//
////pengirim
//		$store = Mage::app()->getStore();
//		$originSetting = Mage::getStoreConfig('shipping');
//		$storePhone = Mage::getStoreConfig('general/store_information/phone');
//		$storeEmail = Mage::getStoreConfig('trans_email/ident_general/email');
//		$storeOwnerName = Mage::getStoreConfig('trans_email/ident_general/name');
//
////penerima
//		$order = Mage::getSingleton('sales/order')->loadByIncrementId($orderId);
//		$shippingAddressId = $order->shipping_address_id;
//		$address = Mage::getModel('sales/order_address')->load($shippingAddressId);
//		$weight = substr($order->weight, 0, -5);
//
//		$timestamp = time();
//		$data = array();
//		for ($i = 0; $i < 31; $i++)
//		{
//			$data[$i] = rand(1, 9);
//		}
//
//		$addressLine = wordwrap($address->street, 35, "<br />");
//		$addressLine = explode("<br />", $addressLine);
//
////config
//		$this->siteId = $getXml->getConfigXml('id');
//		$this->password = $getXml->getConfigXml('password');
//		$this->timeNow = date('Y-m-d');
//		$this->mref = implode('', $data);
//		$this->time = date('c', $timestamp);
//		$this->paymentAccountNumber = $getXml->getConfigXml('payment_account_number');
//		$this->conFullname = $address->firstname . ' ' . $address->lastname;
//		$this->conAddressLine1 = $addressLine[0];
//		$this->conAddressLine2 = $addressLine[1];
//		$this->conCity = $address->city;
//		$this->conRegion = $this->getRegion($address->region);
//		$this->conPostcode = $address->postcode;
//		$this->conCountryId = $address->country_id;
//		$this->conCountry = $this->getCountry($address->country_id);
//		$this->conPersonName = $address->firstname . ' ' . $address->lastname;
//		$this->conTelp = $address->telephone;
//		$this->conEmailFrom = $address->email;
//		$this->conEmailTo = $address->email;
//		$this->pieceWeight = $weight;
//		$this->totalWeight = $weight;
//		$this->globalproductcode;
//		$this->localproductcode;
//		$this->timeNow;
//		$this->oriStoreName = $store->getName();
//		$this->oriStreetLine1 = $originSetting["origin"]["street_line1"];
//		$this->oriStreetLine2 = $originSetting["origin"]["street_line2"];
//		$this->oriCity = $originSetting["origin"]["city"];
//		$this->oriRegionId = $originSetting["origin"]["region_id"];
//		$this->oriPostcode = $originSetting["origin"]["postcode"];
//		$this->oriCountryId = $originSetting["origin"]["country_id"];
//		$this->oriCountry = $this->getCountry($originSetting["origin"]["country_id"]);
//		$this->oriOwnerName = $storeOwnerName;
//		$this->oriPhone = $storePhone;
//		$this->oriEmailFrom = $storeEmail;
//		$this->oriEmailTo = $storeEmail;
//		$this->trackingAwb = $trackingawb;
////		setting pickup
//		$this->accountType = $getXml->getConfigXml('account_type');
//		$this->phoneExtention = $getXml->getConfigXml('phone_extention');
//		$this->locationType = $getXml->getConfigXml('location_type');
//		$this->readyByTime = $getXml->getConfigXml('ready_by_time');
//		$this->closeTime = $getXml->getConfigXml('close_time');
//		$this->afterHoursClosingTime = $getXml->getConfigXml('after_hours_closing_time');
//		$this->afterHoursLocation = $getXml->getConfigXml('after_hours_location');
//		$this->pickupContactName = $getXml->getConfigXml('pickup_contact_name');
//		$this->pickupContactPhone = $getXml->getConfigXml('pickup_contact_phone');
//		$this->pickupContactPhoneExtention = $getXml->getConfigXml('pickup_contact_phone_extention');
//		$this->doorTo = $getXml->getConfigXml('door_to');
//
//		$xml = <<<SCRIPT
//<?xml version="1.0" encoding="UTF-8"
//<req:BookPickupRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com
// book-pickup-req.xsd">
//    <Request>
//			<ServiceHeader>
//				<MessageTime>$this->time</MessageTime>
//				<MessageReference>$this->mref</MessageReference>
//				<SiteID>$this->siteId</SiteID>
//				<Password>$this->password</Password>
//			</ServiceHeader>
//    </Request>
//    <Requestor>
//        <AccountType>$this->accountType</AccountType>
//        <ShipperAccountNumber>$this->paymentAccountNumber</ShipperAccountNumber>
//        <RequestorContact>
//            <PersonName>$this->oriOwnerName</PersonName>
//						<PhoneNumber>$this->oriPhone</PhoneNumber>
//            <PhoneExtention>$this->phoneExtention</PhoneExtention>
//        </RequestorContact>
//    </Requestor>
//    <Place>
//      <LocationType>$this->locationType</LocationType>
//      <CompanyName>$this->oriStoreName</CompanyName>
//			<AddressLine>$this->oriStreetLine1</AddressLine>
//			<AddressLine>$this->oriStreetLine2</AddressLine>
//			<City>$this->oriCity</City>
//			<DivisionCode>$this->oriRegionId</DivisionCode>
//			<PostalCode>$this->oriPostcode</PostalCode>
//			<CountryCode>$this->oriCountryId</CountryCode>
//			<CountryName>$this->oriCountry</CountryName>
//    </Place>
//    <Pickup>
//        <PickupDate>2010-01-18</PickupDate>
//        <ReadyByTime>$this->readyByTime</ReadyByTime>
//        <CloseTime>$this->closeTime</CloseTime>
//        <AfterHoursClosingTime>$this->afterHoursClosingTime</AfterHoursClosingTime>
//        <AfterHoursLocation>$this->afterHoursLocation</AfterHoursLocation>
//		<Pieces>1</Pieces>
// 	    <weight>
//            <Weight>$this->totalWeight</Weight>
//            <WeightUnit>L</WeightUnit>
//        </weight>
//    </Pickup>
//    <PickupContact>
//        <PersonName>$this->pickupContactName</PersonName>
//        <Phone>$this->pickupContactPhone</Phone>
//        <PhoneExtention>$this->pickupContactPhoneExtention</PhoneExtention>
//    </PickupContact>
//    <ShipmentDetails>
//        <AccountType>D</AccountType>
//        <AccountNumber>$this->paymentAccountNumber</AccountNumber>      
//        <BillToAccountNumber>$this->paymentAccountNumber</BillToAccountNumber>
//        <AWBNumber>7520067111</AWBNumber>
//        <NumberOfPieces>1</NumberOfPieces>
//        <Weight>$this->totalWeight</Weight>
//        <WeightUnit>L</WeightUnit>
//        <GlobalProductCode>P</GlobalProductCode>
//        <DoorTo>$this->doorTo</DoorTo>
//        <DimensionUnit>I</DimensionUnit>
//        <InsuredAmount>999999.99</InsuredAmount>
//        <InsuredCurrencyCode>USD</InsuredCurrencyCode>
//        <Pieces>
//            <Weight>$this->pieceWeight</Weight>
//        </Pieces>
//        <SpecialService>S</SpecialService>
//        <SpecialService>I</SpecialService>
//    </ShipmentDetails> 
//</req:BookPickupRequest>
//SCRIPT;
//
//		return $xml;
//	}
//	public function shipmentValidation($orderId, $type = 'tracking')
//	{
//		$getXml = new Dhl_Dhlshipment_Model_Carrier_Dhlshipment();
//
////pengirim
//		$store = Mage::app()->getStore();
//		$originSetting = Mage::getStoreConfig('shipping');
//		$storePhone = Mage::getStoreConfig('general/store_information/phone');
//		$storeEmail = Mage::getStoreConfig('trans_email/ident_general/email');
//		$storeOwnerName = Mage::getStoreConfig('trans_email/ident_general/name');
//
////penerima
//		$order = Mage::getSingleton('sales/order')->loadByIncrementId($orderId);
//		$shippingAddressId = $order->shipping_address_id;
//		$address = Mage::getModel('sales/order_address')->load($shippingAddressId);
//		$weight = substr($order->weight, 0, -5);
//
//		$timestamp = time();
//		$data = array();
//		for ($i = 0; $i < 31; $i++)
//		{
//			$data[$i] = rand(1, 9);
//		}
//
//		$addressLine = wordwrap($address->street, 35, "<br />");
//		$addressLine = explode("<br />", $addressLine);
//
//
////config
//		$this->siteId = $getXml->getConfigXml('id');
//		$this->password = $getXml->getConfigXml('password');
//		$this->timeNow = date('Y-m-d');
//		$this->mref = implode('', $data);
//		$this->time = date('c', $timestamp);
//		$this->paymentAccountNumber = $getXml->getConfigXml('payment_account_number');
//		;
//		$this->conFullname = $address->firstname . ' ' . $address->lastname;
//		$this->conAddressLine1 = $addressLine[0];
//		$this->conAddressLine2 = $addressLine[1];
//		$this->conCity = $address->city;
//		$this->conRegion = $this->getRegion($address->region);
//		$this->conPostcode = $address->postcode;
//		$this->conCountryId = $address->country_id;
//		$this->conCountry = $this->getCountry($address->country_id);
//		$this->conPersonName = $address->firstname . ' ' . $address->lastname;
//		$this->conTelp = $address->telephone;
//		$this->conEmailFrom = $address->email;
//		$this->conEmailTo = $address->email;
//		$this->pieceWeight = $weight;
//		$this->totalWeight = $weight;
//		$this->globalproductcode;
//		$this->localproductcode;
//		$this->timeNow;
//		$this->oriStoreName = $store->getName();
//		$this->oriStreetLine1 = $originSetting["origin"]["street_line1"];
//		$this->oriStreetLine2 = $originSetting["origin"]["street_line2"];
//		$this->oriCity = $originSetting["origin"]["city"];
//		$this->oriRegionId = $originSetting["origin"]["region_id"];
//		$this->oriPostcode = $originSetting["origin"]["postcode"];
//		$this->oriCountryId = $originSetting["origin"]["country_id"];
//		$this->oriCountry = $this->getCountry($originSetting["origin"]["country_id"]);
//		$this->oriOwnerName = $storeOwnerName;
//		$this->oriPhone = $storePhone;
//		$this->oriEmailFrom = $storeEmail;
//		$this->oriEmailTo = $storeEmail;
//		if ($type == 'return')
//		{
//			$this->shippingPaymentType = 'S';
//			$this->dutyPaymentType = 'R';
//		}
//
//
//		$xml = <<<SCRIPT
//<?xml version="1.0" encoding="UTF-8"
//	<req:ShipmentValidateRequestAP xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com
//	ship-val-req_AP.xsd">
//		<Request>
//			<ServiceHeader>
//				<MessageTime>$this->time</MessageTime>
//				<MessageReference>$this->mref</MessageReference>
//				<SiteID>$this->siteId</SiteID>
//				<Password>$this->password</Password>
//			</ServiceHeader>
//		</Request>
//		<LanguageCode>en</LanguageCode>
//		<PiecesEnabled>Y</PiecesEnabled>
//		<Billing>
//			<ShipperAccountNumber>$this->paymentAccountNumber</ShipperAccountNumber>
//			<ShippingPaymentType>S</ShippingPaymentType>
//			<DutyPaymentType>R</DutyPaymentType>
//		</Billing>
//		<Consignee>
//			<CompanyName>$this->conFullname</CompanyName>
//			<AddressLine>$this->conAddressLine1</AddressLine>
//			<AddressLine>$this->conAddressLine2</AddressLine>
//			<City>$this->conCity</City>
//			<PostalCode>$this->conPostcode</PostalCode>
//			<CountryCode>$this->conCountryId</CountryCode>
//			<CountryName>$this->conCountry</CountryName>
//			<Contact>
//				<PersonName>$this->conPersonName</PersonName>
//				<PhoneNumber>$this->conTelp</PhoneNumber>
//				<PhoneExtension>44444</PhoneExtension>
//				<FaxNumber>444444444</FaxNumber>
//				<Telex>44444444444</Telex>
//				<Email>
//					<From>$this->conEmailFrom</From>
//					<To>$this->conEmailTo</To>
//					<cc>testcc1</cc>
//					<cc>testcc2</cc>
//					<Subject>test email</Subject>
//					<ReplyTo>test@dhl.com</ReplyTo>
//					<Body>this is test shipment</Body>
//				</Email>
//			</Contact>
//		</Consignee>
//		<Commodity>
//			<CommodityCode>1</CommodityCode>
//			<CommodityName>String</CommodityName>
//		</Commodity>
//		<Dutiable>
//			<DeclaredValue>10.00</DeclaredValue>
//			<DeclaredCurrency>USD</DeclaredCurrency>
//			<ShipperEIN>Text</ShipperEIN>
//		</Dutiable>
//		<Reference>
//			<ReferenceID>REF-00000008</ReferenceID>
//			<ReferenceType>St</ReferenceType>
//		</Reference>
//		<ShipmentDetails>
//			<NumberOfPieces>1</NumberOfPieces>
//			<CurrencyCode>USD</CurrencyCode>
//			<Pieces>
//							<Piece>
//								<PieceID>1</PieceID>
//								<PackageType>EE</PackageType>
//								<Weight>$this->pieceWeight</Weight>
//							</Piece>
//			</Pieces>
//			<PackageType>CP</PackageType>
//			<Weight>$this->totalWeight</Weight>
//			<DimensionUnit>C</DimensionUnit>
//			<WeightUnit>K</WeightUnit>
//			<GlobalProductCode>$this->globalproductcode</GlobalProductCode>
//			<LocalProductCode>$this->localproductcode</LocalProductCode>
//			<DoorTo>DD</DoorTo>
//			<Date>$this->timeNow</Date>
//			<Contents>For testing purpose only. Please do not ship</Contents>
//			<IsDutiable>Y</IsDutiable>
//			<InsuredAmount>3000.10</InsuredAmount>
//		</ShipmentDetails>
//		<Shipper>
//			<ShipperID>967080215</ShipperID>
//			<CompanyName>$this->oriStoreName</CompanyName>
//			<AddressLine>$this->oriStreetLine1</AddressLine>
//			<AddressLine>$this->oriStreetLine2</AddressLine>
//			<City>$this->oriCity</City>
//			<DivisionCode>$this->oriRegionId</DivisionCode>
//			<PostalCode>$this->oriPostcode</PostalCode>
//			<CountryCode>$this->oriCountryId</CountryCode>
//			<CountryName>$this->oriCountry</CountryName>
//			<Contact>
//				<PersonName>$this->oriOwnerName</PersonName>
//				<PhoneNumber>$this->oriPhone</PhoneNumber>
//				<PhoneExtension>2222</PhoneExtension>
//				<FaxNumber>2222222222</FaxNumber>
//				<Telex>2222222222</Telex>
//				<Email>
//					<From>$this->oriEmailFrom</From>
//					<To>$this->oriEmailTo</To>
//					<cc>CC</cc>
//					<cc>CC</cc>
//					<Subject>Subject</Subject>
//					<ReplyTo>ReplayTo</ReplyTo>
//					<Body>Body</Body>
//				</Email>
//			</Contact>
//		</Shipper>
//		<!--SpecialService>
//			<SpecialServiceType>S</SpecialServiceType>
//			<ChargeValue>3.1</ChargeValue>
//			<CurrencyCode>USD</CurrencyCode>
//		</SpecialService-->
//	</req:ShipmentValidateRequestAP>
//SCRIPT;
//
//		return $xml;
//	}

	public function regionUs()
	{
		return array(
			"Alabama" => "AL",
			"Alaska" => "AK",
			"Arizona" => "AZ",
			"Arkansas" => "AR",
			"California" => "CA",
			"Colorado" => "CO",
			"Connecticut" => "CT",
			"Delaware" => "DE",
			"District of Columbia" => "DC",
			"Florida" => "FL",
			"Georgia" => "GA",
			"Hawaii" => "HI",
			"Idaho" => "ID",
			"Illinois" => "IL",
			"Indiana" => "IN",
			"Iowa" => "IA",
			"Kansas" => "KS",
			"Kentucky" => "KY",
			"Louisiana" => "LA",
			"Maine" => "ME",
			"Montana" => "MT",
			"Nebraska" => "NE",
			"Nevada" => "NV",
			"New Hampshire" => "NH",
			"New Jersey" => "NJ",
			"New Mexico" => "NM",
			"New York" => "NY",
			"North Carolina" => "NC",
			"North Dakota" => "ND",
			"Ohio" => "OH",
			"Oklahoma" => "OK",
			"Oregon" => "OR",
			"Maryland" => "MD",
			"Massachusetts" => "MA",
			"Michigan" => "MI",
			"Minnesota" => "MN",
			"Mississippi" => "MS",
			"Missouri" => "MO",
			"Pennsylvania" => "PA",
			"Rhode Island" => "RI",
			"South Carolina" => "SC",
			"South Dakota" => "SD",
			"Tennessee" => "TN",
			"Texas" => "TX",
			"Utah" => "UT",
			"Vermont" => "VT",
			"Virginia" => "VA",
			"Washington" => "WA",
			"West Virginia" => "WV",
			"Wisconsin" => "WI",
			"Wyoming" => "WY"
		);
	}

	public function asisXml($orderId, $xmlRequestType, $type = 'tracking')
	{
		$this->orderId = $orderId;
		$getXml = new Dhl_Dhlshipment_Model_Carrier_Dhlshipment();

//pengirim
		$store = Mage::app()->getStore();
		$originSetting = Mage::getStoreConfig('shipping');
		$storePhone = Mage::getStoreConfig('general/store_information/phone');
		$storeEmail = Mage::getStoreConfig('trans_email/ident_general/email');
		$storeOwnerName = Mage::getStoreConfig('trans_email/ident_general/name');

//penerima
		$order = Mage::getSingleton('sales/order')->loadByIncrementId($orderId);
		$shippingAddressId = $order->shipping_address_id;
		$address = Mage::getModel('sales/order_address')->load($shippingAddressId);
		$weight = substr($order->weight, 0, -5);
		
		$weight = ceil($weight);
		if($weight<1)
			$weight=1;

		$timestamp = time();
		$data = array();
		for ($i = 0; $i < 31; $i++)
		{
			$data[$i] = rand(1, 9);
		}

		$addressLine = wordwrap($address->street, 35, "<br />");
		$addressLine = explode("<br />", $addressLine);


//config
		$this->xmlRequestType = $xmlRequestType;
		$this->siteId = $getXml->getConfigXml('id');
		$this->password = $getXml->getConfigXml('password');
		$this->timeNow = date('Y-m-d');
		$this->mref = implode('', $data);
		$this->time = date('c', $timestamp);
		$this->paymentAccountNumber = $getXml->getConfigXml('payment_account_number');
		;
		$this->conFullname = $address->firstname . ' ' . $address->lastname;
		$this->conAddressLine1 = $addressLine[0];
		$this->conAddressLine2 = $addressLine[1];
		$this->conCity = $address->city;
		$this->conRegion = $this->getRegion($address->region);
		$this->conPostcode = $address->postcode;
		$this->conCountryId = $address->country_id;
		$this->conCountry = $this->getCountry($address->country_id);
		$this->conPersonName = $address->firstname . ' ' . $address->lastname;
		$this->conTelp = $address->telephone;
		$this->conEmailFrom = $address->email;
		$this->conEmailTo = $address->email;

		$this->pieceWeight = $weight;
		$this->totalWeight = $weight;
		$this->globalproductcode;
		$this->localproductcode;
		$this->timeNow;

		$this->oriStoreName = $store->getName();
		$this->oriStreetLine1 = $originSetting["origin"]["street_line1"];
		$this->oriStreetLine2 = $originSetting["origin"]["street_line2"];
		$this->oriCity = $originSetting["origin"]["city"];
		$this->oriRegionId = $originSetting["origin"]["region_id"];
		$this->oriPostcode = $originSetting["origin"]["postcode"];
		$this->oriCountryId = $originSetting["origin"]["country_id"];
		$this->oriCountry = $this->getCountry($originSetting["origin"]["country_id"]);
		$this->oriOwnerName = $storeOwnerName;
		$this->oriPhone = $storePhone;
		$this->oriEmailFrom = $storeEmail;
		$this->oriEmailTo = $storeEmail;
		if ($type == 'return')
		{
			$this->shippingPaymentType = 'R';
			$this->dutyPaymentType = 'R';

			$this->oriStoreName = $address->firstname . ' ' . $address->lastname;
			$this->oriStreetLine1 = $addressLine[0];
			$this->oriStreetLine2 = $addressLine[1];
			$this->oriCity = $address->city;
			$this->oriRegionId = $this->getRegion($address->region);
			$this->oriPostcode = $address->postcode;
			$this->oriCountryId = $address->country_id;
			$this->oriCountry = $this->getCountry($address->country_id);
			$this->oriOwnerName = $address->firstname . ' ' . $address->lastname;
			$this->oriPhone = $address->telephone;
			$this->oriEmailFrom = $address->email;
			$this->oriEmailTo = $address->email;

			$this->conFullname = $store->getName();
			$this->conAddressLine1 = $originSetting["origin"]["street_line1"];
			$this->conAddressLine2 = $originSetting["origin"]["street_line2"];
			$this->conCity = $originSetting["origin"]["city"];
			$this->conRegion = $originSetting["origin"]["region_id"];
			$this->conPostcode = $originSetting["origin"]["postcode"];
			$this->conCountryId = $originSetting["origin"]["country_id"];
			$this->conCountry = $this->getCountry($originSetting["origin"]["country_id"]);
			$this->conPersonName = $storeOwnerName;
			$this->conTelp = $storePhone;
			$this->conEmailFrom = $storeEmail;
			$this->conEmailTo = $storeEmail;
		}
		elseif ($type == 'tracking')
		{
			$this->shippingPaymentType = 'S';
			$this->dutyPaymentType = 'S';
		}
		//		setting pickup
		$this->accountType = $getXml->getConfigXml('account_type');
		$this->phoneExtention = $getXml->getConfigXml('phone_extention');
		$this->locationType = $getXml->getConfigXml('location_type');
		$this->readyByTime = $getXml->getConfigXml('ready_by_time');
		$this->closeTime = $getXml->getConfigXml('close_time');
		$this->afterHoursClosingTime = $getXml->getConfigXml('after_hours_closing_time');
		$this->afterHoursLocation = $getXml->getConfigXml('after_hours_location');
		$this->pickupContactName = $getXml->getConfigXml('pickup_contact_name');
		$this->pickupContactPhone = $getXml->getConfigXml('pickup_contact_phone');
		$this->pickupContactPhoneExtention = $getXml->getConfigXml('pickup_contact_phone_extention');
		$this->doorTo = $getXml->getConfigXml('door_to');
		$this->dimesionUnit = $getXml->getConfigXml('dimension_unit');
		$this->globalProductCode = $getXml->getConfigXml('global_product_code');
		$this->weightUnit = $getXml->getConfigXml('weight_unit');
		$this->billingAccountNumber = $this->paymentAccountNumber;
		$this->contentMessage = $getXml->getConfigXml('infotext');

		if ($this->xmlRequestType == 'shipmentvalidation' || $this->xmlRequestType == 'bookingpickup')
		{
//			if ($getXml->getConfigXml('outbound') > 0)
//				$this->paymentAccountNumber = $getXml->getConfigXml('outbound');
		}
		if ($this->xmlRequestType == 'shipmentvalidation' || $this->xmlRequestType == 'return')
		{
//			if ($getXml->getConfigXml('payer') > 0)
//				$this->payer = '<BillingAccountNumber>' . $getXml->getConfigXml('payer') . '</BillingAccountNumber>';
//			if ($getXml->getConfigXml('duty_account_number') > 0)
//			{
//				$this->dutyAccountNumber = '<DutyAccountNumber>' . $getXml->getConfigXml('duty_account_number') . '</DutyAccountNumber>';
//				$this->dutyPaymentType = 'T';
//			}
		}
		if ($type == 'return')
		{
			if ($getXml->getConfigXml('inbound') > 0)
				$this->paymentAccountNumber = $getXml->getConfigXml('inbound');
		}
		return $this;
	}

	public function regionType()
	{
		$ap = array(
			'AE' => 'UNITED ARAB EMIRATES',
			'AF' => 'AFGHANISTAN',
			'AL' => 'ALBANIA',
			'AM' => 'ARMENIA',
			'AU' => 'AUSTRALIA',
			'BA' => 'BOSNIA AND HERZEGOVINA',
			'BD' => 'BANGLADESH',
			'BH' => 'BAHRAIN',
			'BN' => 'BRUNEI',
			'BY' => 'BELARUS',
			'CI' => 'COTE DIVOIRE',
			'CN' => 'CHINA ',
			'CY' => 'CYPRUS',
			'DZ' => 'ALGERIA',
			'EG' => 'EGYPT',
			'FJ' => 'FIJI',
			'GH' => 'GHANA',
			'HK' => 'HONG KONG',
			'HR' => 'CROATIA',
			'ID' => 'INDONESIA',
			'IL' => 'ISRAEL',
			'IN' => 'INDIA',
			'IQ' => 'IRAQ',
			'IR' => 'IRAN (ISLAMIC REPUBLIC OF)',
			'JO' => 'JORDAN',
			'JP' => 'JAPAN',
			'KE' => 'KENYA',
			'KG' => 'KYRGYZSTAN',
			'KR' => 'KOREA',
			'KW' => 'KUWAIT',
			'KZ' => 'KAZAKHSTAN',
			'LA' => 'LAO PEOPLES DEMOCRATIC REPUBLIC',
			'LB' => 'LEBANON',
			'LK' => 'SRI LANKA',
			'MA' => 'MOROCCO',
			'MD' => 'MOLDOVA',
			'MK' => 'MACEDONIA',
			'MM' => 'MYANMAR',
			'MO' => 'MACAU',
			'MT' => 'MALTA',
			'MU' => 'MAURITIUS',
			'MY' => 'MALAYSIA',
			'NA' => 'NAMIBIA',
			'NG' => 'NIGERIA',
			'NP' => 'NEPAL',
			'NZ' => 'NEW ZEALAND',
			'OM' => 'OMAN',
			'PH' => 'PHILIPPINES',
			'PK' => 'PAKISTAN',
			'QA' => 'QATAR',
			'RE' => 'REUNION',
			'RS' => 'SERBIA',
			'RU' => 'RUSSIAN FEDERATION',
			'SA' => 'SAUDI ARABIA',
			'SD' => 'SUDAN',
			'SG' => 'SINGAPORE',
			'SN' => 'SENEGAL                            ',
			'SY' => 'SYRIA                             ',
			'TH' => 'THAILAND                          ',
			'TJ' => 'TAJIKISTAN                        ',
			'TR' => 'TURKEY                            ',
			'TW' => 'TAIWAN                            ',
			'UA' => 'UKRAINE                           ',
			'UZ' => 'UZBEKISTAN                        ',
			'VN' => 'VIETNAM                           ',
			'YE' => 'YEMEN ',
			'ZA' => 'SOUTH AFRICA                      ',
		);
		$ea = array(
			'AT' => 'AUSTRIA                           ',
			'BE' => 'BELGIUM                            ',
			'BG' => 'BULGARIA                          ',
			'CH' => 'SWITZERLAND                       ',
			'CZ' => 'CZECH  ',
			'DE' => 'GERMANY                           ',
			'DK' => 'DENMARK                           ',
			'EE' => 'ESTONIA                            ',
			'ES' => 'SPAIN                             ',
			'FI' => 'FINLAND                           ',
			'FR' => 'FRANCE                            ',
			'GB' => 'UNITED KINGDOM                    ',
			'GR' => 'GREECE                            ',
			'HU' => 'HUNGARY                           ',
			'IE' => 'IRELAND ',
			'IS' => 'ICELAND                           ',
			'IT' => 'ITALY                             ',
			'LT' => 'LITHUANIA                         ',
			'LU' => 'LUXEMBOURG                        ',
			'LV' => 'LATVIA                            ',
			'NL' => 'NETHERLANDS ',
			'NO' => 'NORWAY                            ',
			'PL' => 'POLAND                            ',
			'PT' => 'PORTUGAL                          ',
			'RO' => 'ROMANIA                           ',
			'SE' => 'SWEDEN                            ',
			'SI' => 'SLOVENIA                          ',
			'SK' => 'SLOVAKIA                          ',
		);
		$am = array(
			'AG' => 'ANTIGUA                            ',
			'AI' => 'ANGUILLA                           ',
			'AR' => 'ARGENTINA                          ',
			'AW' => 'ARUBA                              ',
			'BB' => 'BARBADOS',
			'BM' => 'BERMUDA                            ',
			'BO' => 'BOLIVIA                            ',
			'BR' => 'BRAZIL                             ',
			'BS' => 'BAHAMAS                            ',
			'CA' => 'CANADA                             ',
			'CL' => 'CHILE                              ',
			'CO' => 'COLOMBIA                           ',
			'CR' => 'COSTA RICA                         ',
			'DM' => 'DOMINICA                           ',
			'DO' => 'DOMINICAN REPUBLIC                 ',
			'EC' => 'ECUADOR                            ',
			'GD' => 'GRENADA                            ',
			'GF' => 'FRENCH GUYANA                      ',
			'GP' => 'GUADELOUPE                         ',
			'GT' => 'GUATEMALA                          ',
			'GU' => 'GUAM                               ',
			'GY' => 'GUYANA (BRITISH)                   ',
			'HN' => 'HONDURAS                           ',
			'HT' => 'HAITI                              ',
			'JM' => 'JAMAICA                            ',
			'KN' => 'ST. KITTS                          ',
			'KY' => 'CAYMAN ISLANDS                     ',
			'LC' => 'ST. LUCIA                          ',
			'MQ' => 'MARTINIQUE                         ',
			'MX' => 'MEXICO                             ',
			'NI' => 'NICARAGUA                          ',
			'PA' => 'PANAMA                             ',
			'PE' => 'PERU                               ',
			'PR' => 'PUERTO RICO                        ',
			'PY' => 'PARAGUAY                           ',
			'SR' => 'SURINAME                           ',
			'SV' => 'EL SALVADOR                        ',
			'TC' => 'TURKS AND CAICOS ISLANDS           ',
			'TT' => 'TRINIDAD AND TOBAGO                ',
			'US' => 'UNITED STATES OF AMERICA   ',
			'UY' => 'URUGUAY                            ',
			'VC' => 'ST. VINCENT                        ',
			'VE' => 'VENEZUELA                          ',
			'VG' => 'VIRGIN ISLANDS (BRITISH)           ',
			'XC' => 'CURACAO                            ',
			'XM' => 'ST. MAARTEN                        ',
			'XN' => 'NEVIS                              ',
			'XY' => 'ST. BARTHELEMY                     ',
		);

		if (array_key_exists($this->oriCountryId, $ap))
			$this->regionType = 'AP';
		elseif (array_key_exists($this->oriCountryId, $ea))
			$this->regionType = 'EA';
		elseif (array_key_exists($this->oriCountryId, $am))
			$this->regionType = 'US';
		else
			$this->regionType = 'KOSONG';

		return $this;
	}

	public function xmlType()
	{
		$this->contentShipmentValidation = "
		<Request>
			<ServiceHeader>
				<MessageTime>$this->time</MessageTime>
				<MessageReference>$this->mref</MessageReference>
				<SiteID>$this->siteId</SiteID>
				<Password>$this->password</Password>
			</ServiceHeader>
		</Request>
		<LanguageCode>en</LanguageCode>
		<PiecesEnabled>Y</PiecesEnabled>
		<Billing>
			<ShipperAccountNumber>$this->paymentAccountNumber</ShipperAccountNumber>
			<ShippingPaymentType>$this->shippingPaymentType</ShippingPaymentType>	
			<BillingAccountNumber>$this->paymentAccountNumber</BillingAccountNumber>
			<DutyPaymentType>$this->dutyPaymentType</DutyPaymentType>
		</Billing>
		<Consignee>
			<CompanyName>$this->conFullname</CompanyName>
			<AddressLine>$this->conAddressLine1</AddressLine>
			<AddressLine>$this->conAddressLine2</AddressLine>
			<City>$this->conCity</City>
			<PostalCode>$this->conPostcode</PostalCode>
			<CountryCode>$this->conCountryId</CountryCode>
			<CountryName>$this->conCountry</CountryName>
			<Contact>
				<PersonName>$this->conPersonName</PersonName>
				<PhoneNumber>$this->conTelp</PhoneNumber>
				<PhoneExtension>44444</PhoneExtension>
				<FaxNumber>444444444</FaxNumber>
				<Telex>44444444444</Telex>
				<Email>
					<From>$this->conEmailFrom</From>
					<To>$this->conEmailTo</To>
					<cc>testcc1</cc>
					<cc>testcc2</cc>
					<Subject>test email</Subject>
					<ReplyTo>test@dhl.com</ReplyTo>
					<Body>this is test shipment</Body>
				</Email>
			</Contact>
		</Consignee>
		<Commodity>
			<CommodityCode>1</CommodityCode>
			<CommodityName>String</CommodityName>
		</Commodity>
		<Dutiable>
			<DeclaredValue>10.00</DeclaredValue>
			<DeclaredCurrency>USD</DeclaredCurrency>
			<ShipperEIN>Text</ShipperEIN>
		</Dutiable>
		<Reference>
			<ReferenceID>$this->orderId</ReferenceID>
			<ReferenceType>St</ReferenceType>
		</Reference>
		<ShipmentDetails>
			<NumberOfPieces>1</NumberOfPieces>
			<CurrencyCode>USD</CurrencyCode>
			<Pieces>
							<Piece>
								<PieceID>1</PieceID>
								<PackageType>EE</PackageType>
								<Weight>$this->pieceWeight</Weight>
							</Piece>
			</Pieces>
			<PackageType>CP</PackageType>
			<Weight>$this->totalWeight</Weight>
			<DimensionUnit>C</DimensionUnit>
			<WeightUnit>K</WeightUnit>
			<GlobalProductCode>$this->globalproductcode</GlobalProductCode>
			<LocalProductCode>$this->localproductcode</LocalProductCode>
			<DoorTo>DD</DoorTo>
			<Date>$this->timeNow</Date>
			<Contents>$this->contentMessage</Contents>
			<IsDutiable>Y</IsDutiable>
			<InsuredAmount>3000.10</InsuredAmount>
		</ShipmentDetails>
		<Shipper>
			<ShipperID>967080215</ShipperID>
			<CompanyName>$this->oriStoreName</CompanyName>
			<AddressLine>$this->oriStreetLine1</AddressLine>
			<AddressLine>$this->oriStreetLine2</AddressLine>
			<City>$this->oriCity</City>
			<PostalCode>$this->oriPostcode</PostalCode>
			<CountryCode>$this->oriCountryId</CountryCode>
			<CountryName>$this->oriCountry</CountryName>
			<Contact>
				<PersonName>$this->oriOwnerName</PersonName>
				<PhoneNumber>$this->oriPhone</PhoneNumber>
				<PhoneExtension>2222</PhoneExtension>
				<FaxNumber>2222222222</FaxNumber>
				<Telex>2222222222</Telex>
				<Email>
					<From>$this->oriEmailFrom</From>
					<To>$this->oriEmailTo</To>
					<cc>CC</cc>
					<cc>CC</cc>
					<Subject>Subject</Subject>
					<ReplyTo>ReplayTo</ReplyTo>
					<Body>Body</Body>
				</Email>
			</Contact>
		</Shipper>";

		$apShipmentValidation = <<<SCRIPT
<?xml version="1.0" encoding="UTF-8"?>
	<req:ShipmentValidateRequestAP xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com
	ship-val-req_AP.xsd">
		$this->contentShipmentValidation
		<!--SpecialService>
			<SpecialServiceType>S</SpecialServiceType>
			<ChargeValue>3.1</ChargeValue>
			<CurrencyCode>USD</CurrencyCode>
		</SpecialService-->
	</req:ShipmentValidateRequestAP>
SCRIPT;

		$eaShipmentValidation = <<<SCRIPT
<?xml version="1.0" encoding="UTF-8"?>
<req:ShipmentValidateRequestEA xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com ship-val-req_EA.xsd">
  $this->contentShipmentValidation
<NewShipper>Y</NewShipper>
</req:ShipmentValidateRequestEA>
SCRIPT;

		$usShipmentValidation = <<<SCRIPT
<?xml version="1.0" encoding="UTF-8"?>		
<req:ShipmentValidateRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com ship-val-req.xsd">
  $this->contentShipmentValidation
  		 <RequestedPickupTime>Y</RequestedPickupTime>
		<NewShipper>Y</NewShipper>
		<DutyAccountNumber>962730810</DutyAccountNumber>
		<ExportDeclaration>
			<InterConsignee>String</InterConsignee>
			<IsPartiesRelation>N</IsPartiesRelation>
			<ECCN>EAR99</ECCN>
			<SignatureName>String</SignatureName>
			<SignatureTitle>String</SignatureTitle>
			<ExportReason>S</ExportReason>
			<ExportReasonCode>P</ExportReasonCode>
			<SedNumber>FTSR</SedNumber>
			<SedNumberType>F</SedNumberType>
			<MxStateCode>St</MxStateCode>
			<ExportLineItem>
				<LineNumber>200</LineNumber>
				<Quantity>32</Quantity>
				<QuantityUnit>String</QuantityUnit>
				<Description>String</Description>
				<Value>200</Value>
				<IsDomestic>Y</IsDomestic>
				<ScheduleB>3002905110</ScheduleB>
				<ECCN>EAR99</ECCN>
				<Weight>
					<Weight>0.5</Weight>
					<WeightUnit>L</WeightUnit>
				</Weight>
				<License>
					<LicenseNumber>D123456</LicenseNumber>
					<ExpiryDate>2120-08-10</ExpiryDate>
				</License>
				<LicenseSymbol>String</LicenseSymbol>
			</ExportLineItem>
		</ExportDeclaration>
		<PieceID>NA</PieceID>
						<PackageType>EE</PackageType>
						<DoorTo>DD</DoorTo>
			<DimensionUnit>C</DimensionUnit>
			<InsuredAmount>3.21</InsuredAmount>
			<PackageType>EE</PackageType>
</req:ShipmentValidateRequest>
SCRIPT;

//============ BOOKING PICKUP XML =====================
		$this->contentBookingPickup =
				"
<Request>
        <ServiceHeader>
				<MessageTime>$this->time</MessageTime>
				<MessageReference>$this->mref</MessageReference>
            <SiteID>$this->siteId</SiteID>
				<Password>$this->password</Password>
        </ServiceHeader>
    </Request>
    <Requestor>
        <AccountType>$this->accountType</AccountType>
        <AccountNumber>$this->paymentAccountNumber</AccountNumber>
        <RequestorContact>
            <PersonName>$this->oriStoreName</PersonName>
            <Phone>$this->phoneExtention</Phone>
            <PhoneExtension>$this->phoneExtention</PhoneExtension>
        </RequestorContact>
    </Requestor>
    <Place>
			<LocationType>B</LocationType>
			<CompanyName>$this->oriStoreName</CompanyName>
			<Address1>$this->oriStreetLine1</Address1>
			<Address2>$this->oriStreetLine2</Address2>
			<PackageLocation>$this->oriCity</PackageLocation>
			<City>$this->oriCity</City>
			<DivisionName>$this->oriCity</DivisionName>
			<CountryCode>$this->oriCountryId</CountryCode>
			<PostalCode>$this->oriPostcode </PostalCode>
    </Place>
    <Pickup>
        <PickupDate>$this->timeNow</PickupDate>
        <ReadyByTime>$this->readyByTime</ReadyByTime>
        <CloseTime>$this->closeTime</CloseTime>
        <Pieces>1</Pieces>
        <weight>
            <Weight>$this->pieceWeight</Weight>
            <WeightUnit>K</WeightUnit>
        </weight>
    </Pickup>
    <PickupContact>
        <PersonName>$this->pickupContactName</PersonName>
        <Phone>$this->pickupContactPhone</Phone>
        <PhoneExtension>$this->pickupContactPhoneExtention</PhoneExtension>
    </PickupContact>
     <ShipmentDetails>
        <AccountType>$this->accountType</AccountType>
        <AccountNumber>$this->paymentAccountNumber</AccountNumber>
        <BillToAccountNumber>$this->paymentAccountNumber</BillToAccountNumber>
        <AWBNumber>8145089842</AWBNumber>
        <NumberOfPieces>1</NumberOfPieces>
        <Weight>$this->totalWeight</Weight>
        <WeightUnit>K</WeightUnit>
        <GlobalProductCode>$this->globalProductCode</GlobalProductCode>
        <DoorTo>$this->doorTo</DoorTo>
        <DimensionUnit>C</DimensionUnit>
        <InsuredAmount>999999.99</InsuredAmount>
        <InsuredCurrencyCode>USD</InsuredCurrencyCode>
        <Pieces>
            <Weight>$this->pieceWeight</Weight>
        </Pieces>
        <SpecialService>S</SpecialService>
        <SpecialService>T</SpecialService>
    </ShipmentDetails>
";

		$apBookingPickup = <<<SCRIPT
<?xml version="1.0" encoding="UTF-8"?>		
<req:BookPickupRequestAP xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com book-pickup-req.xsd">
  $this->contentBookingPickup
</req:BookPickupRequestAP>
SCRIPT;

		$eaBookingPickup = <<<SCRIPT
<?xml version="1.0" encoding="UTF-8"?>		
<req:BookPickupRequestEA xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com book-pickup-req_EA.xsd">
  $this->contentBookingPickup
</req:BookPickupRequestEA>
SCRIPT;

		$usBookingPickup = <<<SCRIPT
<?xml version="1.0" encoding="UTF-8"?>		
<req:BookPickupRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com book-pickup-req.xsd">
  $this->contentBookingPickup
</req:BookPickupRequest>
SCRIPT;

		if ($this->xmlRequestType == 'shipmentvalidation')
		{
			switch ($this->regionType)
			{
				case "US":
					$this->xmlRequest = $usShipmentValidation;
					break;
				case "EA":
					$this->xmlRequest = $eaShipmentValidation;
					break;
				case "AP":
					$this->xmlRequest = $apShipmentValidation;
					break;
			}
		}
		elseif ($this->xmlRequestType == 'bookingpickup')
		{
			switch ($this->regionType)
			{
				case "US":
					$this->xmlRequest = $usBookingPickup;
					break;
				case "EA":
					$this->xmlRequest = $eaBookingPickup;
					break;
				case "AP":
					$this->xmlRequest = $apBookingPickup;
					break;
			}
		}
		return $this;
	}

	public function xmlGenarate()
	{
		$getUrl = new Dhl_Dhlshipment_Model_Carrier_Dhlshipment();
		$client = new Zend_Http_Client();
		$client->setUri($getUrl->getConfigXml('gateway_url'));
		$response = $client->setRawData($this->xmlRequest, 'text/xml')->request('POST');
		if ($response->isSuccessful())
		{
			$this->xmlResponse = $response->getBody();
		}

		$xmlResponse = simplexml_load_string($this->xmlResponse);

		if ($xmlResponse->Note->ActionNote == 'Success')
		{
			return $this;
		}
		else
		{
			echo $xmlResponse->Response->Status->Condition->ConditionData;
			exit;
		}
	}

	public function getResponse()
	{
		return $this->xmlResponse;
	}
}
