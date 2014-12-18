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

class MSP_Bancasella_Model_Adminhtml_System_Config_Source_Uic
{
	protected $_options;

    public function toOptionArray()
    {
    	if (!$this->_options)
    	{
			$this->_options = array(
				array(
					'label' => Mage::helper('msp_bancasella')->__('USD'),
					'value' => 1,
				), array(
					'label' => Mage::helper('msp_bancasella')->__('GBP'),
					'value' => 2,
				), array(
					'label' => Mage::helper('msp_bancasella')->__('CHF'),
					'value' => 3,
				), array(
					'label' => Mage::helper('msp_bancasella')->__('DKK'),
					'value' => 7,
				), array(
					'label' => Mage::helper('msp_bancasella')->__('NOK'),
					'value' => 8,
				), array(
					'label' => Mage::helper('msp_bancasella')->__('SEK'),
					'value' => 9,
				), array(
					'label' => Mage::helper('msp_bancasella')->__('CAD'),
					'value' => 12,
				), array(
					'label' => Mage::helper('msp_bancasella')->__('ITL'),
					'value' => 18,
				), array(
					'label' => Mage::helper('msp_bancasella')->__('JPY'),
					'value' => 71,
				), array(
					'label' => Mage::helper('msp_bancasella')->__('HKD'),
					'value' => 103,
				), array(
					'label' => Mage::helper('msp_bancasella')->__('BRL'),
					'value' => 234,
				), array(
					'label' => Mage::helper('msp_bancasella')->__('EUR'),
					'value' => 242,
				),
			);
    	}

		return $this->_options;
    }
}