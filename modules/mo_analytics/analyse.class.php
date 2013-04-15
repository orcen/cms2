<?php

class analyse{

  protected $analyse_id;
  protected $additionalInfo = false; // use optional data default:false
  protected $gender,$ageGroup; // optional data

  protected $saveToDb = false; // save analysis default: false

  // Medications and Symptoms
  protected $meds = array();
  protected $symptoms = array();


  // constructor
  function __construct(){
    $this->analyse();
  }

  // default function
  function analyse(){
    // get global settings
    global $_sC, $db;

    $this->generate_id();
  }

  // generates id 10 chars long from timestamp crypted with md5
  function generate_id(){
    $this->_set('analyse_id', strtoupper(substr(md5(date('U')),0,10)) );
  }

  // set object attribute
  // doesnt allow to set not defined varibale
  function _set($var,$value) {
		$vars = get_class_vars( get_class($this) );

    // if attribute is defined
		if( array_key_exists($var,$vars) ) {
			$this->$var = $value;
		} else {
      return false;
    }
	}

  // get object attribute
  function _get($var) {
		$vars = get_class_vars( get_class($this) );

    // if attribute is defined
		if( array_key_exists($var,$vars) ) {
      return $this->$var;
		} else {
      return null;
    }
	}

  // add medicament
  function addMed($value) {
    $value = trim($value);
    // prevents duplicates
    if( !in_array($value, $this->meds) ) // not in list
      $this->meds[] = $value; //add it
	}

  // remove medicament
  function removeMed($value){
    if( ( $key = array_search($value,$this->meds) ) !== false) {
      unset($this->meds[$key]);
      return true;
    }
    return false;
  }

  // add symptom
  function addSymptom($value) {
    global $db;
    $value = trim($value); // clean value
    $allreadyIn = false; // is in list = false
    foreach($this->symptoms as $sym ){ // go through list and check
      if( $sym['code'] == $value ){ // is in
        $allreadyIn = true; // set to true
        break;  // break for less memory
      }
    }
    if( $allreadyIn === false ){ // if not in the list
      if( $data = $db->select_row('TRIM(item_name) AS item_name','ICD10-items','item_nr="'.$value.'"') ){ // get info from db
        $this->symptoms[] = array('code'=>$value,'name'=>$data['item_name']); // add to list
      }
    }
  }

  // return array of Medicaments
  function getMeds() {
    return $this->meds;
  }

  // return array of Symptoms
  function getSymptoms() {
    return $this->symptoms;
  }

  // set variables except $analyse_id to null
  function dropData(){
    $this->meds = array();
    $this->symptoms = array();
    $this->additionalInfo = false;
    $this->gender = null; //optional
    $this->ageGroup = null; //optional
    $this->analyse_id = null;
  }

  /* * * * * * * * * * * * * * * * * * *
   *
   *  Functions for the communication
   *  with R-Project
   *
   * * * * * * * * * * * * * * * * * * */

  function sendToR($url){
    $x = curl_init($url);
    curl_setopt($x, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($x, CURLOPT_HEADER, 0);
    curl_setopt($x, CURLOPT_CUSTOMREQUEST, 'GET');

    $exportData = $this->generateCUrlString();
    curl_setopt($x, CURLOPT_POSTFIELDS, $exportData);
    curl_setopt($x, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($x, CURLOPT_REFERER, "http://www.domain.net/");
    curl_setopt($x, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($x, CURLOPT_FAILONERROR,true);

    if($data = curl_exec($x) === false)
    {
      $data = 'Curl error: ' . curl_error($x) . ' <br />';
      $data = '<strong>Output String</strong><br />'
        . '<code>'.$exportData.'</code>';
    }

    curl_close($x);
    return $data;
  }

  // generates the string that will be send to R-Project
  private function generateCUrlString(){

    $stringData = array(
      'analyse_id' => $this->analyse_id,
      'medicaments' => $this->getMeds(),
    );

    $symptoms = $this->getSymptoms();
    foreach($symptoms as $item ){
      $stringData['symptoms'][] = $item['code'];
    }

    if( $this->additionalInfo == true ){
      $stringData['addInfo']['gender'] = $this->gender;
      $stringData['addInfo']['ageGroup'] = $this->ageGroup;
    }
    return json_encode($stringData);
  }


}

?>