<?php
/**
 * Meanbee_SmartAddress
 *
 * This module was developed by Meanbee Internet Solutions Limited.  If you require any
 * support or have any questions please contact us at support@meanbee.com.
 *
 * @category   Meanbee
 * @package    Meanbee_SmartAddress
 * @author     Meanbee Internet Solutions Limited <support@meanbee.com>
 * @copyright  Copyright (c) 2010 Meanbee Internet Solutions Limited  (http://www.meanbee.com)
 * @license    Single Site License, requiring consent from Meanbee Internet Solutions Limited
 */
class Meanbee_SmartAddress_Block_Finder extends Mage_Core_Block_Template {
	public function _construct(){
		parent::_construct();
		$this->setTemplate('postcode/finder.phtml');
	}
	
	public function finder() {
		echo "<div id='meanbee:address_status'><small>Finding addresses..</small></div>";
	}
}
