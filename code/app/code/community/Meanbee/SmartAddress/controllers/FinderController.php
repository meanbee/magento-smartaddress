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
 * @copyright  Copyright (c) 2010 Meanbee Internet Solutions Limited (http://www.meanbee.com)
 * @license    Single Site License, requiring consent from Meanbee Internet Solutions Limited
 */
class Meanbee_SmartAddress_FinderController extends Mage_Core_Controller_Front_Action {
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
    
    public function postDispatch() {
        return 0;
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
                $country = $_GET['country'];
                if (strcmp($country, "GBR") == 0 || strcmp($country, "USA") == 0) {
                    $id = (int) $_GET['id'];
                } else {
                    $id = $_GET['id'];
                }
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
                        echo "<li>" . $result['content'] . "</li>";
                    } elseif (  count( $result['content'] ) == 0 ) {
                        echo "<li>No matching streets</li>";
                    } else {
                        for ($i = 0; $i < count( $result['content'] ); $i++) {
                            echo "<li id=" . $result['content'][$i]['id'] . ">" . $result['content'][$i]['description'] . "</li>";
                        }
                    }
                    echo "</ul>"; //exit;
                }   
            } else {
                echo "<ul><li>No country provided</li></ul>";
            }   
        } else {
            echo "<ul><li>No postcode provided</li</ul>";
        }   
    }

    public function autocomplete_buildingAction() {
        //Retrieve fields
        $street_id = $_POST['street_id'];
        $building = $_POST['building'];

        // Aslong as have data we need, call actions
        if (!empty($street_id)) {
            $call = Mage::getModel('postcode/call');
            $jResult =  $call->findBuildingByStreet($street_id, $building);
            $result = json_decode($jResult, true);
            echo "<ul>";
            if ( $result['error'] == true ) { 
                echo "<li>" . $result['content'] . "</li>";
            } else {
                for ($i = 0; $i < count( $result['content'] ); $i++) {
                    echo "<li id=" . substr($result['content'][$i]['id'],1) . ">" . $result['content'][$i]['description'] . "</li>";
                }
            }
            echo "</ul>";
        } else {
            echo "<ul></ul>";
        }
    }  
}
?>
