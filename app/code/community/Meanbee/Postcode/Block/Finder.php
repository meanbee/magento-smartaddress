<?php
/**
 * Meanbee_Postcode
 *
 * This module was developed by Meanbee Internet Solutions.  If you require any
 * support or have any questions please contact us at support@meanbee.com.
 *
 * @category   Meanbee
 * @package    Meanbee_Postcode
 * @author     Meanbee Internet Solutions <support@meanbee.com>
 * @copyright  Copyright (c) 2009 Meanbee Internet Solutions (http://www.meanbee.com)
 * @license    Single Site License, requiring consent from Meanbee Internet Solutions
 */
class Meanbee_Postcode_Block_Finder extends Mage_Core_Block_Template {
	public function _construct(){
		parent::_construct();
		$this->setTemplate('postcode/finder.phtml');
	}
	
	public function finder() {
		echo "<div id='meanbee:address_status'><small>Finding addresses..</small></div>";
	}
}