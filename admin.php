<?php
session_start();

header('Content-Type: text/html; charset=utf-8');

//include('./system/config.php');

// from autocomplete

if( file_exists( "./system/config" ) ) {  // Does a Config File exist
	require( "./system/systemconfig.class.php" );
	$_sC = new systemConfig( "./system/config" );


  switch( $_sC->_get('db_type') )	{
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
  require( $_sC->_get( 'path_system' ) . 'translate.class.php' );
  require( $_sC->_get( 'path_system' ) . 'user.class.php' );
  require( $_sC->_get( 'path_system' ) . 'filemanager.class.php' );

  require_once($_sC->_get('path_helpers').'form.helper.php');
	require_once($_sC->_get('path_helpers').'table.helper.php');

  // Translater object
  $translater = new translater();
} else {
	die("<p>The config file couldnt by found! Please create one!");
}


$templateFile = file_get_contents($_sC->_get('path_templates').'main_template.html');

/*****************************************************
 *  Content Processing Start
 *
*****************************************************/

$user = new adminUser();

ob_start();

// login data send
if( isset( $_POST['f_login'] ) ) {
  // user exist and password passed
  if( false === $result = $user->login($_POST['f_username'],$_POST['f_password']) ) {
    echo '<p class="error">{L:system:wrong_password}</p>';
}}

// logout
if( isset($_GET['logout']) && filter_var($_GET['logout'],FILTER_VALIDATE_BOOLEAN) ){
  $user->logout(); // destroy session
  header('location: '. $_sC->_get('domain').'admin.php'); // reload site
}

if( false == $user->isLoaded() ) { // no user
  $form = new form(); // new login form
  $form->form_target = './admin.php';
  $form->form_method = 'POST';
  $form->form_id = 'login_form';

  $form->form_fields = array( // form fields
    'fieldset' => 'login',
    'legend' => '{L:system:login_form}',
    'fields' => array(
        array('type'=>'text','name'=>'username','label'=>'{L:system:username}'),
        array('type'=>'password','name'=>'password','label'=>'{L:system:password}'),
        array('type'=>'submit','name'=>'login','class'=>'btt login', 'value'=>'{L:system:login}')
    )
  );

  echo $form->create_output();

} else { // user is logged in

  if( isset($_GET['mod']) ) {
    include($_sC->_get('path_modules') .$_GET['mod'].'/default.php' );

    if( file_exists($_sC->_get('path_modules') .$_GET['mod'].'/lang/de.txt') ) {
      $translater->addFile($_sC->_get('path_modules') .$_GET['mod'].'/lang/de.txt');
    }
  }
}

$content = ob_get_contents();

if( $_sC->_get('debug') == TRUE )	{
	$content .= "<div style='position:absolute; top: 50px; right:0px; border:1px solid #DDD; background-color: #EFEFEF; font-size:10pt;'>\n"
	. "<strong>DEBUG INFO</strong><br />\n"
	. "<i>Configuration</i><br />\n"
	. nl2br( $_sC->showVariables(TRUE) )
	. "<i>_POST Vars</i><br />\n"
	. nl2br( print_r( $_POST, true ) )
	. "<i>_GET Vars</i><br />\n"
	. nl2br( print_r( $_GET, true ) )
	. "<i>_SESSION Vars</i><br />\n"
	. nl2br( print_r( $_SESSION, true ) )
	. "</div>";
}

ob_end_clean();


/*****************************************************
 *
 *  Content Processing End
 ****************************************************/

$styleLinks = '<link href="'.$_sC->_get('domain').'templates/css/admin.css" rel="stylesheet" type="text/css" media="screen" />'."\n"
	. '<link  href="http://fonts.googleapis.com/css?family=MedievalSharp:regular" rel="stylesheet" type="text/css" >';

$markers = array(
 "###TITLE###" => $_sC->_get('system_title'),
 "###LOGINFORM###" => $loginFormResult,
 "###TOPNAVIGATION###" =>  !$user->isLoaded() ? '' :'<ul><li><a href="?mod=sys_import">{L:system:import}</a></li><li><a href="?mod=sys_overview">{L:system:analyse_overview}</a></li><li><a href="?mod=sys_datasheets">{L:system:datasheet}</a></li><li><a href="?mod=sys_datagroups">{L:system:datagroups}</a></li></ul>' ,
 "###NAVIGATION###" =>  !$user->isLoaded() ? '' : $navigation,
 "###CONTENT###" => $content,
 "###BORDER_CONTENT###" => $border_content,
 "###CSSSTYLES###" => $styleLinks,
 '###JSFILE1###' => $_sC->_get('domain').'javascript/jquery.js',
 '###JSFILE2###' => $_sC->_get('domain').'javascript/jquery-handler.js',
 '###USERNAVIGATION###' => null
);

if( $user->isLoaded() ) {
  $markers['###USERNAVIGATION###'] = '<div class="user_nav"> <a href="#" title="{L:user:last_login} '. date($_sC->_get('datetime_format'),$user->_get('last_login')).'">' . $user->_get('name') . ' ' . $user->_get('surname') . '</a>'
    . ' <a href="?logout=1">{L:system:logout}</a> </div>';
}

$mainTemp = getSubpart($templateFile,'###MAINTEMPLATE###');

$html_output = substituteMarkerArray($mainTemp,$markers);


$html_output = $translater->translate($html_output);

print( $html_output );

$db->close();




function processCSV($fullFilename, $firstLine=1){
  global $rowCnt, $db;

  $f = fopen($fullFilename,'r');

  $linNr = 0;

  while( ( $data = fgetcsv($f, 4096, ",", '"') ) !== false){

    if( $linNr < $firstLine){
      $linNr++;
      continue;
    }

    list($titel,$syn, $brand) = $data;

    $synAr = explode(";",$syn);
    $brandAr = explode(";",$brand);

    $synC=count($synAr);
    $brandC=count($brandAr);
    $rowspan = 0;
    if( $synC > $brandC ){
      $rowspan = $synC;
    }elseif( $synC < $brandC || $synC == $brandC ){
      $rowspan = $brandC;
    }

    for($i=0;$i<$rowspan;$i++)
    {
      if(FALSE === $db->select_row('brand','brands','brand="'.$brandAr[$i].'" OR synonym="'.$synAr[$i].'"') )
      {
      $row = array();

      $row['name'] = $titel;
      $row['synonym'] = (isset($synAr[$i])?$synAr[$i]:null);
      $row['brand'] =  (isset($brandAr[$i])?$brandAr[$i]:null);

      if( false == $db->insert($row,'brands') )
          break;
      }
    }
  }
  fclose($f);
}


function processDIMDI($fullFilename){
  global $rowCnt, $db;

  require('./helpers/dimdi.helper.php');

  $f = fopen($fullFilename,'r');

  $linNr = 0;

  $dimdi = new dimdi();

  while (($data = fgets($f, 4096)) !== false ) {
    $dimdi->addItem($data);
    $linNr++;
  }

  fclose($f);

   //echo nl2br(str_replace("  ","&nbsp;",print_r($dimdi->result,true)));

  // Chapter
  // chapter will not be saved in db yet
  for( $k=1; $k<count($dimdi->result); $k++){
    // for every group in kapitel
    foreach($dimdi->result[$k]['groups'] as $group_nr=>$group){
      //group data
      $grRow = array(
        'pid'         => $k,
        'group_nr'    => $group_nr,
        'group_name'  => $group['name']
      );
      // save group to DB
      if( $db->insert($grRow,'ICD10-groups') ){
        // group id
        $groupId = $db->last_ID;
        // every item in group
        foreach($group['items'] as $item_nr=>$item){
          // define item data
          $itRow = array(
            'pid'       => $groupId,
            'item_nr'   => $item_nr,
            'item_name' => $item['text']
          );
          // save item in DB
          if( $db->insert($itRow,'ICD10-items') ){
            // item id
            $itId = $db->last_ID;
            // Including Info for Item
            if( isset($item['inc']) ){
              // save every single info
              for($i=0; $i<count($item['inc']); $i++ ){
                if( false == $db->insert(array('pid'=>$itId,'typ'=>'I','text'=>$item['inc'][$i]),'ICD10-item_details') ) {
                  echo $db->error;
                }
              }
            }
            // Excluding Info for Item
            if( isset($item['exc']) ){
            // save every single info
              for($e=0; $e<count($item['inc']); $e++ ){
                if( false == $db->insert(array('pid'=>$itId,'typ'=>'E','text'=>$item['exc'][$e]),'ICD10-item_details') ) {
                  echo $db->error;
                }
              }
            }

          }
        }
      }
    }
  }

}


?>