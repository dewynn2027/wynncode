<?php
	if($this->uri->segment(3) == "")
	{
?>
<form action="https://www.moneybookers.com/app/payment.pl" method="post" target="_blank"></br>
<input type="text" name="pay_to_email" value="merchant@moneybookers.com"></br>
<input type="text" name="status_url" value="merchant@moneybookers.com">
<input type="text" name="language" value="EN"></br>
<input type="text" name="amount" value="39.60"></br>
<input type="text" name="currency" value="GBP"></br>
<input type="text" name="detail1_description" value="Description:"></br>
<input type="text" name="detail1_text" value="Romeo and Juliet (W. Shakespeare)"></br>
<input type="text" name="confirmation_note" value="Samplemerchant wishes you pleasure reading your new book!">
<input type="submit" value="Pay!">
</form>
<?php
	}else{
?>
<form action="<?php echo base_url('test/debugme/'.$this->uri->segment(3));?>" method="post"></br>
<textarea name="testme" rows='5' cols='100'></textarea></br>
<input type="submit" name='debug' value="Debug!" />
</form>
<?php
}
?>