<h1>{L:system:datagroups}</h1>
<p>{L:system:datagroups_description}</p>

<a href="?mod=sys_datagroups&amp;action=new" title="{L:sytem:new_group_description}">{L:sytem:new_group}</a>

<?
$getAction = filter_var($_GET['action'], FILTER_SANITIZE_STRING);

if( !$getAction ) {

} else {

  if( $getAction == 'new' ) {
    $form = new form();
    $form->form_target = '';
    $form->form_method = 'post';
    $form->form_fields = array(
      array(
        'fieldset' => 'new_datagroup',
        'legend' => '{L:datagroups:new_datagroup}',
        'fields' => array(
          array('type'=>'text','name'=>'name','label'=>'{L:datagroup:groupname}'),
          array('type'=>'textarea','name'=>'description','label'=>'{L:datagroup:description}', 'rows'=>5,'cols'=>45),
          array('type'=>'text','name'=>'search','id'=>'search','label'=>'{L:datagroups:items}','size'=>50),
          array('type'=>'submit','name'=>'send','value'=>'Speichern')
        )
      )
    );
    echo $form->create_output();
  }

}

?>