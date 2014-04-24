<?php
header("Content-Type: text/html;charset=utf-8");

include("conexion.php");
include("calculoDistancia.php");

$latitud = $_POST["latitud"];
$longitud = $_POST["longitud"];
$categoria = $_POST["categoria"];
$radioBusqueda = $_POST["radioBusqueda"];

require_once("JSON.php");  
$json = new Services_JSON;
$i = 0;
$datos=array();

$con = mysql_connect($host,$userDb,$passDb) or die("Sin conexion");
mysql_select_db($db); 

$filtro_categoria = mysql_query("SELECT itm_id FROM itm_categoria WHERE cat_id = 1") or die (mysql_error());
if (mysql_num_rows ($filtro_categoria) == 0)
	{
  		echo "No ahi resultados por categoria <br>";
	}
else
{
 	 while($r_filtro_categoria= mysql_fetch_array($filtro_categoria))
  		{
    		$itm_id = $r_filtro_categoria['itm_id'];

    		$filtro_distancia = mysql_query("SELECT itm_latitud, itm_longitud FROM itm_item WHERE itm_id = '$itm_id'") or die (mysql_error());
			if (mysql_num_rows ($filtro_distancia) == 0)
				{
  					echo "No ahi resultados por distancia<br>";
				}
			 	else
 				{
	 				while($r_filtro_distancia= mysql_fetch_array($filtro_distancia))
  					{
	    				$itm_latitud= $r_filtro_distancia['itm_latitud'];
    					$itm_longitud = $r_filtro_distancia['itm_longitud'];

    					$distancia = distanciaGeodesica($latitud, $longitud, $itm_latitud, $itm_longitud);

    					if($distancia<=$radioBusqueda)
    						{
    							$filtro_final = mysql_query("SELECT itm_nombre, itm_direccion, itm_promedio FROM itm_item WHERE itm_id = '$itm_id'") 
                                  or die (mysql_error());
								if (mysql_num_rows ($filtro_final) == 0)
									{
  										echo "No ahi resultados por filtro_final<br>";
									}
			 					else
 									{
 										while($r_filtro_final= mysql_fetch_array($filtro_final))
  											{                             											         
                          $datos [$i] ["itm_nombre"] = $r_filtro_final['itm_nombre'];
                          //$datos [$i] ["itm_latitud"] = $itm_latitud;
                          //$datos [$i] ["itm_longitud"] = $itm_longitud;                          
                          $datos [$i] ["itm_direccion"] = utf8_decode($r_filtro_final['itm_direccion']);
                          $datos [$i] ["itm_promedio"] = $r_filtro_final["itm_promedio"];
                          $datos [$i] ["distancia"] = $distancia;
                          $i++;
											} 
									}
    						}
					}  	
				}	
		}
} 
echo $json->encode($datos);
?>