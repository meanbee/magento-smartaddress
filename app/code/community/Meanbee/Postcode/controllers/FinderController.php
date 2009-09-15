<?php
/**
 * Meanbee_Postcode_US
 *
 * This module was developed by Meanbee Internet Solutions Limited.  If you require any
 * support or have any questions please contact us at support@meanbee.com.
 *
 * @category   Meanbee
 * @package    Meanbee_Postcode_US
 * @author     Meanbee Internet Solutions Limited <support@meanbee.com>
 * @copyright  Copyright (c) 2009 Meanbee Internet Solutions Limited (http://www.meanbee.com)
 * @license    Single Site License, requiring consent from Meanbee Internet Solutions Limited
 */
class Meanbee_Postcode_FinderController extends Mage_Core_Controller_Front_Action {
    public function preDispatch() {
        if (Mage::getStoreConfig('postcode/options/security') ) {
            if (isset($_SERVER['HTTP_REFERER'])) {                
                $us = Mage::getUrl();
                $us = substr($us, strpos($us, ':') + 3);
                
                preg_match('/https?:\/\/([a-zA-Z0-9\.]+\/)/i', $_SERVER['HTTP_REFERER'], $matches);
                if (count($matches) == 2) {
                    $them = $matches[1]; 
                }
                
                //echo $us . " - " . $them;
                
                if ($us != $them) {
                    echo Zend_Json::encode(array(
                        "error" => true,
                        "content" => "Security check failed.  Request identified as originating from '$them' need '$us'"
                    ));
                    exit;
                }
            } else {
                    echo Zend_Json::encode(array(
                        "error" => true,
                        "content" => "Security check failed.  Unable to identify referrer."
                    ));
                    exit;
            }
        }
    }
    
    public function multipleAction() {
        header("Content-type: application/json");
        
        //Retrieve fields
        $postcode = strtolower(preg_replace("/[^a-zA-Z0-9]/", "", str_replace(' ', '', $_GET['postcode'])));
        $building = isset($_GET['building']) ? $_GET['building'] : '';
        $country = $_GET['country'];

        // Aslong as have data we need, call actions
        if (!empty($postcode)) {
            if (!empty($country)) {
                $call = Mage::getModel('postcode/call');
                echo $call->findMultipleByPostcode($postcode, $building, $country);
            } else {
                echo Zend_Json::encode(array(
                    "error" => true,
                    "content" => "No country provided"
                ));
            }
        } else {
            echo Zend_Json::encode(array(
                "error" => true,
                "content" => "No postcode provided"
            ));
        }
        exit;
    }

    public function singleAction() {
        header("Content-type: application/json");

        if (isset($_GET['id'])) {
            if (isset($_GET['country'])) {
                $id = (int) $_GET['id'];
                $country = $_GET['country'];

                $call = Mage::getModel('postcode/call');
                echo $call->findSingleAddressById($id, $country);
                exit;
            } else {
                echo Zend_Json::encode(array(
                    "error" => true,
                    "content" => "No country provided"
                ));
            }
        } else {
            echo Zend_Json::encode(array(
                "error" => true,
                "content" => "No address ID provided"
            ));
        }
    }
}
