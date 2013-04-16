<h1>{L:system:datagroups}</h1>
<p>{L:system:datagroups_description}</p>

<a href="?mod=sys_datagroups&amp;action=new" title="{L:sytem:new_group_description}">{L:sytem:new_datagroup}</a>

<?

$getAction = filter_var($_GET['action'], FILTER_SANITIZE_STRING);

if( !$getAction ) {

  if( $groupList = $db->select('id, name, description, COUNT(item) as item_count','datagroups AS dg LEFT JOIN datagroups_details AS dd ON dg.id=dd.pid',null,null,'dd.pid') ) {

    $table = new table(0,array('id'=>'datagroups','class'=>'list'));
    $table->table_head(
      array('{L:system:id}','{L:datagroups:groupname}','{L:datagroups:group_description}','{L:datagroups:item_count}')
    );

    for( $i=0; $i<$db->row_count; $i++ ) {
      $table->add_row($groupList[$i]);
    }

    $table->create_output();

    echo $table->result;
  } else {
    echo '<h3>{L:datagroups:no_datagroups_available}</h3>';
  }

} else {

  if( isset($_POST['f_action']) && $_POST['f_action'] == 'new' ) {
    $name = filter_var($_POST['f_name'],FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['f_description'],FILTER_SANITIZE_STRING);
    $list = filter_var_array($_POST['f_list'],FILTER_SANITIZE_STRING);

    if( $db->insert(array('name'=>$name,'description'=>$description), 'datagroups') ) {
      $dg_id = $db->last_ID;

      for( $l=0; $l<count($list); $l++ ) {
        $db->insert(array('pid'=>$dg_id,'item'=>$list[$l]),'datagroups_details');
      }
    } else {
      echo $db->error;
    }
  }

  if( $getAction == 'new' ) {
    $form = new form();
    $form->form_target = '?mod=sys_datagroups&amp;action=new';
    $form->form_id = 'datagroups';
    $form->form_method = 'post';
    $form->form_fields = array(
      array(
        'fieldset' => 'new_datagroup',
        'legend' => '{L:datagroups:new_datagroup}',
        'fields' => array(
          array('type'=>'hidden','name'=>'action','value'=>'new'), // hidden - action
          array('type'=>'text','name'=>'name','label'=>'{L:datagroup:groupname}'), // text - name
          array('type'=>'textarea','name'=>'description','label'=>'{L:datagroup:group_description}', 'rows'=>5,'cols'=>45), // textarea - description
          array('type'=>'text','name'=>'search','id'=>'searchField','label'=>'{L:datagroups:group_items}','size'=>50), // text - searchfield
          array('string'=>'<ul id="itemList"></ul>'), // html item
          array('type'=>'submit','name'=>'send','value'=>'{L:system:save}') // button submit
        )
      )
    );
    echo $form->create_output();
  }
}
?>
<script type="text/javascript">
  $('document').ready(function () {
    $("#searchField")
    .bind( "keydown", function( event ) {
      if ( event.keyCode === $.ui.keyCode.TAB &&
      $( this ).data( "catcomplete" ).menu.active ) {
        event.preventDefault();
      }
    })
    .autocomplete({
      source: './helpers/suggest.php?only=meds',
      minLength: 2,
      delay: 100,
      select: function(event, ui) {
        //console.log(ui.item.label);
        var list = $('#itemList'),
            item = $('<li/>'),
            field = $('<input/>',{'type':'hidden','name':'f_list[]'});
        field.attr('value',ui.item.label);
        item.text(ui.item.label);

        list.append(item);
        $('#datagroups').append(field);
        $("#searchField").val('');
        return false;
      },
      focus: function(event, ui) {
        $("#searchField").val(ui.item.label);
        return false;
      }
    }).val('');
  });
</script>