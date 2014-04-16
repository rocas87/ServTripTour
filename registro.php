<?php
header("Content-Type: text/html;charset=utf-8");
include("conexion.php");
require_once("JSON.php");  

$usr_mail = $_POST["usr_mail"];
$usr_nick = $_POST["usr_nick"];
$usr_nombre = $_POST["usr_nombre"];
$usr_apellido = $_POST["usr_apellido"];
$usr_sexo = $_POST["usr_sexo"];
$usr_fecha_nacimiento = $_POST["usr_fecha_nacimiento"];
$usr_pass = $_POST["usr_pass"];

$valido;

$datos = array();
$json = new Services_JSON;

//Conexion BdD
$con = mysql_connect("127.0.0.1","root","6464610") or die("Sin conexion");
mysql_select_db("triptour");

$mail = mysql_query("SELECT usr_id FROM usr_usuarios WHERE usr_mail = '$usr_mail'") or die (mysql_error());
if (mysql_num_rows ($mail) > 0)
{
	$datos[0] ["valido"] = "mail";
}
$nick = mysql_query("SELECT usr_id FROM usr_usuarios WHERE usr_nick = '$usr_nick'") or die (mysql_error());
if (mysql_num_rows ($nick) > 0)
{
	$datos[0] ["valido"] = "nick";
}
if (mysql_num_rows ($nick) == 0 && mysql_num_rows ($mail) == 0)
{
	
	$usr_edad = CalculaEdad($usr_fecha_nacimiento);
	$usr_grupo = grupo($usr_edad,$usr_sexo);

	$sqlusuario = "INSERT INTO usr_usuarios (usr_mail, usr_nick, usr_nombre, usr_apellido, usr_sexo, usr_fecha_nacimiento, usr_edad, usr_pass, usr_grupo) 
		VALUES('$usr_mail','$usr_nick','$usr_nombre','$usr_apellido','$usr_sexo','$usr_fecha_nacimiento','$usr_edad','$usr_pass',$usr_grupo)";

	$resulResigtro=mysql_query($sqlusuario,$con);
	echo "<br> Item".$resulResigtro;
}

echo $json->encode($datos); 

//Identifica grupo del usuario
function grupo($usr_edad, $usr_sexo)
{
	if(18<=$usr_edad && $usr_edad<=24)
	{
		$usr_grupo = 1;
	}else if (25 <= $usr_edad && $usr_edad<= 34) {
		$usr_grupo = 2;
	}else if (35 <= $usr_edad && $usr_edad<= 44) {
		$usr_grupo = 3;
	}else if (45 <= $usr_edad && $usr_edad<= 54) {
		$usr_grupo = 4;
	}else if (55 <= $usr_edad && $usr_edad<= 64) {
		$usr_grupo = 5;
	}else if (65 <= $usr_edad) {
		$usr_grupo = 6;
	}
	if ($usr_sexo == "M") {
		$usr_grupo = $usr_grupo+6;
	}
	return $usr_grupo;

}
//Calula edad
function CalculaEdad( $fecha ) {
    list($Y,$m,$d) = explode("/",$fecha);
    return( date("md") < $m.$d ? date("Y")-$Y-1 : date("Y")-$Y );
}
?>