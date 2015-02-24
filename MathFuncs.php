<?php
//******* VALUE SIGN ***********************************
function valueSign($num)
{
	return $sign = $num < 0 ? -1 : ( $num > 0 ? 1 : 0 );
}


//******* PRODUCT OF ARRAYS ****************************
function ProductOfArrays($Array1, $Array2, $Size)
{
	for ($i = 0; $i < $Size; $i++)
		$Product[$i]=$Array1[$i]*$Array2[$i];
	return $Product;
}


//******* ROUND TOWARDS ZERO ****************************

function RoundToZero($float) 
{
	if ($float >=0)
	{
		$int = floor($float);
	}
	else
	{
		$int = floor($float) + 1;
	}
	
	return  $int;
}

?>




