<?php

/* Class to handle parsing KML documents */

class kmlDocument{
	
	private $xml = null;
	private $file = null;
	private $docName = null;
	private $docDesc = null;
	private $numPlacemarks = null;
	private $placemarkInfo = array();
	
	function loadData($data){
		/* DIRTY HACK:
		Something about PHP's XPath implimentation makes 
		the searches not work unless this substitution is made. 
		Might be a namespace problem?*/
		
		$data = str_replace("xmlns=", "xmlns:b=", $data);
		
		$this->xml = new SimpleXMLElement($data);
		$this->parseXML();
	}
	
	function loadFile($file){
		if(( $data = file_get_contents($file) ) == false)
			throw new Exception("Error reading file!");
		
		/* DIRTY HACK:
		Something about PHP's XPath implimentation makes 
		the searches not work unless this substitution is made. 
		Might be a namespace problem?*/
		
		$data = str_replace("xmlns=", "xmlns:b=", $data);
		
		try{
			$this->xml = new SimpleXMLElement($data);
		}catch(Exception $e){ throw $e;}
		
		$this->parseXML();
	}
	
	// parses the KML using the Xpath XML library
	function parseXML(){
		$docNames = $this->xml->xpath("Document/name");
		
		if($docNames[0] == ""){
			$docNames = $this->xml->xpath("//name");	
		}
		
		$docName = $docNames[0];
		
		$docDescArr = $this->xml->xpath("Document/description");
		
		// fix this - it will match to the first placemark description
		// if there is no top-level description tag
		
		if($docDescArr[0] == ""){
			$docDescArr = $this->xml->xpath("//description");
		}
		
		$docDesc = $docDescArr[0];
		
		// count up the number of placemarks
		$this->numPlacemarks = count($this->xml->xpath("//Placemark"));
		
		$this->docName = str_replace("\n", "", $docName);
		$this->docDesc = str_replace("\n", "", $docDesc);
	
	
	}
	
	// grabs the name and description tags for all the placemarks
	function readPlacemarkInfo(){
		
		foreach($this->xml->xpath("//Placemark") as $currPlacemark){
			
			$name = str_replace("\n", "", $currPlacemark->name);
			$desc = str_replace("\n", "", $currPlacemark->description);
			$desc = str_replace('"', "'", $desc);
			
			$curr = new placemark($name, $desc);
			array_push($this->placemarkInfo, $curr);
			
		}
		
		return true;
	}
	
	// returns the KML for an arbitrary placemark
	function getPlacemarkXML($placemarkNum){
		$placemarkXML = null;
		$styleID = null;
		$index = 0;
		
		// get the right placemark
		foreach($this->xml->xpath("//Placemark") as $currPlacemark){
			
			if($index == $placemarkNum){
				$placemarkXML .= $currPlacemark->asXML();
				$styleID = str_replace("#", "", $currPlacemark->styleUrl);
			}
			
			$index ++;
		}
		
		// find the right style and grab that also
		foreach($this->xml->xpath("//Style[@id='" . $styleID . "']") as $currStyle){
			$placemarkXML .= $currStyle->asXML() . "\n";
		}
		
		return $placemarkXML;
	}
	
	// gets an array of the placemark's name and descriptions
	function getPlacemarkInfo(){ 
		
		if(count($this->placemarkInfo) == 0)
			$this->readPlacemarkInfo();
		
		return $this->placemarkInfo; 
	}
	function getNumPlacemarks() { return $this->numPlacemarks; }
	function getDocName() { return $this->docName; }
	function getDocDesc() { return $this->docDesc; }
}

class placemark{
	public $name, $desc;
	
	function __construct($name, $desc){
		$this->name = $name;
		$this->desc = $desc;
	}
}

?>
