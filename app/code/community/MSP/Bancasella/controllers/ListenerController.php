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

class MSP_Bancasella_ListenerController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Get checkout session
	 * @return Mage_Checkout_Model_Session
	 */
	protected function _getCheckoutSession()
	{
		return Mage::getSingleton('checkout/session');
	}

	public function indexAction()
	{
		$shopLogin = $this->getRequest()->getParam('a');
		$cryptedString = $this->getRequest()->getParam('b');

		$api = Mage::getSingleton('msp_bancasella/api');

		$info = $api->getDecryptedInformation($cryptedString);
		if (!$info) return;

		$api->handleListenerMessage($info);
	}
}
