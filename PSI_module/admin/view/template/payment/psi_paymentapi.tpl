<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-psipaymentapi" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i>Edit <?php echo $heading_title; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-psipaymentapi" name="form-psipaymentapi" class="form-horizontal">
		  <?php
			foreach($text_field_name as $field)
			{
				if($field['Field'] != "psi_id")
				{
		  ?>
		  <div class="form-group required">
            <label class="col-sm-2 control-label" for="<?php echo $field['Field'];?>"><?php echo ucwords($field['Field']);?></label>
            <div class="col-sm-4">
              <input type="text" name="<?php echo $field['Field'];?>" id="<?php echo $field['Field'];?>" placeholder="<?php echo ucwords($field['Field']);?>" value="<?php echo (isset($psi_credentials_details[$field['Field']])) ? $psi_credentials_details[$field['Field']] : "";?>" class="form-control">
            </div>
          </div>
		  <?php
				}
			}
		  ?>
		  <div class="form-group">
				<label class="col-sm-2 control-label" for="<?php echo $entry_order_status; ?>"><?php echo $entry_order_status; ?></label>
				<div class="col-sm-4">
				  <select name="psi_paymentapi_status" class="form-control">
					<?php if ($entry_order_status) { ?>
					<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
					<option value="0"><?php echo $text_disabled; ?></option>
					<?php } else { ?>
					<option value="1"><?php echo $text_enabled; ?></option>
					<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
					<?php } ?>
				  </select>
				</div>
			</div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?> 