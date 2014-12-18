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

class MSP_Bancasella_GatewayController extends Mage_Core_Controller_Front_Action
{
	protected $_orderId = null;
	protected $_orderInfo = array();

	/**
	 * Get checkout session
	 * @return Mage_Checkout_Model_Session
	 */
	protected function _getCheckoutSession()
	{
		return Mage::getSingleton('checkout/session');
	}

	/**
	 * Get one page checkout model
	 * @return Mage_Checkout_Model_Type_Onepage
	 */
	public function getOnepage()
	{
		return Mage::getSingleton('checkout/type_onepage');
	}

	/**
	 * Prepare response action
	 * @return void
	 */
	protected function _prepareResponseAction()
	{
		$helper = Mage::helper('msp_bancasella');

		$this->_orderId = null;
		$this->_orderInfo = array();

		$shopLogin = $this->getRequest()->getParam('a');
		$cryptedString = $this->getRequest()->getParam('b');

		$api = Mage::getSingleton('msp_bancasella/api');

		$this->_orderInfo = $api->getDecryptedInformation($cryptedString);
		if (!$this->_orderInfo)
			return;

		$order = Mage::getModel('sales/order')->loadByIncrementId($helper->stripOrderPrefix($this->_orderInfo['ShopTransactionID']));
		if (!$order->getId())
			return;

		// Trick for redirecting to original website in case of multidomain
		if ($order->getStore()->getWebsiteId() != Mage::app()->getStore()->getWebsiteId())
		{
			$queryString = http_build_query(array(
				'a' => $shopLogin,
				'b' => $cryptedString,
			));

			$url = Mage::app()->getStore($order->getStore()->getId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK)
				.'msp_bancasella/gateway/'.$this->getRequest()->getActionName().'?'.$queryString;

			header('Location: '.$url);
			exit;
		}

		$this->_getCheckoutSession()->clear();

		$this->_orderId = $order->getId();
	}

	public function indexAction()
	{
		$session = $this->_getCheckoutSession();

		$order = Mage::getModel('sales/order');
		$order->loadByIncrementId($session->getLastRealOrderId());
		$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_HOLDED, Mage::helper('msp_bancasella')->__('Customer was redirected to Bancasella.'));
		$order->save();

		$this->loadLayout();
		$this->renderLayout();

		$session->unsQuoteId();
	}

	public function successAction()
	{
		$this->_prepareResponseAction();
		if (!$this->_orderId)
		{
			$this->_redirect('checkout/cart');
			return;
		}

		$this->loadLayout();
		$this->_initLayoutMessages('checkout/session');
		Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($this->_orderId)));
		$this->renderLayout();
	}

	public function failureAction()
	{
		$this->_prepareResponseAction();
		if (!$this->_orderId)
		{
			$this->_redirect('checkout/cart');
			return;
		}

		$this->loadLayout();
		$this->renderLayout();
	}
}
