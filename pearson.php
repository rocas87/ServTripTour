<?php



function DesviacionMedia($arr, $item)
{
    return $arr[$item] - Promedio($arr);
}   


function DesviacionEstandar($arr, $item)
{
    return pow($arr[$item] - Promedio($arr),2);
}   


function Promedio($arr)
{
    $sum = Sumatorio($arr);
    $num = count($arr);
   
    if($num>0):
	    return $sum/$num;
	else:
		return NULL;
	endif;
}

function Sumatorio($arr)
{
	if(in_array('N/D',$arr)):
		for($i=0;$i<count($arr);$i++):
			if($arr[$i]=='N/D'):
				$arr[$i]=0;
			endif;
		endfor;
	endif;
		
	
   return array_sum($arr);
}

function CorrelacionPearson($arr1, $arr2)
{       
	$k = SumatorioProductoDesviacionMedia($arr1, $arr2);
    $ssmd1 = SumatorioDesviacionMediaAlCuadrado($arr1);
    $ssmd2 = SumatorioDesviacionMediaAlCuadrado($arr2);

    $producto = $ssmd1 * $ssmd2;
   
    $res = sqrt($producto);
   
   	if($res!=0):
	    return $k/$res;
	else:
		return 0;
	endif;
}


function SumatorioProductoDesviacionMedia($arr1, $arr2)
{
    $sum = 0;
    $num = count($arr1);
   
    for($i=0; $i<$num; $i++):
        $sum = $sum + ProductoDesviacionMedia($arr1, $arr2, $i);
    endfor;
   
    return $sum;
}

function ProductoDesviacionMedia($arr1, $arr2, $item)
{
    return (DesviacionMedia($arr1, $item) * DesviacionMedia($arr2, $item));
}

function SumatorioDesviacionMediaAlCuadrado($arr)
{
    $sum = 0;
    $num = count($arr);
   
    for($i=0; $i<$num; $i++):
        $sum = $sum + DesviacionMediaAlCuadrado($arr, $i);
    endfor;
   
    return $sum;
}

function DesviacionMediaAlCuadrado($arr, $item)
{
    return DesviacionMedia($arr, $item) * DesviacionMedia($arr, $item);
}

function SumatorioDesviacionMedia($arr)
{
    $sum = 0;
    $num = count($arr);
   
    for($i=0; $i<$num; $i++):
        $sum = $sum + DesviacionMedia($arr, $i);
    endfor;
   
    return $sum;
}

function SumatorioDesviacionEstandar($arr)
{
    $sum = 0;
   
    $num = count($arr);
   
    for($i=0; $i<$num; $i++):
        $sum = $sum + DesviacionEstandar($arr, $i);
    endfor;
   
    return sqrt($sum/$num);
}
?>