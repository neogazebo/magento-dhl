<?php

/*
 * Ambikuk
 * ambikuk@gmail.com
 * technolyze.net
 */

abstract class Dhl_Dhlshipment_Model_Carrier_Abstract extends Mage_Shipping_Model_Carrier_Abstract
{
	protected $_code = '';

	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
		if (!$this->getConfigFlag('active'))
		{
			return false;
		}

		$result = Mage::getModel('shipping/rate_result');

		if ($request->getAllItems())
		{
			foreach ($request->getAllItems() as $item)
			{
				$weight += ($item->getWeight() * $item->getQty());
			}
		}

		$originSetting = Mage::getStoreConfig('shipping');
		$customerAddressId = Mage::getSingleton('customer/session')->getCustomer()->getShipping();

		if ($customerAddressId)
		{
			$address = Mage::getModel('customer/address')->load($customerAddressId);
			$Quote['countryTo'] = $address->country_id;
			$Quote['postCodeTo'] = $address->postcode;
			$Quote['cityTo'] = $address->city;
		}
		else
		{
			$address = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
			$Quote['postCodeTo'] = $address['postcode'];
			$Quote['countryTo'] = $address['country_id'];
			$Quote['cityTo'] = $address['city'];
		}
		if ($Quote['countryTo'] == null || $Quote['countryTo'] == '')
		{
			$Quote['postCodeTo'] = $request->getDestPostcode();
			$Quote['countryTo'] = $request->getDestCountryId();
			$Quote['cityTo'] = $request->getDestCity();
		}

		$Quote['countryCode'] = $originSetting['origin']['country_id'];
		$Quote['postcode'] = $originSetting['origin']['postcode'];
		$Quote['city'] = $originSetting['origin']['city'];

		$Quote['weight'] = ceil($weight);

		require_once dirname(__FILE__) . '/CDhlToolKit.php';

		$quote = new CQuote($Quote);
		$response = $quote->send();

		if (isset($response))
		{
			$valueApi = CDhlToolKit::request()->render($response);
		}

		foreach ($valueApi as $api)
		{
			$method = Mage::getModel('shipping/rate_result_method');
			$method->setCarrier($this->_code);
			$method->setCarrierTitle($this->_code);
			$method->setMethod($api['name']);
			$method->setMethodTitle($api['name']);
			if ($this->getConfigData('handling_fee') != null || $this->getConfigData('handling_fee') != 0)
				$price = $api['price'] + $this->getConfigData('handling_fee');
			else
				$price = $api['price'];
			$method->setPrice($price);
			$result->append($method);
		}
		if ($valueApi['status'] == 'error')
		{
			$error = Mage::getModel('shipping/rate_result_error');
			$error->setCarrier($this->_code);
			$error->setCarrierTitle($this->getConfigData('title'));
			$error->setErrorMessage($errorTitle);
			$error->setErrorMessage($valueApi['message']);
			return $error;
		}

		return $result;
	}

	public function getAllowedMethods()
	{
		return array($this->_code => $this->getConfigData('name'));
	}
}
