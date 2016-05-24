<div id="content">
    <div class="container-fluid">
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title"><i class="fa fa-credit-card"></i>&nbsp;Credit Card Information</h3></div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" name="form-psi-paymentapi" method="post" class="form-horizontal">
					<div class="form-group required">
						<label class="col-sm-3 control-label" for="first_name">First Name</label>
						<div class="col-sm-8">
						  <input type="text" name="first_name" value="<?php echo $first_name; ?>" class="form-control" readonly/>
						</div>
					</div>
					<div class="form-group required">
						<label class="col-sm-3 control-label" for="last_name">First Name</label>
						<div class="col-sm-8">
						  <input type="text" name="last_name" value="<?php echo $last_name; ?>" class="form-control" readonly/>
						</div>
					</div>
					<div class="form-group required">
						<label class="col-sm-3 control-label" for="email">Email</label>
						<div class="col-sm-8">
						  <input type="text" name="email" value="<?php echo $email; ?>" class="form-control" readonly/>
						</div>
					</div>
					<div class="form-group required">
						<label class="col-sm-3 control-label" for="gender">Gender</label>
						<div class="col-sm-8">
							<select name="gender" class="form-control">
								<option value="">-select-</option>
								<option value="M">MALE</option>
								<option value="F">FEMALE</option>
							</select>
						</div>
					</div>
					<div class="form-group required">
						<label class="col-sm-3 control-label" for="birth_date">DOB</label>
						<div class="col-sm-8">
							<div class='input-group date'>
								<input name="birth_date" value="" placeholder="YYYYMMDD" data-date-format="YYYYMMDD" id="birth_date" class="form-control" type="text">
								<span class="input-group-btn">
								<button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
								</span>
							</div>
						</div>
					</div>
					<div class="form-group required">
						<label class="col-sm-3 control-label" for="card_no">Card Number</label>
						<div class="col-sm-8">
						  <input type="text" name="card_no" id="card_no" placeholder="Card Number" value="" class="form-control">
						</div>
					</div>
					<div class="form-group required">
						<label class="col-sm-3 control-label" for="card_no">cvv</label>
						<div class="col-sm-8">
						  <input type="text" name="cvv" id="cvv" placeholder="CVV" value="" class="form-control">
						</div>
					</div>
					<div class="form-group required">
						<label class="col-sm-3 control-label" for="year">Year of expiry</label>
						<div class="col-sm-8">
						  <input type="text" name="year" id="year" placeholder="YY" value="" class="form-control">
						</div>
					</div>
					<div class="form-group required">
						<label class="col-sm-3 control-label" for="month">Month of expiry</label>
						<div class="col-sm-8">
						  <input type="text" name="month" id="month" placeholder="MM" value="" class="form-control">
						</div>
					</div>
                    <input type="hidden" name="reference_id" value="<?php echo $reference_id; ?>" />
                    <input type="hidden" name="bill_no" value="<?php echo $bill_no; ?>" />
                    <input type="hidden" name="payment_order_no" value="<?php echo $payment_order_no; ?>" />
                    <input type="hidden" name="date_time_request" value="<?php echo $date_time_request; ?>" />
                    <input type="hidden" name="customer_ip" value="<?php echo $customer_ip; ?>" />
                    <input type="hidden" name="currency_code" value="<?php echo $currency_code; ?>" />
                    <input type="hidden" name="phone_no" value="<?php echo $phone_no; ?>" />
                    <input type="hidden" name="zip_code" value="<?php echo $zip_code; ?>" />
                    <input type="hidden" name="address" value="<?php echo $address; ?>" />
                    <input type="hidden" name="city" value="<?php echo $city; ?>" />
                    <input type="hidden" name="state" value="<?php echo $state; ?>" />
                    <input type="hidden" name="country" value="<?php echo $country; ?>" />
                    <input type="hidden" name="s_first_name" value="<?php echo $s_first_name; ?>" />
                    <input type="hidden" name="s_last_name" value="<?php echo $s_last_name; ?>" />
                    <input type="hidden" name="s_email" value="<?php echo $s_email; ?>" />
                    <input type="hidden" name="s_phone_no" value="<?php echo $s_phone_no; ?>" />
                    <input type="hidden" name="s_zip_code" value="<?php echo $s_zip_code; ?>" />
                    <input type="hidden" name="s_address" value="<?php echo $s_address; ?>" />
                    <input type="hidden" name="s_city" value="<?php echo $s_city; ?>" />
                    <input type="hidden" name="s_state" value="<?php echo $s_state; ?>" />
                    <input type="hidden" name="s_country" value="<?php echo $s_country; ?>" />
                    <input type="hidden" name="amount" value="<?php echo $amount; ?>" />
                    <input type="hidden" name="product_desc" value="<?php echo $product_desc; ?>" />
                    <input type="hidden" name="product_type" value="<?php echo $product_type; ?>" />
                    <input type="hidden" name="product_item" value="<?php echo $product_item; ?>" />
                    <input type="hidden" name="product_qty" value="<?php echo $product_qty; ?>" />
                    <input type="hidden" name="product_price" value="<?php echo $product_price; ?>" />
                    <input type="hidden" name="order_status_id" value="<?php echo $order_status_id; ?>" />
                    <input type="hidden" name="date_created" value="<?php echo $date_created; ?>" />

                    <div class="form-group">
                        <div class="col-sm-11">
							<div class="pull-right">
								<input type="submit" value="<?php echo $button_confirm;?>" class="btn primary-btn"/>
							</div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
	<script src="view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
	<link href="view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css" type="text/css" rel="stylesheet" media="screen" />
	<script type="text/javascript">
		$('.date').datetimepicker({
			pickTime: false
		});
		
	</script>
</div>