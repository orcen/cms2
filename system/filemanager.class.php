<?php

class fileManager{

  protected $topDir;
  protected $directories = array();
  protected $files = array();

  public $error;

  function __construct($directory){

    if( is_dir($directory) ){ // is directory

      $this->topDir = $directory; // set topdir info

      if( $dir = opendir($directory) ) {  // dir is open, sets dir handler

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        while( $entry = readdir($dir) ){  // reading directory

          $fullpath = $directory.$entry; // fullpath of entries in dir

          if( is_dir($fullpath) && !in_array($entry, array('.','..'))){ // is dir and not the top two entries
            $dirArr = array(
              'name' => $entry, // only entry name
              'extension' => 'dir' // allways dir by directories
            );

            // add to the array
            array_push($this->directories, $dirArr);
          }

          // file handling
          if( is_file($fullpath) ){ // is file

            $file = array(
              'name' => $entry,
              'extension' => substr($entry,strrpos($entry, '.')+1), // file extension
              'type' => finfo_file($finfo,$fullpath), // file type
              'size' => filesize($fullpath), // file size
            );

            // add to file array
            array_push($this->files, $file);
          }
        }

        finfo_close($finfo); // close finfo handler
        closedir($dir); // close dir
      } else {
        $this->error = '{L:filemanager:cant_open_dir}';
        return false;
      }
    } else {
      $this->error = '{L:filemanager:target_not_dir}';
      return false;
    }
  }

  public function getFiles(){
    return $this->files;
  }

  public function getFileCount(){
    return count($this->files);
  }

  public function getDirectories(){
    return $this->directories;
  }

  public function getDirectoryCount(){
    return count($this->directories);
  }
}
?>