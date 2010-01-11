/*
 * Meanbee_SmartAddress
 *
 * This module was developed by Meanbee Internet Solutions Limited.  If you require any
 * support or have any questions please contact us at support@meanbee.com.
 *
 * @category   Meanbee
 * @package    Meanbee_SmartAddress
 * @author     Meanbee Internet Solutions Limited <support@meanbee.com>
 * @copyright  Copyright (c) 2009 Meanbee Internet Solutions Limited (http://www.meanbee.com)
 * @license    Single Site License, requiring consent from Meanbee Internet Solutions Limited
 */

var street_id;

function getStreet(element, entry) {
    return entry + "&street_id=" + street_id;
} 

function getCountryAndPostcodeBilling(element, entry) {
    return entry + '&country=' + $F('billing:country_id') 
                + '&postcode=' + $F('billing:postcode');
}

function getCountryAndPostcodeShipping(element, entry) {
    return entry + '&country=' + $F('shipping:country_id')
                + '&postcode=' + $F('shipping:postcode');
}

function showCorrectTextBoxes(a) {
    var country = $F(a + ':country_id');

    if (country == 'GB') {
        $('meanbee:' + a + '_address_find').show();
        $('meanbee:' + a + '_address_selector').show();
        $('meanbee:' + a + '_street').hide();
        $('meanbee:' + a + '_building').hide();
    } else if (country == 'US') {
        $('meanbee:' + a + '_address_find').hide();
        $('meanbee:' + a + '_address_selector').hide();
        $('meanbee:' + a + '_street').show();
        $('meanbee:' + a + '_building').show();
    } else {
        $('meanbee:' + a + '_address_find').hide();
        $('meanbee:' + a + '_address_selector').hide();
        $('meanbee:' + a + '_street').show();
        $('meanbee:' + a + '_building').hide();
    }
}

function postcode_observe(a) {
    showCorrectTextBoxes(a);

    $(a + ':country_id').observe('change', function (e) {
        showCorrectTextBoxes(a);
    });

    $('meanbee:' + a + '_address_find').observe('click', function (e) { 
        var postcode = $F(a + ':postcode');
        if (postcode != '') { 
            $('meanbee:' + a + '_address_selector').innerHTML = "Loading..."; 
            postcode_fetchOptionsUK(postcode, a); 
        } 
    }); 
}

function postcode_fetchOptionsUK(p, a) {
    new Ajax.Request(BASE_URL + 'postcode/finder/multiple/', {
        method: 'get',
        parameters: 'postcode=' + p
                    + '&country=GB',
        onSuccess: function(t) {
            var j = t.responseJSON;

            if (!j.error) {
                var c = '<select id="meanbee:' + a + '_address_selector_select">';
                for(var i = 0; i < j.content.length; i++) {
                    c += '<option value="' + j.content[i].id + '">' + j.content[i].description + '</option>'
                }
                c+= '</select>';
                $('meanbee:' + a + '_address_selector').innerHTML = c + ' <button onclick="postcode_fillFields($F(\'meanbee:' + a + '_address_selector_select\'), $F(\'' + a + ':country_id\'), \'' + a + '\' )" type="button">Select Address</button>';
                //$('meanbee:' + a + '_address_selector').innerHTML += '<br /><small><b>Note:</b> Please select your address from the above drop down menu before pressing "Select Address".</small>';
            } else {
                postcode_error(j.content, a);
            }
        }
    });
}

function postcode_autocomplete_selected(text,li) {
    var formName;
    var country;
   
    if (text.id == 'meanbee:billing_autocomplete') {
        formName = 'billing';
        country =  $F('billing:country_id');
    } else if (text.id == 'meanbee:shipping_autocomplete') {
        formName = 'shipping';
        country = $F('shipping:country_id');
    }   
    
    if ( country == 'US' ) { 
        street_id = li.id;
    } else {
        postcode_fillFieldsWorld(li.id, country, formName);
    }   
}

function postcode_autocomplete_building(text,li) {
    var formName;
    var country;
   
    if (text.id == 'meanbee:billing_autocomplete_building') {
        formName = 'billing';
        country =  $F('billing:country_id');
    } else if (text.id == 'meanbee:shipping_autocomplete_building') {
        formName = 'shipping';
        country = $F('shipping:country_id');
    }   

    postcode_fillFieldsUS(li.id, country, formName);
}

function postcode_fillFields(id, country, a) {                
    new Ajax.Request(BASE_URL + 'postcode/finder/single/', {
        method: 'get',
        parameters: 'id=' + id +
                    '&country=' + country,
        onSuccess: function(t) {
            var j = t.responseJSON;
            
            if (!j.error) {
                var lines = new Array(j.content.line1, j.content.line2, j.content.line3, j.content.line4, j.content.line5);
                var concat_line = null;

                $(a + ':country_id').value = 'GB';
                eval(a + 'RegionUpdater.update();');

                for (var i = 0; i < 5; i++) {
                    if (typeof(lines[i]) != "undefined" &&  $(a + ':street' + (i+1)) != null) {
                        $(a + ':street' + (i+1)).value = lines[i];
                    } else if ($(a + ':street' + (i+1)) != null) {
                        $(a + ':street' + (i+1)).value = '';
                    } else if (typeof(lines[i]) != "undefined") {
                        if (concat_line == null) {
                            concat_line = i - 1;
                        }

                        $(a + ':street' + (concat_line+1)).value += ', ' + lines[i];
                    }
                }        

                if (typeof(j.content.organisation_name) != "undefined") {
                    $(a + ':company').value = j.content.organisation_name;
                } else {
                    $(a + ':company').value = '';
                }
                
                if (typeof(j.content.post_town) != "undefined") {
                    $(a + ':city').value = j.content.post_town;
                } else {
                    $(a + ':city').value = '';
                }
                
                if (typeof(j.content.county) != "undefined") {
                    $(a + ':region').value = j.content.county;
                } else {
                    $(a + ':region').value = '';
                }
                
                $(a + ':postcode').value = j.content.postcode;

                $('meanbee:' + a + '_address_selector').innerHTML = '&nbsp;';
            } else {
                postcode_error(j.content, a);
            }
        }
    });
}

function postcode_fillFieldsUS(id, country, a) {
new Ajax.Request(BASE_URL + 'postcode/finder/single/', {
        method: 'get',
        parameters: 'id=' + id +
                    '&country=' + country,
        onSuccess: function(t) {
            var j = t.responseJSON;
            
            if (!j.error) {
                var lines = new Array(j.content.line1, j.content.line2);
                var concat_line = null;

                $(a + ':country_id').value = 'US';
                eval(a + 'RegionUpdater.update();');

                for (var i =0; i < 2; i++) {
                    if (typeof(lines[i]) != "undefined" &&  $(a + ':street' + (i+1)) != null) {
                        $(a + ':street' + (i+1)).value = lines[i];
                    } else if ($(a + ':street' + (i+1)) != null) {
                        $(a + ':street' + (i+1)).value = '';
                    } else if (typeof(lines[i]) != "undefined") {
                        if (concat_line == null) {
                            concat_line = i - 1;
                        }

                        $(a + ':street' + (concat_line+1)).value += ', ' + lines[i];
                    }
                }       

                if (typeof(j.content.organisation_name) != "undefined") {
                    $(a + ':company').value = j.content.organisation_name;
                } else {
                    $(a + ':company').value = '';
                }
                
                if (typeof(j.content.city) != "undefined") {
                    $(a + ':city').value = j.content.city;
                } else {
                    $(a + ':city').value = '';
                }

                if (typeof(j.content.state) != "undefined") {
                    for (region in countryRegions['US']) {
                        if (countryRegions['US'][region].code == j.content.state) {
                            $(a + ':region_id').value = region;
                        }
                    }
                } else {
                    $(a + ':region').value = '';
                }

                $(a + ':postcode').value = j.content.zip;

            } else {
                alert(j.content);
            }
        }
    });
}

function postcode_fillFieldsWorld(id, country, a) {
new Ajax.Request(BASE_URL + 'postcode/finder/single/', {
        method: 'get',
        parameters: 'id=' + id +
                    '&country=' + country,
        onSuccess: function(t) {
            var j = t.responseJSON;

            if (!j.error) {
                var lines = new Array(j.content.street, j.content.district);
                var concat_line = null;
                
                for (var i =0; i < 2; i++) {
                    if (typeof(lines[i]) != "undefined" &&  $(a + ':street' + (i+2)) != null) {
                        $(a + ':street' + (i+2)).value = lines[i];
                    } else if ($(a + ':street' + (i+2)) != null) {
                        $(a + ':street' + (i+2)).value = ''; 
                    } else if (typeof(lines[i]) != "undefined") {
                        if (concat_line == null) {
                            concat_line = i - 1;
                        }

                        $(a + ':street' + (concat_line+2)).value += ', ' + lines[i];
                    }
                } 

                if (typeof(j.content.city) != "undefined") {
                    $(a + ':city').value = j.content.city;
                } else {
                    $(a + ':city').value = '';
                }
                
                if (typeof(j.content.state) != "undefined") {
                    var county_done = false;

                    for (country_item in countryRegions) {
                        if (country_item == country) {
                            for (region in countryRegions[country]) {
                                if (countryRegions[country][region].code == j.content.state) {
                                    $(a + ':region_id').value = region;
                                    county_done = true;
                                }
                            }
                        }
                    }

                    if (!county_done) {
                        $(a + ':region').value = j.content.state;
                    }
                } else {
                    $(a + ':region').value = '';
                }
                
                $(a + ':postcode').value = j.content.postcode;

            } else {
                alert(j.content);
            }
        }
    });
}

function postcode_error(m, a) {
    $('meanbee:' + a + '_address_selector').innerHTML = '&nbsp;';
    alert(m);
}
