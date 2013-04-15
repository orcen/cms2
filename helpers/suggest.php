<?php
header('Content-Type: application/json');

if( false != ( $searchWord = filter_var($_GET['term'],FILTER_SANITIZE_STRING) ) && trim($_GET['term'])!='' ){
  if( file_exists( "../system/config" ) )  // Does a Config File exist
  {
  	require( "../system/systemconfig.class.php" );
  	$_sC = new systemConfig( "../system/config" );


    switch( $_sC->_get('db_type') )
  	{
  		case 'mysql': require_once( $_sC->_get('path_system') ."mysql.class.php" ); break;
  		case 'postgresql': require_once( $_sC->_get('path_system') ."pgsql.class.php" ); break;
  		default: die("No Database Type was set!");
  	}

    # Local database connection
    $db = db::getInstance();

  	$db->_set("server",  $_sC->_get("db_server"));
  	$db->_set("port",    $_sC->_get("db_port"));
  	$db->_set("user",    $_sC->_get("db_user"));
  	$db->_set("password",$_sC->_get("db_password"));
  	$db->_set("database",$_sC->_get("db_database"));
  	$db->_set("coding",  $_sC->_get("db_encoding"));

  	$db->connect();

    require( $_sC->_get( 'path_system' ) . 'functions.php' );

    require_once($_sC->_get('path_helpers').'form.helper.php');
  	require_once($_sC->_get('path_helpers').'table.helper.php');
  }
  $finResult = array();

  if( !isset( $_GET['only'] ) || $_GET['only'] == 'meds') {
    if( $medsResult = $db->select('name,synonym ,brand','brands','TRIM(brand) LIKE "'.$searchWord.'%"',null,'brand',0,15) ) {
      if( $db->row_count > 0 ){
        foreach($medsResult as $mItem) {
          if( false == in_array($mItem['brand'], $finResult))
            $finResult[] = array('category'=>'Medikamente','label'=>trim($mItem['brand']),'value'=>trim($mItem['brand']));
        }
      }
    }
  }
  if( !isset( $_GET['only'] ) || $_GET['only'] == 'symptoms') {
    if( $disResult = $db->select('item_nr, item_name','`ICD10-items`','TRIM(item_name) LIKE "%'.$searchWord.'%"',null,'item_name',0,15) ) {

        foreach($disResult as $dItem) {
          $label =  trim($dItem['item_name']);

          if( strlen($label) > 70 ){
            $pos = stripos($label, $searchWord);
            if($pos > 15)
              $label = substr($label,0,10).'...'.substr($label,$pos-15,strlen($searchWord)+35).'...';
            else
              $label = substr($label,0,70).'...';
          }
          $finResult[] = array('category'=>'Symptome','label'=>trim($label), 'value'=>trim($dItem['item_nr']), 'desc'=>trim($dItem['item_name']) );
        }

    } else {
      echo $db->error;
    }
  }

  echo json_encode($finResult);
}

?>