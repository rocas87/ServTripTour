<?php
header("Content-Type: text/html;charset=utf-8");

include("conexion.php");

$user = $_POST["usuario"];
$pass = $_POST["pass"];

//$user = "Benjamin.Gonzalez@gmail.c";
//$pass = "Benjamin";

require_once("JSON.php");  
$json = new Services_JSON;

$datos = array();

$con = mysql_connect($host,$userDb,$passDb) or die("Sin conexion");
mysql_select_db($db); 

$login = mysql_query("SELECT usr_nick, usr_nombre FROM usr_usuarios WHERE (usr_mail = '$user' OR usr_nick = '$user') AND usr_pass = '$pass' ") or die (mysql_error());
if (mysql_num_rows ($login) == 0)
	{
  		$datos [0] ["valido"] = "0";
	}
else
{
 	 while($log = mysql_fetch_array($login))
  		{
    		$datos [0] ["valido"] = "1";
    		$datos [0] ["usr_nombre"] = $log['usr_nombre'];
    		$datos [0] ["usr_nick"] = $log['usr_nombre'];
    	}
}
echo $json->encode($datos);   		
?>