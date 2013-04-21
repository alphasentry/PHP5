<?php 
	
	try
	{
		// Update this with your AlphaSentry API Key
		$myApiKey = '';
		// Include AlphaSentry PHP5 Client
		require_once('AlphaSentry.php');
		// Create AlphaSentry client object with your API key
		$alphaSentryClient = new AlphaSentry($myApiKey);
		
		// Process API Calls
		if(isset($_REQUEST['sampleAction']) && strlen($_REQUEST['sampleAction']) > 0 && !is_null($alphaSentryClient->Client))
		{
			switch($_REQUEST['sampleAction'])
			{
				case 'login':
					$alphaSentryClient->TrackLogin($_REQUEST['ASVar1'], $_REQUEST['ASVar2'], $_REQUEST['userName'], 'sample');
					break;
				case 'account':
					$alphaSentryClient->TrackAccount($_REQUEST['ASVar1'], $_REQUEST['ASVar2'], $_REQUEST['userName'], 'sample');
					break;
				case 'accountGeoip':
					$myUserLatitude = '';
					$myUserLongitude = '';
					
					if($alphaSentryClient->GeoCode($_REQUEST['location'].' '.$_REQUEST['countryCode']))
					{
						$myUserLatitude = $alphaSentryClient->GeocodeResponse['Latitude'];
						$myUserLongitude = $alphaSentryClient->GeocodeResponse['Longitude'];
					}
					$alphaSentryClient->TrackAccount($_REQUEST['ASVar1'], $_REQUEST['ASVar2'], $_REQUEST['userName'], 'sample', '', $_REQUEST['countryCode'], $myUserLatitude, $myUserLongitude);
					break;
				case 'purchase':
					$alphaSentryClient->TrackPurchase($_REQUEST['ASVar1'], $_REQUEST['ASVar2'], $_REQUEST['purchaseId'], '', 'sample');
					break;
				case 'purchaseGeoip':
					$myUserLatitude = '';
					$myUserLongitude = '';
						
					if($alphaSentryClient->GeoCode($_REQUEST['address'].' '.$_REQUEST['city'].' '.$_REQUEST['state'].' '.$_REQUEST['zip'].' '.$_REQUEST['countryCode']))
					{
						$myUserLatitude = $alphaSentryClient->GeocodeResponse['Latitude'];
						$myUserLongitude = $alphaSentryClient->GeocodeResponse['Longitude'];
					}
					$alphaSentryClient->TrackPurchase($_REQUEST['ASVar1'], $_REQUEST['ASVar2'], $_REQUEST['purchaseId'], $_REQUEST['userId'], 'sample', '', $_REQUEST['countryCode'], $myUserLatitude, $myUserLongitude, $_REQUEST['zip'], $_REQUEST['address']);
					break;
				case 'other':
					$alphaSentryClient->TrackOther($_REQUEST['ASVar1'], $_REQUEST['ASVar2'], '', 'sample');
					break;
				case 'otherUser':
					$alphaSentryClient->TrackOther($_REQUEST['ASVar1'], $_REQUEST['ASVar2'], $_REQUEST['userId'], 'sample');
					break;
				case 'flag':
					$alphaSentryClient->FlagTransaction($_REQUEST['transactionId'], $_REQUEST['flagReason'], $_REQUEST['flagComment']);
					break;
				case 'unflag':
					$alphaSentryClient->UnflagTransaction($_REQUEST['transactionId']);
					break;
				case 'delete':
					$alphaSentryClient->DeleteTransaction($_REQUEST['transactionId']);
					break;
			}
		}
	}
	catch(Exception $e)
	{
		
	}
	
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>AlphaSentry - Sample Page 2</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/bootstrap-responsive.min.css" rel="stylesheet">
	<link href="css/prettify.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container">
	<h1>AlphaSentry Sample Code Analytics</h1>
	<h2>Diagnostics</h2>
	<table class="table table-striped">
		<tbody>
			<? if(!is_null($alphaSentryClient->Client)) { ?>
			<tr>
				<td>API Key</td>
				<td>Set</td>
			</tr>
			<? } else { ?>
			<tr>
				<td>API Key</td>
				<td>Missing</td>
			</tr>
			<tr>
				<td></td>
				<td>
					Please enter your AlphaSentry API key to line 3 of the sample-form-submit.php document or AlphaSentry.php
				</td>
			</tr>
			<? } ?>
			<? if(isset($alphaSentryClient)) { ?>
			<tr>
				<td>Client Creation</td>
				<td>Success</td>
			</tr>
			<? } else { ?>
			<tr>
				<td>Client Creation</td>
				<td>Failed</td>
			</tr>
			<tr>
				<td></td>
				<td>
					We were unable to create a new AlphaSentry Client Object. Please check that the 'AlphaSentry.php' helper class file was successfully imported.
				</td>
			</tr>
			<? } ?>
			<? if(isset($_REQUEST['ASVar1']) && strlen($_REQUEST['ASVar1']) > 0) { ?>
			<tr>
				<td>Var1</td>
				<td>Success</td>
			</tr>
			<? } else { ?>
			<tr>
				<td>Var1</td>
				<td>Failed</td>
			</tr>
			<tr>
				<td></td>
				<td>
					This probably shouldn't happen. Does your browser have javascript turned off?
				</td>
			</tr>
			<? } ?>
			<? if(isset($_REQUEST['ASVar2']) && strlen($_REQUEST['ASVar2']) > 0) { ?>
			<tr>
				<td>Var2</td>
				<td>Success</td>
			</tr>
			<? } else { ?>
			<tr>
				<td>Var2</td>
				<td>Failed</td>
			</tr>
			<tr>
				<td></td>
				<td>
					This may not be a problem. Just make sure that the AlphaSentry div has been included.
				</td>
			</tr>
			<? } ?>
		</tbody>
	</table>
	
	<h2>Data Returned</h2>
	<table class="table table-striped">
		<thead>
			<tr>
				<th>Name</th>
				<th>Value</th>
			</tr>
		</thead>
		<tbody>
			<? 
			if(isset($alphaSentryClient->Response) && is_array($alphaSentryClient->Response)) {
				foreach($alphaSentryClient->Response as $varName => $currVar) {
					if(is_array($currVar)) {
						foreach($currVar as $subVarName => $subCurrVar) {
			?>
			<tr>
				<td><? echo $varName; ?> [<? echo $subVarName; ?>]</td>
				<td><? echo (empty($subCurrVar) ? '' : htmlspecialchars($subCurrVar)); ?></td>
			</tr>
			<? 
						}
					}
					else
					{
						if(!is_string($currVar) && !is_numeric($currVar) && !is_bool($currVar)&& !is_int($currVar) )
							$currVar = '';
			?>
			<tr>
				<td><? echo $varName; ?></td>
				<td><? echo (empty($currVar) ? '' : htmlspecialchars($currVar)); ?></td>
			</tr>
			<?
					}
				} 
			}
			?>
		</tbody>
	</table>
	
	<h2>Raw Request</h2>
	<pre class="prettyprint"><? 
		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = FALSE;
		$dom->loadXML($alphaSentryClient->Client->__getLastRequest());
		$dom->formatOutput = TRUE;
		echo htmlspecialchars($alphaSentryClient->RedactApiKey($dom->saveXml())); 
	?></pre>
	<h2>Raw Response</h2>
	<pre class="prettyprint"><? 
		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = FALSE;
		$dom->loadXML($alphaSentryClient->Client->__getLastResponse());
		$dom->formatOutput = TRUE;
		echo htmlspecialchars($alphaSentryClient->RedactApiKey($dom->saveXml()));
	?></pre>
	<script type="text/javascript" src="https://google-code-prettify.googlecode.com/svn/trunk/src/prettify.js"></script>
	<script type="text/javascript">
		prettyPrint();
	</script>
</div>
</body>
</html>
