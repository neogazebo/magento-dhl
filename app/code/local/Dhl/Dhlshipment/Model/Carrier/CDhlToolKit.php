<?php

/*
 * Ambikuk
 * ambikuk@gmail.com
 * technolyze.net
 */

require_once 'CQuote.php';

class CDhlToolKit
{
	public $url = '';
	public $response = '';

	public function __construct($config = array())
	{
		if (count($config > 0))
		{
			foreach ($config as $key => $value)
			{
				$ths->$key = $value;
			}
		}
		$this->init();
	}

	public function init()
	{
		
	}

	public static function request($url = '')
	{
		$data = new self;
		if ($url != '')
		{
			$data->url = $url;
		}
		return $data;
	}

	public function sendXmlOverPost($xml)
	{
//		var_dump($xml);exit;
		$getUrl = new Dhl_Dhlshipment_Model_Carrier_Dhlshipment();
		$client = new Zend_Http_Client();
		$client->setUri($getUrl->getConfigXml('gateway_url'));
		$response = $client->setRawData($xml, 'text/xml')->request('POST');
		if ($response->isSuccessful())
		{
//			var_dump($response->getBody());exit;
			return $response->getBody();
		}
	}

	public function xmlToArray($input, $callback = null, $recurse = false)
	{
		$data = ((!$recurse) && is_string($input)) ? simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA) : $input;
		if ($data instanceof SimpleXMLElement)
			$data = (array) $data;
		if (is_array($data))
			foreach ($data as &$item)
				$item = $this->xmlToArray($item, $callback, true);
		return (!is_array($data) && is_callable($callback)) ? call_user_func($callback, $data) : $data;
	}

	public function getCallback($response)
	{
		return $this->xmlToArray($response);
	}

	public function country()
	{
		$path = 'continentiso.csv';
		$row = 1;
		if (($handle = fopen("{$path}", "r")) !== FALSE)
		{
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
			{
				$num = count($data);
				$row++;
				$region[$data[0]][$data[2]] = $data[1];
			}
			fclose($handle);
			return $region;
		}
	}

	public function calculateUsd($currency)
	{
		//get used currency 
		$countryCurrency = Mage::app()->getStore()->getCurrentCurrencyCode();
		//get symbol currency
		$confCurrency = Mage::app()->getLocale()->currency($countryCurrency)->getSymbol();

		$allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
		if ($countryCurrency == 'USD')
		{
			return $currency;
		}
		else
		{
			if (in_array("USD", $allowedCurrencies))
			{
				$allCurrency = Mage::getModel('directory/currency')->getCurrencyRates($countryCurrency, array_values($allowedCurrencies));
				$perDollar = 1 / $allCurrency['USD'];
				$fixPrice = $currency * $perDollar;
				return $fixPrice;
			}
			else
			{
				echo "Please Set Your Currency";
				exit;
			}
		}
	}

	public function render($response, $config = array())
	{
		$xml = simplexml_load_string($response);
		$error = $xml->GetQuoteResponse->Note->Condition->ConditionData;
//		var_dump($error);exit;
		if ($xml->GetQuoteResponse->Note->Condition->ConditionData)
		{
			return array('message'=>$xml->GetQuoteResponse->Note->Condition->ConditionData,'status'=>error);
		}
		else
		{
			$getXml = new Dhl_Dhlshipment_Model_Carrier_Dhlshipment();
			$allow = explode(",", $getXml->getConfigXml('allowed_methods'));
			$rArray = array();
//			if ($getXml->getConfigXml('shipment_type') == 'M')
//			{
				$qtdShp = $xml->GetQuoteResponse->BkgDetails->QtdShp;

				foreach ($qtdShp as $qtd)
				{
					if (in_array($qtd->LocalProductCode, $allow))
					{
						$weightCharge = $qtd->WeightCharge;
					$tax = $qtd->WeightChargeTax;
					$ff=0;
					$rasc=0;
					$price = $qtd->ShippingCharge;
					$localProductName = $qtd->ProductShortName;
					
					for($countertemp=0;$countertemp<count($qtd->QtdShpExChrg);$countertemp++){
						if($qtd->QtdShpExChrg[$countertemp]->LocalServiceType=="FF"){
							//we found the Fuel surcharge value. update the costing
							$ff=$qtd->QtdShpExChrg[$countertemp]->ChargeValue;
						}
						if($qtd->QtdShpExChrg[$countertemp]->LocalServiceType=="REMOTE AREA SERVICE"){
							//we found the remote area charge value. update the costing
							$rasc=$qtd->QtdShpExChrg[$countertemp]->ChargeValue;
						}							
					}

					$title = $localProductName;

					if ($getXml->getConfigXml('show_payment') == 'B')
					{
						$title .= ' (Freight Charge:' . $weightCharge . ', Fuel Surcharge:' . $ff;

						if(intval($rasc) > 0)
							$title .= ', Remote Area Service Charge:' . $rasc;

						if (intval($getXml->getConfigXml('handling_fee')) > 0)
						{
							$title .= ', Additional Charge:' . $getXml->getConfigXml('handling_fee');
						}
						if ($getXml->getConfigXml('transit_time') == 1)
						{
							$transit = $qtd->TotalTransitDays + $getXml->getConfigXml('add_transit_day');
							$title .= ', Transit Day:' . $transit . ' Days';
						}
						$title .= ')';
					}
					else if ($getXml->getConfigXml('show_payment') == 'T')
					{
						$title = $localProductName;
					}
					array_push($rArray, array('name' => $title, 'price' => $price));
					}
				}
//			}
//			else if ($getXml->getConfigXml('payment_type') == 'P')
//			{
//				$qtdShp = $xml->GetQuoteResponse->BkgDetails->QtdShp;
//				foreach ($qtdShp as $qtd)
//				{
//					$weightCharge = $qtd->WeightCharge;
//					$tax = $qtd->WeightChargeTax;
//					$ff=0;
//					$rasc=0;
//					$price = $qtd->ShippingCharge;
//					$localProductName = $qtd->ProductShortName;
//					
//					for($countertemp=0;$countertemp<count($qtd->QtdShpExChrg);$countertemp++){
//						if($qtd->QtdShpExChrg[$countertemp]->LocalServiceType=="FF"){
//							//we found the Fuel surcharge value. update the costing
//							$ff=$qtd->QtdShpExChrg[$countertemp]->ChargeValue;
//						}
//						if($qtd->QtdShpExChrg[$countertemp]->LocalServiceType=="REMOTE AREA SERVICE"){
//							//we found the remote area charge value. update the costing
//							$rasc=$qtd->QtdShpExChrg[$countertemp]->ChargeValue;
//						}							
//					}
//					$title = $localProductName;
//
//					if ($getXml->getConfigXml('show_payment') == 'B')
//					{
//						$title .= ' (Freight Charge:' . $weightCharge . ', Fuel Surcharge:' . $ff;
//
//						if(intval($rasc) > 0)
//							$title .= ', Remote Area Service Charge:' . $rasc;
//
//						if (intval($getXml->getXmlHandlingFee()) > 0)
//						{
//							$title .= ', Additional Charge:' . $getXml->getConfigXml('handling_fee');
//						}
//						if ($getXml->getConfigXml('transit_time') == 1)
//						{
//							$transit = $qtd->TotalTransitDays + $getXml->getConfigXml('add_transit_day');
//							$title .= ', Transit Day:' . $transit . ' Days';
//						}
//						$title .= ')';
//					}
//					else if ($getXml->getConfigXml('show_payment') == 'T')
//					{
//						$title = $localProductName;
//					}
//					array_push($rArray, array('name' => $title, 'price' => $price));
//				}
//			}
		}
		return $rArray;
	}

	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
		
	}
}

function do_offset($level)
{
	$offset = ""; // offset for subarry 
	for ($i = 1; $i < $level; $i++)
	{
		$offset = $offset . "<td></td>";
	}
	return $offset;
}

function show_array($array, $level, $sub)
{
	if (is_array($array) == 1)
	{ // check if input is an array
		foreach ($array as $key_val => $value)
		{
			$offset = "";
			if (is_array($value) == 1)
			{ // array is multidimensional
				echo "<tr>";
				$offset = do_offset($level);
				echo $offset . "<td>" . $key_val . "</td>";
				show_array($value, $level + 1, 1);
			}
			else
			{ // (sub)array is not multidim
				if ($sub != 1)
				{ // first entry for subarray
					echo "<tr nosub>";
					$offset = do_offset($level);
				}
				$sub = 0;
				echo $offset . "<td main " . $sub . " width=\"120\">" . $key_val .
				"</td><td width=\"120\">" . $value . "</td>";
				echo "</tr>\n";
			}
		} //foreach $array
	}
	else
	{ // argument $array is not an array
		return;
	}
}

function html_show_array($array)
{
	echo "<table cellspacing=\"0\" border=\"2\">\n";
	show_array($array, 1, 0);
	echo "</table>\n";
}

?>
