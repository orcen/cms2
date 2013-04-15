<?php

/*===========================

  Class: translate
    is used for the translation
    of text placeholdern

============================*/


class translater{

  // attributes
  protected $lang;
  protected $lang_data = array();

  // procedures
  function __construct($lang='de'){
    global $_sC;

    if( file_exists( $_sC->_get('path_syslang').$lang.'.txt') && is_readable( $_sC->_get('path_syslang').$lang.'.txt' ) ) { // if the main language file exists
      $this->processData( $_sC->_get('path_syslang').$lang.'.txt'); // process the data from main language file
    }
  }

  function addFile($file) {
    if( file_exists( $file ) && is_readable( $file )  ) {
      $this->processData($file);
    } else {
      return false;
    }
  }

  function processData($fullname) {
    if( false != $f = fopen($fullname,'r') ) {  // open file and proceed
      while( $data = fgets($f,1024) ){  // read file
        if( substr( trim( $data ), 0, 2 ) != '##' && trim( $data ) != '' ) { // commentaries are with ## prepend so ignore them and empty lines too
          list( $key, $value ) = explode( '=', trim( $data ) );   // split the line by '='; first value is the key and second ist the value
          $this->lang_data[ trim( $key ) ] = trim( $value );  // insert the data in the array
        }
      }
      fclose($f); // close file
    } else {
      return false;
    }
  }

  //
  function translate($raw_text){
    // Search for keywords
    if( preg_match_all('#\{L:([a-z]*):([a-z-_]*)\}#',$raw_text,$matches) ) { // find all string in form of {L:<group>:<key>}

      for($i=0;$i<count($matches[0]);$i++){

        if( in_array($matches[2][$i],array_keys($this->lang_data) ) ){ // if key is in the database
          $raw_text = str_replace($matches[0][$i], $this->lang_data[$matches[2][$i]], $raw_text);  // replace it
        } else {
          $replacement = str_replace(array('-','_'),' ',$matches[2][$i]);
          $replacement = strtoupper(substr($replacement,0,1)).substr($replacement,1);
          $raw_text = str_replace($matches[0][$i], $replacement, $raw_text);
        }
      }

    }
    return $raw_text;
    //return false;
  }
}
?>