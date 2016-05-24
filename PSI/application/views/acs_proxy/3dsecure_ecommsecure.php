<?php 
$acsUrl = (!empty($_POST['acsUrl'])) ? $_POST['acsUrl'] : (!empty($_GET['acsUrl'])) ? $_GET['acsUrl'] : "";
$TermUrl = (!empty($_POST['TermUrl'])) ? $_POST['TermUrl'] : (!empty($_GET['TermUrl'])) ? $_GET['TermUrl'] : "";
$PaReq = (!empty($_POST['PaReq'])) ? $_POST['PaReq'] : (!empty($_GET['PaReq'])) ? $_GET['PaReq'] : "";
$PaReq = str_replace(" ", "+", $PaReq);
$MD = (!empty($_POST['MD'])) ? $_POST['MD'] : (!empty($_GET['MD'])) ? $_GET['MD'] : "";
?> 
<form action="<?php echo $acsUrl;?>" method="POST">
<textarea name="PaReq" style="width: 400px;" placeholder="PaReq"><?php echo $PaReq;?></textarea>
<input type="text" name="MD" value="<?php echo $MD;?>" style="width: 400px;" placeholder="MD">
<input type="hidden" name="TermUrl" value="<?php echo base_url('/test/get3dsecure');?>" placeholder="TermUrl">
<input type="text" name="MerchantTermUrl" value="" style="width: 400px;" placeholder="TermUrl">
<input type="submit" name="send" value="Submit" style="width: 400px;" placeholder="TermURL"><br/>
</form>

