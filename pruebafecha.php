
<?php

$hoy = getdate();
$fecha = "1900-".$hoy["mon"]."-".$hoy["mday"];
$dia = $hoy ["weekday"];
$hora = $hoy ["hours"].":".$hoy["minutes"].":00.000000";

echo "dia:".$dia."<br>";
echo "Hora: ".$hora."<br>";
echo "Fecha:".$fecha."<br>";

for ($i=0; $i < 5 ; $i++) 
{ 
	$recomendacion[$i] = $i;
	$ar_combinado[$i] = $i;
}

combinado($recomendacion);

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