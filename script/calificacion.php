<?php

include("conexion.php");

set_time_limit (3600);

$con = mysql_connect($host,$user,$pass) or die("Sin conexion");
mysql_select_db($db); 

//1068
for ($i=522; $i <= 1068 ; $i++) 
{ 
	$randUsuario = rand(50,200);
	for ($x=0; $x < $randUsuario; $x++)
	{
		$randItem = rand(1,1000);
		$randCa = rand(0,5);
		if ($randCa > 0) 
		{
			$calif = mysql_query("SELECT * FROM itm_calificacion WHERE usr_id = '$i' and itm_id = '$randItem' ") or die (mysql_error());
			if (mysql_num_rows ($calif) == 0) 
			{
				$randRating = rand(1,5);
				$sqlitem = "insert into itm_calificacion (usr_id, itm_id, itm_rating) values ('$i', '$randItem', '$randRating')";  				
				echo "usuario: ".$i."item: ".$randItem."calificacion: ".$randRating."<br>";
				$resultitem=mysql_query($sqlitem,$con);
				echo "<br> Item".$resultitem;	
			}	
		}
	}
}

?>