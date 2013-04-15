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
    $form->form_id = 'datagroups';
    $form->form_method = 'post';
    $form->form_fields = array(
      array(
        'fieldset' => 'new_datagroup',
        'legend' => '{L:datagroups:new_datagroup}',
        'fields' => array(
          array('type'=>'text','name'=>'name','label'=>'{L:datagroup:groupname}'),
          array('type'=>'textarea','name'=>'description','label'=>'{L:datagroup:description}', 'rows'=>5,'cols'=>45),
          array('type'=>'text','name'=>'search','id'=>'searchField','label'=>'{L:datagroups:items}','size'=>50),
          array('type'=>'select','name'=>'itemList','id'=>'itemList','size'=>15,'value'=>''),
          array('type'=>'submit','name'=>'send','value'=>'Speichern')
        )
      )
    );
    echo $form->create_output();
  }
}
?>

<script src="<?$_sC->_get('domain')?>files/js/jquery-1.9.0.js"></script>
<script src="<?$_sC->_get('domain')?>files/js/jquery-ui-1.9.0.custom.js"></script>
<script type="text/javascript">
  $.widget( "custom.catcomplete", $.ui.autocomplete, {
        _renderMenu: function( ul, items ) {
            var that = this;
            $.each( items, function( index, item ) {
                that._renderItemData( ul, item );
            });
        }
    });
  function suggestValues() {
    $("#searchField")
    .bind( "keydown", function( event ) {
      if ( event.keyCode === $.ui.keyCode.TAB && $( this ).data( "catcomplete" ).menu.active ) {
        event.preventDefault();
      }
    })
    .catcomplete({
      source: './helpers/suggest.php?only=meds',
      minLength: 2,
      delay: 0,
      appendTo:'#datagroups',
      focus: function(event, ui){
        alert('test');
        $("#searchField").val(ui.item.label);
        return false;
      },
      select: function(event, ui){
        var item = ui.item;
        var show = $('<option/>',{'value':item.value,'text':item.value});

        $('#itemList').addOption(show);

        return false;
      }
    }).attr('value','');
  }

  $('document').ready(function () {
    suggestValues();
  });
</script>