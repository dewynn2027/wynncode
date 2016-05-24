<link href="<?php echo base_url('media/css/style.css');?>" rel="stylesheet">
<style>
iframe{
	padding: 0px; 
	margin: 0px;
}
body, html{
	padding: 0px; margin: 0px; border: 0px;
}
</style>
<?php 
	
	$response 	= "xmldata|".$xmldata."|jsondata|".$jsondata;
	if((int)$sendMsg != 1)
	{
		if($tdCondition == "001")
		{

?>
	<html>
	<body onload="document.postform.submit()">
			<form id="postform" name="postform" action="<?php echo $tdFullDetails->rp_redirectUrl;?>" method="post" target="childiframe">
			<input type="hidden" name="action" value="<?php echo $tdFullDetails->rp_action;?>">
			<br />
			<input type="hidden" name="amount" value="<?php echo $tdFullDetails->rp_amount;?>">
			<br />
			<input type="hidden" name="method" value="<?php echo $tdFullDetails->rp_method;?>">
			<br />
			<input type="hidden" name="payment_id" value="<?php echo $tdFullDetails->rp_paymentId;?>">
			<br />
			<input type="hidden" name="callback_url" value="<?php echo $tdFullDetails->rp_callbackUrl;?>">
			<br />
			<input type="hidden" name="card_number" value="<?php echo $tdFullDetails->rp_cardNumber;?>">
			<br />
			</form>
			<iframe id="childiframe" name="childiframe" src="" height="400" width="400" scrolling="no" style="padding: 0px; marging 0px;" marginheight="0" marginwidth="0" frameborder="0"></iframe>
			<script type="text/javascript">
			
			// #Listen to message from child window
			window.addEventListener('message',function(e)
			{
				if (e.origin === "https://beta.3dsecure.ecommsecure.com")
					parent.postMessage(e.data, '*');
			},false);
			</script>
	</body>
	</html>
	<?php
		}else
		{
	?>
		<div style="width: 300px; margin: 0px auto;">
			<div align="center">
				<h3>Authentication Complete<h3>
			</div>
			<div class="footer">&nbsp;</div>
		</div>	
		<script type="text/javascript">	
			// #send message to parent
			parent.postMessage('<?php echo $response;?>', 'https://beta.3dsecure.ecommsecure.com');
		</script>
	 
<?php
		}
	}

	if((int)$sendMsg == 1)
	{
?>
	<script type="text/javascript">
		parent.postMessage('<?php echo $response;?>', '*');
	</script>
<?php
	}
?>
	