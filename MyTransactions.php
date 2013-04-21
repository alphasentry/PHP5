<?php


// you can insert validation logic into this IF clause to deteremine if this page should be functional
if(true) 
{
// Set the ForceSecure value to TRUE to force this page to an HTTPS connection
$alphaSentryForceSecure = false;
// Set the ShowRaw value to TRUE to show raw SOAP data
$alphaSentryShowRaw = false;
// Enter your AlphaSentry API key here
$alphaSentryKey = '';
// Change this form token to a dynamic nonce (to prevent cross-site scripting attacks)
$formToken = 'CHANGE_ME';
// Change the Template Name if you change the name of this file
$alphaSentryTemplateName = 'MyTransactions.php';
// Killing the session if the form token doesn't match
if(empty($_REQUEST['formToken']) || $_REQUEST['formToken'] != $formToken)
{
	if($_REQUEST['Action'] == 'FlagTransaction' || $_REQUEST['Action'] == 'UnflagTransaction' || $_REQUEST['Action'] == 'DeleteTransaction')
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

// Array of the types of transactions
$alphaSentryTransactionTypes = array('Login', 'Account', 'Purchase', 'Other');
// Array of the valid fields by which to order the transaction results
$alphaSentryOrderBys = array('FlagCount' => 'Flag Count', 'RiskScore' => 'Risk Score', 'TransactionTime' => 'Transaction Time', 'GeoIpDistance' => 'GeoIp Distance', 'DevicesPerUser' => 'Devices Per User', 'UsersPerDevice' => 'Users Per Device', 'PurchasesPerIp' => 'Purchases Per Ip', 'AccountsPerIp' => 'Accounts Per Ip', 'PurchasesPerDevice' => 'Purchases Per Device');
// Array of the transaction fields
$alphaSentryFields = array('TransactionId' => 'Trans. Id', 'UserId' => 'User Id', 'UserIp' => 'IP', 'UserUserAgent' => 'UserAgent', 'UserVar1' => 'Var1', 'UserVar2' => 'Var2', 'PurchaseId' => 'Purchase Id', 'DeviceId' => 'Device', 'TransactionType' => 'Type', 'TransactionTime' => 'Time', 'RiskScore' => 'Score', 'ClientIp' => 'Server IP', 'ClientDomainName' => 'Server Domain', 'Flagged' => 'Flagged', 'DevicesPerUser' => 'D/U', 'UsersPerDevice' => 'U/D', 'AccountsPerIp' => 'A/IP', 'PurchasesPerIp' => 'P/IP', 'PurchasesPerDevice' => 'P/D', 'FlagCount' => 'Flags', 'UserCountryCode' => 'Country', 'GeoIpCity' => 'Geo City', 'GeoIpRegion' => 'Geo Reg', 'GeoIpCountryCode' => 'IP Country', 'GeoIpDistance' => 'Distance', 'FlagReason' => 'Flag Reason', 'FlagComment' => 'Flag Comment');
// Array of the fields on which the user can click and automatically filter results
$alphaSentryClickableFields = array('DeviceId', 'UserId', 'UserIp', 'UserUserAgent', 'UserVar1', 'UserVar2');
// Array of ordering options... ascending or descending
$alphaSentryOrders = array('ASC' => 'ASC', 'DESC' => 'DESC');
// Array of query size options
$alphaSentryLimits = array(10, 25, 50, 100, 250, 1000, 2500);
// Array of geographic distance filtering options (in km)
$alphaSentryDistances = array(10, 25, 50, 100, 250, 1000, 2000, 4000);

// Setting default return limit to 100 transactions
if(!isset($_REQUEST['Limit']))
	$_REQUEST['Limit'] = 100;

// Process page action
switch ($_REQUEST['Action']) 
{
	case 'BrowseTransactions':
	case '':
		$alphaSentry->BrowseTransactions($_REQUEST['TransactionType'], strlen($_REQUEST['StartDate'])?strtotime($_REQUEST['StartDate']):'', strlen($_REQUEST['EndDate'])?strtotime($_REQUEST['EndDate']):'', $_REQUEST['MinFlagCount'], $_REQUEST['MaxFlagCount'], $_REQUEST['MinRiskScore'], $_REQUEST['MaxRiskScore'], $_REQUEST['UserVar1'], $_REQUEST['UserVar2'], $_REQUEST['ClientIp'], $_REQUEST['ClientDomainName'], $_REQUEST['MinGeoIpDistance'], $_REQUEST['MaxGeoIpDistance'], $_REQUEST['OrderBy'], $_REQUEST['Order'], $_REQUEST['Limit'], $_REQUEST['NextToken']);
	break;
	case 'GetTransactions':
		$alphaSentry->GetTransactions($_REQUEST['TransactionId'], $_REQUEST['TransactionType'], $_REQUEST['DeviceId'], $_REQUEST['UserId'], $_REQUEST['UserIp'], $_REQUEST['UserUserAgent'], $_REQUEST['PurchaseId'], $_REQUEST['UserVar1'], $_REQUEST['UserVar2'], $_REQUEST['OrderBy'], $_REQUEST['Order'], $_REQUEST['Limit'], $_REQUEST['NextToken']);
	break;
	case 'FlagTransaction':
		$alphaSentry->FlagTransaction($_REQUEST['TransactionId'], $_REQUEST['FlagReason'], $_REQUEST['FlagComment']);
		if($alphaSentry->Response['Success'])
			$alphaSentryReturnString = $alphaSentry->Response['Success'].','.$alphaSentry->Response['FreeCredits'].','.$alphaSentry->Response['PaidCredits'];
	break;
	case 'UnflagTransaction':
		$alphaSentry->UnflagTransaction($_REQUEST['TransactionId']);
		if($alphaSentry->Response['Success'])
			$alphaSentryReturnString = $alphaSentry->Response['Success'].','.$alphaSentry->Response['FreeCredits'].','.$alphaSentry->Response['PaidCredits'];
	break;
	case 'DeleteTransaction':
		$alphaSentry->DeleteTransaction($_REQUEST['TransactionId']);
		if($alphaSentry->Response['Success'])
			$alphaSentryReturnString = $alphaSentry->Response['Success'].','.$alphaSentry->Response['FreeCredits'].','.$alphaSentry->Response['PaidCredits'];
	break;
}

// Initializing the transaction table
$alphaSentryTransactionTable = '';
// If an array of transactions is returned...
if(is_array($alphaSentry->Response['Transactions']))
{
	// By default hide all fields unless a value is returned in one of the results
	$alphaSentryExcludedFields = $alphaSentryFields;
	$alphaSentryIncludedFields = array();
	foreach($alphaSentry->Response['Transactions'] as $alphaSentryTransaction)
	{
		$alphaSentryTransaction = get_object_vars($alphaSentryTransaction);
		foreach($alphaSentryExcludedFields as $alphaSentryFieldName => $alphaSentryFieldHeader)
		{
			if(!empty($alphaSentryTransaction[$alphaSentryFieldName]))
			{
				$alphaSentryIncludedFields[$alphaSentryFieldName] = $alphaSentryFieldHeader;
				unset($alphaSentryExcludedFields[$alphaSentryFieldName]);
			}
		}
	}
	// List headers for Transactions Table
	$alphaSentryFieldHeaders = '
	<thead>
	<tr>';
	foreach($alphaSentryIncludedFields as  $alphaSentryFieldName => $alphaSentryFieldHeader)
		$alphaSentryFieldHeaders .= '<th style="white-space: nowrap;">'.$alphaSentryFieldHeader.'</th>';
	$alphaSentryFieldHeaders .= '<td></td><td></td>
	</tr>
	</thead>';
	
	// Create next/previous page buttons if applicable with the NextToken value
	$alphaSentryNextPage = '';
	
	if(!empty($_REQUEST['NextToken']))
		$alphaSentryNextPage .= '<a class="btn" href="#" onclick="history.go(-1);"><i class="icon-chevron-left"></i> Prev Page</a> ';
	if(!empty($alphaSentry->Response['NextToken']))
	{
		$alphaSentryTempRequest = $_REQUEST;
		unset($alphaSentryTempRequest['mode']);
		$alphaSentryTempRequest['NextToken'] = $alphaSentry->Response['NextToken'];
		$alphaSentryNextPage .= '<a class="btn" href="'.$alphaSentryTemplateName.'?'.http_build_query($alphaSentryTempRequest, '', '&amp;').'">Next Page <i class="icon-chevron-right"></i></a> ';
	}
	
	// Start the Transactions Table
	$alphaSentryTransactionTable = '<div>'.count($alphaSentry->Response['Transactions']).' transactions found. '.$alphaSentryNextPage.'</div>
		<table class="table table-striped table-condensed">'.$alphaSentryFieldHeaders.'<tbody>';
	
	// Add each transaction to the table
	foreach($alphaSentry->Response['Transactions'] as $alphaSentryTransaction)
	{
		$alphaSentryTransaction = get_object_vars($alphaSentryTransaction);
		$alphaSentryTransactionTable .= '
		<tr id="asTransactions_row_'.$alphaSentryTransaction['TransactionId'].'">'; 
		foreach($alphaSentryIncludedFields as $alphaSentryFieldName => $alphaSentryFieldHeader)
		{
			$alphaSentryTransactionTable .= '<td id="asTransactions_'.$alphaSentryTransaction['TransactionId'].'_'.$alphaSentryFieldName.'" style="white-space: nowrap;">';
			if($alphaSentryFieldName == 'TransactionId')
				$alphaSentryTransactionTable .= '<a href="#" onclick="asShowTransaction(\''.$alphaSentryTransaction['TransactionId'].'\');">';
			if(in_array($alphaSentryFieldName, $alphaSentryClickableFields))
				$alphaSentryTransactionTable .= '<a href="#" onclick="document.getElementById(\'asSearch_'.$alphaSentryFieldName.'\').value = \''.htmlspecialchars($alphaSentryTransaction[$alphaSentryFieldName]).'\'; asGetTransactions();">';
			if( $alphaSentryFieldName == 'TransactionTime')
			{
				if($alphaSentryFieldName == 'TransactionTime')
					$alphaSentryTransactionTable .= date('m/d/y h:ia', $alphaSentryTransaction['TransactionTime']);
			}
			else
			{
				if(is_numeric($alphaSentryTransaction[$alphaSentryFieldName]) || strlen($alphaSentryTransaction[$alphaSentryFieldName]) < strlen($alphaSentryFieldHeader) || $alphaSentryFieldName == 'UserId' || $alphaSentryFieldName == 'UserIp' || $alphaSentryFieldName == 'TransactionType')
					$alphaSentryTransactionTable .= htmlspecialchars($alphaSentryTransaction[$alphaSentryFieldName]);
				else
					$alphaSentryTransactionTable .= '<span title="'.htmlspecialchars($alphaSentryTransaction[$alphaSentryFieldName]).'">'.htmlspecialchars(substr($alphaSentryTransaction[$alphaSentryFieldName], 0, max((strlen($alphaSentryFieldHeader) - 3), 3)).'...').'</span>';
			}
			if($alphaSentryFieldName == 'TransactionId' || in_array($alphaSentryFieldName, $alphaSentryClickableFields))
				$alphaSentryTransactionTable .= '</a>';
			$alphaSentryTransactionTable .= '</td>';
		}
		
		if(!empty($alphaSentryTransaction['Flagged']) && $alphaSentryTransaction['Flagged'])
			$alphaSentryTransactionTable .= '<td id="asTransactions_'.$alphaSentryTransaction['TransactionId'].'_Flag" style="text-align: center;"><a href="#" onclick="asUnflagTransaction(\''.$alphaSentryTransaction['TransactionId'].'\');" class="btn">Unflag</a>';
		else
			$alphaSentryTransactionTable .= '<td id="asTransactions_'.$alphaSentryTransaction['TransactionId'].'_Flag" style="text-align: center;"><a href="#" onclick="asFlagTransactionQuick(\''.$alphaSentryTransaction['TransactionId'].'\');" class="btn">Flag</a>';
		$alphaSentryTransactionTable .= '<td><a href="#" onclick="asDeleteTransaction(\''.$alphaSentryTransaction['TransactionId'].'\');" class="btn">Delete</a></td>';
		
		$alphaSentryTransactionTable .= '<form action="'.$alphaSentryTemplateName.'">';
			foreach($alphaSentryFields as $alphaSentryFieldName => $alphaSentryFieldHeader)
				$alphaSentryTransactionTable .= '<input type="hidden" id="asTransactions_field_'.$alphaSentryTransaction['TransactionId'].'_'.$alphaSentryFieldName.'" name="'.$alphaSentryFieldName.'" value="'.htmlspecialchars($alphaSentryTransaction[$alphaSentryFieldName]).'" />';
		$alphaSentryTransactionTable .= '</form></td>';
		$alphaSentryTransactionTable .= '</tr>'; 
	}
	$alphaSentryTransactionTable .= '</tbody></table>'; 
}

// Show Errors if applicable
if(is_array($alphaSentry->Response['Errors']))
	$alphaSentryErrorsString = implode(';', $alphaSentry->Response['Errors']);
else
	$alphaSentryErrorsString = '';
// Show credits if applicable
if(!strlen($alphaSentry->Response['FreeCredits']))
	$alphaSentry->Response['FreeCredits'] = 0;
if(!strlen($alphaSentry->Response['PaidCredits']))
	$alphaSentry->Response['PaidCredits'] = 0;

// Add javascript to update the credits strings
$alphaSentryTransactionTable .= '
<form action="'.$alphaSentryTemplateName.'"><input type="hidden" name="FreeCredits" id="asStatus_Transactions_FreeCredits" value="'.$alphaSentry->Response['FreeCredits'].'" /><input type="hidden" name="PaidCredits" id="asStatus_Transactions_PaidCredits" value="'.$alphaSentry->Response['PaidCredits'].'" /><input type="hidden" name="Errors" id="asStatus_Transactions_Errors" value="'.htmlspecialchars($alphaSentryErrorsString).'" /></form>
<script type="text/javascript"> 
<!-- 
asUpdateCredits(\''.$alphaSentry->Response['FreeCredits'].'\',\''.$alphaSentry->Response['PaidCredits'].'\');
-->
</script>';

// Generate options to select transaction type
$alphaSentryTransactionTypeSelect = '';
foreach($alphaSentryTransactionTypes as $alphaSentryTransactionType)
{
	$alphaSentrySelected = '';
	if($_REQUEST['TransactionType'] == $alphaSentryTransactionType)
		$alphaSentrySelected = ' selected="selected"';
	$alphaSentryTransactionTypeSelect .= '
		<option value="'.$alphaSentryTransactionType.'"'.$alphaSentrySelected.'>'.$alphaSentryTransactionType.'</option>';
}

// Generate options to select GeoIP distance filters
$alphaSentryMinGeoIpDistanceSelect = '';
foreach($alphaSentryDistances as $alphaSentryDistance)
{
	$alphaSentrySelected = '';
	if($_REQUEST['MinGeoIpDistance'] == $alphaSentryDistance)
		$alphaSentrySelected = ' selected="selected"';
	$alphaSentryMinGeoIpDistanceSelect .= '
		<option value="'.$alphaSentryDistance.'"'.$alphaSentrySelected.'>'.$alphaSentryDistance.'</option>';
}
$alphaSentryMaxGeoIpDistanceSelect = '';
foreach($alphaSentryDistances as $alphaSentryDistance)
{
	$alphaSentrySelected = '';
	if($_REQUEST['MaxGeoIpDistance'] == $alphaSentryDistance)
		$alphaSentrySelected = ' selected="selected"';
	$alphaSentryMaxGeoIpDistanceSelect .= '
		<option value="'.$alphaSentryDistance.'"'.$alphaSentrySelected.'>'.$alphaSentryDistance.'</option>';
}

// Generate options to select transactions ordering and size limiting options
$alphaSentryOrderBySelect = '';
foreach($alphaSentryOrderBys as $alphaSentryOrderByName => $alphaSentryOrderBy)
{
	$alphaSentrySelected = '';
	if($_REQUEST['OrderBy'] == $alphaSentryOrderByName)
		$alphaSentrySelected = ' selected="selected"';
	$alphaSentryOrderBySelect .= '
		<option value="'.$alphaSentryOrderByName.'"'.$alphaSentrySelected.'>'.$alphaSentryOrderBy.'</option>';
}
$alphaSentryOrderSelect = '';
foreach($alphaSentryOrders as $alphaSentryOrderName => $alphaSentryOrder)
{
	$alphaSentrySelected = '';
	if($_REQUEST['Order'] == $alphaSentryOrderName)
		$alphaSentrySelected = ' selected="selected"';
	$alphaSentryOrderSelect .= '
		<option value="'.$alphaSentryOrderName.'"'.$alphaSentrySelected.'>'.$alphaSentryOrder.'</option>';
}
$alphaSentryLimitSelect = '';
foreach($alphaSentryLimits as $alphaSentryLimit)
{
	$alphaSentrySelected = '';
	if($_REQUEST['Limit'] == $alphaSentryLimit)
		$alphaSentrySelected = ' selected="selected"';
	$alphaSentryLimitSelect .= '
		<option value="'.$alphaSentryLimit.'"'.$alphaSentrySelected.'>'.$alphaSentryLimit.'</option>';
}

// If page request isn't an AJAX request...
if($_GET['mode'] != 'ajax')
{
	// Display the full form and page
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>My AlphaSentry Transactions</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/bootstrap-responsive.min.css" rel="stylesheet">
	<link href="css/prettify.css" rel="stylesheet" type="text/css" />
</head>

<body onload="">
<div class="container">
<script type="text/javascript">
	// Makes API Call to BrowseTransactions
	function asBrowseTransactions()
	{
		asShowBrowse();

		// Build the query string
		var BrowseFieldList = 'TransactionType, StartDate, EndDate, MinFlagCount, MaxFlagCount, MinRiskScore, MaxRiskScore, UserVar1, UserVar2, ClientIp, ClientDomainName, MinGeoIpDistance, MaxGeoIpDistance, OrderBy, Order, Limit, NextToken';
		var BrowseFields = BrowseFieldList.split(', ');
		var Prefix = 'asBrowse_';
		var PostData = 'Action=BrowseTransactions';
		for (var CurrentField in BrowseFields)
		{
			if(document.getElementById(Prefix + BrowseFields[CurrentField]))
			{
				if(document.getElementById(Prefix + BrowseFields[CurrentField]).value)
				{
					PostData += '&' + BrowseFields[CurrentField] + '=' + escape(document.getElementById(Prefix + BrowseFields[CurrentField]).value);
				} else if(document.getElementById(Prefix + BrowseFields[CurrentField]).options && document.getElementById(Prefix + BrowseFields[CurrentField]).options[document.getElementById(Prefix + BrowseFields[CurrentField]).selectedIndex].value != '')
				{
					PostData += '&' + BrowseFields[CurrentField] + '=' + escape(document.getElementById(Prefix + BrowseFields[CurrentField]).options[document.getElementById(Prefix + BrowseFields[CurrentField]).selectedIndex].value);
				}
			}
		}

		// Set tasks after AJAX request is complete
		var Request = new asAjaxRequest();
		Request.onreadystatechange=function()
		{
			if (Request.readyState==4)
			{
				if (Request.status == 200 || window.location.href.indexOf('http') == -1)
				{
					// Update Transactions table
					document.getElementById('as_transactions').innerHTML = Request.responseText;
					// Update credits
					asUpdateCredits(document.getElementById('asStatus_Transactions_FreeCredits').value, document.getElementById('asStatus_Transactions_PaidCredits').value);
					// Update status and errors
					asUpdateStatus('Request completed. (' + PostData + ')');
					asUpdateErrors(document.getElementById('asStatus_Transactions_Errors').value);
				}
				else
				{
					//
				}
			}
		};
		Request.open('POST', './<? echo $alphaSentryTemplateName; ?>?mode=ajax', true);
		Request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		Request.send(PostData);
		asUpdateStatus('Requesting transactions... (' + PostData + ')');
	}

	// Reset all of the Browse variables
	function asClearBrowse()
	{
		var BrowseFieldList = 'TransactionType, StartDate, EndDate, MinFlagCount, MaxFlagCount, MinRiskScore, MaxRiskScore, UserVar1, UserVar2, ClientIp, ClientDomainName, MinGeoIpDistance, MaxGeoIpDistance, OrderBy, Order, Limit, NextToken';
		var BrowseFields = BrowseFieldList.split(', ');
		var Prefix = 'asBrowse_';
		for (var CurrentField in BrowseFields)
		{
			if(document.getElementById(Prefix + BrowseFields[CurrentField]))
			{
				if(document.getElementById(Prefix + BrowseFields[CurrentField]).value)
				{
					document.getElementById(Prefix + BrowseFields[CurrentField]).value = '';
				} else if(document.getElementById(Prefix + BrowseFields[CurrentField]).options)
				{
					document.getElementById(Prefix + BrowseFields[CurrentField]).selectedIndex = 0;
				}
			}
		}
	}

	// Show the browse section
	function asShowBrowse()
	{
		asShowById('as_browse');
		asShowById('as_browse_hide');
		asHideById('as_browse_show');
	}

	// Hide the browse section
	function asHideBrowse()
	{
		asHideById('as_browse');
		asShowById('as_browse_show');
		asHideById('as_browse_hide');
	}

	// Execute the GetTransactions API call and update the transactions table
	function asGetTransactions()
	{
		asShowSearch();

		// Build the query string
		var GetFieldList = 'TransactionId, TransactionType, DeviceId, UserId, UserIp, UserUserAgent, PurchaseId, UserVar1, UserVar2, OrderBy, Order, Limit, NextToken';
		var GetFields = GetFieldList.split(', ');
		var Prefix = 'asSearch_';
		var PostData = 'Action=GetTransactions';
		for (var CurrentField in GetFields)
		{
			if(document.getElementById(Prefix + GetFields[CurrentField]))
			{
				if(document.getElementById(Prefix + GetFields[CurrentField]).value)
				{
					PostData += '&' + GetFields[CurrentField] + '=' + escape(document.getElementById(Prefix + GetFields[CurrentField]).value);
				} else if(document.getElementById(Prefix + GetFields[CurrentField]).options && document.getElementById(Prefix + GetFields[CurrentField]).options[document.getElementById(Prefix + GetFields[CurrentField]).selectedIndex].value != '')
				{
					PostData += '&' + GetFields[CurrentField] + '=' + escape(document.getElementById(Prefix + GetFields[CurrentField]).options[document.getElementById(Prefix + GetFields[CurrentField]).selectedIndex].value);
				}
			}
		}

		// Set tasks after AJAX request is complete
		var Request = new asAjaxRequest();
		Request.onreadystatechange=function()
		{
			if (Request.readyState==4)
			{
				if (Request.status == 200 || window.location.href.indexOf('http') == -1)
				{
					// Update Transactions Table
					document.getElementById('as_transactions').innerHTML = Request.responseText;
					// Update Transaction Counts
					asUpdateCredits(document.getElementById('asStatus_Transactions_FreeCredits').value, document.getElementById('asStatus_Transactions_PaidCredits').value);
					// Update status and errors
					asUpdateStatus('Request completed. (' + PostData + ')');
					asUpdateErrors(document.getElementById('asStatus_Transactions_Errors').value);
				}
				else
				{
					//
				}
			}
		};

		// Post the query String
		Request.open('POST', './<? echo $alphaSentryTemplateName; ?>?mode=ajax', true);
		Request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		Request.send(PostData);
		asUpdateStatus('Requesting transactions... (' + PostData + ')');
	}

	// Reset all of the Get variables
	function asClearGet()
	{
		var GetFieldList = 'TransactionId, TransactionType, DeviceId, UserId, UserIp, UserUserAgent, PurchaseId, UserVar1, UserVar2, OrderBy, Order, Limit, NextToken';
		var GetFields = GetFieldList.split(', ');
		var Prefix = 'asSearch_';
		for (var CurrentField in GetFields)
		{
			if(document.getElementById(Prefix + GetFields[CurrentField]))
			{
				if(document.getElementById(Prefix + GetFields[CurrentField]).value)
				{
					document.getElementById(Prefix + GetFields[CurrentField]).value = '';
				} else if(document.getElementById(Prefix + GetFields[CurrentField]).options)
				{
					document.getElementById(Prefix + GetFields[CurrentField]).selectedIndex = 0;
				}
			}
		}
	}

	// Show the Search Transactions form
	function asShowSearch()
	{
		asShowById('as_search');
		asShowById('as_search_hide');
		asHideById('as_search_show');
	}

	// Hide the Search Transactions form
	function asHideSearch()
	{
		asHideById('as_search');
		asShowById('as_search_show');
		asHideById('as_search_hide');
	}

	// Show a selected transaction
	function asShowTransaction(TransactionId)
	{
		var FieldList = '<? $alphaSentryFieldNames = array_keys($alphaSentryFields); echo implode(', ', $alphaSentryFieldNames); ?>';
		var Fields = FieldList.split(', ');
		var Prefix = 'asTransactions_field_';
		var ShowPrefix = 'asTransaction_value_';
		var GeoFieldList = 'UserCountryCode, GeoIpLatitude, GeoIpLongitude, GeoIpRegion, GeoIpCity, GeoIpCountryCode, GeoIpDistance';
		var GeoFields = GeoFieldList.split(', ');
		var ShowGeoTable = false;
		var Flagged = false;
		
		if(document.getElementById('asTransaction_id'))
			document.getElementById('asTransaction_id').value = TransactionId;

		// Update all of the Transaction fields with the data from the selected transaction
		for (var CurrentField in Fields)
		{
			if(Fields[CurrentField] == 'Flagged')
			{
				if(document.getElementById(Prefix + TransactionId + '_' + Fields[CurrentField]) && document.getElementById(Prefix + TransactionId + '_' + Fields[CurrentField]).value == '1')
					Flagged = true;
			}
			if(document.getElementById(Prefix + TransactionId + '_' + Fields[CurrentField]))
			{
				if(document.getElementById(ShowPrefix + Fields[CurrentField]))
				{
					if(Fields[CurrentField] == 'TransactionTime' || Fields[CurrentField] == 'FlagReason' || Fields[CurrentField] == 'GeoIpDistance')
					{
						if(Fields[CurrentField] == 'TransactionTime')
						{
							document.getElementById(ShowPrefix + Fields[CurrentField]).innerHTML = new Date(document.getElementById(Prefix + TransactionId + '_' + Fields[CurrentField]).value * 1000);
						}
						if(Fields[CurrentField] == 'FlagReason')
						{
							document.getElementById(ShowPrefix + Fields[CurrentField]).value = document.getElementById(Prefix + TransactionId + '_' + Fields[CurrentField]).value;
						}
						if(Fields[CurrentField] == 'GeoIpDistance')
						{
							if(document.getElementById(Prefix + TransactionId + '_' + Fields[CurrentField]).value.length > 0)
								document.getElementById(ShowPrefix + Fields[CurrentField]).value = document.getElementById(Prefix + TransactionId + '_' + Fields[CurrentField]).value + 'km';
							else
								document.getElementById(ShowPrefix + Fields[CurrentField]).value = document.getElementById(Prefix + TransactionId + '_' + Fields[CurrentField]).value + '&nbsp;';
						}
					}
					else
					{
						if(Fields[CurrentField] == 'FlagComment' || document.getElementById(Prefix + TransactionId + '_' + Fields[CurrentField]).value.length > 0)
							document.getElementById(ShowPrefix + Fields[CurrentField]).innerHTML = document.getElementById(Prefix + TransactionId + '_' + Fields[CurrentField]).value;
						else
							document.getElementById(ShowPrefix + Fields[CurrentField]).innerHTML = document.getElementById(Prefix + TransactionId + '_' + Fields[CurrentField]).value + '&nbsp;';
					}
				} 
			}
		}

		// Determine whether to show fields related to Geographic data
		for (var CurrentField in GeoFields)
		{
			if(document.getElementById(Prefix + TransactionId + '_' + GeoFields[CurrentField]))
			{
				if(document.getElementById(Prefix + TransactionId + '_' + GeoFields[CurrentField]).value != '')
					ShowGeoTable = true;
			}
		}

		// Show the transactions div
		if(document.getElementById('as_transaction'))
		{
			document.getElementById('as_transaction').style.visibility = 'visible';
			document.getElementById('as_transaction').style.display = 'block';
			asHideById('as_transaction_show');
			asShowById('as_transaction_hide');
		}
		if(ShowGeoTable)
			asShowById('asTransaction_table_GeoIp');
		else
			asHideById('asTransaction_table_GeoIp');

		// Show flagged data
		if(Flagged)
		{
			if(document.getElementById('asTransaction_table_Flag_buttons'))
				document.getElementById('asTransaction_table_Flag_buttons').innerHTML = '<a href="#" onclick="asFlagTransaction(\'' + TransactionId + '\');" class="btn">Update Flag</a> <a href="#" onclick="asUnflagTransaction(\'' + TransactionId + '\');" class="btn">Unflag</a>';
		}
		else
		{
			if(document.getElementById('asTransaction_table_Flag_buttons'))
				document.getElementById('asTransaction_table_Flag_buttons').innerHTML = '<a href="#" onclick="asFlagTransaction(\'' + TransactionId + '\');" class="btn">Flag</a>';
		}
	}

	// Hide the transaction div
	function asHideTransaction()
	{
		asHideById('as_transaction');
		asShowById('as_transaction_show');
		asHideById('as_transaction_hide');
	}

	// Flag a transaction
	function asFlagTransaction(TransactionId)
	{
		// Build query string
		var GetFieldList = 'FlagReason, FlagComment';
		var GetFields = GetFieldList.split(', ');
		var Prefix = 'asTransaction_value_';
		var PostData = 'formToken=<? echo $formToken; ?>&Action=FlagTransaction&TransactionId=' + escape(TransactionId);
		for (var CurrentField in GetFields)
		{
			if(document.getElementById(Prefix + GetFields[CurrentField]))
			{
				if(document.getElementById(Prefix + GetFields[CurrentField]).value)
				{
					if(document.getElementById('asTransactions_field_' + TransactionId + '_' + GetFields[CurrentField]))
					{
						document.getElementById('asTransactions_field_' + TransactionId + '_' + GetFields[CurrentField]).value = document.getElementById(Prefix + GetFields[CurrentField]).value;
					}
					PostData += '&' + GetFields[CurrentField] + '=' + escape(document.getElementById(Prefix + GetFields[CurrentField]).value);
				} else if(document.getElementById(Prefix + GetFields[CurrentField]).options && document.getElementById(Prefix + GetFields[CurrentField]).options[document.getElementById(Prefix + GetFields[CurrentField]).selectedIndex].value != '')
				{
					PostData += '&' + GetFields[CurrentField] + '=' + escape(document.getElementById(Prefix + GetFields[CurrentField]).options[document.getElementById(Prefix + GetFields[CurrentField]).selectedIndex].value);
				}
			}
		}

		// Change the flag button to unflag
		if(document.getElementById('asTransactions_' + TransactionId + '_Flag'))
			document.getElementById('asTransactions_' + TransactionId + '_Flag').innerHTML = '<a href="#" onclick="asUnflagTransaction(\'' + TransactionId + '\');" class="btn">Flagging...</a>';

		// Set tasks after AJAX request is complete
		var Request = new asAjaxRequest();
		Request.TransactionId = TransactionId;
		
		Request.onreadystatechange=function()
		{
			if (Request.readyState==4)
			{
				if (Request.status == 200 || window.location.href.indexOf('http') == -1)
				{
					// Update the credits values
					var responseData = Request.responseText.split(',');
					asUpdateCredits(responseData[1], responseData[2]);
					asUpdateStatus('Request completed. (' + PostData + ')');

					if(responseData[0] == '1')
					{
						// Change the flag button to unflag
						if(document.getElementById('asTransactions_' + Request.TransactionId + '_Flag'))
							document.getElementById('asTransactions_' + Request.TransactionId + '_Flag').innerHTML = '<a href="#" onclick="asUnflagTransaction(\'' + Request.TransactionId + '\');" class="btn">Unflag</a>';
					}
					else
					{
						// Change the flag button to unflag
						if(document.getElementById('asTransactions_' + Request.TransactionId + '_Flag'))
							document.getElementById('asTransactions_' + Request.TransactionId + '_Flag').innerHTML = '<a href="#" onclick="asFlagTransaction(\'' + Request.TransactionId + '\');" class="btn">Flag</a>';
					}
					asShowTransaction(Request.TransactionId);
				}
				else
				{
					//
				}
			}
		};

		// Post ajax request
		Request.open('POST', './<? echo $alphaSentryTemplateName; ?>?mode=ajax', true);
		Request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		Request.send(PostData);
		asUpdateStatus('Flagging transaction... (' + PostData + ')');
	}

	// Flag a transaction using the transactions table
	function asFlagTransactionQuick(TransactionId)
	{
		var PostData = 'formToken=<? echo $formToken; ?>&Action=FlagTransaction&TransactionId=' + escape(TransactionId);

		if(document.getElementById('asTransactions_' + TransactionId + '_Flag'))
			document.getElementById('asTransactions_' + TransactionId + '_Flag').innerHTML = '<a href="#" onclick="asUnflagTransaction(\'' + TransactionId + '\');" class="btn">Flagging...</a>';
		
		// Set tasks after AJAX request is complete
		var Request = new asAjaxRequest();
		Request.TransactionId = TransactionId;
		
		Request.onreadystatechange=function()
		{
			if (Request.readyState==4)
			{
				if (Request.status == 200 || window.location.href.indexOf('http') == -1)
				{
					// Update credit values
					var responseData = Request.responseText.split(',');
					asUpdateCredits(responseData[1], responseData[2]);
					asUpdateStatus('Request completed. (' + PostData + ')');
					if(responseData[0] == '1')
					{
						// Change the flag button to unflag
						if(document.getElementById('asTransactions_' + Request.TransactionId + '_Flag'))
							document.getElementById('asTransactions_' + Request.TransactionId + '_Flag').innerHTML = '<a href="#" onclick="asUnflagTransaction(\'' + Request.TransactionId + '\');" class="btn">Unflag</a>';
					}
					else
					{
						// Change the flag button to unflag
						if(document.getElementById('asTransactions_' + Request.TransactionId + '_Flag'))
							document.getElementById('asTransactions_' + Request.TransactionId + '_Flag').innerHTML = '<a href="#" onclick="asFlagTransaction(\'' + Request.TransactionId + '\');" class="btn">Flag</a>';
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
		asUpdateStatus('Flagging transaction... (' + PostData + ')');
	}

	// Unflag a transaction
	function asUnflagTransaction(TransactionId)
	{
		var GetFieldList = 'FlagReason, FlagComment';
		var GetFields = GetFieldList.split(', ');
		var PostData = 'formToken=<? echo $formToken; ?>&Action=UnflagTransaction&TransactionId=' + escape(TransactionId);
		
		for (var CurrentField in GetFields)
		{
			if(document.getElementById('asTransactions_field_' + TransactionId + '_' + GetFields[CurrentField]))
			{
				document.getElementById('asTransactions_field_' + TransactionId + '_' + GetFields[CurrentField]).value = '';
			}
		}
		// Update flag/unflag button
		if(document.getElementById('asTransactions_' + TransactionId + '_Flag'))
			document.getElementById('asTransactions_' + TransactionId + '_Flag').innerHTML = '<a href="#" onclick="asFlagTransaction(\'' + TransactionId + '\');" class="btn">Unflagging...</a>';
		
		// Set tasks after AJAX request is complete
		var Request = new asAjaxRequest();
		Request.TransactionId = TransactionId;
		
		Request.onreadystatechange=function()
		{
			if (Request.readyState==4)
			{
				if (Request.status == 200 || window.location.href.indexOf('http') == -1)
				{
					// Update credits available
					var responseData = Request.responseText.split(',');
					asUpdateCredits(responseData[1], responseData[2]);
					asUpdateStatus('Request completed. (' + PostData + ')');
					if(responseData[0] == '1')
					{
						// Change the flag button to unflag
						if(document.getElementById('asTransactions_' + Request.TransactionId + '_Flag'))
							document.getElementById('asTransactions_' + Request.TransactionId + '_Flag').innerHTML = '<a href="#" onclick="asFlagTransaction(\'' + Request.TransactionId + '\');" class="btn">Flag</a>';
					}
					else
					{
						// Change the flag button to unflag
						if(document.getElementById('asTransactions_' + Request.TransactionId + '_Flag'))
							document.getElementById('asTransactions_' + Request.TransactionId + '_Flag').innerHTML = '<a href="#" onclick="asUnflagTransaction(\'' + Request.TransactionId + '\');" class="btn">Unflag</a>';
					}
					asShowTransaction(Request.TransactionId);
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
		asUpdateStatus('Unflagging transaction... (' + PostData + ')');
	}

	// Delete a transaction
	function asDeleteTransaction(TransactionId)
	{
		var PostData = 'formToken=<? echo $formToken; ?>&Action=DeleteTransaction&TransactionId=' + escape(TransactionId);
		
		// Set tasks after AJAX request is complete
		var Request = new asAjaxRequest();
		Request.TransactionId = TransactionId;
		
		Request.onreadystatechange=function()
		{
			if (Request.readyState==4)
			{
				if (Request.status == 200 || window.location.href.indexOf('http') == -1)
				{
					// Update credits available
					var responseData = Request.responseText.split(',');
					asUpdateCredits(responseData[1], responseData[2]);
					asUpdateStatus('Request completed. (' + PostData + ')');
					if(responseData[0] == '1')
					{
						// Remove transaction from transactions table
						if(document.getElementById('asTransactions_row_' + Request.TransactionId))
							document.getElementById('asTransactions_row_' + Request.TransactionId).parentNode.removeChild(document.getElementById('asTransactions_row_' + Request.TransactionId));
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
		asUpdateStatus('Flagging transaction... (' + PostData + ')');
	}

	// Update status bar
	function asUpdateStatus(Status)
	{
		if(document.getElementById('asStatus_value_status'))
		{
			if(Status.length > 0)
				document.getElementById('asStatus_value_status').innerHTML = '<div class="alert alert-info">' + Status + '</div>';
			else
				document.getElementById('asStatus_value_status').innerHTML = '';
		}
	}

	// Update the errors list
	function asUpdateErrors(Errors)
	{
		var ErrorString = '';
		if(Errors.length > 0)
		{
			var ErrorList = Errors.split(';');
			ErrorString = '<strong>Errors:</strong> <ul>';
			for (var CurrentError in ErrorList)
			{
				ErrorString = ErrorString + '<li>' + ErrorList[CurrentError] + '</li>';
			}
			ErrorString = ErrorString + '</ul>';
		}
		
		if(document.getElementById('asStatus_value_errors'))
		{
			if(ErrorString.length > 0)
				document.getElementById('asStatus_value_errors').innerHTML = '<div class="alert alert-error">' + ErrorString + '</div>';
			else
				document.getElementById('asStatus_value_errors').innerHTML = '';
		}	
	}

	// Update the Free and Paid credits values
	function asUpdateCredits(FreeCredits, PaidCredits)
	{
		if(document.getElementById('asStatus_value_FreeCredits'))
			document.getElementById('asStatus_value_FreeCredits').innerHTML = FreeCredits;
		if(document.getElementById('asStatus_value_PaidCredits'))
			document.getElementById('asStatus_value_PaidCredits').innerHTML = PaidCredits;
	}

	// Hide a DOM element by ID, if it exists
	function asHideById(ElementId)
	{
		if(document.getElementById(ElementId))
		{
			document.getElementById(ElementId).style.visibility = 'hidden';
			document.getElementById(ElementId).style.display = 'none';
		}
	}

	// Show a DOM element by ID, if it exists
	function asShowById(ElementId)
	{
		if(document.getElementById(ElementId))
		{
			document.getElementById(ElementId).style.visibility = 'visible';
			document.getElementById(ElementId).style.display = 'inline';
		}
	}

	// Define AJAX request
	function asAjaxRequest()
	{
		var activexmodes=['Msxml2.XMLHTTP', 'Microsoft.XMLHTTP'];
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
<h1>AlphaSentry Transactions
	<span class="pull-right">
		<small>
			<!-- Area to display Free and Paid credits remaining, updated with each API call -->
			Free Credits: <span id="asStatus_value_FreeCredits"></span> Paid Credits: <span id="asStatus_value_PaidCredits"></span>
		</small>
	</span>
</h1>
<!-- Empty div to be filled with status info dynamically when appropriate -->
<div class="row">
	<div class="span12" id="asStatus_value_status"></div>
</div>
<!-- Empty div to be filled with errors dynamically when appropriate -->
<div class="row">
	<div class="span12" id="asStatus_value_errors"></div>
</div>
<!-- Form for browse transactions -->
<div class="row">
	<div class="span12">
		<div class="well well-small">
			<div>
				<h3>Browse Transactions
				<span class="pull-right">
					<small>
						<span id="as_browse_show" style="visibility:hidden; display:none;"><a href="#" onclick="asShowBrowse();">Show</a></span> <span id="as_browse_hide"><a href="#" onclick="asHideBrowse();">Hide</a></span> - <a href="#" onclick="asClearBrowse();">Clear</a> - <a href="#" onclick="asBrowseTransactions();">Update</a>
					</small>
				</span></h3>
			</div>
			<div id="as_browse">
				<form action="<? echo $alphaSentryTemplateName; ?>" method="post" name="asBrowse">
					<table style="margin: 0 auto;">
						<tbody>
							<tr>
								<td><label for="asBrowse_StartDate">Start Date </label> <input type="text" id="asBrowse_StartDate" name="StartDate" value="<? echo $_REQUEST['StartDate']; ?>" class="input-medium"/></td>
								<td><label for="asBrowse_MinFlagCount">Min Flags </label> <input type="text" id="asBrowse_MinFlagCount" name="MinFlagCount" value="<? echo $_REQUEST['MinFlagCount']; ?>" class="input-mini"/></td>
								<td><label for="asBrowse_MinRiskScore">Min Score </label> <input type="text" id="asBrowse_MinRiskScore" name="MinRiskScore" value="<? echo $_REQUEST['MinRiskScore']; ?>" class="input-mini"/></td>
								<td><label for="asBrowse_UserVar1">UserVar1 </label> <input type="text" id="asBrowse_UserVar1" name="UserVar1" value="<? echo $_REQUEST['UserVar1']; ?>" class="input-small"/></td>
								<td><label for="asBrowse_ClientIp">Server Ip </label> <input type="text" id="asBrowse_ClientIp" name="ClientIp" value="<? echo $_REQUEST['ClientIp']; ?>" class="input-small"/></td>
								<td><label for="asBrowse_MinGeoIpDistance">Min Distance </label>
									<select id="asBrowse_MinGeoIpDistance" name="MinGeoIpDistance" class="input-small">
										<option value=""></option>
										<? echo $alphaSentryMinGeoIpDistanceSelect; ?>
									</select>
								</td>
							</tr>
							<tr>
								<td><label for="asBrowse_EndDate">End Date </label> <input type="text" id="asBrowse_EndDate" name="EndDate" value="<? echo $_REQUEST['EndDate']; ?>" class="input-medium"/></td>
								<td><label for="asBrowse_MaxFlagCount">Max Flags </label> <input type="text" id="asBrowse_MaxFlagCount" name="MaxFlagCount" value="<? echo $_REQUEST['MaxFlagCount']; ?>" class="input-mini"/></td>
								<td><label for="asBrowse_MaxRiskScore">Max Score </label> <input type="text" id="asBrowse_MaxRiskScore" name="MaxRiskScore" value="<? echo $_REQUEST['MaxRiskScore']; ?>" class="input-mini" /></td>
								<td><label for="asBrowse_UserVar2">UserVar2 </label> <input type="text" id="asBrowse_UserVar2" name="UserVar2" value="<? echo $_REQUEST['UserVar2']; ?>" class="input-small"/></td>
								<td><label for="asBrowse_ClientDomainName">Server Domain </label> <input type="text" id="asBrowse_ClientDomainName" name="ClientDomainName" value="<? echo $_REQUEST['ClientDomainName']; ?>" class="input-small"/></td>
								<td><label for="asBrowse_MaxGeoIpDistance">Max Distance </label>
									<select id="asBrowse_MaxGeoIpDistance" name="MaxGeoIpDistance" class="input-small">
										<option value=""></option>
										<? echo $alphaSentryMaxGeoIpDistanceSelect; ?>
									</select>
								</td>
							</tr>
							<tr>
								<td><label for="asBrowse_TransactionType">Type </label>
									<select id="asBrowse_TransactionType" name="TransactionType" class="input-medium">
										<option value="">Any</option>
										<? echo $alphaSentryTransactionTypeSelect; ?>
									</select>
								</td>
								<td><label for="asBrowse_Limit">Max Results </label>
									<select id="asBrowse_Limit" name="Limit" class="input-mini">
										<option value=""></option>
										<? echo $alphaSentryLimitSelect; ?>
									</select>
								</td>
								<td colspan="2">
									<label for="asBrowse_OrderBy">Order By </label>
									<select id="asBrowse_OrderBy" name="OrderBy" class="input-large">
										<option value=""></option>
										<? echo $alphaSentryOrderBySelect; ?>
									</select>
								</td>
								<td colspan="2">
									<label for="asBrowse_Order">Order </label>
									<select id="asBrowse_Order" name="Order" class="input-small">
										<option value=""></option>
										<? echo $alphaSentryOrderSelect; ?>
									</select>
								</td>
								<td><input type="hidden" name="Action" value="BrowseTransactions" /></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- Form for Search Transactions -->
<div class="row">
	<div class="span12">
		<div class="well well-small">
			<div>
				<h3>Search Transactions
				<span class="pull-right">
					<small>
						<span id="as_search_show"><a href="#" onclick="asShowSearch();">Show</a></span> <span id="as_search_hide" style="visibility:hidden; display:none;"><a href="#" onclick="asHideSearch();">Hide</a></span> - <a href="#" onclick="asClearGet();">Clear</a> - <a href="#" onclick="asGetTransactions();">Update</a>
					</small>
				</span></h3>
			</div>
			<div id="as_search" style="visibility:hidden; display:none;">
				<form action="<? echo $alphaSentryTemplateName; ?>" method="post" name="asSearch">
					<table style="margin: 0 auto;">
						<tbody>
							<tr>
								<td><label for="asSearch_TransactionId">Transaction Id </label> <input type="text" id="asSearch_TransactionId" name="TransactionId" value="<? echo $_REQUEST['TransactionId']; ?>" class="input-large"/></td>
								<td><label for="asSearch_UserVar1">UserVar1 </label> <input type="text" id="asSearch_UserVar1" name="UserVar1" value="<? echo $_REQUEST['UserVar1']; ?>" class="input-small"/></td>
								<td><label for="asSearch_UserId">User Id </label> <input type="text" id="asSearch_UserId" name="UserId" value="<? echo $_REQUEST['UserId']; ?>" class="input-medium"/></td>
								<td><label for="asSearch_UserIp">User IP </label> <input type="text" id="asSearch_UserIp" name="UserIp" value="<? echo $_REQUEST['UserIp']; ?>" class="input-small"/></td>
							</tr>
							<tr>
								<td><label for="asSearch_DeviceId">Device Id </label> <input type="text" id="asSearch_DeviceId" name="DeviceId" value="<? echo $_REQUEST['DeviceId']; ?>" class="input-large"/></td>
								<td><label for="asSearch_UserVar2">UserVar2 </label><input type="text" id="asSearch_UserVar2" name="UserVar2" value="<? echo $_REQUEST['UserVar2']; ?>" class="input-small"/></td>
								<td><label for="asSearch_PurchaseId">Purchase Id </label> <input type="text" id="asSearch_PurchaseId" name="PurchaseId" value="<? echo $_REQUEST['PurchaseId']; ?>" class="input-medium"/></td>
								<td><label for="asSearch_UserUserAgent">UserAgent </label><input type="text" id="asSearch_UserUserAgent" name="UserUserAgent" value="<? echo $_REQUEST['UserUserAgent']; ?>" class="input-small"/></td>
							</tr>
							<tr>
								<td><label for="asSearch_TransactionType">Type </label>
									<select id="asSearch_TransactionType" name="TransactionType" class="input-medium">
										<option value="">Any</option>
										<? echo $alphaSentryTransactionTypeSelect; ?>
									</select>	
								</td>
								<td><label for="asSearch_Limit">Max Results </label>
									<select id="asSearch_Limit" name="Limit" class="input-mini">
										<option value=""></option>
										<? echo $alphaSentryLimitSelect; ?>
									</select>
								</td>
								<td><label for="asSearch_OrderBy">Order By </label>
									<select id="asSearch_OrderBy" name="OrderBy" class="input-medium">
										<option value=""></option>
										<? echo $alphaSentryOrderBySelect; ?>
									</select>
								</td>
								<td>	
									<label for="asSearch_Order">Order </label>
									<select id="asSearch_Order" name="Order" class="input-small">
										<option value=""></option>
										<? echo $alphaSentryOrderSelect; ?>
									</select>
									<input type="hidden" name="Action" value="GetTransactions" />
								</td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- Form to display extended details of a selected transaction -->
<div class="row">
	<div class="span12">
		<div class="well well-small">
			<div>
				<h3>Current Transaction
				<span class="pull-right">
					<small>
						<span id="as_transaction_show"><a href="#" onclick="asShowTransaction();">Show</a></span> <span id="as_transaction_hide" style="visibility:hidden; display:none;"><a href="#" onclick="asHideTransaction();">Hide</a></span>
					</small>
				</span></h3>
			</div>
			<div id="as_transaction" style="visibility:hidden;display:none;">
				<form action="<? echo $alphaSentryTemplateName; ?>">
					<dl class="dl-horizontal">
						<dt>Transaction ID</dt>
						<dd id="asTransaction_value_TransactionId"></dd>
						<dt>Transaction Time</dt>
						<dd id="asTransaction_value_TransactionTime"></dd>
						<dt>Device ID</dt>
						<dd id="asTransaction_value_DeviceId"></dd>
						<dt>UserAgent</dt>
						<dd id="asTransaction_value_UserUserAgent"></dd>
					</dl>
					<table style="border-collapse:separate; border-spacing: 5px;">
						<tbody>
							<tr>
								<td style="vertical-align: top;">
									<dl class="dl-horizontal">
										<dt>User ID</dt>
										<dd id="asTransaction_value_UserId"></dd>
										<dt>Purchase ID</dt>
										<dd id="asTransaction_value_PurchaseId"></dd>
										<dt>User IP</dt>
										<dd id="asTransaction_value_UserIp"></dd>
										<dt>Risk Score</dt>
										<dd id="asTransaction_value_RiskScore"></dd>
										<dt>Transaction Type</dt>
										<dd id="asTransaction_value_TransactionType"></dd>
										<dt>Server IP</dt>
										<dd id="asTransaction_value_ClientIp"></dd>
										<dt>Server Domain</dt>
										<dd id="asTransaction_value_ClientDomainName"></dd>
									</dl>
								</td>
								<td id="asTransaction_table_Counts" style="vertical-align: top;">
									<dl class="dl-horizontal">
										<dt>Flags</dt>
										<dd id="asTransaction_value_FlagCount"></dd>
										<dt>UserVar1</dt>
										<dd id="asTransaction_value_UserVar1"></dd>
										<dt>UserVar2</dt>
										<dd id="asTransaction_value_UserVar2"></dd>
										<dt>Devices per User</dt>
										<dd id="asTransaction_value_DevicesPerUser"></dd>
										<dt>Accounts per IP</dt>
										<dd id="asTransaction_value_AccountsPerIp"></dd>
										<dt>Purchases per IP</dt>
										<dd id="asTransaction_value_PurchasesPerIp"></dd>
										<dt>Purchases per Device</dt>
										<dd id="asTransaction_value_PurchasesPerDevice"></dd>
									</dl>
								</td>
								<td id="asTransaction_table_GeoIp" style="vertical-align: top;">
									<dl class="dl-horizontal">
										<dt>User Country</dt>
										<dd id="asTransaction_value_UserCountryCode"></dd>
										<dt>GeoIp City</dt>
										<dd id="asTransaction_value_GeoIpCity"></dd>
										<dt>GeoIp Region</dt>
										<dd id="asTransaction_value_GeoIpRegion"></dd>
										<dt>GeoIp Country</dt>
										<dd id="asTransaction_value_GeoIpCountryCode"></dd>
										<dt>GeoIp Distance</dt>
										<dd id="asTransaction_value_GeoIpDistance"></dd>
										<dt>GeoIp Latitude</dt>
										<dd id="asTransaction_value_GeoIpLatitude"></dd>
										<dt>GeoIp Longitude</dt>
										<dd id="asTransaction_value_GeoIpLongitude"></dd>
									</dl>
								</td>
								<td id="asTransaction_table_Flag" style="vertical-align: top;">
									<label for="asTransaction_value_FlagReason"><strong>Flag Reason</strong></label>
									<input type="text" name="FlagReason" id="asTransaction_value_FlagReason" class="input-large" />
									<label for="asTransaction_value_FlagComment"><strong>Flag Comment</strong></label>
									<textarea name="FlagComment" id="asTransaction_value_FlagComment" rows="5" class="input-large"></textarea>
									<div id="asTransaction_table_Flag_buttons"></div>
								</td>
							</tr>
						</tbody>
					</table>
				<input type="hidden" id="asTransaction_id" name="TransactionId" value="" />
				</form>
			</div>
		</div>
	</div>
</div>
<!-- Display Transacitons Table -->
<h2>Transactions</h2>
<div id="as_transactions">
<?php 
}
if($_REQUEST['Action'] != 'FlagTransaction' && $_REQUEST['Action'] != 'UnflagTransaction' && $_REQUEST['Action'] != 'DeleteTransaction')
{
?>
	<? echo  $alphaSentryTransactionTable; ?>
<?
// If $alphaSentryShowRaw is set to TRUE show raw SOAP request and response
if($alphaSentryShowRaw)
{
?>
<h2>Raw Request</h2>
<pre class="prettyprint"><? 
	$alphaSentryDom = new DOMDocument;
	$alphaSentryDom->preserveWhiteSpace = FALSE;
	$alphaSentryDom->loadXML($alphaSentry->Client->__getLastRequest());
	$alphaSentryDom->formatOutput = TRUE;
	echo htmlspecialchars($alphaSentry->RedactApiKey($alphaSentryDom->saveXml())); 
?></pre>
<h2>Raw Response</h2>
<pre class="prettyprint"><? 
	$alphaSentryDom = new DOMDocument;
	$alphaSentryDom->preserveWhiteSpace = FALSE;
	$alphaSentryDom->loadXML($alphaSentry->Client->__getLastResponse());
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
<?php 
}
else
{
	// Echo AJAX response only if it's AJAX mode
	echo $alphaSentryReturnString;
}
	
// If AJAX mode, close the page
if($_GET['mode'] != 'ajax')
{
?>

</div>
</div>
</body>
</html>
<?php }} ?>
