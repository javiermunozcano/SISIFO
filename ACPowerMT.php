<?php

function ACPowerMT($POWER,$TRANSFORMER,$TIME) {

	//Parameters
	$pervacrg=$TRANSFORMER['pervacrg'];
	$percurg=$TRANSFORMER['percurg'];
	$DRt=$TRANSFORMER['DRt'];
	
	//Calculations
	for ($d = 0; $d < $TIME['Ndays']; $d++) 
		{
		for ($h = 0; $h < $TIME['Nsteps']; $h++) 
			{
			//Transformer losses
			$pertrafo[$d][$h]=$pervacrg+(pow($POWER['PAC'][$d][$h]/$DRt,2))*$percurg;
			//AC power injected by the system in MV, normalised to the nominal
			//PV power, after discounting transformer losses:
			$PACMT[$d][$h]=$POWER['PAC'][$d][$h]-$pertrafo[$d][$h];
		
			}//end FOR $h Nsteps
		}//end FOR $d Ndays
	
//Add AC power in MV to POWER	
$POWER['PACMT']=$PACMT;

return $POWER;
}

?>