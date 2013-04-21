<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>AlphaSentry - Sample AutoSubmit Page</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/bootstrap-responsive.min.css" rel="stylesheet">
	<link href="css/prettify.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container">
	<h1>AlphaSentry Sample Code Autosubmit Form</h1>
	<p>
		The purpose of this page is to demonstrate an autosubmit form to be used with Alphasentry integrations with systems like PayPal and oAuth. If this page does not automatically submit, something is wrong!
	</p>
	<form action="sample-form-submit.php" method="post" name="autoform">
		<fieldset>
			<input type="hidden" name="sampleAction" value="login" />
			<input type="hidden" class="asentry_1" name="ASVar1" value="" />
			<input type="hidden" class="asentry_2" name="ASVar2" value="" />
			<div>
				<input type="submit" value="Login" class="btn" />
			</div>
		</fieldset>
	</form>
	
	<!-- THIS CODE NEEDS TO BE ON EVERY PAGE WHERE FORM DATA IS COLLECTED FOR THE ALPHASENTRY API -->
	<div id="asentry_3" style="position:absolute; top:-1px; left:0px; height:1px; width:1px; z-index:-1;"></div>
	<script type="text/javascript">
		var AlphaSentryOptions = {   
			class1 : 'asentry_1', 
			class2 : 'asentry_2', 
			divName : 'asentry_3',
			autoSubmit : 'autoform'
		};
	</script>
	<script type="text/javascript" src="https://as1.alphasentry.com/as1/as1.js"></script>
	<!-- END REQUIRED CODE -->
</div>
</body>
</html>