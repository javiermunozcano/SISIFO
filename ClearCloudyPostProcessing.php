<?php

function ClearCloudyPostProcessing($MONTHLYClear,$MONTHLYCloudy,$Gdm0) {
	
	//Month length, number of days
	$MonthLength=[31,28,31,30,31,30,31,31,30,31,30,31];
	$fin=count($MonthLength);
	//Monthly horizontal, according the daily monthly averages
	for ($i = 0; $i < $fin; $i++) 
	{
		$Gm0[$i]=$Gdm0[$i]*$MonthLength[$i];
	}
	
	//Calculation of the fractions of each type of sky
	for ($i = 0; $i < $fin; $i++) 
		{
		//Clear Sky Fraction
		$CSFm[$i]=($Gm0[$i]-$MONTHLYCloudy['G0m'][$i])/($MONTHLYClear['G0m'][$i]-$MONTHLYCloudy['G0m'][$i]);
		//Cloudy (Diffuse) Sky Fraction
		$DSFm[$i]=1-$CSFm[$i];
		}

	//Post-processing
	//Structure field names
	$keys = array_keys($MONTHLYClear);
	//Number of fields, Nfields
	$Nfields=count($MONTHLYClear);
	//Multiply each component by the relevant fraction
	foreach ($keys as $i)
	{
		for ($j = 0; $j < 12; $j++)
		{
			$MONTHLY[$i][$j]=$MONTHLYClear[$i][$j]*$CSFm[$j] + $MONTHLYCloudy[$i][$j]*$DSFm[$j];
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