<?php

/* #############################################
####### PARSEADOR EN PHP BY: AEX12 #############
##############################################*/

function parsear($cadena, $buscar1, $buscar2){
$char = strlen($cadena);
$char1 = strlen($buscar1);
$number1 = strpos($cadena, $buscar1);
$number2 = strpos($cadena, $buscar2);
$cadena1 = substr($cadena, $number1 + $char1);
$number2 = $char - $number2;
$number2 = $number2;
$number2 = "-".$number2;
$cadena1 = substr($cadena1, 0, $number2);
return $cadena1;
}

?>
