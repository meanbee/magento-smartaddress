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
        $street = isset($_GET['street']) ? $_GET['street'] : '';
        $country = $_GET['country'];

        // As long as have data we need, call actions
        if (!empty($postcode)) {
            if (!empty($country)) {
                $call = Mage::getModel('postcode/call');
                $countryCodes = Mage::getSingleton('postcode/countrycodes');
                $country = $countryCodes->convertCountryCode($country);
                if (is_null($country)) {
                    echo Zend_Json::encode(array(
                        "error" => true,
                        "content" => "Invalid country provided"
                    ));
                } else {
                    echo $call->findMultipleByPostcode($postcode, $street, $country);
                }
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
                $countryCodes = Mage::getSingleton('postcode/countrycodes');
                $country = $countryCodes->convertCountryCode($country);
                if (is_null($country)) {
                    echo Zend_Json::encode(array(
                        "error" => true,
                        "content" => "Invalid country provided"
                    ));
                } else {
                    $call = Mage::getModel('postcode/call');
                    echo $call->findSingleAddressById($id, $country);
                }
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

    public function autocompleteAction() {
        //Retrieve fields
        $postcode = strtolower(preg_replace("/[^a-zA-Z0-9]/", "", str_replace(' ', '', $_POST['postcode'])));
        $street = isset($_POST['street']) ? $_POST['street'] : ''; 
        $country = $_POST['country'];

        // Aslong as have data we need, call actions
        if (!empty($postcode)) {
            if (!empty($country)) {
                $call = Mage::getModel('postcode/call');
                $countryCodes = Mage::getSingleton('postcode/countrycodes');
                $country = $countryCodes->convertCountryCode($country);
                if (is_null($country)) {
                    alert('Invalid Country provided'); 
                } else {
                    $jResult =  $call->findMultipleByPostcode($postcode, $street, $country);
                    $result = json_decode($jResult, true);
                    echo "<ul>";
                    if ( $result['error'] == true ) {
                        echo "<li>Webmaster: " . $result['content'] . "</li>";
                    } else {
                        for ($i = 0; $i < count( $result['content'] ); $i++) {
                            echo "<li id=" . $result['content'][$i]['id'] . ">" . $result['content'][$i]['description'] . "</li>";
                        }
                    }
                    echo "</ul>";
                }   
            } else {
                echo "<ul><li>No country provided</li></ul>";
            }   
        } else {
            echo "<ul><li>No postcode provided</li</ul>";
        }   
    }
}
