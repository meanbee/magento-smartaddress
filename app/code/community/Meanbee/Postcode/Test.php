<?php
require_once '../../../../Mage.php';

Varien_Profiler::enable();
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);

umask(0);
Mage::app();

$obj = Mage::getModel('postcode/call');
//$obj->findAddresses('ba2 3hu');
//$obj->findSingleAddress('1147233.00');