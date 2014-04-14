<?php
include("conexion.php");

$con = mysql_connect($host,$user,$pass) or die("Sin conexion");
mysql_select_db($db); 

$consulta_id = mysql_query("SELECT itm_id FROM itm_item") or die (mysql_error());
if (mysql_num_rows ($consulta_id) == 0)
{
  echo "No ahi resultados <br>";
}
 else
 {
  while($r_consulta_id = mysql_fetch_array($consulta_id))
  	{
  		$itm_id = $r_consulta_id['itm_id'];
  		$cat_id = rand(1,10);

  		$sqlitem = "insert into itm_categoria (itm_id,cat_id) 
		values ('$itm_id','$cat_id')";

		echo "ID: ".$itm_id."Cat: ".$cat_id;
		$resultitem=mysql_query($sqlitem,$con);
		echo "Res: ".$resultitem;
	
		if (rand(1,0) ==1) {
			$cat_id1 = rand(1,10);
			if($cat_id1 != $cat_id){
				$sqlitem1 = "insert into itm_categoria (itm_id,cat_id) 
				values ('$itm_id','$cat_id1')";
				echo "ID: ".$itm_id."Cat: ".$cat_id1;
				$resultitem=mysql_query($sqlitem1,$con);
				echo "Res: ".$resultitem;
			}
  			
		}
		echo "<br>";
	}
 }
?>