<?php
header("Content-Type: text/html;charset=utf-8");

include("conexion.php");
include("calculoDistancia.php");

require_once("JSON.php");  
$json = new Services_JSON;
//$data = array("nombre" => "Albert", "apellido" => "Camus");
//echo $json->encode($data);

//$latitud = $_Post["latitud"];
//$longitud = $_Post["longitud"];
//$categoria = $_Post["categoria"];
//$radioBusqueda = $_Post["radioBusqueda"];

//Coordenadas casa
$latitud = -33.4547699;
$longitud = -70.64989889999998;
$categoria = 1;
$radioBusqueda = 2;

$i = 0;
$aux1 = 0;
$aux2 = 0;

$datos=array();

$con = mysql_connect($host,$user,$pass) or die("Sin conexion");
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
  						$aux1++;
	    				$itm_latitud= $r_filtro_distancia['itm_latitud'];
    					$itm_longitud = $r_filtro_distancia['itm_longitud'];

    					$distancia = distanciaGeodesica($latitud, $longitud, $itm_latitud, $itm_longitud);

    					if($distancia>=$radioBusqueda)
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
  												$aux2++;
    											$itm_nombre = $r_filtro_final['itm_nombre'];
    											//lat, long, dist
    											$itm_direccion = $r_filtro_final['itm_direccion'];
                          $itm_direccion = utf8_decode($itm_direccion);
     											$itm_promedio = $r_filtro_final['itm_promedio'];

                           //$datos[] = $r_filtro_final;

    											//echo "Nombre: ".$itm_nombre." Latitud,Longitud: ".$itm_latitud.",".$itm_longitud." 
    											//Distancia: ".$distancia." Direccion: ".$itm_direccion." Promedio: ".$itm_promedio."<br>";
                          $i++;
                          $datos [$i] ["itm_nombre"] = $itm_nombre;
                          $datos [$i] ["itm_latitud"] = $itm_latitud;
                          $datos [$i] ["itm_longitud"] = $itm_longitud;
                          $datos [$i] ["distancia"] = $distancia;
                          $datos [$i] ["itm_direccion"] = $itm_direccion;
											} 
									}
    						}
					}  	
				}	
		}
}
//echo "Resultados: ".mysql_num_rows($filtro_categoria)."<br>";
//echo "Resultados: ".$aux1."<br>";
//echo "Resultados: ".$aux2."<br>";
echo $json->encode($datos);

/*$sql="select itm_id where from itm_item";
  $datos=array();
  $rs=mysql_query($sql,$con);
  while($row=mysql_fetch_object($rs)){
       $datos[] = $row;
  }
  echo json_encode($datos);
  */
?>