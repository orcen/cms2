<h1>{L:system:import_module}</h1>
<?php

$dir = $_sC->_get('path_uploads');
if( isset($_GET['dir']) && $_GET['dir'] = filter_var($_GET['dir'],FILTER_SANITIZE_STRING)){
  $dir .= $_GET['dir'].'/';
}

$fileManager = new fileManager($dir);


/*print_r($fileManager->getFiles());
print_r($fileManager->getDirectories());*/

  $params = array('class'=>'list file');

  $uperDir = substr($dir,strlen($_sC->_get('path_uploads'))-1,strlen($dir)-strrpos($dir,'/'));

  $table = new table(0,$params);
  $table->table_head(array(
    (isset($_GET['dir'])?$uperDir:'{L:filemanager:file_icon}'),
    '{L:filemanager:filename}',
    '{L:filemanager:filetype}',
    '{L:filemanager:mime_type}',
    '{L:filemanager:file_size}',
    '{L:system:actions}')
  );

  // shows directories
  foreach($fileManager->getDirectories() as $dir){
    $icon = '<span class="sprite dir"></span>';
    $row = array($icon,'<a href="?mod=sys_import&amp;dir='. urlencode($dir['name']) .'">' . $dir['name'] . '</a>',$dir['extension'],'','','');
    $table->add_row($row);
  }

  // show files
  foreach($fileManager->getFiles() as $file){

    $icon = '<span class="sprite '.$file['extension'].'"></span>';

    $actions = '<a href="" class="action sprite fopen" title="{L:filemanager:file_open}">{L:filemanager:file_open}</a>'
      . '<a href="" class="action sprite fedit" title="{L:filemanager:file_edit}">{L:filemanager:file_edit}</a>'
      . '<a href="" class="action sprite fremove" title="{L:filemanager:file_remove}">{L:filemanager:file_remove}</a>';
    $size = round($file['size']/1024,2).'kB';
    $row = array($icon,$file['name'],$file['extension'],$file['type'],$size,$actions);
    $table->add_row($row);
  }

  $table->create_output();
  echo $table->result;



/*if( false == isset($_GET['file']) ){
  $dir = opendir($BASE_PATH.'data/');


  while( $file = readdir($dir) ){
    if( $file != "." && $file != "..")
    {
      echo '<a href="?page=import&amp;file='.$file.'">'.$file.'</a><br />';
    }
  }

  closedir($dir);
}elseif( isset($_GET['file']) && ($file = filter_var($_GET['file'],FILTER_SANITIZE_STRING)) ){

  echo "<a href='?page=import'>Zurück</a><br />";

  if( file_exists($BASE_PATH.'data/'.$file) ){
    $fullFilename =  $BASE_PATH.'data/'.$file;
    $rowCnt = 0;

    if( substr($file,strrpos($file,".")+1) == 'csv'){
      processCSV($fullFilename);
    }elseif(substr($file,strrpos($file,".")+1) == 'txt'){
      echo processDIMDI($fullFilename);
    }
  }
}  */
?>