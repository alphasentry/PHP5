<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>AlphaSentry - Sample Page</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/bootstrap-responsive.min.css" rel="stylesheet">
	<link href="css/prettify.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container">
	<h1>AlphaSentry Sample Code Form</h1>
	<p>
		The purpose of this page is to provide several sample forms and show how they are submitted to the AlphaSentry Sentry API.  See <a href="http://www.alphasentry.com/docs/">API documentation</a> for more details.
	</p>
	<form action="sample-form-submit.php" method="post">
		<fieldset>
			<legend>Login</legend>
			<label for="login-userName">UserName</label>
			<input id="login-userName" name="userName" type="text" class="input-xlarge" placeholder="myUserName" />
			<label for="login-password">Password</label>
			<input id="login-password" name="password" type="password" class="input-xlarge" placeholder="abcd1234" />
			<input type="hidden" name="sampleAction" value="login" />
			<input type="hidden" class="asentry_1" name="ASVar1" value="" />
			<input type="hidden" class="asentry_2" name="ASVar2" value="" />
			<div>
				<input type="submit" value="Login" class="btn" />
			</div>
		</fieldset>
	</form>
	
	<form action="sample-form-submit.php" method="post">
		<fieldset>
			<legend>New Account</legend>
			<label for="account-userName">UserName</label>
			<input id="account-userName" name="userName" type="text" class="input-xlarge" placeholder="myUserName" />
			<label for="account-userEmail">Email</label>
			<input id="account-userEmail" name="userEmail" type="text" class="input-xlarge" placeholder="user@email.com" />
			<label for="account-password">Password</label>
			<input id="account-password" name="password" type="password" class="input-xlarge" placeholder="abcd1234" />
			<input type="hidden" name="sampleAction" value="account" />
			<input type="hidden" class="asentry_1" name="ASVar1" value="" />
			<input type="hidden" class="asentry_2" name="ASVar2" value="" />
			<div>
				<input type="submit" value="Create Account" class="btn" />
			</div>
		</fieldset>
	</form>
	
	<form action="sample-form-submit.php" method="post">
		<fieldset>
			<legend>New Account with GeoIP</legend>
			<label for="accountGeoip-userName">UserName</label>
			<input id="accountGeoip-userName" name="userName" type="text" class="input-xlarge" placeholder="myUserName" />
			<label for="accountGeoip-userEmail">Email</label>
			<input id="accountGeoip-userEmail" name="userEmail" type="text" class="input-xlarge" placeholder="user@email.com" />
			<label for="accountGeoip-password">Password</label>
			<input id="accountGeoip-password" name="password" type="password" class="input-xlarge" placeholder="abcd1234" />
			<label for="accountGeoip-location">Location</label>
			<input id="accountGeoip-location" name="location" type="text" class="input-xlarge" placeholder="New York, NY" />
			<label for="accountGeoip-countryCode">Country Code</label>
			<input id="accountGeoip-countryCode" name="countryCode" type="text" class="input-xlarge" placeholder="US" />
			<input type="hidden" name="sampleAction" value="accountGeoip" />
			<input type="hidden" class="asentry_1" name="ASVar1" value="" />
			<input type="hidden" class="asentry_2" name="ASVar2" value="" />
			<div>
				<input type="submit" value="Create Account" class="btn" />
			</div>
		</fieldset>
	</form>
	
	<form action="sample-form-submit.php" method="post">
		<fieldset>
			<legend>Purchase</legend>
			<label for="purchase-purchaseId">Purchase Id</label>
			<input id="purchase-purchaseId" name="purchaseId" type="text" class="input-xlarge" placeholder="54321" />
			<input type="hidden" name="sampleAction" value="purchase" />
			<input type="hidden" class="asentry_1" name="ASVar1" value="" />
			<input type="hidden" class="asentry_2" name="ASVar2" value="" />
			<div>
				<input type="submit" value="Purchase" class="btn" />
			</div>
		</fieldset>
	</form>
	
	<form action="sample-form-submit.php" method="post">
		<fieldset>
			<legend>Purchase with GeoIP</legend>
			<label for="purchaseGeoip-purchaseId">Purchase Id</label>
			<input id="purchaseGeoip-purchaseId" name="purchaseId" type="text" class="input-xlarge" placeholder="54321" />
			<label for="purchaseGeoip-userId">User Id</label>
			<input id="purchaseGeoip-userId" name="userId" type="text" class="input-xlarge" placeholder="myUserName" />
			<label for="purchaseGeoip-address">Address</label>
			<input id="purchaseGeoip-address" name="address" type="text" class="input-xlarge" placeholder="7 Wall St" />
			<label for="purchaseGeoip-city">City</label>
			<input id="purchaseGeoip-city" name="city" type="text" class="input-xlarge" placeholder="New York" />
			<label for="purchaseGeoip-state">State</label>
			<input id="purchaseGeoip-state" name="state" type="text" class="input-xlarge" placeholder="NY" />
			<label for="purchaseGeoip-zip">Zip Code</label>
			<input id="purchaseGeoip-zip" name="zip" type="text" class="input-xlarge" placeholder="10006" />
			<label for="purchaseGeoip-countryCode">Country Code</label>
			<input id="purchaseGeoip-countryCode" name="countryCode" type="text" class="input-xlarge" placeholder="US" />
			<input type="hidden" name="sampleAction" value="purchaseGeoip" />
			<input type="hidden" class="asentry_1" name="ASVar1" value="" />
			<input type="hidden" class="asentry_2" name="ASVar2" value="" />
			<div>
				<input type="submit" value="Purchase" class="btn" />
			</div>
		</fieldset>
	</form>
	
	<form action="sample-form-submit.php" method="post">
		<fieldset>
			<legend>Other</legend>
			<label for="other-comment">Anonymous Comment</label>
			<input id="other-comment" name="comment" type="text" class="input-xlarge" placeholder="I like your blog post!" />
			<input type="hidden" name="sampleAction" value="other" />
			<input type="hidden" class="asentry_1" name="ASVar1" value="" />
			<input type="hidden" class="asentry_2" name="ASVar2" value="" />
			<div>
				<input type="submit" value="Post Comment" class="btn" />
			</div>
		</fieldset>
	</form>
	
	<form action="sample-form-submit.php" method="post">
		<fieldset>
			<legend>Other with User Data</legend>
			<label for="otherUser-userId">User Id</label>
			<input id="otherUser-userId" name="userId" type="text" class="input-xlarge" placeholder="myUserName" />
			<label for="otherUser-comment">User Comment</label>
			<input id="otherUser-comment" name="comment" type="text" class="input-xlarge" placeholder="I like your blog post!" />
			<input type="hidden" name="sampleAction" value="otherUser" />
			<input type="hidden" class="asentry_1" name="ASVar1" value="" />
			<input type="hidden" class="asentry_2" name="ASVar2" value="" />
			<div>
				<input type="submit" value="Post Comment" class="btn" />
			</div>
		</fieldset>
	</form>
	
	<form action="sample-form-submit.php" method="post">
		<fieldset>
			<legend>Flag a Transaction</legend>
			<label for="flag-transactionId">Transaction Id</label>
			<input id="flag-transactionId" name="transactionId" type="text" class="input-xlarge" placeholder="abcd-12345abcdefg-123.45.67.89" />
			<label for="flag-flagReason">Flag Reason</label>
			<input id="flag-flagReason" name="flagReason" type="text" class="input-xlarge" placeholder="spam" />
			<label for="flag-flagComment">Flag Comment</label>
			<input id="flag-flagComment" name="flagComment" type="text" class="input-xlarge" placeholder="Sent unwanted messages." />
			<input type="hidden" name="sampleAction" value="flag" />
			<input type="hidden" class="asentry_1" name="ASVar1" value="" />
			<input type="hidden" class="asentry_2" name="ASVar2" value="" />
			<div>
				<input type="submit" value="Flag Transaction" class="btn" />
			</div>
		</fieldset>
	</form>
	
	<form action="sample-form-submit.php" method="post">
		<fieldset>
			<legend>Unflag a Transaction</legend>
			<label for="unflag-transactionId">Transaction Id</label>
			<input id="unflag-transactionId" name="transactionId" type="text" class="input-xlarge" placeholder="abcd-12345abcdefg-123.45.67.89" />
			<input type="hidden" name="sampleAction" value="unflag" />
			<input type="hidden" class="asentry_1" name="ASVar1" value="" />
			<input type="hidden" class="asentry_2" name="ASVar2" value="" />
			<div>
				<input type="submit" value="Unflag Transaction" class="btn" />
			</div>
		</fieldset>
	</form>
	
	<form action="sample-form-submit.php" method="post">
		<fieldset>
			<legend>Delete a Transaction</legend>
			<label for="delete-transactionId">Transaction Id</label>
			<input id="delete-transactionId" name="transactionId" type="text" class="input-xlarge" placeholder="abcd-12345abcdefg-123.45.67.89" />
			<input type="hidden" name="sampleAction" value="delete" />
			<input type="hidden" class="asentry_1" name="ASVar1" value="" />
			<input type="hidden" class="asentry_2" name="ASVar2" value="" />
			<div>
				<input type="submit" value="Delete Transaction" class="btn" />
			</div>
		</fieldset>
	</form>
	
	<!-- THIS CODE NEEDS TO BE ON EVERY PAGE WHERE FORM DATA IS COLLECTED FOR THE ALPHASENTRY API -->
	<div id="asentry_3" style="position:absolute; top:-1px; left:0px; height:1px; width:1px; z-index:-1;"></div>
	<script type="text/javascript">
		var AlphaSentryOptions = {   
			class1 : 'asentry_1', 
			class2 : 'asentry_2', 
			divName : 'asentry_3'
		};
	</script>
	<script type="text/javascript" src="https://as1.alphasentry.com/as1/as1.js"></script>
	<!-- END REQUIRED CODE -->
	
	<p>
		<a href="sample-autosubmit-form.php">Click here</a> for the autosubmit form.
	</p>
</div>
</body>
</html>