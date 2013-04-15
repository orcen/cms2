<h2>{L:system:brands}</h2>

<?php
  $db->select('name','brands');
  echo $db->error;
  echo '<strong>{L:system:entries_count}: </strong>'.$db->row_count . '<br />';

  // items per page
  $perPage = 50;

  // Page count
  $pages =  ceil( $db->row_count / $perPage );


  echo '{L:system:page_count}:' . $pages . '<br />';

  $table = new table(0,array('class'=>'list'));

  $table->table_head(array('{L:system:brand_name}', '{L:system:brand_synonym}', '{L:system:brand_brand}'));
  $select = $db->select('name, synonym, brand', 'brands',null, null, 'name ASC', 0, $perPage);
  foreach($select as $row){
    $table->add_row($row);
  }

  $table->create_output();

  print($table->result);
?>
