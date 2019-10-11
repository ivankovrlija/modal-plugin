<?php
if (session_id() === "") {
	session_start();
}
$path = $_SERVER['DOCUMENT_ROOT']; 
include_once $path . '/wp-load.php';

$info = $_POST['info'];
$_SESSION['info'] = array();
$_SESSION['info'] = $info;
return $_SESSION['info'];
?>
