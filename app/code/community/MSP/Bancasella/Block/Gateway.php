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

class MSP_Bancasella_Block_Gateway extends Mage_Core_Block_Template
{
	/**
	 * Return redirect URL
	 *
	 * @return string
	 */
	public function getRedirectUrl()
	{
		$session = Mage::getSingleton('checkout/session');
		$order = Mage::getModel('sales/order');
		$order->loadByIncrementId($session->getLastRealOrderId());

		return Mage::getSingleton('msp_bancasella/api')->getGatewayUrl($order);
	}
}