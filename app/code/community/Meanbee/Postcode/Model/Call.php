<?php
/**
 * Meanbee_Postcode
 *
 * This module was developed by Meanbee Internet Solutions.  If you require any
 * support or have any questions please contact us at support@meanbee.com.
 *
 * Portions of this software uses code found at:
 *   - http://www.postcodeanywhere.co.uk/developers
 *
 * @category   Meanbee
 * @package    Meanbee_Postcode
 * @author     Meanbee Internet Solutions <support@meanbee.com>
 * @copyright  Copyright (c) 2009 Meanbee Internet Solutions (http://www.meanbee.com)
 * @license    Single Site License, requiring consent from Meanbee Internet Solutions
 */
class Meanbee_Postcode_Model_Call {    
    public function findMultipleByPostcode($postcode) {
        $license = trim(Mage::getStoreConfig('postcode/auth/license'));
        $account = trim(Mage::getStoreConfig('postcode/auth/account'));
        
        if (!empty($license) && !empty($account)) {
            try {
                $result = $this->_submitFindAddressesRequest($postcode, $account, $license, '');
                return $this->_success($result);
            } catch (Exception $e) {
                return $this->_error($e->getMessage());
            }
        } else {
                return $this->_error('License and/or Account keys are not set in the configuration');
        }
    }
    
    public function findSingleAddressById($id) {        
        $license = trim(Mage::getStoreConfig('postcode/auth/license'));
        $account = trim(Mage::getStoreConfig('postcode/auth/account'));
        
        if (!empty($license) && !empty($account)) {
            $id = (int) $id;
            
            try {
                $result = $this->_submitFindSingleAddressRequest($id, 'english', 'simple', $account, $license, '', '');
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
    
    protected function _submitFindAddressesRequest($postcode, $account_code, $license_code, $machine_id) {
        //Built with help from James at http://www.omlet.co.uk/
        //Build the url
        $url = "http://services.postcodeanywhere.co.uk/xml.aspx?";
        $url .= "&action=lookup";
        $url .= "&type=by_postcode";
        $url .= "&postcode=" . urlencode($postcode);
        $url .= "&account_code=" . urlencode($account_code);
        $url .= "&license_code=" . urlencode($license_code);
        $url .= "&machine_id=" . urlencode($machine_id);
        //Make the request
        $data = simplexml_load_string(file_get_contents($url));
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

    protected function _submitFindSingleAddressRequest($id, $language, $style, $account_code, $license_code, $machine_id, $options) {
        //Built with help from James at http://www.omlet.co.uk/
        //Build the url
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
}
