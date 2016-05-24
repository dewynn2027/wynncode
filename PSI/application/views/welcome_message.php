<!DOCTYPE html>
<?php
	$domain = explode(".",$_SERVER['HTTP_HOST']);
	$ico = ($domain[1] == "transcommglobal") ? "favicon.ico" : "alz.ico";
?>
<html>
<head>
<meta charset="UTF-8">
<title>API</title>
<link href="<?php echo base_url('media/css/style.css');?>" rel="stylesheet">
<link rel="shortcut icon" href="<?php echo base_url('media/images/'.$ico);?>">
</head>

<body class="body-color-white">
	<?php 
	
	if($domain[1]=="transcommglobal")
	{
	?>
	<div class="container-black">
		<div align="center" class="imgCont"><img src="<?php echo base_url('media/images/transcomm.png');?>"></div>
		<div align="left" class="textMsg">Welcome to PSI </div>
		<div align="left" class="content">
			<p>We are a worldwide provider of payment processing products and services that facilitate, support and accelerate the processing of funds for merchants, large corporations, financial institutions and government agencies. Amongst other payment modes, we have the systems and capabilities to process online payment traffic brands including Visa, MasterCard, American Express, China Unionpay. We offer flexible, expandable end-to-end payment solutions that are built to streamline the electronic payment process on an at an economic price and help any size business run efficiently and securely.</p>
		</div>
		<div class="footer">
			<p>© 2014 - <a  href="https://www.transcommglobal.com/products.html" >Transcomm Global Limited</a></p>
		</div>
	</div>
	<?php
	}else if($domain[1]=="paymentsystemsintegration")
	{
	?>
	<div class="container-black">
		<!--<div align="center" class="imgCont"><img src="<?php echo base_url('media/images/transcomm.png');?>"></div>-->
		<div align="left" class="textMsg">Welcome to PSI </div>
		<div align="left" class="content">
			<p>We are a worldwide provider of payment processing products and services that facilitate, support and accelerate the processing of funds for merchants, large corporations, financial institutions and government agencies. Amongst other payment modes, we have the systems and capabilities to process online payment traffic brands including Visa, MasterCard, American Express, China Unionpay. We offer flexible, expandable end-to-end payment solutions that are built to streamline the electronic payment process on an at an economic price and help any size business run efficiently and securely.</p>
		</div>
		<div class="footer">
			<!--<p>© 2014 - <a  href="https://www.transcommglobal.com/products.html" >Transcomm Global Limited</a></p>-->
		</div>
	</div>
	<?php
	}else if($domain[1]=="ecommsecure")
	{	
	?>
	<div class="container-white">
		<div align="left" class="textMsg">Method Not Allowed</div>
		<div align="left" class="content">
			<p align="center">The web application has received your request, but has determined it to be in a non-standard format.</p>
		</div>
		<div class="footer">
			<p>© 2016 - ecommsecure.com</p>
		</div>
	</div>
	<?php
	}else
	{
	?>
	<div class="container-white">
		<div align="center" class="imgCont"><img src="<?php echo base_url('media/images/allianz.png');?>"  width="500px;"></div>
		<div align="left" class="textMsg">Welcome to Allianz Metro Limited </div>
		<div align="left" class="content">
			<p>AML incorporates the best web design, web development and SEO techniques to help clients achieve  measurably greater market success. Our digital marketing experts and IT experts manage and implement powerful online marketing strategies for our clients including web design,  SEO,  CRM, e-commerce and email marketing techniques to ensure a professional customised solution.</p>
		</div>
		<div class="footer">
			<p>© 2014 - <a  href="http://www.allianzmetro.com/" >Allianz Metro Limited Inc.</a></p>
		</div>
	</div>
	<?php
	}
	?>
</body>

</html>
