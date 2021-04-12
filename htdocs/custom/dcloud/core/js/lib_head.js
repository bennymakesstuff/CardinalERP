// Copyright (C) 2014 Regis Houssin	<regis.houssin@capnetworks.com>
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
// or see http://www.gnu.org/

//
// \file       /dropbox/core/js/lib_head.js
// \brief      File that include javascript functions (included if option use_javascript activated)
//

/*
 *
 */
function setDcloudConstant(url, code, input, entity) {
	$.get( url, {
		action: "set",
		name: code,
		entity: entity
	},
	function() {
		$("#set_" + code).hide();
		$("#del_" + code).show();
		$.each(input, function(type, data) {
			// Enable another element
			if (type == "enabled") {
				$.each(data, function(key, value) {
					var newvalue=(value.search("^#") < 0 ? "#" : "") + value;
					$(newvalue).removeAttr("disabled");
				});
			// Disable another element
			} else if (type == "disabled") {
				$.each(data, function(key, value) {
					var newvalue=(value.search("^#") < 0 ? "#" : "") + value;
					$(newvalue).attr("disabled", true);
				});
			// enable and disable another element
			} else if (type == "disabledenabled") {
				$.each(data, function(key, value) {
					var newvalue=(value.search("^#") < 0 ? "#" : "") + value;
					$(newvalue).removeAttr("disabled");
				});
			// Show another element
			} else if (type == "showhide" || type == "show") {
				$.each(data, function(key, value) {
					var newvalue=(value.search("^#") < 0 ? "#" : "") + value;
					$(newvalue).css("display", "table").css("border", "1px solid #D0D0D0");
				});
			// Show button
			} else if (type == "showhidebutton" || type == "showbutton") {
				$.each(data, function(key, value) {
					$('#sync_button_' + value).show();
					$('#sync_info_' + value).show();
					$('#sync_button_div_' + value).removeClass('dcloud-nosync-button');
					$('#sync_button_div_' + value).addClass('dcloud-sync-button');
					$('#sync_info_div_' + value).removeClass('dcloud-nosync-info');
					$('#sync_info_div_' + value).addClass('dcloud-sync-info');
				});
			// Set another constant
			} else if (type == "set" || type == "del") {
				$.each(data, function(key, value) {
					if (type == "set") {
						$("#set_" + value).hide();
						$("#del_" + value).show();
						$.get( url, {
							action: type,
							name: key,
							value: value,
							entity: entity
						});
					} else if (type == "del") {
						$("#del_" + value).hide();
						$("#set_" + value).show();
						$.get( url, {
							action: type,
							name: value,
							entity: entity
						});
					}
				});
			}
		});
	});
}

/*
 *
 */
function delDcloudConstant(url, code, input, entity) {
	$.get( url, {
		action: "del",
		name: code,
		entity: entity
	},
	function() {
		$("#del_" + code).hide();
		$("#set_" + code).show();
		$.each(input, function(type, data) {
			// Enable another element
			if (type == "enabled") {
				$.each(data, function(key, value) {
					var newvalue=(value.search("^#") < 0 ? "#" : "") + value;
					$(newvalue).removeAttr("disabled");
				});
			// Disable another element
			} else if (type == "disabled") {
				$.each(data, function(key, value) {
					var newvalue=(value.search("^#") < 0 ? "#" : "") + value;
					$(newvalue).attr("disabled", true);
				});
			// enable and disable another element
			} else if (type == "disabledenabled") {
				$.each(data, function(key, value) {
					var newvalue=(value.search("^#") < 0 ? "#" : "") + value;
					$(newvalue).attr("disabled", true);
				});
			} else if (type == "showhide" || type == "hide") {
				$.each(data, function(key, value) {
					var newvalue=(value.search("^#") < 0 ? "#" : "") + value;
					$(newvalue).css("display", "none");
				});
			// Hide button
			} else if (type == "showhidebutton" || type == "hidebutton") {
				$.each(data, function(key, value) {
					$('#sync_button_' + value).hide();
					$('#sync_info_' + value).hide();
					$('#sync_button_div_' + value).removeClass('dcloud-sync-button');
					$('#sync_button_div_' + value).addClass('dcloud-nosync-button');
					$('#sync_info_div_' + value).removeClass('dcloud-sync-info');
					$('#sync_info_div_' + value).addClass('dcloud-nosync-info');
					
				});
			// Delete another constant
			} else if (type == "set" || type == "del") {
				$.each(data, function(key, value) {
					if (type == "set") {
						$("#set_" + value).hide();
						$("#del_" + value).show();
						$.get( url, {
							action: type,
							name: key,
							value: value,
							entity: entity
						});
					} else if (type == "del") {
						$("#del_" + value).hide();
						$("#set_" + value).show();
						$.get( url, {
							action: type,
							name: value,
							entity: entity
						});
					}
				});
			}
		});
	});
}

/*
 *
 */
function confirmDcloudConstantAction(action, url, code, input, box, entity, yesButton, noButton) {
	var boxConfirm = box;
	$("#confirm_" + code)
			.attr("title", boxConfirm.title)
			.html(boxConfirm.content)
			.dialog({
				resizable: false,
				height: 170,
				width: 500,
				modal: true,
				buttons: [
					{
						id : 'yesButton_' + code,
						text : yesButton,
						click : function() {
							if (action == "set") {
								setDcloudConstant(url, code, input, entity);
							} else if (action == "del") {
								delDcloudConstant(url, code, input, entity);
							}
							// Close dialog
							$(this).dialog("close");
							// Execute another method
							if (boxConfirm.method) {
								var fnName = boxConfirm.method;
								if (window.hasOwnProperty(fnName)) {
									window[fnName]();
								}
							}
						}
					},
					{
						id : 'noButton_' + code,
						text : noButton,
						click : function() {
							$(this).dialog("close");
						}
					}
				]
			});
	// For information dialog box only, hide the noButton
	if (boxConfirm.info) {
		$("#noButton_" + code).button().hide();
	}
}

/**
*
*  Base64 encode / decode
*  http://www.webtoolkit.info/
*
**/

var Base64 = {

   // private property
   _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

   // public method for encoding
   encode : function (input) {
       var output = "";
       var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
       var i = 0;

       input = Base64._utf8_encode(input);

       while (i < input.length) {

           chr1 = input.charCodeAt(i++);
           chr2 = input.charCodeAt(i++);
           chr3 = input.charCodeAt(i++);

           enc1 = chr1 >> 2;
           enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
           enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
           enc4 = chr3 & 63;

           if (isNaN(chr2)) {
               enc3 = enc4 = 64;
           } else if (isNaN(chr3)) {
               enc4 = 64;
           }

           output = output +
           this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
           this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

       }

       return output;
   },

   // public method for decoding
   decode : function (input) {
       var output = "";
       var chr1, chr2, chr3;
       var enc1, enc2, enc3, enc4;
       var i = 0;

       input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

       while (i < input.length) {

           enc1 = this._keyStr.indexOf(input.charAt(i++));
           enc2 = this._keyStr.indexOf(input.charAt(i++));
           enc3 = this._keyStr.indexOf(input.charAt(i++));
           enc4 = this._keyStr.indexOf(input.charAt(i++));

           chr1 = (enc1 << 2) | (enc2 >> 4);
           chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
           chr3 = ((enc3 & 3) << 6) | enc4;

           output = output + String.fromCharCode(chr1);

           if (enc3 != 64) {
               output = output + String.fromCharCode(chr2);
           }
           if (enc4 != 64) {
               output = output + String.fromCharCode(chr3);
           }

       }

       output = Base64._utf8_decode(output);

       return output;

   },

   // private method for UTF-8 encoding
   _utf8_encode : function (string) {
       string = string.replace(/\r\n/g,"\n");
       var utftext = "";

       for (var n = 0; n < string.length; n++) {

           var c = string.charCodeAt(n);

           if (c < 128) {
               utftext += String.fromCharCode(c);
           }
           else if((c > 127) && (c < 2048)) {
               utftext += String.fromCharCode((c >> 6) | 192);
               utftext += String.fromCharCode((c & 63) | 128);
           }
           else {
               utftext += String.fromCharCode((c >> 12) | 224);
               utftext += String.fromCharCode(((c >> 6) & 63) | 128);
               utftext += String.fromCharCode((c & 63) | 128);
           }

       }

       return utftext;
   },

   // private method for UTF-8 decoding
   _utf8_decode : function (utftext) {
       var string = "";
       var i = 0;
       var c = c1 = c2 = 0;

       while ( i < utftext.length ) {

           c = utftext.charCodeAt(i);

           if (c < 128) {
               string += String.fromCharCode(c);
               i++;
           }
           else if((c > 191) && (c < 224)) {
               c2 = utftext.charCodeAt(i+1);
               string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
               i += 2;
           }
           else {
               c2 = utftext.charCodeAt(i+1);
               c3 = utftext.charCodeAt(i+2);
               string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
               i += 3;
           }

       }

       return string;
   }

}
