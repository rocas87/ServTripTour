
<?php
header("Content-Type: text/html;charset=utf-8");
include('parseador.php');

for ($i=0; $i < 5 ; $i++) 
{ 
	$recomendacion[$i] = $i;
	$ar_combinado[$i] = $i;
}

$combinados = combinado($recomendacion);
for ($i=0; $i < count($combinados); $i++) { 
    echo $combinados[$i]."<br>";
}
/*
$map_url = "https://maps.googleapis.com/maps/api/directions/xml?&origin=madrid&destination=Barcelona&mode=driving&language=es&region=es&sensor=false";
$response_xml_data = file_get_contents($map_url);
$xml = simplexml_load_string($response_xml_data);
$duracion = $xml->route->leg->duration->text;
$distancia = $xml->route->leg->distance->text;

$tmp_hr = explode("h", $duracion);
$tmp_min = parsear($duracion,"h "," min");
$dist = explode(" ", $distancia);

echo $tmp_hr[0]."<br>";
echo $tmp_min."<br>";
echo $dist[0];
*/
function combinado($elementos) 
{
  $combinaciones[0] = 0;
  $aux=0;
  for ($i = 0; $i < count($elementos); $i++) {
    for ($j = 1; $j < count($elementos); $j++) 
    {
      if ($elementos[$i] != $elementos[$j] && $elementos[$i] < $elementos[$j]) 
      {
        $combinaciones[$aux] = $elementos[$i].",".$elementos[$j];
        $aux++;
      }
      for ($k = 2; $k < count($elementos); $k++) 
      {
        if ($elementos[$i] != $elementos[$j] && $elementos[$i] != $elementos[$k] && $elementos[$j] != $elementos[$k]
            && $elementos[$i] < $elementos[$j] && $elementos[$i] < $elementos[$k] && $elementos[$j] < $elementos[$k]) 
        {
          $combinaciones[$aux] = $elementos[$i].",".$elementos[$j].",".$elementos[$k];
          $aux++;
        }

        for ($l = 3; $l < count($elementos); $l++) 
        {
          if ($elementos[$i] != $elementos[$j] && $elementos[$i] != $elementos[$k] && $elementos[$i] != $elementos[$l]
              && $elementos[$j] != $elementos[$k] && $elementos[$j] != $elementos[$l] && $elementos[$k] != $elementos[$l]
              && $elementos[$i] < $elementos[$j] && $elementos[$i] < $elementos[$k] && $elementos[$i] < $elementos[$l]
              && $elementos[$j] < $elementos[$k] && $elementos[$j] < $elementos[$l] && $elementos[$k] < $elementos[$l])
          {
            $combinaciones[$aux] = $elementos[$i].",".$elementos[$j].",".$elementos[$k].",".$elementos[$l];
            $aux++;
          }

          for ($m=4; $m < count($elementos); $m++) 
          {
            if($elementos[$i] != $elementos[$j] && $elementos[$i] != $elementos[$k] && $elementos[$i] != $elementos[$l] && $elementos[$i] != $elementos[$m]
               && $elementos[$j] != $elementos[$k] && $elementos[$j] != $elementos[$l] && $elementos[$j] != $elementos[$m]
               && $elementos[$k] != $elementos[$l] && $elementos[$k] != $elementos[$m]
               && $elementos[$l] != $elementos[$m]
               && $elementos[$i] < $elementos[$j] && $elementos[$i] < $elementos[$k] && $elementos[$i] < $elementos[$l] && $elementos[$i] < $elementos[$m]
               && $elementos[$j] < $elementos[$k] && $elementos[$j] < $elementos[$l] && $elementos[$j] < $elementos[$m]
               && $elementos[$k] < $elementos[$l] && $elementos[$k] < $elementos[$m]
               && $elementos[$l] < $elementos[$m])
            {
                $combinaciones[$aux] = $elementos[$i].",".$elementos[$j].",".$elementos[$k].",".$elementos[$l].",".$elementos[$m];
                $aux++;
            }
          }
        }
      }
    }
  }
  return $combinaciones;
}
?>