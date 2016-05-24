<?php
// Author : Abraham Heriales Jr
// Power by : sitejrh.com
// for more new cool technologies come visit www.sitejrh.com
class Myxsd_generator{
	// variable declarations
	private $addrules = array();
	private $addelement = array();
	private $complexement = array();
	private $addcomplexelement = array();
	private $xmlfile = null;
	private $xsdfile = null;

/*======================================================================================
|		Setting rules
|======================================================================================*/
	function set_rules($rulesname = null, $basename = null, $patternval= null){
	
			$arr = array("rulename" => $rulesname, "basename"=>$basename, "pattern"=>$patternval);
			array_push($this->addrules,$arr);
	}

/*======================================================================================
|		Setting elements
|======================================================================================*/
	function set_element($elementname = null, $type = null, $minOccurs = null, $maxOccurs = null){
			$elerr = array("name" => $elementname, "type"=>$type, "minOccurs"=>$minOccurs, "maxOccurs"=>$maxOccurs);
			array_push($this->addelement,$elerr);
	}

/*======================================================================================
|		Setting elements
|======================================================================================*/
	function set_complex($elementname = null, $comelement = null){
			$elerr = array("name" => $elementname, "comelement"=>$comelement);
			array_push($this->complexement,$elerr);
			
	}	
	
/*======================================================================================
|		Setting xml file
|======================================================================================*/
	function set_xml($xmlf = null){
		$this->xmlfile = $xmlf; 
	}
	
/*======================================================================================
|		Setting xsd file
|======================================================================================*/
	function set_xsd($xsdf = null){
		$this->xsdfile = $xsdf; 
	}

	
/*======================================================================================
|		Start validating
|======================================================================================*/
	function xmlxsd_validate(){

		libxml_use_internal_errors(true);
		$xml = new DOMDocument();
		$xml->load($this->xmlfile);

		 $start = time();
				if (!$xml->schemaValidate($this->xsdfile)) {
					$errors = libxml_get_errors();
					foreach($errors as $msg){
						//echo "<b>Schema Error [" . $msg->code . "]</b> " . $msg->message . "<br />";
						//echo "On File " . $msg->file . "<br />";
						//echo "Line " . $msg->line . "<br />";
						
						return array('error' => $msg->code,'msg' =>$msg->message, 'file' => $msg->file, 'line' => $msg->line);												
					}
						return $errors;
				}
				else{
					return array("msg"=>"SUCCESS");
				}
				
			$end = time();
			print " <br /><br/> Validation runtime in " . ($end-$start) . " seconds.\n";			
	}	
/*======================================================================================
|		create xsd file
|======================================================================================*/
	function render_xsd(){
	
		$folderpath = $_SERVER['DOCUMENT_ROOT'] . "/" . "application/libraries/myxsd/" . $this->xsdfile;
		//$myfile = fopen($this->xsdfile, "w") or die("Unable to open file!");
		$myfile = fopen($folderpath, "w") or die("Unable to open file!");
		$txt = "<?xml version='1.0' encoding='UTF-8' ?>\n";
		$txt .= "<xs:schema attributeFormDefault='unqualified' elementFormDefault='qualified' xmlns:xs='http://www.w3.org/2001/XMLSchema'>\n\n";

		foreach($this->addrules as $narr){
			
			$txt .= "<xs:simpleType name='" . $narr['rulename'] . "'>\n";
			$txt .= " <xs:restriction base='xs:" . $narr['basename']  . "'>\n";
			if($narr['pattern'] == "null"){
					// do nothing
			}
			else{
				$txt .= " <xs:pattern value='" . $narr['pattern']  . "'/>\n";
			}
			$txt .= "</xs:restriction>\n";	
			$txt .= "</xs:simpleType>\n\n";	
		}

		//check if array is empty or not
		$notempty = array_filter($this->addelement);
		if(!empty($notempty)) {
		
			$txt .= " <xs:element name='Parameters'>\n";
			$txt .= "<xs:complexType>\n";
			$txt .= "<xs:sequence>\n";
				
			foreach($this->addelement as $elearr){
				if($elearr['type']=="null"){
					$txt .= "<xs:element name='" . $elearr['name'] . "'/>\n";
				}
				elseif($elearr['minOccurs']=="null" && $elearr['maxOccurs']=="null"){
					$txt .= "<xs:element name='" . $elearr['name'] . "' type='" . $elearr['type'] ."'/>\n";
				}
				elseif($elearr['minOccurs']=="null"){
					$txt .= "<xs:element name='" . $elearr['name'] . "' type='" . $elearr['type'] ."' maxOccurs='" . $elearr['maxOccurs'] ."'/>\n";
				}
				elseif($elearr['maxOccurs']=="null"){
					$txt .= "<xs:element name='" . $elearr['name'] . "' type='" . $elearr['type'] ."' minOccurs='" . $elearr['minOccurs'] ."'/>\n";
				}
				else{
					$txt .= "<xs:element name='" . $elearr['name'] . "' type='" . $elearr['type'] ."' />\n";

				}
			}
			
			$txt .= "</xs:sequence>\n";
			$txt .= "</xs:complexType>\n";
			$txt .= "</xs:element>\n";
			
		}
		else{
			//do nothing
		}
				
		$txt .= "</xs:schema>"; 
		fwrite($myfile, $txt);
		fclose($myfile);
	}
}


?> 

