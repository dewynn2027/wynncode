<link href="<?php echo base_url('media/css/style.css');?>" rel="stylesheet">
<?php 
	
	$response 	= "xmldata|".$xmldata."|jsondata|".$jsondata;
	if((int)$sendMsg != 1)
	{
		if($tdCondition == "001")
		{
?>
			<link href="<?php echo base_url('media/css/style.css');?>" rel="stylesheet">
			<title>Payment Gateway Secure Payment</title>
			<form name="Payer" id="Payer" action="<?php echo $tdRedirectUrl;?>" method="POST">
			<input type=hidden name="PaReq" value="<?php echo $tdFullDetails->td_paReq;?>">
			<input type=hidden name="TermUrl" value="<?php echo $tdFullDetails->td_termUrl;?>">
			<input type=hidden name="MD" value="<?php echo $tdFullDetails->td_md;?>">
			</form>
			<script type="text/javascript">
			document.forms[0].submit();
			// #Listen to message from child window
			window.addEventListener('message',function(e)
			{
				if (e.origin === "https://3dsecure.ecommsecure.com")
					parent.postMessage(e.data, '*');
			},false);
			</script>
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
			parent.postMessage('<?php echo $response;?>', '*');
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
	
