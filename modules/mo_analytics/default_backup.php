<?php

// Path for this module
$module_path = $_sC->_get('path_modules').'mo_analytics/';

// template file
$module_templateFile = file_get_contents($module_path.'templates/default.html');

//markers for the template
$module_markers = array(
  '###INFO_ADDITIONAL###' => null,
  '###BUTTON_NEXT###' => null,
  '###BUTTON_BACK###' => null,
  '###BUTTON_RESET###' => null,
  '###LIST_MEDS###' => null,
  '###LIST_SYMPTOMS###' => null,
  '###FORM_ADDITIONAL###' => null,
  );

// no data or action shows the form
/*
  TODO:

  change it to template file
*/
if( empty( $_POST ) && !isset( $_GET['action'] ) ) {

//  show Formular for data
?>
  <form accept="text/html" accept-charset="utf-8" action="index.php" enctype="multipart/formdata" method="post" id="searchForm" xml:lang="de">
  <fieldset>
  <label for='searchField'> {L:analyse:main_label}</label>
  <input type="text" name="search" id='searchField' size='90' value='' />
  <small>{L:analyse:main_label_info}</small>
  </fieldset>
  <ul id='searchList'>
  </ul>
  <fieldset>
  <button type='submit' name='send_data'>
  <?php
    if( isset($_SESSION['analyse']) && ( $analyse_id = $_SESSION['analyse']->_get('analyse_id') ) != '' ){
      echo '{L:analyse:add_data}';
    } else {
      echo '{L:analyse:start_analyse}';
    }
    ?>
  </button>
  </fieldset>
  </form>

<?php

  // show link to overview if allready data ready
  if( isset($_SESSION['analyse']) && ( $analyse_id = $_SESSION['analyse']->_get( 'analyse_id' ) ) != '' ){
    echo '<a href="index.php?module=analyse&action=overview&analyse_id='.$analyse_id.'" class="button" id="toOverview">{L:analyse:overview}<i class="icon-arrow-right"></i></a>',"\n";
  }

} else {

  // process data
  if( !isset( $_POST['meds'] ) && !isset( $_POST['symptoms'] ) && !isset( $_POST['f_additional'] ) && !isset( $_GET['action'] ) ){
    unset( $_POST );
    echo '<p>{L:analyse:error-no_data_send}</p>';
    header( 'refresh:5; url=redirect.php' );

  } elseif( !isset($_GET['analyse_id']) ) {

    /*******************
     *  Controler
     *
     *******************/
    // create object if not yet set
    if( false === is_object($_SESSION['analyse']) ) {
      $_SESSION['analyse'] = new analyse();
    }

    if( isset($_GET['action']) ) {
      $action = filter_var( $_GET['action'], FILTER_SANITIZE_STRING);
      // delete all data and go to start page
      if( $action=='drop_data' ) {

        $_SESSION['analyse']->dropData();
        // reload page
        header('location: index.php?module=analyse');
        exit;

      } elseif( $action == 'remove_data' ) {
        if( isset($_GET['med']) ) {
          $_SESSION['analyse']->removeMed( urldecode($_GET['med']) );
        } elseif( isset($_GET['sym']) ) {
          $_SESSION['analyse']->removeSymptom( urldecode($_GET['sym']) );
        }
        // redirect to overview site
        header( 'location: index.php?module=analyse&action=overview&analyse_id=' . $_SESSION['analyse']->_get( 'analyse_id' ) );
        exit;
      } elseif( $action=='process_data' ) { // send data to extern server

        $result = $_SESSION['analyse']->sendToR($_sC->_get('rProjectServerURL'));
        echo $result; // just for debuging, should be extended after some data are retrieved
        include( $module_path . 'show_result.php');
      }

    } else {

      // generate Id if not set
      // happens after drop data
      if( $_SESSION['analyse']->_get('analyse_id') == '' ) {
         $_SESSION['analyse']->generate_id();
      }

      // add Medicaments to Object
      if( isset($_POST['meds']) ) {
        foreach($_POST['meds'] as $med){
          // clean string
          $med = filter_var($med,FILTER_SANITIZE_STRING);
          $_SESSION['analyse']->addMed($med);
        }
      }

      //add Symptoms to Object
      if( isset($_POST['symptoms']) ) {
        foreach($_POST['symptoms'] as $sympt){
          // clean string
          if( $sympt = filter_var($sympt,FILTER_SANITIZE_STRING) ) {
            $_SESSION['analyse']->addSymptom($sympt);
          }
        }
      }

      // set adittional(optional) info
      if( isset($_POST['f_additional']) ){
        $_SESSION['analyse']->_set( 'additionalInfo', true );
        $_SESSION['analyse']->_set( 'gender', $_POST['f_gender'] );
        $_SESSION['analyse']->_set( 'ageGroup', $_POST['f_ageGroup'] );
      }

      // redirect to overview site
      header( 'location: index.php?module=analyse&action=overview&analyse_id=' . $_SESSION['analyse']->_get( 'analyse_id' ) );
      exit;
    }
  } elseif( isset($_GET['analyse_id']) && isset($_GET['action']) ) {
    // no analyses saved
    // after saving in DB this will be overworked, so it shows the analyse
    // but only after login and for the admin or the owner
    if( !isset($_SESSION['analyse']) || $_GET['analyse_id'] !== $_SESSION['analyse']->_get('analyse_id') ) {
      header('location: '.$_sC->_get('domain').'index.php');
    }

    // show return link for another search
    $module_markers['###BUTTON_BACK###'] =  '<a href="./index.php?module=analyse" class="button back" title="Sie können weitere Daten angeben. Diese werde den bereits angegeben hinzugefügt." >'
      .'<i class="icon-arrow-left"></i>{L:system:add_next_data}</a> <br />';
    $module_markers['###BUTTON_RESET###'] =  '<a href="./index.php?module=analyse&action=drop_data" class="button delete" titel="Alle Daten werden gelöscht!!" '
        .'onclick=" return confirm(\'Wollen Sie die Daten wirklich löschen?\'); return false;"><i class="icon-trash"></i>{L:system:drop_data} </a>';


    switch( filter_var( $_GET['action'], FILTER_SANITIZE_STRING ) ) {
      default:
      case 'overview':
        include( $module_path . 'overview.php' );
      break;




    }
  }
}
$templatePart = '###OVERVIEW###';

$temp = getSubpart($module_templateFile,$templatePart);

echo substituteMarkerArray($temp,$module_markers);


?>