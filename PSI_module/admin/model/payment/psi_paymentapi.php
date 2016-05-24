<?php
class ModelPaymentPsiPaymentapi extends Model {
	public function install() {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "psi_credentials` (
			  `psi_id` INT(11) NOT NULL AUTO_INCREMENT, 
			  `api_username` VARCHAR(100), 
			  `api_password` VARCHAR(100), 
			  `api_key` VARCHAR(100), 
			  `bo_username` VARCHAR(100), 
			  `bo_password` VARCHAR(100), 
			  `endpoint` VARCHAR(255),
			  PRIMARY KEY (`psi_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
		
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "psi_order_transaction` (
			  `id` INT(11) NOT NULL AUTO_INCREMENT,
			  `order_id` INT(11) NOT NULL, 
			  `reference_id` VARCHAR(100), 
			  `bill_no` VARCHAR(100), 
			  `date_time_request` bigint(16), 
			  `payment_order_no` VARCHAR(100), 
			  `currency_code` VARCHAR(15), 
			  `customer_ip` VARCHAR(20), 
			  `card_no` VARCHAR(20), 
			  `month` VARCHAR(2), 
			  `year` VARCHAR(4), 
			  `first_name` VARCHAR(50), 
			  `last_name` VARCHAR(50), 
			  `gender` VARCHAR(10), 
			  `birth_date` VARCHAR(10), 
			  `email` VARCHAR(100), 
			  `phone_no` VARCHAR(50), 
			  `zip_code` VARCHAR(20), 
			  `address` VARCHAR(255), 
			  `city` VARCHAR(50), 
			  `state` VARCHAR(50), 
			  `country` VARCHAR(50), 
			  `s_first_name` VARCHAR(50), 
			  `s_last_name` VARCHAR(50), 
			  `s_gender` VARCHAR(10), 
			  `s_birth_date` VARCHAR(10), 
			  `s_email` VARCHAR(100), 
			  `s_phone_no` VARCHAR(50), 
			  `s_zip_code` VARCHAR(20), 
			  `s_address` VARCHAR(255), 
			  `s_city` VARCHAR(50), 
			  `s_state` VARCHAR(50), 
			  `s_country` VARCHAR(50),
			  `s_ship_type` VARCHAR(5),
			  `amount` decimal(18,2),
			  `product_desc` LONGTEXT,
			  `product_type` LONGTEXT,
			  `product_item` LONGTEXT,
			  `product_qty` LONGTEXT,
			  `product_price` LONGTEXT,
			  `order_status_id` INT(11),
			  `date_created` DATETIME,
			  `date_completed` DATETIME,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
	}

	public function uninstall() 
	{
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "psi_credentials`;");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "psi_order_transaction`;");
	}
	
	public function get_psi_credentials_fields()
	{
		$fields = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "psi_credentials`");
		return $fields->rows;
	}
	
	public function get_psi_credentials_details()
	{
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "psi_credentials` WHERE `psi_id` = 1");
		return $query->row;
	}
	
	public function insert_credentials($data_arr)
	{
		$keys = array();
		$values = array();
		foreach($data_arr as $k=>$v)
		{
			
			$keys = array_merge($keys, array($k));
			$values = array_merge($values, array($v));
		}
		$isexist = $this->db->query("SELECT `psi_id` FROM `".DB_PREFIX . "psi_credentials`");
		if($isexist->num_rows > 0)
		{
			$this->db->query("UPDATE `".DB_PREFIX . "psi_credentials` SET api_username='".$data_arr['api_username']."', api_password='".$data_arr['api_password']."', api_key='".$data_arr['api_key']."', bo_username='".$data_arr['bo_username']."', bo_password='".$data_arr['bo_password']."',endpoint='".$data_arr['endpoint']."' WHERE `psi_id` = 1");
		}else
		{
			$setvalues = "";
			foreach($values as $val)
			{
				$setvalues .= "'".$val."', ";
			}
			$finalvalues = substr($setvalues,0,strlen($setvalues) - 2);
			$this->db->query("INSERT INTO `".DB_PREFIX . "psi_credentials`(".implode(', ', $keys).") VALUES (".$finalvalues.")");	
		}
		
	}
	
}
?>