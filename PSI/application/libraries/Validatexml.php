<?php
if (!defined('KOUNT_VALIDATION_FILE'))
{
  /*
   * Path to input validation file.
   *
   * @var string
   */
  define('KOUNT_VALIDATION_FILE', DIRECTORY_SEPARATOR .'var'. DIRECTORY_SEPARATOR .'www'. DIRECTORY_SEPARATOR .'html'. DIRECTORY_SEPARATOR .'PSI_stage'. DIRECTORY_SEPARATOR . 'validxmlparams' . DIRECTORY_SEPARATOR . 'validxml.xml');
}

class Validatexml {

  
  public function validate ($data) {
    // $errors = array();
	$this->xml = "";
    if (null === $this->xml) {
      $this->xml = simplexml_load_file(KOUNT_VALIDATION_FILE);
    }
	// return $data;
    // request mode
	/*
    $mode = (isset($data['MODE']))? $data['MODE']: null;

    $cartKeys = self::findCartKeys($data);
	*/
    // validate each param in the xml file
	
    foreach ($this->xml->param as $rule) {
      $name = trim($rule->attributes()->name);
		echo "<br/>";
		echo $name;
      // check for required fields
	  /*
      if (isset($rule->required)) {
        $required = false;
        if (isset($rule->required->mode)) {
          // check current request mode vs list of modes that require $name
          // It really takes some effort to stand out as a bad api in php, but
          // simplexml really goes above and beyond in creating an api that
          // only the original author could find intuitative.
          foreach ($rule->required->mode as $reqMode) {
            $required = $required || ($reqMode == $mode);
          }

        } else {
          // if `required` node doesn't contain a mode child we know that
          // this key is unconditionally required.
          $required = true;
        }

        if ($required &&
            !(isset($data[$name]) || isset($cartKeys[$name]))) {
          $errors[] =
              Kount_Ris_ValidationError::requiredFieldError($name, $mode);
        }
      } // end if rule is required

      // validate field contents if provided
      if (isset($data[$name])) {
        $errors = array_merge(
            $errors, self::validateField($rule, $name, $data));

      } else if (isset($cartKeys[$name])) {
        foreach ($cartKeys[$name] as $key) {
          $errors = array_merge(
              $errors, self::validateField($rule, $key, $data));
        }
      }
	  */
    } // end foreach xml node
	
    // return $errors;
  } //end validate



} // end Kount_Util_Khash
