<?php
include("conexion.php");

$con=mysql_connect($host,$user,$pass) or die ("problemas con servidor");
mysql_select_db($db,$con) or die("problemas con bd");

//Consulto los items que coinciden con la categoria
$item = mysql_query("SELECT itm_direccion FROM itm_item WHERE itm_id = 1") or die (mysql_error());

if (mysql_num_rows ($item) == 0)
{
  echo "No ahi resultados <br>";
}
 else
 {
  while($r_item = mysql_fetch_array($item))
  	{
    	$itm_direccion = $r_item['itm_direccion'];
    	echo "Direccion: ".$itm_direccion;
	}
    
  }
?>