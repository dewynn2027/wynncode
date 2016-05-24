<?php

if(empty($_POST['PaRes'])) $_POST['PaRes'] = "";
if(empty($_POST['MD'])) $_POST['MD'] = "";
?>
<form action="<?php echo base_url("test/sendPaRes");?>" method="POST">
<input type="text" name="TermUrl" value="" style="width: 600px;" placeholder="TermUrl"><br/>
<input type="text" name="csrf" value="" style="width: 600px;" placeholder="csrf"><br/>
<input type="text" name="PaRes" value="<?php echo $_POST['PaRes'];?>" style="width: 600px;" placeholder="PaRes"><br/>
<input type="text" name="MD" value="<?php echo $_POST['MD'];?>" style="width: 600px;" placeholder="MD"><br/>
<input type="submit" name="send" value="Submit" style="width: 600px;" placeholder="TermURL"><br/>
</form>
