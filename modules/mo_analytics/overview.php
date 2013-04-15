<?php
/*******************
 *  View
 *
 *******************/

 $templatePart = '###OVERVIEW###';

 // show link for process data, by edit change the link at the bottom
$module_markers['###BUTTON_NEXT###'] = '<a href="./index.php?module=analyse&amp;action=process_data" class="button next" title="" >'
  .'{L:system:send_data}<i class="icon-arrow-right"></i></a>';

$genderList = array('m'=>'{L:system:male}','f'=>'{L:system:female}');
$ageGroupList = array('10-25'=>'10-25','25-50'=>'25-50','50-75'=>'50-75','75+'=>'75+');

echo '<h1 class="overview-heading">{L:system:query_nr}: '.$_SESSION['analyse']->_get('analyse_id').'</h1>', "\n";

if( $_SESSION['analyse']->_get('additionalInfo') === true ){
  $module_markers['###INFO_ADDITIONAL###'] = "<h2>{L:system:additional_info}</h2>";
  $module_markers['###INFO_ADDITIONAL###'] .= "<strong>{L:system:gender}</strong>: ".$genderList[$_SESSION['analyse']->_get('gender')]."<br />\n";
  $module_markers['###INFO_ADDITIONAL###'] .= "<strong>{L:system:age_group}</strong>: ".$ageGroupList[$_SESSION['analyse']->_get('ageGroup')]."<br />\n";
}

$module_markers['###LIST_MEDS###'] = '<h2>{L:analyse:medicamen_list}</h2>'."\n".'<ul class="list">'."\n";

foreach($_SESSION['analyse']->getMeds() as $med){
  $module_markers['###LIST_MEDS###'] .= '<li>'.$med.'<a href="./index.php?module=analyse&amp;action=remove_data&amp;med='.urlencode($med).'"><i class="icon-remove" title="{L:module:delete_med}"></i></a></li>'."\n";
}
$module_markers['###LIST_MEDS###'] .= '</ul>';

$module_markers['###LIST_SYMPTOMS###'] = '<h2>{L:analyse:symptom_list}</h2>'."\n". '<ul class="list">'."\n";
foreach($_SESSION['analyse']->getSymptoms() as $sympt){
  $module_markers['###LIST_SYMPTOMS###'] .= '<li>'.$sympt['name'].'<a href="./index.php?module=analyse&amp;action=remove_data&amp;sym='.urlencode($sympt['code']).'"><i class="icon-remove" title="{L:module:delete_symptom}"></i></a></li>'."\n";
}
$module_markers['###LIST_SYMPTOMS###'] .= '</ul>';

//
// additional Info Formular

$form = new form();
$form->form_target = 'index.php?module=analyse';
$form->form_id = 'additionalInfo';
$form->form_fields = array(
  array( 'fieldset'=>'info',
    'legend' => '{L:system:additional_info}',
    'fields'=>array(
      array('type'=>'select','name'=>'gender','label'=>'{L:analyse:gender}','value'=>$genderList,'selected'=>$_SESSION['analyse']->_get('gender')),
      array('type'=>'select','name'=>'ageGroup','label'=>'{L:analyse:age_group}','value'=>$ageGroupList,'selected'=>$_SESSION['analyse']->_get('ageGroup'))
    )
  ),
  array( 'fieldset'=>'controls',
    'fields'=>array(
      array('type'=>'submit','name'=>'additional','value'=>'{L:analyse:add_info}')
    )
  )
);

$module_markers['###FORM_ADDITIONAL###'] = $form->create_output();
?>