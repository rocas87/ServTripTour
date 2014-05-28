<?php
$tiempo_inicio = microtime_float();
header("Content-Type: text/html;charset=utf-8");
set_time_limit (0);

include("conexion.php");
include("pearson.php");

$item_id = 0;
$usuario_id = 0;

//Conexion BdD
$con=mysql_connect($host,$userDb,$passDb) or die ("problemas con servidor");
mysql_select_db($db,$con) or die("problemas con bd");

$sql_item = mysql_query("SELECT itm_id FROM itm_item") or die (mysql_error());

      while ($r_sql_item= mysql_fetch_array($sql_item))
      {
           	//Arreglo items que pasan filtro
        	$id_itm[$item_id] = $r_sql_item['itm_id'];
          	$item_id++;
       }  

//id de todos los usuarios que persenecen al grupo
$consulta_usuarios = mysql_query("SELECT usr_id FROM usr_usuarios WHERE usr_grupo = 1 ") or die (mysql_error());
while($reg4 = mysql_fetch_array($consulta_usuarios))	
	{
		//Arreglo usuarios similares
		$id_usuarios[$usuario_id] = $reg4['usr_id'];
		$usuario_id++;
	}

//Genero la matriz de Calificaciones de Usuario v/s items
$aux_columna = 1;
$item_usuario[0][0]="i/u";
for($columna=0; $columna < $usuario_id; $columna++)
	{
		$item_usuario[0][$aux_columna] = $id_usuarios[$columna];
		$id_usuario = $id_usuarios[$columna];
		$aux_fila = 1;
		for($fila=0; $fila< $item_id; $fila++)
			{
				
				$item_usuario[$aux_fila][0] = $id_itm[$fila];
				$id_item = $id_itm[$fila];
			
				$q_calificaciones = mysql_query("SELECT itm_rating FROM itm_calificacion WHERE usr_id = '$id_usuario' and 
					itm_id = '$id_item'") or die (mysql_error());
				if (mysql_num_rows ($q_calificaciones) == 0) 
				{
					$item_usuario[$aux_fila][$aux_columna] = 0;
				}
				else
				{
					while($reg5 = mysql_fetch_array($q_calificaciones))	
					{
						$item_usuario[$aux_fila][$aux_columna] = $reg5['itm_rating'];
					}
				}
				
				$aux_fila++;
			}
		
		$aux_columna++;
	}

unset($id_usuarios, $id_itm, $reg5, $aux_columna, $aux_fila);
//Calcula la matriz de Pearson	

for ($filPear=584; $filPear <= $item_id; $filPear++) 
{
	//Arreglo1
	for ($i=1; $i <= $usuario_id; $i++) 
	{ 
		$ar[$i-1] = $item_usuario[$filPear][$i];
	}
	$fila = $item_usuario[$filPear][0];
	for ($colPear=1; $colPear <= $item_id; $colPear++) 
	{ 
		//$arr2 = arreglo2($columna, $usuario_id, $item_usuario);
		for ($i=1; $i <= $usuario_id; $i++) 
		{ 
			$arr[$i-1] = $item_usuario[$colPear][$i];
		}
		$pearson[$filPear][$colPear] = abs(CorrelacionPearson($ar, $arr));
		$aux = $aux."|".$pearson[$filPear][$colPear];
	}
	$recomendacion = mysql_query("INSERT INTO rec_grupo1 (itm_id, pearson) VALUES ('$fila','$aux')") or die (mysql_error());
	unset($aux);
}
$tiempo_fin = microtime_float();
echo "<br>Tiempo empleado: " . ($tiempo_fin - $tiempo_inicio);
function microtime_float()
{
list($useg, $seg) = explode(" ", microtime());
return ((float)$useg + (float)$seg);
}
?>