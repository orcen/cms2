<h1 class='overview-heading'>{L:system:datasheet}</h1>

<ul class='subnav'>
  <li><a href='?mod=sys_datasheets&amp;section=brands'<?php echo(isset($_GET['section']) || $_GET['section'] == 'brands'?' class="active"':null);?>>{L:system:brands}</a></li>
  <li><a href='?mod=sys_datasheets&amp;section=symptoms'<?php echo(isset($_GET['section']) && $_GET['section'] == 'symptoms'?' class="active"':null);?>>{L:system:symptoms}</a></li>
</ul>

<?php
  if( !isset($_GET['section']) || $_GET['section'] == 'brands' ){

    include($_sC->_get('path_modules') . 'sys_datasheets/brands.php');

 } elseif( isset($_GET['section']) && $_GET['section'] == 'symptoms' ) {  //Symptoms Part of the page

   include($_sC->_get('path_modules') . 'sys_datasheets/symptoms.php');
 }
?>