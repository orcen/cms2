<?php
require($_sC->_get( 'path_system' ) . 'PasswordHash.php');

// primray class for Users

class user{

  var $data;
  var $userId;

  var $sessionIdentifier = 'user';
  var $userTable = 'system_users';

  function __construct(){
    // activate Session if not allready done
    if( false == isset($_SESSION) ) session_start();

    if( isset($_SESSION[$this->sessionIdentifier]) && $_SESSION[$this->sessionIdentifier] != '' ){
      $this->getUserData( $_SESSION[$this->sessionIdentifier] );
    }
  }


  protected function getUserData($uid){
    global $db;

    if( $userData = $db->select_row('name, surname, email, username, last_login, tstamp',$this->userTable,'uid='.$uid) ){
      $this->data = $userData;
      $this->userId = $uid;
      return true;
    } else {
      echo $db->error;
      return false;
    }
  }

  function login($username, $password){
    global $db;

    if( ($username = filter_var($username,FILTER_SANITIZE_STRING)) && ($password = filter_var($password,FILTER_SANITIZE_STRING)) ) {

      if( $resData = $db->select_row('uid, password',$this->userTable,'username = "'. $username .'"') ) {
        $t_hasher = new PasswordHash(8, FALSE);

        if( $t_hasher->CheckPassword($password, $resData['password']) ) {

          if( $this->getUserData($resData['uid']) ) {
            $this->setLoginTime();
            $_SESSION[$this->sessionIdentifier] = $resData['uid'];

            return true;
          }
        }
      }
    }
    return false;
  }

  public function logout(){
    $this->data = null;
    $this->userId = null;
    $_SESSION[$this->sessionIdentifier] = null;
  }

  public function insertUser($data){
    global $db;
    $db->insert($data,$this->userTable);
  }

  public function isLoaded(){
    return empty( $this->userId ) ? false : true;
  }

  public function _get($field){
    if( isset($this->data[$field]) ){
      return $this->data[$field];
    }
  }

  protected function setLoginTime(){
    global $db;
    $db->update(array('last_login'=>time()),$this->userTable,'uid='.$this->userId);
  }
}


class adminUser extends user{
  var $sessionIdentifier = 'adminUser';
}


class clientUser extends user{

}


/*
$t_hasher = new PasswordHash(8, FALSE);
$correct = '';
$hash = $t_hasher->HashPassword($correct);

$data = array('name'=>'Jan','surname'=>'Benes','username'=>'admin','password'=>$hash);

$user = new adminUser();
//var_dump(get_class_methods('adminUser'));
//echo $user->insertUser($data);


print 'Hash: ' . $hash . "\n";  */

?>