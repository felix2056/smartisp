var mes = "Right click disabled. Immihelp.com is Copyright 1999-2015.";

// function clickIE4(){
// if (event.button==2){
// alert(mes);
// return false;
// }
// }
//
// function clickNS4(e){
// if (document.layers||document.getElementById&&!document.all){
// if (e.which==2||e.which==3){
// alert(mes);
// return false;
// }
// }
// }
//
// if (document.layers){
// document.captureEvents(Event.MOUSEDOWN);
// document.onmousedown=clickNS4;
// }
// else if (document.all&&!document.getElementById){
// document.onmousedown=clickIE4;
// }
//
// document.oncontextmenu=new Function("alert(mes);return false")
// 

function printPage() {
	var usertype = navigator.userAgent.toLowerCase();

	if (window.print) {
		setTimeout('window.print();', 200);
	} else if (usertype.indexOf("mac") != -1) {
		alert("Press 'Cmd+p' on your keyboard to print article.");
	} else {
		alert("Press 'Ctrl+p' on your keyboard to print article.")
	}
}

function closeWindow() {
	window.close()
}

var printCmd = '<br><table width="100%"><tr><td align="right"><input type="submit" name="printSubmit" value=" Print " onClick="printPage()">&nbsp;&nbsp;<input type="submit" name="closeSubmit" value=" Close " onClick="closeWindow()"></td></tr></table>';

function hidePrint() {
	if (document.all) {
		document.all.topPrint.innerHTML = "";
		document.all.bottomPrint.innerHTML = "";
	}
}

function showPrint() {
	if (document.all) {
		document.all.topPrint.innerHTML = printCmd;
		document.all.bottomPrint.innerHTML = printCmd;
	}
}

function printerFriendly() {
	window
			.open(
					"/script/printer.php?page=" + escape(document.URL)
							+ "&style=/style.css&lnk=1",
					'so',
					'toolbar=0,scrollbars=1,location=0,status=1,menubars=0,resizable=1,width=700,height=550');

	return false;
}

function disableSelection(target) {
	if (typeof target.onselectstart != "undefined") // IE route
		target.onselectstart = function() {
			return false
		}
	else if (typeof target.style.MozUserSelect != "undefined") // Firefox route
		target.style.MozUserSelect = "none"
	else
		// All other route (ie: Opera)
		target.onmousedown = function() {
			return false
		}
	target.style.cursor = "default"
}

function popUpExplanation(page) {
	window
			.open(
					page,
					'so',
					'toolbar=0,scrollbars=1,location=0,status=1,menubars=0,resizable=1,width=550,height=400');
}

function popUpLarge(page) {
	window
			.open(
					page,
					'so',
					'toolbar=0,scrollbars=1,location=0,status=1,menubars=0,resizable=1,width=700,height=550');
}

function popUpFull(page) {
	window
			.open(page, 'so',
					'toolbar=0,scrollbars=1,location=0,status=1,menubars=0,resizable=1');
}

/** ************** Browser Selection JavaScript *********** */
/*
 * IE6 Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; GTB6.5; .NET CLR
 * 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; MS-RTC LM 8)
 * 
 * IE 8 Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; GTB6.3;
 * .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; .NET CLR
 * 3.0.4506.2152; .NET CLR 3.5.30729)
 * 
 * FireFox Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.6)
 * Gecko/20100625 Firefox/3.6.6 (.NET CLR 3.5.30729)
 * 
 * Google Chrome Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US)
 * AppleWebKit/533.4 (KHTML, like Gecko) Chrome/5.0.375.99 Safari/533.4
 * 
 * Safari Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/531.9
 * (KHTML, like Gecko) Version/4.0.3 Safari/531.9.1
 */
var isBrowserIE = false, browserIEVersion = 0;
var isBrowserFireFox = false, browserFFVersion = 0;
var isBrowserOpera = false, browserOperaVersion = 0;
var isBrowserChrome = false, browserChromeVersion = 0;
var isBrowserSafari = false, browserSafariVersion = 0;

// test for MSIE x.x;
if (/MSIE (\d+\.\d+);/.test(navigator.userAgent)) {
	isBrowserIE = true;
	browserIEVersion = new Number(RegExp.$1) // capture x.x portion and store
												// as a number, 8, 7, 6, 5
}

// test for Firefox/x.x or Firefox x.x (ignoring remaining digits);
else if (/Firefox[\/\s](\d+\.\d+)/.test(navigator.userAgent)) {
	isBrowserFireFox = true;
	browserFFVersion = new Number(RegExp.$1) // capture x.x portion and store
												// as a number, 3, 2, 1
}

// test for Opera/x.x or Opera x.x (ignoring remaining decimal places);
else if (/Opera[\/\s](\d+\.\d+)/.test(navigator.userAgent)) {
	isBrowserOpera = true;
	browserOperaVersion = new Number(RegExp.$1) // capture x.x portion and store
												// as a number, 10, 9, 8, 7
}

// test for Chrome/x.x (ignoring remaining digits);
else if (/Chrome[\/\s](\d+\.\d+)/.test(navigator.userAgent)) {
	isBrowserChrome = true;
	browserChromeVersion = new Number(RegExp.$1) // capture x.x portion and
													// store as a number, 3, 2,
													// 1
}

// test for Safari (ignoring remaining digits);
else if (/Safari[\/\s](\d+\.\d+)/.test(navigator.userAgent)) {
	isBrowserSafari = true;
	if (/Version[\/\s](\d+\.\d+)/.test(navigator.userAgent)) {
		browserSafariVersion = new Number(RegExp.$1) // capture x.x portion
														// and store as a
														// number, 3, 2, 1
	}
}
/** ************** End - Browser Selection JavaScript *********** */

/** ***************** JavaScript Code for Browser Specific CSS ************** */
/*
 * CSS Browser Selector v0.3.5 (Feb 05, 2010) Rafael Lima (http://rafael.adm.br)
 * http://rafael.adm.br/css_browser_selector License:
 * http://creativecommons.org/licenses/by/2.5/ Contributors:
 * http://rafael.adm.br/css_browser_selector#contributors
 */
function css_browser_selector(u) {
	var ua = u.toLowerCase(), is = function(t) {
		return ua.indexOf(t) > -1;
	}, g = 'gecko', w = 'webkit', s = 'safari', o = 'opera', h = document.documentElement, b = [
			(!(/opera|webtv/i.test(ua)) && /msie\s(\d)/.test(ua)) ? ('ie ie' + RegExp.$1)
					: is('firefox/2') ? g + ' ff2'
							: is('firefox/3.5') ? g + ' ff3 ff3_5'
									: is('firefox/3') ? g + ' ff3'
											: is('gecko/') ? g
													: is('opera') ? o
															+ (/version\/(\d+)/
																	.test(ua) ? ' '
																	+ o
																	+ RegExp.$1
																	: (/opera(\s|\/)(\d+)/
																			.test(ua) ? ' '
																			+ o
																			+ RegExp.$2
																			: ''))
															: is('konqueror') ? 'konqueror'
																	: is('chrome') ? w
																			+ ' chrome'
																			: is('iron') ? w
																					+ ' iron'
																					: is('applewebkit/') ? w
																							+ ' '
																							+ s
																							+ (/version\/(\d+)/
																									.test(ua) ? ' '
																									+ s
																									+ RegExp.$1
																									: '')
																							: is('mozilla/') ? g
																									: '',
			is('j2me') ? 'mobile'
					: is('iphone') ? 'iphone'
							: is('ipod') ? 'ipod'
									: is('mac') ? 'mac'
											: is('darwin') ? 'mac'
													: is('webtv') ? 'webtv'
															: is('win') ? 'win'
																	: is('freebsd') ? 'freebsd'
																			: (is('x11') || is('linux')) ? 'linux'
																					: '',
			'js' ];
	c = b.join(' ');
	h.className += ' ' + c;
	return c;
};
css_browser_selector(navigator.userAgent);
/**
 * ***************** End - JavaScript Code for Browser Specific CSS
 * **************
 */

function dodate(formname, field, showalert) {
	tmpdate = "document." + formname + "." + field + ".value";
	tmpdate = eval(tmpdate);

	re = new RegExp("[^0-9]", "gi");
	tmpdate = tmpdate.replace(re, "/");
	datearray = tmpdate.split("/");

	if (datearray.length == 1) {
		if (tmpdate.length == 8) {
			tmp01 = tmpdate.slice(0, 2);
			tmp02 = tmpdate.slice(2, 4);
			tmp03 = tmpdate.slice(4, 8);
			tmpdate = String(tmp01) + "/" + String(tmp02) + "/" + String(tmp03);
		}
		if (tmpdate.length == 6) {
			tmp01 = tmpdate.slice(0, 2);
			tmp02 = tmpdate.slice(2, 4);
			tmp03 = tmpdate.slice(4, 6);
			tmpdate = String(tmp01) + "/" + String(tmp02) + "/" + String(tmp03);
		}
	}

	datearray = tmpdate.split("/");

	if (datearray.length == 3) {
		if (datearray[2].length == 2 || datearray[2].length == 1) {
			now = new Date();
			now = now.getFullYear();
			now = String(now);
			nowbegin = now.slice(0, 2);
			nowend = now.slice(2, 4);
			if (datearray[2] < (Number(nowend) + 10)) {
				if (datearray[2].length == 2) {
					tmpdate = String(datearray[0]) + "/" + String(datearray[1])
							+ "/" + String(nowbegin) + String(datearray[2]);
				} else {
					tmpdate = String(datearray[0]) + "/" + String(datearray[1])
							+ "/" + String(nowbegin) + "0"
							+ String(datearray[2]);
				}
			}
		}
	}

	if (tmpdate == "") {
		showalert = "N";
	}

	tmpdate = doformatdate(tmpdate);
	if (tmpdate == "NaN/NaN/NaN") {
		tmpdate = "";
		if (showalert == "Y") {
			alert("Error : Incorrect Date Format.");
		}
	}
	document.forms[formname].elements[field].value = tmpdate;
}

// Title: Timestamp picker
// Description: See the demo at url
// URL: http://www.geocities.com/tspicker/
// Version: 1.0.a (Date selector only)
// Date: 12-12-2001 (mm-dd-yyyy)
// Author: Denis Gritcyuk <denis@softcomplex.com>; <tspicker@yahoo.com>
// Notes: Permission given to use this script in any kind of applications if
// header lines are left unchanged. Feel free to contact the author
// for feature requests and/or donations

function show_calendar4(str_target, str_datetime) {
	var arr_months = [ "January", "February", "March", "April", "May", "June",
			"July", "August", "September", "October", "November", "December" ];
	var week_days = [ "Su", "Mo", "Tu", "We", "Th", "Fr", "Sa" ];
	var n_weekstart = 1; // day week starts from (normally 0 or 1)

	var dt_datetime = (str_datetime == null || str_datetime == "" ? new Date()
			: str2dt4(str_datetime));
	var dt_prev_month = new Date(dt_datetime);
	dt_prev_month.setMonth(dt_datetime.getMonth() - 1);
	var dt_next_month = new Date(dt_datetime);
	dt_next_month.setMonth(dt_datetime.getMonth() + 1);
	var dt_firstday = new Date(dt_datetime);
	dt_firstday.setDate(1);
	dt_firstday.setDate(1 - (7 + dt_firstday.getDay() - n_weekstart) % 7);
	var dt_lastday = new Date(dt_next_month);
	dt_lastday.setDate(0);

	// html generation (feel free to tune it for your particular application)
	// print calendar header
	var str_buffer = new String(
			"<html>\n"
					+ "<head>\n"
					+ "	<title>Calendar</title>\n"
					+ "</head>\n"
					+ "<body bgcolor=\"White\">\n"
					+ "<table class=\"clsOTable\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n"
					+ "<tr><td bgcolor=\"#4682B4\">\n"
					+ "<table cellspacing=\"1\" cellpadding=\"3\" border=\"0\" width=\"100%\">\n"
					+ "<tr>\n	<td bgcolor=\"#4682B4\"><a href=\"javascript:window.opener.show_calendar4('"
					+ str_target
					+ "', '"
					+ dt2dtstr4(dt_prev_month)
					+ "');\">"
					+ "<img src=\"/images/prev.gif\" width=\"16\" height=\"16\" border=\"0\""
					+ " alt=\"previous month\"></a></td>\n"
					+ "	<td bgcolor=\"#4682B4\" colspan=\"5\">"
					+ "<font color=\"white\" face=\"tahoma, verdana\" size=\"2\">"
					+ arr_months[dt_datetime.getMonth()]
					+ " "
					+ dt_datetime.getFullYear()
					+ "</font></td>\n"
					+ "	<td bgcolor=\"#4682B4\" align=\"right\"><a href=\"javascript:window.opener.show_calendar4('"
					+ str_target
					+ "', '"
					+ dt2dtstr4(dt_next_month)
					+ "');\">"
					+ "<img src=\"/images/next.gif\" width=\"16\" height=\"16\" border=\"0\""
					+ " alt=\"next month\"></a></td>\n</tr>\n");

	var dt_current_day = new Date(dt_firstday);
	// print weekdays titles
	str_buffer += "<tr>\n";
	for ( var n = 0; n < 7; n++)
		str_buffer += "	<td bgcolor=\"#87CEFA\">"
				+ "<font color=\"white\" face=\"tahoma, verdana\" size=\"2\">"
				+ week_days[(n_weekstart + n) % 7] + "</font></td>\n";
	// print calendar table
	str_buffer += "</tr>\n";
	while (dt_current_day.getMonth() == dt_datetime.getMonth()
			|| dt_current_day.getMonth() == dt_firstday.getMonth()) {
		// print row heder
		str_buffer += "<tr>\n";
		for ( var n_current_wday = 0; n_current_wday < 7; n_current_wday++) {
			if (dt_current_day.getDate() == dt_datetime.getDate()
					&& dt_current_day.getMonth() == dt_datetime.getMonth())
				// print current date
				str_buffer += "	<td bgcolor=\"#FFB6C1\" align=\"right\">";
			else if (dt_current_day.getDay() == 0
					|| dt_current_day.getDay() == 6)
				// weekend days
				str_buffer += "	<td bgcolor=\"#DBEAF5\" align=\"right\">";
			else
				// print working days of current month
				str_buffer += "	<td bgcolor=\"white\" align=\"right\">";

			if (dt_current_day.getMonth() == dt_datetime.getMonth())
				// print days of current month
				str_buffer += "<a href=\"javascript:window.opener."
						+ str_target
						+ ".value='"
						+ dt2dtstr4(dt_current_day)
						+ "'; window.close();\">"
						+ "<font color=\"black\" face=\"tahoma, verdana\" size=\"2\">";
			else
				// print days of other months
				str_buffer += "<a href=\"javascript:window.opener."
						+ str_target
						+ ".value='"
						+ dt2dtstr4(dt_current_day)
						+ "'; window.close();\">"
						+ "<font color=\"gray\" face=\"tahoma, verdana\" size=\"2\">";
			str_buffer += dt_current_day.getDate() + "</font></a></td>\n";
			dt_current_day.setDate(dt_current_day.getDate() + 1);
		}
		// print row footer
		str_buffer += "</tr>\n";
	}
	// print calendar footer
	str_buffer += "</table>\n" + "</tr>\n</td>\n</table>\n" + "</body>\n"
			+ "</html>\n";

	var vWinCal = window.open("", "Calendar",
			"width=420,height=250,status=no,resizable=yes,top=200,left=200");
	vWinCal.opener = self;
	vWinCal.focus();
	var calc_doc = vWinCal.document;
	calc_doc.write(str_buffer);
	calc_doc.close();
}

function doformatdate(tmpdate) {
	check_date = new Date(tmpdate);
	month = check_date.getMonth();
	month = eval("month += 1");
	date = check_date.getDate();
	year = check_date.getFullYear();
	var month_leading_zero = "";
	var date_leading_zero = "";
	if (month < 10) {
		month_leading_zero = 0;
	}
	if (date < 10) {
		date_leading_zero = 0;
	}
	formatdate = "" + month_leading_zero + month + "/" + date_leading_zero
			+ date + "/" + year;
	return formatdate;
}

// datetime parsing and formatting routimes. modify them if you wish other
// datetime format
function str2dt4(str_datetime) {
	var re_date = /^(\d+)\/(\d+)\/(\d+)$/;
	if (!re_date.exec(str_datetime))
		return alert("Invalid Datetime format: " + str_datetime);
	return (new Date(RegExp.$3, RegExp.$1 - 1, RegExp.$2));
}
function dt2dtstr4(dt_datetime) {
	return (new String((dt_datetime.getMonth() + 1) + "/"
			+ dt_datetime.getDate() + "/" + dt_datetime.getFullYear()));
}

function googleTranslateElementInit() {
	new google.translate.TranslateElement({
		pageLanguage : 'en',
		gaTrack : true,
		gaId : 'UA-555650-2',
		layout : google.translate.TranslateElement.InlineLayout.SIMPLE
	}, 'google_translate_element');
}

/*
 * function socialIcons() { socialIcons('en'); }
 * 
 * function socialIcons(language) { if (isBrowserIE==true && browserIEVersion<=6) { }
 * else { document.write( "<td align=\"right\" valign=\"top\">" + "<table
 * cellspacing=\"0\" cellpadding=\"0\" border=\"0\">" + "<tr>" + "<td valign=\"top\">" + "<div
 * class=\"addthis_toolbox addthis_default_style\">" + "<a
 * class=\"addthis_button_google_plusone\" g:plusone:size=\"medium\"></a>" + "<a
 * class=\"addthis_button_facebook_like\" fb:like:layout=\"button_count\"></a>" + "<a
 * class=\"addthis_button_tweet\"></a>" + "<a class=\"addthis_counter
 * addthis_pill_style\"></a>" + "</div>" + "<script
 * type=\"text/javascript\">var addthis_config = { \"ui_language\": language,
 * \"data_track_clickback\":true};</script>" + "<script
 * type=\"text/javascript\"
 * src=\"http://s7.addthis.com/js/250/addthis_widget.js#pubid=immihelp\"></script>" + "</td>" + "<td valign=\"top\">&nbsp;" + "<script
 * type=\"text/javascript\" src=\"http://platform.linkedin.com/in.js\"></script>" + "<script
 * type=\"in/share\" data-counter=\"right\"></script>" + "</td>" + "<td valign=\"top\">" + "<div
 * id=\"google_translate_element\"></div>" + "<script
 * src=\"//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit\"></script>" + "</td>" + "</tr>" + "</table>" + "</td>"); } }
 */

function socialIcons() {
	if (isBrowserIE == true && browserIEVersion <= 6) {
	} else {
		document
				.write("<td align=\"right\" valign=\"top\">"
						+ "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">"
						+ "<tr>"
						+ "<td valign=\"top\">"
						+ "<span class='st_fblike_hcount' displayText='Facebook Like'></span>"
						+ "<span class='st_plusone_hcount' displayText='Google +1'></span>"
						+ "<span class='st_twitter_hcount' displayText='Tweet'></span>"
						+ "<span class='st_baidu_hcount' displayText='Baidu'></span>"
						+ "<span class='st_sharethis_hcount' displayText='ShareThis'></span>"
						+ "</td>"
						+ "<td valign=\"top\">"
						+ "<div id=\"google_translate_element\"></div>"
						+ "<script src=\"//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit\"></script>"
						+ "</td>" + "</tr>" + "</table>" + "</td>");
	}
}