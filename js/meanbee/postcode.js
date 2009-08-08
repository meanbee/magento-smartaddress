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

function postcode_observe(a) {
	$('meanbee:' + a + '_address_find').observe('click', function (e) {
		var v = $F(a + ':postcode');
		if (v != '') {
			$('meanbee:' + a + '_address_selector').innerHTML = "Loading..";
			postcode_fetchOptions(v, a);
		}
	});
}

function postcode_fetchOptions(p, a) {
	new Ajax.Request(BASE_URL + 'postcode/finder/multiple/', {
		method: 'get',
		parameters: 'postcode=' + p,
		onSuccess: function(t) {
			var j = t.responseJSON;

			if (!j.error) {
				var c = '<select id="meanbee:' + a + '_address_selector_select">';
				for(var i = 0; i < j.content.length; i++) {
					c += '<option value="' + j.content[i].id + '">' + j.content[i].description + '</option>'
				}
				c+= '</select>';
				$('meanbee:' + a + '_address_selector').innerHTML = c + ' <button onclick="postcode_fillFields($F(\'meanbee:' + a + '_address_selector_select\'), \'' + a + '\')" type="button">Select Address</button>';
				//$('meanbee:' + a + '_address_selector').innerHTML += '<br /><small><b>Note:</b> Please select your address from the above drop down menu before pressing "Select Address".</small>';
			} else {
				postcode_error(j.content, a);
			}
		}
	});
}

function postcode_fillFields(id, a) {				
	new Ajax.Request(BASE_URL + 'postcode/finder/single/', {
		method: 'get',
		parameters: 'id=' + id,
		onSuccess: function(t) {
			var j = t.responseJSON;
			
			if (!j.error) {
				$(a + ':country_id').value = 'GB';
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
