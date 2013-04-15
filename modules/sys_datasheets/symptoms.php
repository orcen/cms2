
<h2>{L:system:symptoms}</h2>
<?php
$nl = "\n\r";

$db->select('id','`ICD10-items`');
echo $db->error;
echo '<strong>{L:system:entries_count}: </strong>'.$db->row_count;

if( !isset($_GET['capitel']) ) {

  if( $capitels = $db->select('pid', '`ICD10-groups`',null,null,'pid') ){
    echo '<ul class="overview">'.$nl;

    foreach( $capitels as $cap ){
      echo '<li><a href="?mod=sys_datasheets&amp;section=symptoms&amp;capitel=' . $cap['pid'] . '"> {L:symptoms:capitel} ' . $cap['pid'] . '</a></li>'.$nl;
    }

    echo '</ul>'.$nl;
  } else {
    echo $db->error;
  }
} elseif( isset($_GET['capitel'] ) && !isset($_GET['group'])) {
  if( $groups = $db->select('id,`group_nr`,`group_name`', '`ICD10-groups`','pid='.$_GET['capitel']) ){
    echo '<a href="?mod=sys_datasheets&amp;section=symptoms"> {L:symptoms:capitel} ' . $_GET['capitel'] . '</a>'.$nl;
    echo '<ul class="overview">'.$nl;
    foreach( $groups as $grp ){
      echo '<li><a href="?mod=sys_datasheets&amp;section=symptoms&amp;capitel=' . $_GET['capitel'] . '&amp;group=' . $grp['id'] . '"> ' . $grp['group_nr'] . ' - ' . $grp['group_name'] . '</a></li>'.$nl;
    }

    echo '</ul>'.$nl;
  } else {
    echo $db->error;
  }

} elseif( isset($_GET['group']) && !isset( $_GET['item']) ) {

  if( $items = $db->select('id,`item_nr`,`item_name`', '`ICD10-items`','pid='.$_GET['group']) ){
    echo '<a href="?mod=sys_datasheets&amp;section=symptoms&amp;capitel='. $_GET['capitel'] .'"> {L:symptoms:capitel} </a>'.$nl;
    echo '<ul class="overview">'.$nl;
    foreach( $items as $item ){
      echo '<li><a href="?mod=sys_datasheets&amp;section=symptoms&amp;capitel='. $_GET['capitel'] .'&amp;group='. $_GET['group'] .'&amp;item='.$item['id'].'">'
       . $item['item_nr'] . ' - ' . $item['item_name'] . '</a></li>'.$nl;
    }

    echo '</ul>'.$nl;
  } else {
    echo $db->error;
  }
} elseif( isset($_GET['item']) ) {
  echo '<a href="?mod=sys_datasheets&amp;section=symptoms&amp;capitel='. $_GET['capitel'] .'&amp;group='. $_GET['group'] .'"> {L:symptoms:capitel} </a>'.$nl;

}

?>