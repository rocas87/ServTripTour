<?php
header("Content-Type: text/html;charset=utf-8");
set_time_limit (3600);

include("conexion.php");
include("pearson.php");
include("calculoDistancia.php");
require_once("JSON.php");  

//Variables resividas desde el usuario
$usuario = $_POST['usuario'];
$latitud = $_POST["latitud"];
$longitud = $_POST["longitud"];
$categoria = $_POST["categoria"];
$radioBusqueda = $_POST["radioBusqueda"];

//Def Variables
$json = new Services_JSON;

$i = 0;
$item_id = 0;
$usuario_id = 0;

$datos=array();

$hoy = getdate();
$fecha = "1900-".$hoy["mon"]."-".$hoy["mday"];
$dia = $hoy ["weekday"];
$hora = $hoy ["hours"].":".$hoy["minutes"].":00.000000";

$latitud = "-33.4547699";
$longitud = "-70.64989889999998";
$categoria = 10;
$radioBusqueda = 20;
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

//Filtro por id_categoria, Espacio temporal (Fecha, dia y hora) y radio de busqueda
$filtro_categoria = mysql_query("SELECT itm_id FROM itm_categoria WHERE cat_id = '$categoria' ") or die (mysql_error());

while ($r_filtro_categoria= mysql_fetch_array($filtro_categoria))
  {
    $id_categoria = $r_filtro_categoria['itm_id'];
    //Lunes
    if($dia == "Monday")
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE itm_id ='$id_categoria' and hr_lun_ini < '$hora' and 
        hr_lun_cie > '$hora'") or die (mysql_error());
    }
    //Martes
    elseif($dia == "Tuesday")
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE itm_id ='$id_categoria' and hr_mar_ini < '$hora' and 
        hr_mar_cie > '$hora'") or die (mysql_error());
    }
    //Miercoles
    elseif ($dia == "Wednesday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE itm_id ='$id_categoria' and hr_mier_ini < '$hora' and 
        hr_mier_cie > '$hora'") or die (mysql_error());
    }
    //Jueves
    elseif ($dia == "Thursday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE itm_id ='$id_categoria' and hr_juev_ini < '$hora' and 
        hr_juev_cie > '$hora'") or die (mysql_error());
    }
    //Viernes
    elseif ($dia == "Friday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE itm_id ='$id_categoria' and hr_vier_ini < '$hora' and 
        hr_vier_cie > '$hora'") or die (mysql_error());
    }
    //Sabado
    elseif ($dia == "Saturday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE itm_id ='$id_categoria' and hr_sab_ini < '$hora' and 
        hr_sab_cie > '$hora'") or die (mysql_error());
    }
    //Domingo
    elseif ($dia == "Sunday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE itm_id ='$id_categoria' and hr_dom_ini < '$hora' and 
        hr_dom_cie > '$hora'") or die (mysql_error());
    }

    while ($r_filtro_disponible = mysql_fetch_array($filtro_disponible))
    {
      $id_disponible = $r_filtro_disponible['itm_id'];
      $filtro_distancia_fecha = mysql_query("SELECT itm_id, itm_latitud, itm_longitud FROM itm_item 
        WHERE itm_id ='$id_disponible' and itm_fecha_inicio < '$fecha' and itm_fecha_termino > '$fecha'") or die (mysql_error());
      $i++;
      while ($r_filtro_distancia_fecha= mysql_fetch_array($filtro_distancia_fecha))
      {
        $itm_latitud= $r_filtro_distancia_fecha['itm_latitud'];
        $itm_longitud = $r_filtro_distancia_fecha['itm_longitud'];

        $distancia = distanciaGeodesica($latitud, $longitud, $itm_latitud, $itm_longitud);

        if ($distancia<=$radioBusqueda)
        {
        	//Arreglo items que pasan filtro
        	$id_itm[$item_id] = $r_filtro_distancia_fecha['itm_id'];
          	$item_id++;
        }       
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

//Imprimo matriz item_usuario
echo "<br> aux_idusuairo".$usuario_id."<br>";
echo "aux_item".$item_id."<br>";
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
	//$arr1 = arreglo1($filPear, $usuario_id, $item_usuario);
	for ($i=1; $i <= $usuario_id; $i++) 
	{ 
		$arr1[$i-1] = $item_usuario[$filPear][$i];
	}

	for ($colPear=1; $colPear <= $item_id; $colPear++) 
	{ 
		//$arr2 = arreglo2($columna, $usuario_id, $item_usuario);
		for ($i=1; $i <= $usuario_id; $i++) 
		{ 
			$arr2[$i-1] = $item_usuario[$colPear][$i];
		}

		$pearson[$filPear][$colPear] = abs(CorrelacionPearson($arr1, $arr2));

		echo "|".$pearson[$filPear][$colPear]."|";
	}

	echo "**<br>";
}



if (mysql_num_rows ($filtro_categoria) == 0) 
{
	$datos [0] ["resultado"] = "categoria";
}
elseif ($item_id==0) 
{
	$datos [0] ["resultado"] = "radioBusqueda";
}
echo $json->encode($datos);
?>