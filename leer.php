<?php
$archivo = "recomendacion/RecomendacionGrupo1.txt";
$abrir = fopen($archivo,'r+');
$contenido = fread($abrir,filesize($archivo));
fclose($abrir);
 
// Separar linea por linea
$contenido = explode("\n",$contenido);
echo count($contenido);
?>