<?php
header("Content-Type: text/html;charset=utf-8");
include("conexion.php");
include("parseador.php");

//Plaza de armas
$latitud=-33.442909;
$longitud=-70.65386999999998; 

//Conexion BdD
$con = mysql_connect($host,$user,$pass) or die("Sin conexion");
mysql_select_db("triptour");

for ($i=1001 ; $i <= 1004 ; $i++ ) { 
	echo "-------------------------------------- <br>";
//Nombre del item
	$itm_nombre = "ItemNº".$i; 
	echo "Nombre item: ".$itm_nombre."<br>";
	$itm_nombre = utf8_decode($itm_nombre);
//Coordenadas y Direccion del item
	//Random radio 10 km aprox.
	$itm_latitud = rand(0,99)/1000;
	$itm_longitud = rand(0,99)/1000;
	if(rand(0,1)==1){
		$itm_latitud = $latitud+$itm_latitud;
	}else{
		$itm_latitud = $latitud-$itm_latitud;
	}
	if(rand(0,1)==1){
		$itm_longitud = $longitud+$itm_longitud;
	}else{
		$itm_longitud = $longitud-$itm_longitud;
	}
	//Consulta por la direccion de acuerdo a las coordenadas
	$consulta = file_get_contents("http://maps.googleapis.com/maps/api/geocode/xml?latlng=".$itm_latitud.",".$itm_longitud."&sensor=false");
	$opcion = explode(" ", $consulta);
	$itm_direccion = parsear($consulta,$opcion[6],"Chile");
	$itm_direccion = $itm_direccion."Chile.";
	//Imprime direccion y distancia 
	$dis = distanciaGeodesica($latitud,$longitud,$itm_latitud,$itm_longitud);
	echo "Direccion: ".$itm_direccion."// Distancia: ".$dis."<br>";
	$itm_direccion = utf8_decode($itm_direccion);
//Telefono
	$itm_telefono = "+562".rand(1000000,9999999);
	echo "Telefono: ".$itm_telefono."<br>";
//Coordenadas
	echo "Coordenadas: ".$itm_latitud.",".$itm_longitud."<br>";
//Peridos funcionamiento
	$dia = rand(1,30);
	$mes = rand(1,12);
	$itm_fecha_inicio = "1900-".$mes."-".$dia;

	$dia = rand($dia,30);
	$mes = rand($mes,12);
	$itm_fecha_termino = "1900-".$mes."-".$dia;

	echo "Fecha de inicio: ".$itm_fecha_inicio."<br>";
	echo "Fecha de termino: ".$itm_fecha_termino."<br>";
//Promedio del item
	$itm_promedio = rand(0,5);
	echo "Promedio calificacion: ".$itm_promedio."<br>";
//Horarios de atencion
	$hora = rand(0,23);
	$minuto = rand(0,60);
	$hr_ini = $hora.":".$minuto.":00";

	$hora = rand($hora,23);
	$minuto = rand($minuto,60);
	$hr_cie = $hora.":".$minuto.":00";
	echo "Hora inicio: ".$hr_ini.", Hora cierre: ".$hr_cie."<br>" ;

	echo "---------------------------------------";
//Insert datos 
	//Insertar items
	$sqlitem = "insert into itm_item (itm_nombre,itm_direccion,itm_telefono,itm_latitud,itm_longitud,itm_fecha_inicio,itm_fecha_termino,itm_promedio) 
		values ('$itm_nombre','$itm_direccion','$itm_telefono','$itm_latitud','$itm_longitud','$itm_fecha_inicio','$itm_fecha_termino','$itm_promedio')";
	//Insertar horarios items
	$sqlhorario = "insert into itm_horario (hr_lun_ini,hr_lun_cie,hr_mar_ini,hr_mar_cie,hr_mier_ini,hr_mier_cie,
										hr_juev_ini,hr_juev_cie,hr_vier_ini,hr_vier_cie,hr_sab_ini,hr_sab_cie,hr_dom_ini,hr_dom_cie) 
		values ('$hr_ini','$hr_cie','$hr_ini','$hr_cie','$hr_ini','$hr_cie','$hr_ini','$hr_cie','$hr_ini','$hr_cie','$hr_ini','$hr_cie','$hr_ini','$hr_cie')";

	$resultitem=mysql_query($sqlitem,$con);
	echo "<br> Item".$resultitem;

	$resulthorario=mysql_query($sqlhorario,$con);
	echo "<br> Horario".$resulthorario;
}

//Funcion que calcula la distancia entre 2 ptos.
function distanciaGeodesica($lat1, $long1, $lat2, $long2){ 

$degtorad = 0.01745329; 
$radtodeg = 57.29577951; 

$dlong = ($long1 - $long2); 
$dvalue = (sin($lat1 * $degtorad) * sin($lat2 * $degtorad)) 
+ (cos($lat1 * $degtorad) * cos($lat2 * $degtorad) 
* cos($dlong * $degtorad)); 

$dd = acos($dvalue) * $radtodeg; 

$miles = ($dd * 69.16); 
$km = ($dd * 111.302); 

return $km; 
} 
?>