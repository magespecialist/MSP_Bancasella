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

require_once(BP.DS.'app'.DS.'code'.DS.'community'.DS.'MSP'.DS.'Bancasella'.DS.'lib'.DS.'nusoap.php');
class MSP_Bancasella_Model_Api
{
	protected $_helper = null;

	/**
	 * Return bancasella helper
	 * @return MSP_Bancasella_Helper_Data
	 */
	protected function _getBancasellaHelper()
	{
		if (!$this->_helper)
			$this->_helper = Mage::helper('msp_bancasella');

		return $this->_helper;
	}

	/**
	 * Return order array params
	 * @param Mage_Sales_Model_Order $order
	 * @return array
	 */
	protected function _getOrderParams(Mage_Sales_Model_Order $order)
	{
		$helper = $this->_getBancasellaHelper();

		$return = array(
			'shopLogin' => $helper->getShopLogin(),
			'uicCode' => $helper->getUic(),
			'amount' => number_format($order->getGrandTotal(), 2, '.', ''),
			'shopTransactionId' => $helper->addOrderPrefix($order->getIncrementId()),
		);

		if ($helper->getFieldBuyerEmail())
			$return['buyerEmail'] = $order->getCustomerEmail();

		if ($helper->getFieldBuyerName())
			$return['buyerName'] = $order->getCustomerName();

		if ($helper->getFieldLanguage())
			$return['languageId'] = $helper->getLanguage();

		return $return;
	}

	/**
	 * Get server-to-server url
	 * @param Mage_Sales_Model_Order $order
	 * @return true
	 */
	protected function _getEncryptedString(Mage_Sales_Model_Order $order)
	{
		$wsdl = $this->_getBancasellaHelper()->getS2sUrl();
		$client = new nusoap_client($wsdl, true);

		$params = $this->_getOrderParams($order);

		$objectresult = $client->call('Encrypt', $params);

		$err = $client->getError();
		if ($err)
		{
			Mage::throwException(Mage::helper('msp_bancasella')->__('Bancasella Webservice Error: %s', $err));
			return null;
		}

		$errCode = $objectresult['EncryptResult']['GestPayCryptDecrypt']['ErrorCode'];
		if ($errCode != '0')
			Mage::throwException(Mage::helper('msp_bancasella')->__('Bancasella Webservice Error(%s): %s', $errCode, $objectresult['EncryptResult']['GestPayCryptDecrypt']['ErrorDescription']));

		return $objectresult['EncryptResult']['GestPayCryptDecrypt']['CryptDecryptString'];
	}

	/**
	 * Get decrypted string
	 * @param string $cryptedString
	 * @return object
	 */
	public function getDecryptedInformation($cryptedString)
	{
		$helper = $this->_getBancasellaHelper();

		$wsdl = $helper->getS2sUrl();
		$client = new nusoap_client($wsdl, true);

		$shopLogin = $helper->getShopLogin();
		$params = array(
			'shopLogin' => $shopLogin,
			'CryptedString' => $cryptedString
		);

		$objectResult = $client->call('Decrypt', $params);
		$err = $client->getError();
		if ($err) return null;

		return $objectResult['DecryptResult']['GestPayCryptDecrypt'];
	}

	/**
	 * Get Bancasella payment gateway URL
	 * @param Mage_Sales_Model_Order $order
	 * @return string
	 */
	public function getGatewayUrl(Mage_Sales_Model_Order $order)
	{
		$helper = $this->_getBancasellaHelper();
		return $helper->getGwUrl().'?a='.urlencode($helper->getShopLogin()).'&b='.urlencode($this->_getEncryptedString($order));
	}

	/**
	 * Handle listener message
	 * @param array $info
	 * @return MSP_Bancasella_Model_Api
	 */
	public function handleListenerMessage(array $info)
	{
		$helper = $this->_getBancasellaHelper();

		$incrementId = $helper->stripOrderPrefix($info['ShopTransactionID']);

		/* @var $order Mage_Sales_Model_Order */
		$order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);

		if (!$order->getId())
			return;

		$transactionUpdate = null;

		$state = $order->getState();
 		switch ($info['TransactionResult'])
		{
			case 'XX':
				$comment = 'Order on-hold for banck check verification';
				$state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
				break;

			case 'OK':
				$comment = 'Order authorized by BancaSella, auth code: '.$info['AuthorizationCode'].', tid: '.$info['BankTransactionID'];
				$state = Mage_Sales_Model_Order::STATE_PROCESSING;
				$transactionUpdate = Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE;
				break;

			case 'KO':
				$comment = 'Order denied by BancaSella: ['.$info['ErrorCode'].'] '.$info['ErrorDescription'];
				$state = Mage_Sales_Model_Order::STATE_CANCELED;
				$transactionUpdate = Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID;
				break;
		}

		if (!$comment) return;

		if ($transactionUpdate)
		{
			$payment = $order->getPayment();

			$payment->setTransactionId($order->getIncrementId().'-'.$transactionUpdate);
			$transaction = $payment->addTransaction($transactionUpdate, null, false, $comment);
			$transaction->save();
		}

		if ($state == Mage_Sales_Model_Order::STATE_PROCESSING)
		{
			$order->sendNewOrderEmail();
		}

		$oldState = $order->getState();

		$customerNotification = false;
		switch ($state)
		{
			case Mage_Sales_Model_Order::STATE_CANCELED:
				$customerNotification = true;
				$order->cancel();
				break;
		}

		$order->addStatusToHistory($state, Mage::helper('msp_bancasella')->__('BancaSella Status: %s', $comment));
		if (($oldState != $state) && $customerNotification) $order->sendOrderUpdateEmail(true);
		$order->save();

		return $this;
	}
}