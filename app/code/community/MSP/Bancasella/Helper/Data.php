<?php
/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@idealiagroup.com so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_Bancasella
 * @copyright  Copyright (c) 2013 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MSP_Bancasella_Helper_Data extends Mage_Core_Helper_Abstract
{
	const GW_URL = 'https://ecomm.sella.it/pagam/pagam.aspx';
	const GW_URL_TEST = 'https://testecomm.sella.it/pagam/pagam.aspx';

	const S2S_URL = 'https://ecomms2s.sella.it/gestpay/gestpayws/WSCryptDecrypt.asmx?WSDL';
	const S2S_URL_TEST = 'https://testecomm.sella.it/gestpay/gestpayws/WSCryptDecrypt.asmx?WSDL';

	const XML_PATH_ACCOUNT_TEST_MODE = 'msp_bancasella/account/test-mode';
	const XML_PATH_ACCOUNT_SHOP_LOGIN = 'msp_bancasella/account/merchant';
	const XML_PATH_ACCOUNT_CURRENCY_CODE = 'msp_bancasella/account/currency-code';
	const XML_PATH_ACCOUNT_LANGUAGE = 'msp_bancasella/account/language';
	const XML_PATH_ACCOUNT_ORDER_PREFIX = 'msp_bancasella/account/order-prefix';

	const XML_PATH_FIELD_BUYER_NAME = 'msp_bancasella/fields/buyer-name';
	const XML_PATH_FIELD_BUYER_EMAIL = 'msp_bancasella/fields/buyer-email';
	const XML_PATH_FIELD_LANGUAGE = 'msp_bancasella/fields/language';

	/**
	 * Return true on test mode
	 * @param
	 * @return bool
	 */
	public function getIsTest()
	{
		return (bool) Mage::getStoreConfig(self::XML_PATH_ACCOUNT_TEST_MODE);
	}

	/**
	 * Get ecomm URL
	 * @param
	 * @return string
	 */
	public function getGwUrl()
	{
		if ($this->getIsTest())
			return self::GW_URL_TEST;

		return self::GW_URL;
	}

	/**
	 * Get server-to-server URL
	 * @param
	 * @return string
	 */
	public function getS2sUrl()
	{
		if ($this->getIsTest())
			return self::S2S_URL_TEST;

		return self::S2S_URL;
	}

	/**
	 * Get merchant login
	 * @return string
	 */
	public function getShopLogin()
	{
		return Mage::getStoreConfig(self::XML_PATH_ACCOUNT_SHOP_LOGIN);
	}

	/**
	 * Get UIC
	 * @return int
	 */
	public function getUic()
	{
		return intval(Mage::getStoreConfig(self::XML_PATH_ACCOUNT_CURRENCY_CODE));
	}

	/**
	 * Get language
	 * @return int
	 */
	public function getLanguage()
	{
		return intval(Mage::getStoreConfig(self::XML_PATH_ACCOUNT_LANGUAGE));
	}

	/**
	 * Check whenever to send buyer name
	 * @return bool
	 */
	public function getFieldBuyerName()
	{
		return intval(Mage::getStoreConfig(self::XML_PATH_FIELD_BUYER_NAME));
	}

	/**
	 * Check whenever to send buyer email
	 * @return bool
	 */
	public function getFieldBuyerEmail()
	{
		return intval(Mage::getStoreConfig(self::XML_PATH_FIELD_BUYER_EMAIL));
	}

	/**
	 * Check whenever to send language
	 * @return bool
	 */
	public function getFieldLanguage()
	{
		return intval(Mage::getStoreConfig(self::XML_PATH_FIELD_LANGUAGE));
	}

	/**
	 * Get order prefix
	 * @return string
	 */
	public function getOrderPrefix()
	{
		return Mage::getStoreConfig(self::XML_PATH_ACCOUNT_ORDER_PREFIX);
	}

	/**
	 * Stripe order prefix from order number
	 * @param string $orderId
	 * @return string
	 */
	public function stripOrderPrefix($orderId)
	{
		$parts = explode('_', $orderId);
		return array_pop($parts);
	}

	/**
	 * Add order prefix from order number
	 * @param string $orderId
	 * @return string
	 */
	public function addOrderPrefix($orderId)
	{
		if ($this->getOrderPrefix())
			return $this->getOrderPrefix().'_'.$orderId;

		return $orderId;
	}
}
