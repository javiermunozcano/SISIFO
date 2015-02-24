<?php

function CellTemperature($Ta,$Gref,$PVMOD,$TIME) {

	//Calculations
	for ($d = 0; $d < $TIME['Ndays']; $d++) 
		{
		for ($h = 0; $h < $TIME['Nsteps']; $h++) 
			{
			$Tc[$d][$h]=$Ta[$d][$h]+0.9*$Gref[$d][$h]*($PVMOD['NOCT']-20)/800;			
			}
		}
return $Tc;
}

?>
