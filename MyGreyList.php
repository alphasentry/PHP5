<?php
// you can insert validation logic into this IF clause to deteremine if this page should be functional
if(true) 
{
// Set the ForceSecure value to TRUE to force this page to an HTTPS connection
$alphaSentryForceSecure = false;
// Set the ShowRaw value to TRUE
$alphaSentryShowRaw = false;
// Enter your AlphaSentry API key here
$alphaSentryKey = '';
// Change this form token to a nonce (to prevent cross-site scripting attacks)
$formToken = 'CHANGE_ME';
// Change the Template Name if you change the name of this file
$alphaSentryTemplateName = 'MyGreyList.php';
// Cancel action if formtoken doesn't match
if(empty($_REQUEST['formToken']) || $_REQUEST['formToken'] != $formToken)
{
	if($_REQUEST['Action'] == 'SetValue' || $_REQUEST['Action'] == 'RemoveItem' || $_REQUEST['Action'] == 'IncrementValue' || $_REQUEST['Action'] == 'DecrementValue')
	{
		die('0');
	}
}

// Be sure to include the proper path of the API helper class
require_once('AlphaSentry.php');

// Creating a new API Helper object
$alphaSentry = new AlphaSentry($alphaSentryKey);

// Forcing to a secure connection if ForceSecure is true and user is not connecting via HTTPS
if ($alphaSentryForceSecure && $_SERVER['HTTPS'] != 'on') 
{ 
	$url = 'https://'. $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']; 
	header('Location: '.$url); 
	exit; 
}

// Initializing all variables if they are not already set.
if(empty($_REQUEST['ItemId']))
	$_REQUEST['ItemId'] = '';
if(empty($_REQUEST['ListName']))
	$_REQUEST['ListName'] = '';
if(empty($_REQUEST['Expires']))
	$_REQUEST['Expires'] = '';
if(empty($_REQUEST['Value']))
	$_REQUEST['Value'] = '';
if(empty($_REQUEST['Action']))
	$_REQUEST['Action'] = '';
if(empty($_REQUEST['Order']))
	$_REQUEST['Order'] = '';
if(empty($_REQUEST['Limit']))
	$_REQUEST['Limit'] = '20';
if(empty($_REQUEST['NextToken']))
	$_REQUEST['NextToken'] = '';

$unescapeVars = array('ListName', 'ItemId', 'Value');
foreach($unescapeVars as $unescapeVar)
{
	if (isset($_REQUEST[$unescapeVar]) && get_magic_quotes_gpc())
	{
		$_REQUEST[$unescapeVar] = stripslashes($_REQUEST[$unescapeVar]);
	}
}

// Perform GreyList actions
switch ($_REQUEST['Action'])
{
	case 'SetValue':
		$alphaSentry->GreyListSetValue($_REQUEST['ItemId'], $_REQUEST['ListName'], $_REQUEST['Expires'], $_REQUEST['Value']);
		break;
	case 'GetValue':
		$alphaSentry->GreyListGetValue($_REQUEST['ItemId'], $_REQUEST['ListName'], $_REQUEST['Expires']);
		break;
	case 'CheckItem':
		$alphaSentry->GreyListCheckItem($_REQUEST['ItemId'], $_REQUEST['ListName'], $_REQUEST['Expires']);
		break;
	case 'RemoveItem':
		$alphaSentry->GreyListRemoveItem($_REQUEST['ItemId'], $_REQUEST['ListName'], $_REQUEST['Expires']);
		break;
	case 'IncrementValue':
		$alphaSentry->GreyListIncrementValue($_REQUEST['ItemId'], $_REQUEST['ListName'], $_REQUEST['Expires'], $_REQUEST['Value']);
		break;;
	case 'DecrementValue':
		$alphaSentry->GreyListDecrementValue($_REQUEST['ItemId'], $_REQUEST['ListName'], $_REQUEST['Expires'], $_REQUEST['Value']);
		break;
	case 'BrowseItems':
		$alphaSentry->GreyListBrowseItems($_REQUEST['Expires'], $_REQUEST['Order'], $_REQUEST['Limit'], $_REQUEST['NextToken']);
		break;
}


// Get credits remaining (only if an API call was executed
$alphaSentryPaidCredits = '';
$alphaSentryFreeCredits = '';
if(isset($alphaSentry->GreyListResponse['FreeCredits']))
	$alphaSentryFreeCredits = $alphaSentry->GreyListResponse['FreeCredits'];
if(isset($alphaSentry->GreyListResponse['PaidCredits']))
	$alphaSentryPaidCredits = $alphaSentry->GreyListResponse['PaidCredits'];

// Read NextToken and set next/previous page buttons as necessary
$alphaSentryNextPage = '';
if(!empty($_REQUEST['NextToken']))
	$alphaSentryNextPage .= '<a class="btn" href="#" onclick="history.go(-1);"><i class="icon-chevron-left"></i> Prev Page</a> ';
if(!empty($alphaSentry->GreyListResponse['NextToken']))
{
	$alphaSentryTempRequest = $_REQUEST;
	$alphaSentryTempRequest['NextToken'] = $alphaSentry->GreyListResponse['NextToken'];
	$alphaSentryNextPage .= '<a class="btn" href="'.$alphaSentryTemplateName.'?'.http_build_query($alphaSentryTempRequest, '', '&amp;').'">Next Page <i class="icon-chevron-right"></i></a> ';
}

// check if page request is an AJAX call
if($_GET['mode'] == 'ajax')
{
	echo $alphaSentry->GreyListResponse['Success'].','.$alphaSentry->GreyListResponse['FreeCredits'].','.$alphaSentry->GreyListResponse['PaidCredits'];
}
else
{
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>My AlphaSentry GreyList</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/bootstrap-responsive.min.css" rel="stylesheet">
	<link href="css/prettify.css" rel="stylesheet" type="text/css" />
</head>

<body onload="">
<script type="text/javascript">
// Remove Item from GreyList and remove from table
function asRemoveItem(ItemId, ListName, Expires)
{
	// Build query string
	var PostData = 'formToken=<? echo $formToken; ?>&Action=RemoveItem&ItemId=' + escape(ItemId) + '&ListName=' + escape(ListName) + '&Expires=' + escape(Expires);

	var Request = new asAjaxRequest();
	Request.ItemId = ItemId;
	Request.ListName = ListName;
	Request.Expires = Expires;
	
	// Set tasks after AJAX request is complete
	Request.onreadystatechange=function()
	{
		if (Request.readyState==4)
		{
			if (Request.status == 200 || window.location.href.indexOf('http') == -1)
			{
				// Process response
				var responseData = Request.responseText.split(',');
				// Update credit counts
				asUpdateCredits(responseData[1], responseData[2]);

				// If it's successful
				if(responseData[0] == '1')
				{
					// Remove from items table
					if(document.getElementById('asBrowseItems_Row' + Request.ListName + '-' + Request.ItemId + '-' + Request.Expires))
						document.getElementById('asBrowseItems_Row' + Request.ListName + '-' + Request.ItemId + '-' + Request.Expires).parentNode.removeChild(document.getElementById('asBrowseItems_Row' + Request.ListName + '-' + Request.ItemId + '-' + Request.Expires));
				}
			}
			else
			{
				//
			}
		}
	};
	// Post AJAX request
	Request.open('POST', './<? echo $alphaSentryTemplateName; ?>?mode=ajax', true);
	Request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	Request.send(PostData);
}

// Update the counts for Free and Paid Credits
function asUpdateCredits(FreeCredits, PaidCredits)
{
	if(document.getElementById('asStatus_value_FreeCredits'))
		document.getElementById('asStatus_value_FreeCredits').innerHTML = FreeCredits;
	if(document.getElementById('asStatus_value_PaidCredits'))
		document.getElementById('asStatus_value_PaidCredits').innerHTML = PaidCredits;
}

// Hide a section when the "Hide" button is clicked
function asHideSection(SectionName)
{
	asHideById('asGreyList_section_' + SectionName);
	asHideById('asGreyList_hide_' + SectionName);
	asShowById('asGreyList_show_' + SectionName);
}

// Show a section when the "Show" button is clicked
function asShowSection(SectionName)
{
	asShowById('asGreyList_section_' + SectionName);
	asShowById('asGreyList_hide_' + SectionName);
	asHideById('asGreyList_show_' + SectionName);
}

// Hide an item by DOM ID if it exists
function asHideById(ElementId)
{
	if(document.getElementById(ElementId))
	{
		document.getElementById(ElementId).style.visibility = 'hidden';
		document.getElementById(ElementId).style.display = 'none';
	}
}

// Show an item by DOM ID if it exists
function asShowById(ElementId)
{
	if(document.getElementById(ElementId))
	{
		document.getElementById(ElementId).style.visibility = 'visible';
		document.getElementById(ElementId).style.display = 'inline';
	}
}

// Define an AJAX request
function asAjaxRequest()
{
	var activexmodes = ['Msxml2.XMLHTTP', 'Microsoft.XMLHTTP'];
	var TransactionId = '';
	var ItemId = '';
	var ListName = '';
	var Expires = '';
	
	if (window.ActiveXObject)
	{
		for (var i = 0; i < activexmodes.length; i++)
		{
			try
			{
				return new ActiveXObject(activexmodes[i]);
			}
			catch(e)
			{
			}
		}
	}
	else if (window.XMLHttpRequest)
		return new XMLHttpRequest();
	else
		return false;
}

</script>
<div class="container">
<!-- Show header, including credit counts if data is available -->
<h1>My AlphaSentry GreyList
	<span class="pull-right">
	<? if(strlen($alphaSentryPaidCredits) > 0 || strlen($alphaSentryFreeCredits) > 0) { ?>
		<small>
			Free Credits: <span id="asStatus_value_FreeCredits"><? echo number_format($alphaSentryFreeCredits); ?></span> Paid Credits: <span id="asStatus_value_PaidCredits"><? echo number_format($alphaSentryPaidCredits); ?></span>
		</small>
	<? } ?>
		<a href="<? echo $alphaSentryTemplateName; ?>" class="btn">Clear All</a>
	</span>
</h1>
<!-- Show errors, if applicable -->
<div class="row">
	<div class="span12" id="asStatus_value_errors">
	<? if(strlen($_REQUEST['Action']) && is_array($alphaSentry->GreyListResponse['Errors']) && count($alphaSentry->GreyListResponse['Errors']) > 0) { ?>
		<div class="alert alert-error">
			<strong>Errors:</strong>
			<ul>
				<? foreach($alphaSentry->GreyListResponse['Errors'] as $alphaSentryError) { ?>
					<li><? echo $alphaSentryError; ?></li>
				<? } ?>
			</ul>
		</div>
	<? } ?>
	</div>
</div>
<div class="row">
	<div class="span12" id="asStatus_value_status">
	</div>
</div>
<!-- Input form for the SetValue API Call -->
<div class="row">
	<div class="span12">
		<div class="well well-small">
			<h3>Set Value
			<span class="pull-right">
				<small>
					<span id="asGreyList_show_SetValue"<? echo ($_REQUEST['Action'] == 'SetValue' ? ' style="visibility:hidden; display:none;"' : ''); ?>><a href="#" onclick="asShowSection('SetValue');">Show</a></span> <span id="asGreyList_hide_SetValue"<? echo ($_REQUEST['Action'] == 'SetValue' ? '' : ' style="visibility:hidden; display:none;"'); ?>><a href="#" onclick="asHideSection('SetValue');">Hide</a></span>
				</small>
			</span></h3>
			<div id="asGreyList_section_SetValue"<? echo ($_REQUEST['Action'] == 'SetValue' ? '' : ' style="visibility:hidden; display:none;"'); ?>>
				<form action="<? echo $alphaSentryTemplateName; ?>" method="GET">
					<fieldset>
						<legend>Set GreyList Item Value</legend>
						<div class="row">
							<div class="span2">
								<label for="asSetValue_ItemId">Item ID</label>
								<input type="text" class="span2" id="asSetValue_ItemId" name="ItemId" value="<? echo htmlspecialchars($_REQUEST['ItemId']); ?>" />
							</div>
							<div class="span2">
								<label for="asSetValue_ListName">List Name</label>
								<input type="text" class="span2" id="asSetValue_ListName" name="ListName" value="<? echo htmlspecialchars($_REQUEST['ListName']); ?>" />
							</div>
							<div class="span2">
								<label for="asSetValue_Expires">Expires</label>
								<select id="asSetValue_Expires" class="span2" name="Expires">
									<option value="Hour"<? echo ($_REQUEST['Expires'] == 'Hour' ? ' selected="selected"' : ''); ?>>Hour</option>
									<option value="Day"<? echo ($_REQUEST['Expires'] == 'Day' ? ' selected="selected"' : ''); ?>>Day</option>
									<option value="Week"<? echo ($_REQUEST['Expires'] == 'Week' ? ' selected="selected"' : ''); ?>>Week</option>
									<option value="Month"<? echo ($_REQUEST['Expires'] == 'Month' ? ' selected="selected"' : ''); ?>>Month</option>
									<option value="Year"<? echo ($_REQUEST['Expires'] == 'Year' ? ' selected="selected"' : ''); ?>>Year</option>
									<option value="Never"<? echo ($_REQUEST['Expires'] == 'Never' ? ' selected="selected"' : ''); ?>>Never</option>
								</select>
							</div>
							<div class="span2">
								<label for="asSetValue_Value">Value</label>
								<input type="text" class="span2" id="asSetValue_Value" name="Value" value="<? echo htmlspecialchars($_REQUEST['Value']); ?>" />
							</div>
							<div class="span1 offset2">
								<label for="">&nbsp;</label>
								<input type="hidden" name="formToken" value="<? echo $formToken; ?>" />
								<input type="hidden" name="Action" value="SetValue" />
								<input type="submit" class="btn" value="Set Value" />
							</div>
						</div>
					</fieldset>
				</form>
			</div>
			<? if($_REQUEST['Action'] == 'SetValue') { ?>
				<h4>Response: "<? echo htmlspecialchars($alphaSentry->GreyListResponse['Value']); ?>"</h4>
			<? } ?>
		</div>
	</div>
</div>
<!-- Input form for the GetValue and CheckItem API calls -->
<div class="row">
	<div class="span12">
		<div class="well well-small">
			<h3>Get Value
			<span class="pull-right">
				<small>
					<span id="asGreyList_show_GetValue"<? echo (($_REQUEST['Action'] == 'GetValue' || $_REQUEST['Action'] == 'CheckItem') ? ' style="visibility:hidden; display:none;"' : ''); ?>><a href="#" onclick="asShowSection('GetValue');">Show</a></span> <span id="asGreyList_hide_GetValue"<? echo (($_REQUEST['Action'] == 'GetValue' || $_REQUEST['Action'] == 'CheckItem') ? '' : ' style="visibility:hidden; display:none;"'); ?>><a href="#" onclick="asHideSection('GetValue');">Hide</a></span>
				</small>
			</span></h3>
			<div id="asGreyList_section_GetValue"<? echo (($_REQUEST['Action'] == 'GetValue' || $_REQUEST['Action'] == 'CheckItem') ? '' : ' style="visibility:hidden; display:none;"'); ?>>
				<form action="<? echo $alphaSentryTemplateName; ?>" method="GET">
					<fieldset>
						<legend>Get or Check GreyList Item Value</legend>
						<div class="row">
							<div class="span2">
								<label for="asGetValue_ItemId">Item ID</label>
								<input type="text" class="span2" id="asSetValue_ItemId" name="ItemId" value="<? echo htmlspecialchars($_REQUEST['ItemId']); ?>" />
							</div>
							<div class="span2">
								<label for="asGetValue_ListName">List Name</label>
								<input type="text" class="span2" id="asGetValue_ListName" name="ListName" value="<? echo htmlspecialchars($_REQUEST['ListName']); ?>" />
							</div>
							<div class="span2">
								<label for="asGetValue_Expires">Expires</label>
								<select id="asGetValue_Expires" class="span2" name="Expires">
									<option value="Hour"<? echo ($_REQUEST['Expires'] == 'Hour' ? ' selected="selected"' : ''); ?>>Hour</option>
									<option value="Day"<? echo ($_REQUEST['Expires'] == 'Day' ? ' selected="selected"' : ''); ?>>Day</option>
									<option value="Week"<? echo ($_REQUEST['Expires'] == 'Week' ? ' selected="selected"' : ''); ?>>Week</option>
									<option value="Month"<? echo ($_REQUEST['Expires'] == 'Month' ? ' selected="selected"' : ''); ?>>Month</option>
									<option value="Year"<? echo ($_REQUEST['Expires'] == 'Year' ? ' selected="selected"' : ''); ?>>Year</option>
									<option value="Never"<? echo ($_REQUEST['Expires'] == 'Never' ? ' selected="selected"' : ''); ?>>Never</option>
								</select>
							</div>
							<div class="span2">
								<label for="asGetValue_Action">Action</label>
								<select id="asGetValue_Action" class="span2" name="Action">
									<option value="GetValue"<? echo ($_REQUEST['Action'] == 'GetValue' ? ' selected="selected"' : ''); ?>>Get Value</option>
									<option value="CheckItem"<? echo ($_REQUEST['Action'] == 'CheckItem' ? ' selected="selected"' : ''); ?>>Check Item</option>
								</select>					</div>
							<div class="span1 offset2">
								<label for="">&nbsp;</label>
								<input type="hidden" name="formToken" value="<? echo $formToken; ?>" />
								<input type="submit" class="btn" value="Get/Check" />
							</div>
						</div>
					</fieldset>
				</form>
			</div>
			<? if($_REQUEST['Action'] == 'GetValue' || $_REQUEST['Action'] == 'CheckItem') { ?>
				<h4>Response: "<? echo htmlspecialchars($alphaSentry->GreyListResponse['Value']); ?>"</h4>
			<? } ?>
		</div>
	</div>
</div>
<!-- Input form for the RemoveItem API Call -->
<div class="row">
	<div class="span12">
		<div class="well well-small">
			<h3>Remove Item
			<span class="pull-right">
				<small>
					<span id="asGreyList_show_RemoveItem"<? echo ($_REQUEST['Action'] == 'RemoveItem' ? ' style="visibility:hidden; display:none;"' : ''); ?>><a href="#" onclick="asShowSection('RemoveItem');">Show</a></span> <span id="asGreyList_hide_RemoveItem"<? echo ($_REQUEST['Action'] == 'RemoveItem' ? '' : ' style="visibility:hidden; display:none;"'); ?>><a href="#" onclick="asHideSection('RemoveItem');">Hide</a></span>
				</small>
			</span></h3>
			<div id="asGreyList_section_RemoveItem"<? echo ($_REQUEST['Action'] == 'RemoveItem' ? '' : ' style="visibility:hidden; display:none;"'); ?>>
				<form action="<? echo $alphaSentryTemplateName; ?>" method="GET">
					<fieldset>
						<legend>Remove GreyList Item</legend>
						<div class="row">
							<div class="span2">
								<label for="asRemoveValue_ItemId">Item ID</label>
								<input type="text" class="span2" id="asRemoveValue_ItemId" name="ItemId" value="<? echo htmlspecialchars($_REQUEST['ItemId']); ?>" />
							</div>
							<div class="span2">
								<label for="asRemoveValue_ListName">List Name</label>
								<input type="text" class="span2" id="asRemoveValue_ListName" name="ListName" value="<? echo htmlspecialchars($_REQUEST['ListName']); ?>" />
							</div>
							<div class="span2">
								<label for="asRemoveValue_Expires">Expires</label>
								<select id="asRemoveValue_Expires" class="span2" name="Expires">
									<option value="Hour"<? echo ($_REQUEST['Expires'] == 'Hour' ? ' selected="selected"' : ''); ?>>Hour</option>
									<option value="Day"<? echo ($_REQUEST['Expires'] == 'Day' ? ' selected="selected"' : ''); ?>>Day</option>
									<option value="Week"<? echo ($_REQUEST['Expires'] == 'Week' ? ' selected="selected"' : ''); ?>>Week</option>
									<option value="Month"<? echo ($_REQUEST['Expires'] == 'Month' ? ' selected="selected"' : ''); ?>>Month</option>
									<option value="Year"<? echo ($_REQUEST['Expires'] == 'Year' ? ' selected="selected"' : ''); ?>>Year</option>
									<option value="Never"<? echo ($_REQUEST['Expires'] == 'Never' ? ' selected="selected"' : ''); ?>>Never</option>
								</select>
							</div>
							<div class="span1 offset4">
								<label for="">&nbsp;</label>
								<input type="hidden" name="Action" value="RemoveItem" />
								<input type="hidden" name="formToken" value="<? echo $formToken; ?>" />
								<input type="submit" class="btn" value="Remove" />
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		<? if($_REQUEST['Action'] == 'RemoveItem') { ?>
			<h4>Response: "<? echo htmlspecialchars($alphaSentry->GreyListResponse['Value']); ?>"</h4>
		<? } ?>
		</div>
	</div>
</div>

<!-- Input form for IncrementValue/DecrementValue API Calls -->
<div class="row">
	<div class="span12">
		<div class="well well-small">
		<h3>Increment/Decrement
		<span class="pull-right">
			<small>
				<span id="asGreyList_show_IncrementValue"<? echo (($_REQUEST['Action'] == 'IncrementValue' || $_REQUEST['Action'] == 'DecrementValue') ? ' style="visibility:hidden; display:none;"' : ''); ?>><a href="#" onclick="asShowSection('IncrementValue');">Show</a></span> <span id="asGreyList_hide_IncrementValue"<? echo (($_REQUEST['Action'] == 'IncrementValue' || $_REQUEST['Action'] == 'DecrementValue') ? '' : ' style="visibility:hidden; display:none;"'); ?>><a href="#" onclick="asHideSection('IncrementValue');">Hide</a></span>
			</small>
		</span></h3>
		<div id="asGreyList_section_IncrementValue"<? echo (($_REQUEST['Action'] == 'IncrementValue' || $_REQUEST['Action'] == 'DecrementValue') ? '' : ' style="visibility:hidden; display:none;"'); ?>>
			<form action="<? echo $alphaSentryTemplateName; ?>" method="GET">
				<fieldset>
					<legend>Increment or Decrement GreyList Item Value</legend>
					<div class="row">
						<div class="span2">
							<label for="asIncrementValue_ItemId">Item ID</label>
							<input type="text" class="span2" id="asIncrementValue_ItemId" name="ItemId" value="<? echo htmlspecialchars($_REQUEST['ItemId']); ?>" />
						</div>
						<div class="span2">
							<label for="asIncrementValue_ListName">List Name</label>
							<input type="text" class="span2" id="asIncrementValue_ListName" name="ListName" value="<? echo htmlspecialchars($_REQUEST['ListName']); ?>" />
						</div>
						<div class="span2">
							<label for="asIncrementValue_Expires">Expires</label>
							<select id="asIncrementValue_Expires" class="span2" name="Expires">
								<option value="Hour"<? echo ($_REQUEST['Expires'] == 'Hour' ? ' selected="selected"' : ''); ?>>Hour</option>
								<option value="Day"<? echo ($_REQUEST['Expires'] == 'Day' ? ' selected="selected"' : ''); ?>>Day</option>
								<option value="Week"<? echo ($_REQUEST['Expires'] == 'Week' ? ' selected="selected"' : ''); ?>>Week</option>
								<option value="Month"<? echo ($_REQUEST['Expires'] == 'Month' ? ' selected="selected"' : ''); ?>>Month</option>
								<option value="Year"<? echo ($_REQUEST['Expires'] == 'Year' ? ' selected="selected"' : ''); ?>>Year</option>
								<option value="Never"<? echo ($_REQUEST['Expires'] == 'Never' ? ' selected="selected"' : ''); ?>>Never</option>
							</select>
						</div>
						<div class="span2">
							<label for="asIncrementValue_Value">Value</label>
							<input type="text" class="span2" id="asIncrementValue_Value" name="Value" value="<? echo htmlspecialchars($_REQUEST['Value']); ?>" />
						</div>
						<div class="span2">
							<label for="asIncrementValue_Action">Action</label>
							<select id="asIncrementValue_Action" class="span2" name="Action">
								<option value="IncrementValue"<? echo ($_REQUEST['Action'] == 'IncrementValue' ? ' selected="selected"' : ''); ?>>Increment</option>
								<option value="DecrementValue"<? echo ($_REQUEST['Action'] == 'DecrementValue' ? ' selected="selected"' : ''); ?>>Decrement</option>
							</select>
						</div>
						<div class="span1">
							<label for="">&nbsp;</label>
							<input type="hidden" name="formToken" value="<? echo $formToken; ?>" />
							<input type="submit" class="btn" value="Update" />
						</div>
					</div>
				</fieldset>
			</form>
		</div>
		<? if($_REQUEST['Action'] == 'IncrementValue' || $_REQUEST['Action'] == 'DecrementValue') { ?>
			<h4>Response: "<? echo htmlspecialchars($alphaSentry->GreyListResponse['Value']); ?>"</h4>
		<? } ?>
		</div>
	</div>
</div>
<!-- Input form for BrowseItems API Call -->
<div class="row">
	<div class="span12">
		<div class="well well-small">
			<h3>Browse Items
			<span class="pull-right">
				<small>
					<span id="asGreyList_show_BrowseItems"<? echo (($_REQUEST['Action'] == 'BrowseItems' || empty($_REQUEST['Action'])) ? ' style="visibility:hidden; display:none;"' : ''); ?>><a href="#" onclick="asShowSection('BrowseItems');">Show</a></span> <span id="asGreyList_hide_BrowseItems"<? echo (($_REQUEST['Action'] == 'BrowseItems' || empty($_REQUEST['Action'])) ? '' : ' style="visibility:hidden; display:none;"'); ?>><a href="#" onclick="asHideSection('BrowseItems');">Hide</a></span>
				</small>
			</span></h3>
			<div id="asGreyList_section_BrowseItems"<? echo (($_REQUEST['Action'] == 'BrowseItems' || empty($_REQUEST['Action'])) ? '' : ' style="visibility:hidden; display:none;"'); ?>>
				<form action="<? echo $alphaSentryTemplateName; ?>" method="GET">
					<fieldset>
						<legend>Browse GreyList Items</legend>
						<div class="row">
							<div class="span2">
								<label for="asBrowseItems_Expires">Expires</label>
								<select id="asBrowseItems_Expires" class="span2" name="Expires">
									<option value="Hour"<? echo ($_REQUEST['Expires'] == 'Hour' ? ' selected="selected"' : ''); ?>>Hour</option>
									<option value="Day"<? echo ($_REQUEST['Expires'] == 'Day' ? ' selected="selected"' : ''); ?>>Day</option>
									<option value="Week"<? echo ($_REQUEST['Expires'] == 'Week' ? ' selected="selected"' : ''); ?>>Week</option>
									<option value="Month"<? echo ($_REQUEST['Expires'] == 'Month' ? ' selected="selected"' : ''); ?>>Month</option>
									<option value="Year"<? echo ($_REQUEST['Expires'] == 'Year' ? ' selected="selected"' : ''); ?>>Year</option>
									<option value="Never"<? echo ($_REQUEST['Expires'] == 'Never' ? ' selected="selected"' : ''); ?>>Never</option>
								</select>
							</div>
							<div class="span2">
								<label for="asBrowseItems_Value">Limit</label>
								<input type="text" class="span2" id="asBrowseItems_Value" name="Limit" value="<? echo htmlspecialchars($_REQUEST['Limit']); ?>" />
							</div>
							<div class="span2">
								<label for="asBrowseItems_Action">Order</label>
								<select id="asBrowseItems_Action" class="span2" name="Order">
									<option value="DESC"<? echo ($_REQUEST['Order'] == 'DESC' ? ' selected="selected"' : ''); ?>>Descending</option>
									<option value="ASC"<? echo ($_REQUEST['Order'] == 'ASC' ? ' selected="selected"' : ''); ?>>Ascending</option>
								</select>
							</div>
							<div class="span1">
								<label for="">&nbsp;</label>
								<input type="hidden" name="Action" value="BrowseItems" />
								<input type="submit" class="btn" value="Update" />
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
		<!-- Displaying items if BrowseItems API call returns items -->
		<? if($_REQUEST['Action'] == 'BrowseItems') { ?>
		<div><? echo count($alphaSentry->GreyListResponse['Items']).' items found. '.$alphaSentryNextPage; ?></div>
		<? if(count($alphaSentry->GreyListResponse['Items']) > 0) { ?>
		<table class="table table-striped table-condensed">
			<thead>
				<tr>
					<th>Item ID</th>
					<th>List Name</th>
					<th>Expires</th>
					<th>Value</th>
					<th>Item Time</th>
					<td></td>
				</tr>
			</thead>
			<tbody>
				<? foreach ($alphaSentry->GreyListResponse['Items'] as $item) { ?>
					<tr id="asBrowseItems_Row<? echo $item->ListName; ?>-<? echo $item->ItemId; ?>-<? echo addslashes($item->Expires); ?>">
						<td><? echo $item->ItemId; ?></td>
						<td><? echo $item->ListName; ?></td>
						<td><? echo $item->Expires; ?></td>
						<td><? echo $item->Value; ?></td>
						<td><? echo date('m/d/y h:ia', $item->ItemTime);?></td>
						<td><a href="#" class="btn" onclick="asRemoveItem('<? echo addslashes($item->ItemId); ?>', '<? echo addslashes($item->ListName); ?>', '<? echo addslashes($item->Expires); ?>');">Remove</a></td>
					</tr>
				<? } ?>
			</tbody>
		</table>
		<? }} ?>
	</div>
</div>
<?
// Display Raw SOAP data if $alphaSentryShowRaw is set to TRUE
if($alphaSentryShowRaw && strlen($alphaSentry->GreyListClient->__getLastRequest()))
{
?>
<h2>Raw Request</h2>
<pre class="prettyprint"><? 
	$alphaSentryDom = new DOMDocument;
	$alphaSentryDom->preserveWhiteSpace = FALSE;
	$alphaSentryDom->loadXML($alphaSentry->GreyListClient->__getLastRequest());
	$alphaSentryDom->formatOutput = TRUE;
	echo htmlspecialchars($alphaSentry->RedactApiKey($alphaSentryDom->saveXml())); 
?></pre>
<h2>Raw Response</h2>
<pre class="prettyprint"><? 
	$alphaSentryDom = new DOMDocument;
	$alphaSentryDom->preserveWhiteSpace = FALSE;
	$alphaSentryDom->loadXML($alphaSentry->GreyListClient->__getLastResponse());
	$alphaSentryDom->formatOutput = TRUE;
	echo htmlspecialchars($alphaSentry->RedactApiKey($alphaSentryDom->saveXml()));
?></pre>
<script type="text/javascript" src="https://google-code-prettify.googlecode.com/svn/trunk/src/prettify.js"></script>
<script type="text/javascript">
	prettyPrint();
</script>
<?
}
?>
</div>
</body>
</html><?
}}
?>
