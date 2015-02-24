<?php

function ClearPostProcessing($MONTHLYClear,$Gdm0) {
	
//Month length, number of days
	$MonthLength=[31,28,31,30,31,30,31,31,30,31,30,31];

//Calculation of the monthly Clear Sky Indices
	//Monthly values
	$fin=count($MONTHLYClear['G0m']);
	for ($i = 0; $i < $fin; $i++) 
		{
		$CSIm[$i]=($Gdm0[$i]*$MonthLength[$i])/($MONTHLYClear['G0m'][$i]);
		}	
	
//Post-processing
	//Structure field names
	$keys = array_keys($MONTHLYClear);
	//Number of fields, Nfields
	$Nfields=count($MONTHLYClear);
	//Multiplies the monthly values by the CSIm
	//for ($i = 0; $i < $Nfields; $i++) 
	foreach ($keys as $i) 
	{
		for ($j = 0; $j < 12; $j++) 
		{
			$MONTHLY[$i][$j]=$MONTHLYClear[$i][$j]*$CSIm[$j];
		}
	}
	
//Recalculation of Monthly PRs (ideal)
$fin1=count($MONTHLY['EDCm']);
	for ($i = 0; $i < $fin1; $i++)
		{
		$MONTHLY['PRDCm'][$i]=$MONTHLY['EDCm'][$i]/($MONTHLY['Gm'][$i]/1000);
		$MONTHLY['PRACm'][$i]=$MONTHLY['EACm'][$i]/($MONTHLY['Gm'][$i]/1000);
		$MONTHLY['PRACMTm'][$i]=$MONTHLY['EACMTm'][$i]/($MONTHLY['Gm'][$i]/1000);
		}
	
	
return $MONTHLY;
}

?>
