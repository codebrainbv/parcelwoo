$( init );
var TIMER_TOP = null;
var TIMER_LOWER = null;
var TOUCHMOUSE_DOWN = (!is_touch_device() ? 'mousedown' : 'touchstart');
var TOUCHMOUSE_UP = (!is_touch_device() ? 'mouseup' : 'touchend');

var aTopDroppableContents = [];
var aLowerDroppableContents = [];
var ilowerLock = 0;
var iTopLock = 0;
					
/* insert default widgets on first logon */
function defaultWidgetSet()
{
	if(!!jQuery.cookie('sortableLowerContentParcelCookie'+sUsernameMd5)) 
	{

	} else {
		jQuery.cookie.raw = true;
		var sCookieContent = "";
		jQuery.cookie('sortableLowerContentParcelCookie'+sUsernameMd5,sCookieContent,{ path: '/' });
		jQuery.cookie.raw = false;
	}
}

/* SORTABLE LIST COOKIE */
var sortablelistCookieExp = 365;



var topSortablelistSelector = '#content-row-upper-col1';
var topSortablelistCookieName = 'sortableTopWidgetsParcelCookie'+sUsernameMd5;
function topSortablelistOrder() {
	jQuery.cookie(topSortablelistCookieName, jQuery(topSortablelistSelector).sortable("toArray"), {expires: sortablelistCookieExp, path: "/"});
}
function topSortablelistRestoreOrder() {
	var i, previousorder, cookie = jQuery.cookie(topSortablelistCookieName);
	if (!cookie) { return; }
	previousorder = cookie.split(',');
	for (i = 0; i < previousorder.length; i++) {
		jQuery('#'+previousorder[i]).appendTo(jQuery(topSortablelistSelector));
	}
}

var topSortableContentCookieName = 'sortableTopContentParcelCookie'+sUsernameMd5;
function topSortableContent() {
	jQuery.cookie(topSortableContentCookieName, aTopDroppableContents, {expires: sortablelistCookieExp, path: "/"});
}
function topSortableContentRestore() {
	var i, previousorder, cookie = jQuery.cookie(topSortableContentCookieName);
	if (!cookie) { return; }
	previousorder = cookie.split(',');
	for (i = 0; i < previousorder.length; i++) {
		var widgetName = previousorder[i];
		widgetName = widgetName.replace(/-topDraggable/, '');
		$("#col" + i + "-topDroppable").load("widgets/top/" + widgetName + ".php");
	}
}
function topFillFromCookie() {
	var i, previousorder, cookie = jQuery.cookie(topSortableContentCookieName);
	if (!cookie) { return; }
	previousorder = cookie.split(',');
	for (i = 0; i < previousorder.length; i++) {
		aTopDroppableContents[i] = previousorder[i];
		
		$("#" + previousorder[i]).css("display", "none"); //verwijderd de topWidgetent selectie uit het menu
	}
}

function resetTopWidgets() {
	jQuery.removeCookie('sortableTopWidgetsParcelCookie'+sUsernameMd5, { path: '/' });
	jQuery.removeCookie('sortableTopContentParcelCookie'+sUsernameMd5, { path: '/' });
	window.location.reload();
}



var lowerSortablelist1Selector = '#content-row-lower-col1';
var lowerSortablelist1CookieName = 'sortableLowerWidgets1ParcelCookie'+sUsernameMd5;
function lowerSortablelist1Order() {
	jQuery.cookie(lowerSortablelist1CookieName, jQuery(lowerSortablelist1Selector).sortable("toArray"), {expires: sortablelistCookieExp, path: "/"});
}
function lowerSortablelist1RestoreOrder() {
	var i, previousorder, cookie = jQuery.cookie(lowerSortablelist1CookieName);
	if (!cookie) { return; }
	previousorder = cookie.split(',');
	for (i = 0; i < previousorder.length; i++) {
		jQuery('#'+previousorder[i]).appendTo(jQuery(lowerSortablelist1Selector));
	}
}

var lowerSortablelist2Selector = '#content-row-lower-col2';
var lowerSortablelist2CookieName = 'sortableLowerWidgets2ParcelCookie'+sUsernameMd5;
function lowerSortablelist2Order() {
	jQuery.cookie(lowerSortablelist2CookieName, jQuery(lowerSortablelist2Selector).sortable("toArray"), {expires: sortablelistCookieExp, path: "/"});
}
function lowerSortablelist2RestoreOrder() {
	var i, previousorder, cookie = jQuery.cookie(lowerSortablelist2CookieName);
	if (!cookie) { return; }
	previousorder = cookie.split(',');
	for (i = 0; i < previousorder.length; i++) {
		jQuery('#'+previousorder[i]).appendTo(jQuery(lowerSortablelist2Selector));
	}
}

var lowerSortableContentCookieName = 'sortableLowerContentParcelCookie'+sUsernameMd5;
function lowerSortableContent() {
	jQuery.cookie(lowerSortableContentCookieName, aLowerDroppableContents, {expires: sortablelistCookieExp, path: "/"});
}
function lowerSortableContentRestore() {
	var i, previousorder, cookie = jQuery.cookie(lowerSortableContentCookieName);
	if (!cookie) { return; }
	previousorder = cookie.split(',');
	for (i = 0; i < previousorder.length; i++) {
		var widgetName = previousorder[i];
		widgetName = widgetName.replace(/-lowerDraggable/, '');
		$("#bigCol" + i + "-lowerDroppable").load("widgets/lower/" + widgetName + ".php");
	}
}
function lowerFillFromCookie() {
	var i, previousorder, cookie = jQuery.cookie(lowerSortableContentCookieName);
	if (!cookie) { return; }
	previousorder = cookie.split(',');
	for (i = 0; i < previousorder.length; i++) {
		aLowerDroppableContents[i] = previousorder[i];
		
		$("#" + previousorder[i]).css("display", "none"); //verwijderd de lowerWidgetent selectie uit het menu
	}
}

function resetLowerWidgets() {
	jQuery.removeCookie('sortableLowerWidgets1ParcelCookie'+sUsernameMd5, { path: '/' });
	jQuery.removeCookie('sortableLowerWidgets2ParcelCookie'+sUsernameMd5, { path: '/' });
	jQuery.removeCookie('sortableLowerContentParcelCookie'+sUsernameMd5, { path: '/' });
	window.location.reload();
}


function init() {
	
	defaultWidgetSet();
	
	//top widgets-------------------------------------------------------------------------------------------------------------
	topFillFromCookie();
	topSortablelistRestoreOrder();
	topSortableContentRestore();
	$('.content-row-upper').on(TOUCHMOUSE_DOWN, function(event, ui) 
	{
		if(iTopLock == 0){
			event.stopPropagation()
			TIMER_TOP = setTimeout(function() 
			{
				$('#content-row-upper-col1').sortable(
				{
					update: function(event, ui) 
						{
							topSortablelistOrder();
							var changedList = this.id;
							var order = $(this).sortable('toArray');
							var aTopPositions = order.join(',');
						},
					helper:'clone', 
					revert: true, 
					stop: function()
						{
							$('#content-row-upper-col1').sortable('destroy'); 
						} 
				});
			}, 1250 );
		}
	}).on(TOUCHMOUSE_UP, function() { clearTimeout(TIMER_TOP); });
	
	$('#content-row-upper-col1').disableSelection();
	$("[id$=topDraggable]").draggable({ 
		revert: 'invalid' //zet het element terug als hij niet succesvol is gedropt *1
	}); //maak id's die eindigen met "draggable" draggable 
	$("[id$=topDroppable]").droppable({  //maak id's die eindigen met "droppable" droppable
		accept: '[id$=topDraggable]', //accepteer alleen draggables die eindigen met "topDraggable" *1
		drop: handleTopDropEvent
	});
	
	//lower widgets-------------------------------------------------------------------------------------------------------------
	lowerFillFromCookie();
	lowerSortablelist1RestoreOrder();
	lowerSortablelist2RestoreOrder();
	lowerSortableContentRestore();
	$('.content-row-lower-col').on(TOUCHMOUSE_DOWN, function(event, ui) 
	{
		if(ilowerLock == 0){
			event.stopPropagation()
			TIMER_LOWER = setTimeout(function() 
			{ 
				$('#content-row-lower-col1,#content-row-lower-col2').sortable(
				{
					update: function(event, ui) 
						{
							lowerSortablelist1Order();
							lowerSortablelist2Order();
							var changedList = this.id;
							var order = $(this).sortable('toArray');
							var aLowerPositions = order.join(',');
						},
					revert: true,
					connectWith: '#content-row-lower-col1,#content-row-lower-col2',
					helper:'clone',
					stop: function()
						{
							$('.content-row-lower-col').sortable('destroy'); 
						} 
				});
			}, 1250 );
		}
	}).on(TOUCHMOUSE_UP, function() { clearTimeout(TIMER_LOWER); });

	$('.content-row-lower-col').disableSelection();
	$("[id$=lowerDraggable]").draggable({ 
		revert: 'invalid' //zet het element terug als hij niet succesvol is gedropt *1
	}); //maak id's die eindigen met "draggable" draggable 
	$("[id$=lowerDroppable]").droppable({  //maak id's die eindigen met "droppable" droppable
		accept: '[id$=lowerDraggable]', //accepteer alleen draggables die eindigen met "lowerDraggable" *1
		drop: handleLowerDropEvent
	});
	
}
 
 
//top widgets------------------------------------------------------------------------------------------------------------- 
function handleTopDropEvent( event, ui ) {
  var topDraggable = ui.draggable;
  var topDroppable = $(this);
  var topDroppableId = topDroppable.attr('id');
  var topDroppableNumber = topDroppableId.replace(/-topDroppable/, '');
  var topDroppableNumber = topDroppableNumber.replace(/col/, '');
  

		var sId = $("#" + topDroppable.attr('id') + '> div').attr('id');
		if(sId) //als sId bestaat
		{
			$("#" + sId + '-topDraggable').css({ "display": "block", "left": '0px', 'top': '0px' }); //zet sId terug in menu
		}
		
		switch(topDraggable.attr('id')){ //gaat kijken naar welk topWidgetent is gebruikt
			
			case 'topwidget1-topDraggable': //als topWidget1-topDraggable is gebruikt:
				$(function(){
					$("#" + topDroppableId).load("widgets/top/topwidget1.php"); 
				});
				
				aTopDroppableContents[topDroppableNumber] =  'topwidget1-topDraggable';
			break;
			
			case 'currencyconverter-topDraggable':
				$(function(){
					$("#" + topDroppableId).load("widgets/top/currencyconverter.php"); 
				});
				
				aTopDroppableContents[topDroppableNumber] =  'currencyconverter-topDraggable';
			break;
			
			case 'topwidget3-topDraggable':
				$(function(){
					$("#" + topDroppableId).load("widgets/top/topwidget3.html"); 
				});
				
				aTopDroppableContents[topDroppableNumber] =  'topwidget3-topDraggable';
				
			break;
			
			case 'topwidget4-topDraggable':
				$(function(){
					$("#" + topDroppableId).load("widgets/top/topwidget4.html"); 
				});
				
				aTopDroppableContents[topDroppableNumber] =  'topwidget4-topDraggable';
			break;
			
			case 'topwidget5-topDraggable':
				$(function(){
					$("#" + topDroppableId).load("widgets/top/topwidget5.html"); 
				});
				
				aTopDroppableContents[topDroppableNumber] =  'topwidget5-topDraggable';
			break;
			
			case 'topwidget6-topDraggable':
				$(function(){
					$("#" + topDroppableId).load("widgets/top/topwidget6.html"); 
				});
				
				aTopDroppableContents[topDroppableNumber] =  'topwidget6-topDraggable';
			break;
			
			default:
				alert("define topDraggable in html and widgets.js");
		}
	  topSortableContent();
	  $("#" + topDraggable.attr('id')).css("display", "none"); //verwijderd de topWidgetent selectie uit het menu
  
  //alert( 'ID "' + topDraggable.attr('id') + '" was dropped onto "' +  '" ' );
}


//lower widgets-------------------------------------------------------------------------------------------------------------
function handleLowerDropEvent( event, ui ) {
  var lowerDraggable = ui.draggable;
  var lowerDroppable = $(this);
  var lowerDroppableId = lowerDroppable.attr('id');
  var lowerDroppableNumber = lowerDroppableId.replace(/-lowerDroppable/, '');
  var lowerDroppableNumber = lowerDroppableNumber.replace(/bigCol0/, '');
  var lowerDroppableNumber = lowerDroppableNumber.replace(/bigCol/, '');

		var sId = $("#" + lowerDroppableId + '> div').attr('id');
		if(sId) //als sId bestaat
		{
			$("#" + sId + '-lowerDraggable').css({ "display": "block", "left": '0px', 'top': '0px' }); //zet sId terug in menu
		}
		
		
		
		switch(lowerDraggable.attr('id')){ //gaat kijken naar welk LowerWidgetent is gebruikt

			case 'contactForm-lowerDraggable':
				$(function(){
					$("#" + lowerDroppableId).load("widgets/lower/contactForm.php"); 
				});
				aLowerDroppableContents[lowerDroppableNumber] = 'contactForm-lowerDraggable';
			break;
			
			case 'ordersWorldMap-lowerDraggable':
				$(function(){
					$("#" + lowerDroppableId).load("widgets/lower/ordersWorldMap.php"); 
				});
				aLowerDroppableContents[lowerDroppableNumber] = 'ordersWorldMap-lowerDraggable';
			break;
			
			case 'ordersEuMap-lowerDraggable':
				$(function(){
					$("#" + lowerDroppableId).load("widgets/lower/ordersEuMap.php"); 
				});
				aLowerDroppableContents[lowerDroppableNumber] = 'ordersEuMap-lowerDraggable';
			break;
			
			case 'ordersNetherlandsMap-lowerDraggable':
				$(function(){
					$("#" + lowerDroppableId).load("widgets/lower/ordersNetherlandsMap.php"); 
				});
				aLowerDroppableContents[lowerDroppableNumber] = 'ordersNetherlandsMap-lowerDraggable';
			break;
			
			default:
				alert("define lowerDraggable in html and widgets.js");
		}
	  lowerSortableContent();
	  $("#" + lowerDraggable.attr('id')).css("display", "none"); //verwijderd de lowerWidgetent selectie uit het menu
	  disableRightClick();
  
}

function disableLowerDragging(){
	ilowerLock = 1;
}
function enableLowerDragging(){
	ilowerLock = 0;
}
function disableTopDragging(){
	iTopLock = 1;
}
function enableTopDragging(){
	iTopLock = 0;
}