<?php
$archivo = "RecomendacionGrupo1.txt";
$abrir = fopen($archivo,'r+');
$contenido = fread($abrir,filesize($archivo));
fclose($abrir);
 
// Separar linea por linea
$contenido = explode("\n",$contenido);

echo count($contenido);
/*for ($i=0; $i < count($contenido) ; $i++) {
	$aux = explode("|", $contenido[$i]); 
 	if($aux[0] == "389")
 	{
 		$contenido[$i] = 'jajaja little monkey';
 	}
 } 
// Modificar linea deseada ( 2 ) 
//$contenido[2] = 'jajaja little monkey';
 
// Unir archivo
$contenido = implode("\n",$contenido);
 
// Guardar Archivo
$abrir = fopen($archivo,'w');
fwrite($abrir,$contenido);
fclose($abrir);*/
?>