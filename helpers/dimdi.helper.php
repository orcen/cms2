<?php
class dimdi{

  var $result = array();

  var $actCptl;

  function __construct(){
    $this->dimdi();
  }

  function __destruct() {
    unset($this->result);
  }

  function dimdi(){
    global $db;

    $this->actCptl = 0;
    $this->actGroup = 0;
  }

  function processText($data){

    $typ1 = substr($data,0,1);
    $typ2 = substr($data,1,1);

    //echo $data;
    switch($typ1){
      // Kapitel Info
      case '0':

        if( $typ2 == 'T'){

          if( substr($data,3,7) == 'Kapitel' ){
            $this->actCptl ++;
            $this->result[$this->actCptl]['name'] = substr($data,3);
          }

        } elseif( $typ2 == 'G') {
          list($value, $info) = explode(" ",substr($data,3), 2);
          if( strpos($value, '-') ){
            $this->result[$this->actCptl]['groups'][$value]['name'] = $info;
          }
        }
        break;

      // Gruppe
      case '1':
        if( $typ2 == 'T'){
          if( preg_match('/\(([A-Z][0-9]{2}-[A-Z][0-9]{2})\)/',$data, $match) )
          {
            $this->actGroup = $match[1];
          }
        }
        break;

      // Untergruppe 1
      case '2':
        break;

      // Untergruppe 3
      case '3':
        break;


      // 3-Steller
      case '4':
      // 4-Steller
      case '5':
      // 5-Steller
      case '6':
        if( $typ2 == 'T'){
          $value = null;
          $info = null;
          list($value, $info) = explode(" ",substr($data,3), 2);
          $this->lstItem = $typ1.$typ2.'-'.$value;
          $this->result[$this->actCptl]['groups'][$this->actGroup]['items'][$value]['text'] = trim($info);
          /*$this->result[$this->actCptl]['groups'][$this->actGroup]['items'][$value]['exc'] = array();
          $this->result[$this->actCptl]['groups'][$this->actGroup]['items'][$value]['inc'] = array();*/

        } elseif( $typ2 == 'E' && $this->lstItem != ''){

          $this->lstItem = substr_replace($this->lstItem, $typ2, 1, 1);
          $this->result[$this->actCptl]['groups'][$this->actGroup]['items'][substr($this->lstItem,3)]['exc'][] = trim(substr($data,3));

        } elseif( $typ2=='I' && $this->lstItem != ''){

          $this->lstItem = substr_replace($this->lstItem, $typ2, 1, 1);
          $this->result[$this->actCptl]['groups'][$this->actGroup]['items'][substr($this->lstItem,3)]['inc'][] = trim(substr($data,3));
        }
        break;
    }
  }

  function addItem($text){
    global $typ2;

    $text = str_replace(array("\n","\r"),'',$text);

    if( substr($text,0,1) != ' ' ){
      $this->processText($text);

    }else{

      if( $this->lstItem != null ) {

        $item = substr($this->lstItem,3);

        if( $this->lstItem[1] == 'I' ) {
          $last = count($this->result[$this->actCptl]['groups'][$this->actGroup]['items'][$item]['inc'])-1;
          $this->result[$this->actCptl]['groups'][$this->actGroup]['items'][$item]['inc'][$last] .= ' '.trim($text);

        } elseif( $this->lstItem[1] == 'E' ) {
          $last = count($this->result[$this->actCptl]['groups'][$this->actGroup]['items'][$item]['exc'])-1;
          $this->result[$this->actCptl]['groups'][$this->actGroup]['items'][$item]['exc'][$last] .= ' '.trim($text);

        } elseif( $this->lstItem[1] == 'T' ) {
          $this->result[$this->actCptl]['groups'][$this->actGroup]['items'][$item]['text'] .= ' '.trim($text);
        }
      }
    }
  }
}
?>
