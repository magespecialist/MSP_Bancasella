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

class MSP_Bancasella_Model_Gateway extends Mage_Payment_Model_Method_Abstract
{
	protected $_code = 'msp_bancasella_gateway';
	protected $_formBlockType = 'msp_bancasella/gateway_form';

	protected $_isGateway = true;
	protected $_canAuthorize = true;
	protected $_canCapture = false;
	protected $_canCapturePartial = false;
	protected $_canRefund = false;
	protected $_canRefundInvoicePartial = false;
	protected $_canVoid = false;
	protected $_canUseInternal = false;
	protected $_canUseCheckout = true;
	protected $_canUseForMultishipping = true;

	public function getOrderPlaceRedirectUrl()
	{
		return Mage::getUrl('msp_bancasella/gateway/index');
	}

    public function processInvoice($invoice, $payment)
    {
        $invoice->setTransactionId($payment->getOrder()->getIncrementId());
        $payment->setIsTransactionClosed(1);
        return $this;
    }

    public function processBeforeRefund($invoice, $payment)
    {
        $payment->setRefundTransactionId($payment->getOrder()->getIncrementId());
        return $this;
    }
}
