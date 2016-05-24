<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>3D Secure</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
		<!-- Optional theme -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
		<!-- Latest compiled and minified JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
		<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js');?>"></script>
		<![endif]-->
		<!-- Latest compiled and minified CSS -->
		<style>
		body {
		    background: #e9e9e9 url("<?php echo base_url('media/images/tiny_grid.png');?>") repeat 0 0;
		}
		</style>
		
	</head>
	<body style="padding: 0px; margin: 0px; border: 0px;">
		<div class="container" style="width: 400px; height: 400px; font-size: 11px; padding: 0px 0px 0px 0px;">
			<div class="panel panel-default">
			  	<div class="panel-heading">
			    	<h3 class="panel-title" style="font-size: 13px; font-weight: 600;">ACS Emulator Module</h3>
			  	</div>
			  	<div class="panel-body">
						<form method="post" action="<?php echo base_url('acs/vform?billNo='.$this->input->get('billNo'));?>" class="form-horizontal" novalidate="novalidate" role="form">
							<div class="form-group" style="margin: 0px 0px 0px 0px;">
								<div class="col-md-6">
									<img src="<?php echo base_url('media/images/mastercard-securecode.png');?>" height="68" width="148">
								</div>
								<?php 
									if($errorCode == 1) echo '<label class="col-md-6" style="padding-top: 12px; margin: 0px auto; color: red;">Invalid SecurityCode, please input password: "hint".</label>';
									if($errorCode == 2) echo '<label class="col-md-6" style="padding-top: 12px; margin: 0px auto; color: red;">cannot procces authentication billNo was empty.</label>';
								?>
							</div>
							<div class="form-group" style="margin: 0px 0px 0px 0px;">
								<label class="col-md-6" style="font-weight: 600; margin-bottom: 1px">Merchant:</label>
								<div class="col-md-6" style="font-weight: 600; font-style: italic;"><?php echo ($transDetails != false) ? $transDetails->apiUsername : "";?></div>
							</div>
							<div class="form-group" style="margin: 0px 0px 0px 0px;">
								<label class="col-md-6" style="font-weight: 600; margin-bottom: 1px">Amount:</label>
								<div class="col-md-6" style="font-weight: 600; font-style: italic;"><?php echo ($transDetails != false) ? $transDetails->currency." ".$transDetails->amount : "";?></div>
							</div>
							<div class="form-group" style="margin: 0px 0px 0px 0px;">
								<label class="col-md-6" style="font-weight: 600; margin-bottom: 1px">Date Time:</label>
								<div class="col-md-6" style="font-weight: 600; font-style: italic;"><?php echo ($transDetails != false) ? $transDetails->dateTimeRequest : "";?></div>
							</div>
							<div class="form-group" style="margin: 0px 0px 0px 0px;">
								<label class="col-md-6" style="font-weight: 600; margin-bottom: 1px">CardNumber(PAN):</label>
								<div class="col-md-6" style="font-weight: 600; font-style: italic;"><?php echo ($transDetails != false) ? substr($transDetails->cardNumber,0,1)." XXXXXXXXXXXX ".substr($transDetails->cardNumber,13,3) : "";?></div>
							</div>
							<div class="form-group" style="margin: 0px 0px 0px 0px;">
								<label class="col-md-6" style="font-weight: 600; margin-bottom: 1px">Personal Greetings:</label>
								<div class="col-md-6" style="font-weight: 600; font-style: italic;"><?php echo ($transDetails != false) ? 'password: "hint"' : "";?></div>
							</div>
							
							<div class="form-group" style="margin: 0px 0px 0px 0px;">
								<label class="col-md-6" style="font-weight: 600;">Please enter SecurityCode:</label>
								<div class="col-md-6">
									<input class="form-control" id="securityCode" name="securityCode" placeholder="Enter SecurityCode" type="password">
								</div>
							</div>
							<div class="form-group" style="margin: 0px 0px 0px 0px;">
								<label class="col-md-6">&nbsp;</label>
								<div class="col-md-6">
									&nbsp; <input class="btn btn-primary" name="authenticate3D" value="Ok" type="submit">
									<input class="btn btn-primary" name="authenticate3DCancel" value="Cancel" type="submit">
								</div>
								<!-- /controls -->				
							</div>
							<!-- /control-group -->
						</form>
					</div>
				</div>
			
		</div>
		<!-- /container --> 

	</body>
</html>