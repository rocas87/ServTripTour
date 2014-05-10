<?php
header("Content-Type: text/html;charset=utf-8");
set_time_limit (3600);

include("conexion.php");
include("pearson.php");
include("calculoDistancia.php");
require_once("JSON.php");  
/*
//Variables resividas desde el usuario
$usuario = $_POST['usuario'];
$latitud = $_POST["latitud"];
$longitud = $_POST["longitud"];
$categoria = $_POST["categoria"];
$distMaxima = $_POST["distMaxima"];
*/
//Def Variables
$json = new Services_JSON;

$item_id = 0;
$usuario_id = 0;
$valor = 0;
$indice = 0;
$d = 0;

$datos=array();

$hoy = getdate();
$fecha = "1900-".$hoy["mon"]."-".$hoy["mday"];
$dia = $hoy ["weekday"];
$hora = $hoy ["hours"].":".$hoy["minutes"].":00.000000";

$latitud = "-33.4547699";
$longitud = "-70.64989889999998";
$distMaxima = 10;
$usuario = "benjamin.g";

//Conexion BdD
$con=mysql_connect($host,$userDb,$passDb) or die ("problemas con servidor");
mysql_select_db($db,$con) or die("problemas con bd");

//id_grupo del usuario
$inf_usuario = mysql_query("SELECT usr_grupo FROM usr_usuarios WHERE usr_mail = '$usuario' or usr_nick = '$usuario'") or die (mysql_error());
while($reg_inf=mysql_fetch_array($inf_usuario))
{
	$usr_grupo = $reg_inf['usr_grupo'];
	echo "grupo".$usr_grupo;
}

unset($inf_usuario, $reg_inf);

    //Lunes
    if($dia == "Monday")
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE hr_lun_ini < '$hora' and hr_lun_cie > '$hora'") 
      or die (mysql_error());
    }
    //Martes
    elseif($dia == "Tuesday")
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE hr_mar_ini < '$hora' and hr_mar_cie > '$hora'") 
      or die (mysql_error());
    }
    //Miercoles
    elseif ($dia == "Wednesday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE hr_mier_ini < '$hora' and hr_mier_cie > '$hora'") 
      or die (mysql_error());
    }
    //Jueves
    elseif ($dia == "Thursday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE hr_juev_ini < '$hora' and hr_juev_cie > '$hora'") 
      or die (mysql_error());
    }
    //Viernes
    elseif ($dia == "Friday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE hr_vier_ini < '$hora' and hr_vier_cie > '$hora'") 
      or die (mysql_error());
    }
    //Sabado
    elseif ($dia == "Saturday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE hr_sab_ini < '$hora' and hr_sab_cie > '$hora'") 
      or die (mysql_error());
    }
    //Domingo
    elseif ($dia == "Sunday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE hr_dom_ini < '$hora' and hr_dom_cie > '$hora'") 
      or die (mysql_error());
    }

    while ($r_filtro_disponible = mysql_fetch_array($filtro_disponible))
    {
      $id_disponible = $r_filtro_disponible['itm_id'];
      $filtro_distancia_fecha = mysql_query("SELECT itm_id, itm_latitud, itm_longitud FROM itm_item 
        WHERE itm_id ='$id_disponible' and itm_fecha_inicio < '$fecha' and itm_fecha_termino > '$fecha'") or die (mysql_error());

      while ($r_filtro_distancia_fecha= mysql_fetch_array($filtro_distancia_fecha))
      {
        $itm_latitud= $r_filtro_distancia_fecha['itm_latitud'];
        $itm_longitud = $r_filtro_distancia_fecha['itm_longitud'];

        $distancia = distanciaGeodesica($latitud, $longitud, $itm_latitud, $itm_longitud);

        if ($distancia<=$distMaxima)
        {
        	//Arreglo items que pasan filtro
        	$id_itm[$item_id] = $r_filtro_distancia_fecha['itm_id'];
          	$item_id++;
        }       
      }
    }
//id de todos los usuarios que persenecen al grupo
$consulta_usuarios = mysql_query("SELECT usr_id FROM usr_usuarios WHERE usr_grupo = '$usr_grupo'") or die (mysql_error());
while($reg4 = mysql_fetch_array($consulta_usuarios))	
	{
		//Arreglo usuarios similares
		$id_usuarios[$usuario_id] = $reg4['usr_id'];
		$usuario_id++;
	}

unset($r_filtro_disponible, $r_filtro_distancia_fecha, $consulta_usuarios, $reg4);

echo "<br> Usuarios: ".$usuario_id."<br>";
echo "N° Item: ".$item_id."<br>";

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

//Imprimo matriz item_usuario

for($fila=0; $fila<= $item_id; $fila++)
	{
		for($columna=0; $columna <= $usuario_id; $columna++)
			{
			echo $item_usuario[$fila][$columna]."  |  ";
			}
		echo "/<br>";
	}

//Calcula la matriz de Pearson	
echo"---------------- <br>";

for ($filPear=1; $filPear <= $item_id; $filPear++) 
{
	//Arreglo1
	for ($i=1; $i <= $usuario_id; $i++) 
	{ 
		$ar[$i-1] = $item_usuario[$filPear][$i];
	}
	for ($colPear=1; $colPear <= $item_id; $colPear++) 
	{ 
		//$arr2 = arreglo2($columna, $usuario_id, $item_usuario);
		for ($i=1; $i <= $usuario_id; $i++) 
		{ 
			$arr[$i-1] = $item_usuario[$colPear][$i];
		}
		$pearson[$filPear][$colPear] = abs(CorrelacionPearson($ar, $arr));

		echo "|".$pearson[$filPear][$colPear]."|";
	}
	echo "**<br>";
}

//Selecciono los mayores
for($fila=1; $fila <= $item_id; $fila++)
{
	for($columna=1; $columna <= $item_id; $columna++)
	{
		if ($valor < $pearson[$fila][$columna] && $pearson[$fila][$columna] != 1) 
		{
			$valor = $pearson[$fila][$columna];
			$mayor[$fila] = $valor;
		}
	}
	$valor = 0;
}
//Ordeno los mas importantes
arsort($mayor);
foreach
 ($mayor as $key => $val) 
{
    $recomendacion[$indice] = $item_usuario[$key][0];
    $indice++;
}

if(5 < $indice)
{
    for ($i=0; $i < 5 ; $i++) 
    {
        $combina[$i] = $recomendacion[$i];
    } 

}
else
{
    for ($i=0; $i < count($recomendacion) ; $i++) 
    {
        $combina[$i] = $recomendacion[$i];
    }
}

echo count($combina)."<br>";
combinado($combina);
/*//Imprimo los 5 mayores
if(5 <= $indice)
{
	for ($i=0; $i < 4 ; $i++) 
	{
		$combinado = $recomendacion[$i].",".$recomendacion[$i++];
		for ($i=0; $i < ; $i++) { 
			# code...
		}
	} 

}
else
{
	for ($i=0; $i < $indice ; $i++) 
	{ 
		$sql_recomendacion = mysql_query("SELECT itm_nombre, itm_direccion, itm_promedio, itm_latitud, itm_longitud FROM itm_item 
        WHERE itm_id ='$recomendacion[$i]'") or die (mysql_error());

		while ($r_recomendacion= mysql_fetch_array($sql_recomendacion))
		{
			$datos [$d] ["resultado"] = "1";                                                           
          	$datos [$d] ["itm_nombre"] = $r_recomendacion['itm_nombre'];                         
          	$datos [$d] ["itm_direccion"] = utf8_decode($r_recomendacion['itm_direccion']);
          	$datos [$d] ["itm_promedio"] = $r_recomendacion["itm_promedio"];
          	$datos [$d] ["distancia"] = distanciaGeodesica($latitud, $longitud, $r_recomendacion['itm_latitud'], $r_recomendacion['itm_longitud']);
          	$datos [$d] ["itm_latitud"] = $r_recomendacion['itm_latitud'];
          	$datos [$d] ["itm_longitud"] = $r_recomendacion['itm_longitud'];
	        $d++;
		}
	}
}
*/
if ($item_id==0) 
{
	$datos [0] ["resultado"] = "Distancia Maxima";
}
echo $json->encode($datos);

function combinado($elementos) {
        for ($i = 0; $i < count($elementos); $i++) {
            for ($j = 1; $j < count($elementos); $j++) {
                if ($elementos[$i] != $elementos[$j]) {
                    echo $elementos[$i].",".$elementos[$j]."<br>";
                }
                for ($k = 2; $k < count($elementos); $k++) {
                    if ($elementos[$i] != $elementos[$j] && $elementos[$i] != $elementos[$k] && $elementos[$j] != $elementos[$k]) {
                        echo $elementos[$i].",".$elementos[$j].",".$elementos[$k]."<br>";
                    }
                    for ($l = 3; $l < count($elementos); $l++) {
                        if ($elementos[$i] != $elementos[$j] && $elementos[$i] != $elementos[$k] && $elementos[$i] != $elementos[$l]
                             && $elementos[$j] != $elementos[$k] && $elementos[$j] != $elementos[$l] && $elementos[$k] != $elementos[$l]) {
                           echo $elementos[$i].",".$elementos[$j].",".$elementos[$k].",".$elementos[$l]."<br>";
                        }
                        for ($m=4; $m < count($elementos); $m++) { 
                            if (
                                $elementos[$i] != $elementos[$j] && $elementos[$i] != $elementos[$k] && $elementos[$i] != $elementos[$l] && $elementos[$i] != $elementos[$m]
                             && $elementos[$j] != $elementos[$k] && $elementos[$j] != $elementos[$l] && $elementos[$j] != $elementos[$m]
                             && $elementos[$k] != $elementos[$l] && $elementos[$k] != $elementos[$m]
                             && $elementos[$l] != $elementos[$m]) {
                                echo $elementos[$i].",".$elementos[$j].",".$elementos[$k].",".$elementos[$l].",".$elementos[$m]."<br>";
                            }
                        }
                    }
                }
            }
        }
    }
?>