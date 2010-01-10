<?php
/**
 * Meanbee_SmartAddress
 *
 * This module was developed by Meanbee Internet Solutions Limited.  If you require any
 * support or have any questions please contact us at support@meanbee.com.
 *
 * Portions of this software uses code found at:
 *   - http://www.postcodeanywhere.co.uk/developers
 *
 * @category   Meanbee
 * @package    Meanbee_SmartAddress
 * @author     Meanbee Internet Solutions <support@meanbee.com>
 * @copyright  Copyright (c) 2009 Meanbee Internet Solutions Limited (http://www.meanbee.com)
 * @license    Single Site License, requiring consent from Meanbee Internet Solutions Limited
 */
class Meanbee_SmartAddress_Model_Call {    
    public function findMultipleByPostcode($postcode, $street, $country) {
        $license = trim(Mage::getStoreConfig('postcode/auth/license'));
        $account = trim(Mage::getStoreConfig('postcode/auth/account'));
        
        if (!empty($license) && !empty($account)) {
            try {
                if (strcmp($country, "GBR") == 0) {
                    $result = $this->_submitFindAddressesRequestUK($postcode, $account, $license, '');
                } elseif (strcmp($country, "USA") == 0) {
                    if (isset($street)) {
                        $result = $this->_submitFindAddressesRequestUS($street, $postcode, $account, $license, '');
                    } else {
                        return $this->_error("No street given.");
                    }
                } else {
                    if (isset($street)) {
                        $result = $this->_submitFindAddressesRequestWorld($street, $postcode, $country, $account, $license);
                    } else {
                        return $this->_error("No street given.");
                    }
                }
                return $this->_success($result);
            } catch (Exception $e) {
                return $this->_error($e->getMessage());
            }
        } else {
            return $this->_error('License and/or Account keys are not set in the configuration');
        }
    }
    
    
    /*
     * Takes Address ID and country code and returns complete
     * address information for ID in that country. 
     */
    public function findSingleAddressById($id, $country) {        
        $license = trim(Mage::getStoreConfig('postcode/auth/license'));
        $account = trim(Mage::getStoreConfig('postcode/auth/account'));
        
        if (!empty($license) && !empty($account)) {
            if( strcmp($country, "USA") == 0 || strcmp($country, "GBR") == 0) {
                if (!is_numeric($id)) {
                    // @TODO Does it really have to be numeric?
                    return $this->_error('Address ID was not numeric!');
                }
            }

            try {
                $result = array();

                //Find addresses (different call depending on country)
                if (strcmp($country, "GBR") == 0) {
                    $result = $this->_submitFindSingleAddressRequestUK($id, 'english', 'simple', $account, $license, '', '');
                } elseif (strcmp($country, "USA") == 0) {
                    $result = $this->_submitFindSingleAddressRequestUS($id, $account, $license, '');
                } else {
                    $result = $this->_findSingleAddressRequestWorld(urldecode("$id"));
                }

                // Check results
                if (count($result)) {
                    return $this->_success($result[0]);
                } else {
                    return $this->_error("Unable to find address ($id)");
                }
            } catch (Exception $e) {
                    return $this->_error($e->getMessage());
            }
        } else {
                return $this->_error('License and/or Account keys are not set in the configuration');
        }
    }

    public function findBuildingByStreet($street_id, $building) {
        $license = trim(Mage::getStoreConfig('postcode/auth/license'));
        $account = trim(Mage::getStoreConfig('postcode/auth/account'));
    
        if (!empty($license) && !empty($account)) {
            if (!is_numeric($street_id)) {
                // @TODO Does it really have to be numeric?
                return $this->_error('Street ID was not numeric!');
            }
            
            try {
                $result = array();

                $result = $this->_submitFindBuildingByStreet($street_id, $building, $account, $license, '');

                // Check results
                if (count($result)) {
                    return $this->_success($result);
                } else {
                    return $this->_error("Unable to find address");
                }
            } catch (Exception $e) {
                    return $this->_error($e->getMessage());
            }
        } else {
                return $this->_error('License and/or Account keys are not set in the configuration');
        }
    }
 
    protected function _error($content) {
        return Zend_Json::encode(array(
                "error" => true,
                "content" => $content
            )
        );
    }
    
    protected function _success($content) {
        return Zend_Json::encode(array(
            "error" => false,
            "content" => $content
        ));
    }
    
    protected function _submitFindAddressesRequestUK($postcode, $account_code, $license_code, $machine_id) {
        //Built with help from James at http://www.omlet.co.uk/
        
        //Build UK lookup URL
        $url = "http://services.postcodeanywhere.co.uk/xml.aspx?";
        $url .= "&action=lookup";
        $url .= "&type=by_postcode";
        $url .= "&postcode=" . urlencode($postcode);
        $url .= "&account_code=" . urlencode($account_code);
        $url .= "&license_code=" . urlencode($license_code);
        $url .= "&machine_id=" . urlencode($machine_id);
        
        //Make the request
        $data = simplexml_load_string(file_get_contents($url));
        $output = array();
        
        //Check for an error
        if ($data->Schema['Items']==2) {
            throw new exception ($data->Data->Item['message']);
        }

        //Create the response
        foreach ($data->Data->children() as $row) {
            $rowItems="";
            foreach($row->attributes() as $key => $value) {
                $rowItems[$key]=strval($value);
            }
            $output[] = $rowItems;
        }

        //Return the result
        return $output;
    }

    /*
     * Find addresses in the US given building and zipcode
     */
    protected function _submitFindAddressesRequestUS($street, $postcode, $account_code, $license_code, $machine_id) {
        //Built with help from James at http://www.omlet.co.uk/
   
        //Build US lookup URL
        $url = "http://services.postcodeanywhere.co.uk/xml.aspx?";
        $url .= "action=lookup";
        $url .= "&type=by_street";
        $url .= "&country=us";
        $url .= "&Street=" . urlencode($street);
        $url .= "&CityOrZIP=" . urlencode($postcode);
        $url .= "&account_code=" . urlencode($account_code);
        $url .= "&license_code=" . urlencode($license_code);
        $url .= "&machine_id=" . urlencode($machine_id);
        
        //Make the request
        $data = simplexml_load_string(file_get_contents($url));
        
        $output = array();
         
        //Check for an error
        if ( $data->Schema['Items'] == 2 ) {
            throw new exception ($data->Data->Item['message']);
        }
     
        //Create the response
        foreach ( $data->Data->children() as $row ) {
            $rowItems = "";
            foreach ( $row->attributes() as $key => $value ) {
                $rowItems[$key] = strval($value);
            }
            $output[] = $rowItems;
        }
        
        //Return the result
        return $output;
    }

    /*
     * Find addresses in the rest of the World using Streetn and Postcode
     */
    protected function _submitFindAddressesRequestWorld($street, $postcode, $country, $account_code, $license_code) {
        //Built with help from James at http://www.omlet.co.uk/

        //Build World lookup URL
        $url = "http://services.postcodeanywhere.co.uk/xml.aspx?";
        $url .= "action=international";
        $url .= "&type=fetch_streets";
        $url .= "&country=" . urlencode($country);
        $url .= "&street=" . urlencode($street);
        $url .= "&postcode=" . urlencode($postcode);
        $url .= "&account_code=" . urlencode($account_code);
        $url .= "&license_code=" . urlencode($license_code);

        //Make the request
        $data = simplexml_load_string(file_get_contents($url));
        $output = array();

        //Check for an error
        if ($data->Schema['Items']==2) {
            throw new exception ($data->Data->Item['message']);
        }

        //Create the response
        foreach ($data->Data->children() as $row) {
            $rowItems ="";
            $rowItems['description'] = "";
            $id_value = "'";
            foreach($row->attributes() as $key => $value) {
                $rowItems['description'] .= strval($value) . ", ";
                $id_value .= $key . '=' . strval($value) . '{}';
            }
            $id_value = substr($id_value,0,strlen($id_value)-2) . "'";
            $rowItems['id'] = strval($id_value);
          
            $output[] = $rowItems;
        }
   
        //Return the result
            return $output;
    }

    protected function _submitFindSingleAddressRequestUK($id, $language, $style, $account_code, $license_code, $machine_id, $options) {
        //Built with help from James at http://www.omlet.co.uk/
        
        //Build UK ID lookup URL
        $url = "http://services.postcodeanywhere.co.uk/xml.aspx?";
        $url .= "&action=fetch";
        $url .= "&id=" . urlencode($id);
        $url .= "&language=" . urlencode($language);
        $url .= "&style=" . urlencode($style);
        $url .= "&account_code=" . urlencode($account_code);
        $url .= "&license_code=" . urlencode($license_code);
        $url .= "&machine_id=" . urlencode($machine_id);
        $url .= "&options=" . urlencode($options);
        
        //Make the request
        
        $data = simplexml_load_string(file_get_contents($url));
        $output = array();

        //Check for an error
        if ($data->Schema['Items']==2) {
                 throw new exception ($data->Data->Item['message']);
        }

        //Create the response
        foreach ($data->Data->children() as $row) {
            $rowItems="";
            foreach($row->attributes() as $key => $value) {
                $rowItems[$key]=strval($value);
            }
            $output[] = $rowItems;
        }

        //Return the result
        return $output;
    }

    protected function _submitFindSingleAddressRequestUS($id, $account_code, $license_code, $machine_id) {
        //Built with help from James at http://www.omlet.co.uk/
        
        //Build US ID lookup URL
        $url = "http://services.postcodeanywhere.co.uk/xml.aspx?";
        $url .= "action=fetch";
        $url .= "&country=us";
        $url .= "&style=simple";
        $url .= "&id=$" . $id;
        $url .= "&account_code=" . urlencode($account_code);
        $url .= "&license_code=" . urlencode($license_code);
        $url .= "&machine_id=" . urlencode($machine_id);
     
        //Make the request
        $data = simplexml_load_string(file_get_contents($url));
        $output = array();

        //Check for an error
        if ($data->Schema['Items']==2) {
            throw new exception ($data->Data->Item['message']);
        }
       
        //Create the response
        foreach ($data->Data->children() as $row) {
            $rowItems="";
            foreach($row->attributes() as $key => $value) {
                $rowItems[$key]=strval($value);
            }
            $output[] = $rowItems;
        }
        
        //Return the result
        return $output;
    }

    protected function _submitFindBuildingByStreet($street_id, $building, $account_code, $license_code, $machine_id) {
        $url = "http://services.postcodeanywhere.co.uk/xml.aspx?";
        $url .= "action=lookup";
        $url .= "&type=by_streetkey";
        $url .= "&country=us";
        $url .= "&streetkey=" . urlencode($street_id);
        $url .= "&account_code=" . urlencode($account_code);
        $url .= "&license_code=" . urlencode($license_code);
        $url .= "&machine_id=" . urlencode($machine_id);

        //Make the request
        $data = simplexml_load_string(file_get_contents($url));
        $output = array();

        //Check for an error
        if ($data->Schema['Items']==2) {
            throw new exception ($data->Data->Item['message']);
        }

        //Create the response
        foreach ($data->Data->children() as $row) {
            $rowItems="";
            foreach($row->attributes() as $key => $value) {
                $rowItems[$key]=strval($value);
            }
            if (preg_match("/^$building.+$/", $rowItems['description'])) {
                $output[] = $rowItems;
            }
        }
        
        //Return the result
        return $output;

    }

    protected function _findSingleAddressRequestWorld($id) {
        // Split on {} to give each field back
        $rows = explode('{}', $id);
        $rowItems = "";
        foreach($rows as $row) {
            // Then split on = to find key and value
            $items = explode('=', $row);
            $rowItems[$items[0]] = $items[1];
        }
        $output[]  = $rowItems;
        
        // Return the result.
        return $output;
    }
}
?>
