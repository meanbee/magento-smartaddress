/*
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

function postcode_observe(a) {
	$(a + 'street2').observe('onfocus', function (e) {
		var postcode = $F(a + ':postcode');
		var country = $F(a + ':country_id');
        var street = $F(a + ':street2');
        if (postcode != '' && country != '') {
			$('meanbee:' + a + '_address_selector').innerHTML = "Loading..";
			postcode_fetchOptions(postcode, street, country, a);
		}
	});
}

function postcode_fetchOptions(p, s, c, a) {
	new Ajax.Request(BASE_URL + 'postcode/finder/multiple/', {
		method: 'get',
		parameters: 'postcode=' + p
                    + '&street=' + s
                    + '&country=' + c,
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

function postcode_fillFields(id, country, a) {				
	new Ajax.Request(BASE_URL + 'postcode/finder/single/', {
		method: 'get',
		parameters: 'id=' + id +
                    '&country=' + country,
		onSuccess: function(t) {
			var j = t.responseJSON;
			
			if (!j.error) {
				if (typeof(j.content.country) != "undefined") {
                    $(a + ':country_id').value = j.content.country;
				} else {
                    $(a + ':country_id').value = 'GB';
                }
                eval(a + 'RegionUpdater.update();');

				if (typeof(j.content.line1) != "undefined") {
					$(a + ':street1').value = j.content.line1;
				} else {
					$(a + ':street1').value = '';
				}
				
				if (typeof(j.content.line2) != "undefined") {
					$(a + ':street2').value = j.content.line2;
				} else {
					$(a + ':street2').value = '';
				}
				
				if (typeof(j.content.line3) != "undefined" && $(a + ':street3') != null) {
					$(a + ':street3').value = j.content.line3;
				} else if($(a + ':street3') != null) {
					$(a + ':street3').value = '';
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


function postcode_error(m, a) {
	$('meanbee:' + a + '_address_selector').innerHTML = '&nbsp;';
	alert(m);
}
