<?php

/*
 * Ambikuk
 * ambikuk@gmail.com
 * technolyze.net
 */

class CQuote
{
	public $remoteAreaService = false;
	public $arrayResponse = array();
	public $siteId = 'APMediaM9';
	public $password = 'apwoEM27X';
	public $requestType;
	public $address;
	public $postcode;
	public $city;
	public $state;
	public $countryCode;
	public $countryName = '';
	public $paymentCountryCode = 'SG';
	public $paymentDate;
	public $dimensionUnit;
	public $weightUnit;
	public $xml;
	public $countryTo;
	public $postCodeTo;
	public $isDutiable;
	public $suburbTo;
	public $weight;
	public $paymentCountry;
	public $paymentAccountNumber;
	public $networkTypeCode;
	public $duitableFlag;
	public $duitableCurrency;
	public $globalProductCode;
	public $shipmentType;

	public function __construct($config = array())
	{
		if (count($config) > 0)
		{
			foreach ($config as $key => $value)
			{
				$this->$key = $value;
			}
		}
		$timestamp = time();
		$this->time = date('c', $timestamp);
		$data = array();
		for ($i = 0; $i < 31; $i++)
		{
			$data[$i] = rand(1, 9);
		}
		$this->mref = implode('', $data);
		$this->init();
	}

	public function init()
	{
		if ($this->isDutiable == 0)
		{
			$this->isDutiable = 'N';
		}
		if ($this->isDutiable == 1)
		{
			$this->isDutiable = 'Y';
		}
		if (empty($this->paymentDate))
		{
			$this->paymentDate = date("Y-m-d");
		}
	}

	public function send()
	{
		$getXml = new Dhl_Dhlshipment_Model_Carrier_Dhlshipment();
		$request = CDhlToolKit::request($getXml->getConfigXml('gateway_url'))->sendXmlOverPost($this->getXml());
		$this->arrayResponse = CDhlToolKit::request()->getCallBack($request);
		
		return $request;
	}

	public function getXml()
	{
		$getXml = new Dhl_Dhlshipment_Model_Carrier_Dhlshipment();
		$this->siteId = $getXml->getConfigXml('id');
		$this->password = $getXml->getConfigXml('password');
		$this->paymentCountry = $getXml->getConfigXml('payment_country');
		$this->weightUnit = $getXml->getConfigXml('weight_unit');
		$this->dimensionUnit = $getXml->getConfigXml('dimension_unit');
		$this->paymentAccountNumber = $getXml->getConfigXml('payment_account_number');
		$this->networkTypeCode = $getXml->getConfigXml('network_type_code');
		$this->duitableFlag = $getXml->getConfigXml('duitable_flag');
		$this->duitableCurrency = $getXml->getConfigXml('duitable_currency');
		$this->globalProductCode = $getXml->getConfigXml('global_product_code');
		$this->timeNow = date('Y-m-d');
		$this->readyTimeGmt = $getXml->getConfigXml('ready_time_gmt');
		$this->shipmentType = $getXml->getConfigXml('shipment_type');
		if ($this->shipmentType == 'P')
		{
			return $xml = <<<SCRIPT
﻿<?xml version="1.0" encoding="UTF-8"?>
<p:DCTRequest xmlns:p="http://www.dhl.com" xmlns:p1="http://www.dhl.com/datatypes" xmlns:p2="http://www.dhl.com/DCTRequestdatatypes" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com DCT-req.xsd ">
  <GetQuote>
    <Request>
      <ServiceHeader>
        <MessageTime>$this->time</MessageTime>
        <MessageReference>$this->mref</MessageReference>
        <SiteID>$this->siteId</SiteID>
        <Password>$this->password</Password>
      </ServiceHeader>
    </Request>
    <From>
      <CountryCode>$this->countryCode</CountryCode>
      <Postalcode>$this->postcode</Postalcode>
    </From>
    <BkgDetails>
      <PaymentCountryCode>$this->paymentCountry</PaymentCountryCode>
      <Date>$this->timeNow</Date>
      <ReadyTime>PT10H21M</ReadyTime>
      <ReadyTimeGMTOffset>$this->readyTimeGmt</ReadyTimeGMTOffset>
      <DimensionUnit>$this->dimensionUnit</DimensionUnit>
      <WeightUnit>$this->weightUnit</WeightUnit>
      <Pieces>
        <Piece>
          <PieceID>1</PieceID>
          <Weight>$this->weight</Weight>
        </Piece>
      </Pieces>
      <PaymentAccountNumber>$this->paymentAccountNumber</PaymentAccountNumber>
      <IsDutiable>$this->duitableFlag</IsDutiable>
      <NetworkTypeCode>$this->networkTypeCode</NetworkTypeCode>
      <QtdShp>
        <GlobalProductCode>$this->globalProductCode</GlobalProductCode>
      </QtdShp>
    </BkgDetails>
    <To>
      <CountryCode>$this->countryTo</CountryCode>
      <Postalcode>$this->postCodeTo</Postalcode>
      <City>$this->postCityTo</City>
    </To>
    <Dutiable>
      <DeclaredCurrency>$this->duitableCurrency</DeclaredCurrency>
      <DeclaredValue>0</DeclaredValue>
    </Dutiable>
  </GetQuote>
</p:DCTRequest>
SCRIPT;
		}
		else
		{
			$xml = <<<SCRIPT
<?xml version="1.0" encoding="UTF-8"?>
<p:DCTRequest xmlns:p="http://www.dhl.com" xmlns:p1="http://www.dhl.com/datatypes" xmlns:p2="http://www.dhl.com/DCTRequestdatatypes" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com DCT-req.xsd ">
  <GetQuote>
    <Request>
      <ServiceHeader>
        <MessageTime>$this->time</MessageTime>
        <MessageReference>$this->mref</MessageReference>
        <SiteID>$this->siteId</SiteID>
        <Password>$this->password</Password>
      </ServiceHeader>
    </Request>
    <From>
      <CountryCode>$this->countryCode</CountryCode>
      <Postalcode>$this->postcode</Postalcode>
    </From>
    <BkgDetails>
      <PaymentCountryCode>$this->countryCode</PaymentCountryCode>
      <Date>$this->paymentDate</Date>
      <ReadyTime>PT10H21M</ReadyTime>
      <ReadyTimeGMTOffset>+01:00</ReadyTimeGMTOffset>
      <DimensionUnit>$this->dimensionUnit</DimensionUnit>
      <WeightUnit>$this->weightUnit</WeightUnit>
      <Pieces>
        <Piece>
          <PieceID>1</PieceID>
          <Height>1</Height>
          <Depth>1</Depth>
          <Width>1</Width>
          <Weight>$this->weight</Weight>
        </Piece>
      </Pieces> 
      <IsDutiable>Y</IsDutiable>
      <NetworkTypeCode>AL</NetworkTypeCode>	
      <InsuredValue>0</InsuredValue>
      <InsuredCurrency>USD</InsuredCurrency>
    </BkgDetails>
    <To>
      <CountryCode>$this->countryTo</CountryCode>
      <Postalcode>$this->postCodeTo</Postalcode>
			<City>$this->postCityTo</City>
    </To>
   <Dutiable>
      <DeclaredCurrency>USD</DeclaredCurrency>
      <DeclaredValue>10.0</DeclaredValue>
    </Dutiable>
  </GetQuote>
</p:DCTRequest>
SCRIPT;
			return $xml;
		}
	}

	public function getErrorMessage()
	{
		if ($this->arrayResponse != array())
		{
			foreach ($this->arrayResponse['GetQuoteResponse']['Note'] as $key => $value)
			{
				if ($key = 'Condition')
					$return = $value;
			}
			return $return;
		}
	}

	public function errorTable()
	{

		$data = $this->getErrorMessage();
		$n = count($data);
		$return = '<table class="listTable"><thead><tr><th colspan="' . $n . '">Error Code :</th></tr></thead><tbody><tr>';
		foreach ($data as $key => $value)
		{
			$return.="<th>$key</th>";
		}
		$return.="</tr>";

		foreach ($this->getErrorMessage() as $key => $value)
		{
			$return.="<td>$value</td>";
		}
		$return.='</tbody></table>';

		return $return;
	}

	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
		
	}
}

?>
