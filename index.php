<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
//include('./system/config.php');

// from autocomplete

if( file_exists( "./system/config" ) )  // Does a Config File exist
{
	require( "./system/systemconfig.class.php" );
	$_sC = new systemConfig( "./system/config" );


  switch( $_sC->_get('db_type') )
	{
		case 'mysql': require_once( $_sC->_get('path_system') ."mysql.class.php" ); break;
		case 'postgresql': require_once( $_sC->_get('path_system') ."pgsql.class.php" ); break;
		default: die("No Database Type was set!");
	}

  # Local database connection
  $db = db::getInstance();

	$db->_set("server",$_sC->_get("db_server"));
	$db->_set("port",$_sC->_get("db_port"));
	$db->_set("user",$_sC->_get("db_user"));
	$db->_set("password",$_sC->_get("db_password"));
	$db->_set("database",$_sC->_get("db_database"));
	$db->_set("coding",$_sC->_get("db_encoding"));

	$db->connect();

  require( $_sC->_get( 'path_system' ) . 'functions.php' );
  require( $_sC->_get( 'path_system' ) . 'translate.class.php' );

  require_once($_sC->_get('path_helpers').'form.helper.php');
	require_once($_sC->_get('path_helpers').'table.helper.php');

  // Translater object
  $translater = new translater();
}
else
{
	die("<p>The config file couldnt be found! Please create one!");
}


$templateFile = file_get_contents($_sC->_get('path_templates').'frontend-template.html');

/*****************************************************
 *  Content Processing Start
 *
 ****************************************************/


 /********************************************
  *
  *    include analytics class
  *    Because it will be stored in SESSION
  *    the class definition must be called
  *    before the session start to prevent
  *    INCOMPLETE_PHP_OBJET
  ********************************************/
require( $_sC->_get('path_modules') . 'mo_analytics/analyse.class.php' );

session_start();

ob_start();

if( isset($_GET['page']) && false != ($page = filter_var($_GET['page'],FILTER_SANITIZE_STRING) ) ){
  echo '<a href="./index.php">&lt;- Zurück</a>';
  switch($page){
    case 'help':
    //echo $_sC->_get('path_pages') . 'de/help.html';
      include( $_sC->_get('path_pages') . 'de/help.html' );
    break;
  }

}else{
  if( !isset($_GET['module']) || false == ( $mod = filter_var($_GET['module'],FILTER_SANITIZE_STRING) ) ){
    $mod = 'analyse';
  }

  switch( $mod ){
    case 'analyse':
      // Analitycs content
      include($_sC->_get('path_modules').'mo_analytics/default.php');
      $translater->addFile($_sC->_get('path_modules') . 'mo_analytics/lang/de.txt');
      break;
  }

}


$content = ob_get_contents();

if( $_sC->_get('debug') == TRUE )	{

	$content .= "<div style='position:absolute;right:0px; border:1px solid #DDD; background-color: #EFEFEF; font-size:10pt;'>\n"
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


$langnav = '<a href="#" title="">DE</a> <a href="#" title="">EN</a> ';


/*****************************************************
 *
 *  Content Processing End
 ****************************************************/

$styleLinks = ' <link rel="stylesheet" href="./templates/css/reseter.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="./templates/css/default.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="./templates/css/fonts/customfont/fontawesome.css" type="text/css" media="screen" />';

$markers = array(
  "###LANG###" => 'de',
  "###TITLE###"=>$_sC->_get('system_title'),
  "###LOGINFORM###"=>'',
  "###TOPNAVIGATION###"=>'',
  "###LANGNAVIGATION###"=>$langnav,
  "###NAVIGATION###"=>'',
  "###CONTENT###"=>$content,
  "###BORDER_CONTENT###"=>'',
  '###FOOTER###' => '',
  "###CSSSTYLES###"=>$styleLinks,
  '###JSFILE1###' => 'http://code.jquery.com/jquery-1.9.0.js',
  '###JSFILE2###' => 'http://code.jquery.com/ui/1.9.0/jquery-ui.js'
);

//print_r($markers);

$mainTemp = getSubpart($templateFile,'###MAINTEMPLATE###');

$html_output = substituteMarkerArray($mainTemp,$markers);

//$html_output = $translater->translate($html_output);
$translater->translate(&$html_output);

print( $html_output );

//echo memory_get_peak_usage()/1024/1024;
// cleanup
$db->close();

unset($translater);
unset($_sC);
?>