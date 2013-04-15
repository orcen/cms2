<?php
if( false == ($page = filter_var($_GET['page'],FILTER_SANITIZE_STRING) ) ){
  header('location: index.php');
} elseif( isset($_GET['url']) && ($url = filter_var($_GET['url'],FILTER_SANITIZE_STRING) ) ) {
  header('location: '.urldecode($url));
} else {
  header('location: index.php?page='.$page);
}
?>