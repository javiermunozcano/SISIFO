<?php

function Eccentricity($TIME) {
	
	// Initialization to zero
	$Eo = array_fill(0 , $TIME['Ndays']-1, 0);
	// Calculation
	for ($i = 0; $i < $TIME['Ndays']; $i++) {
			// Day
			$d = $TIME['SimulationDays'][$i];
			// Spencer equations
			// Gamma
			$gamma = ($d-1)*2*pi()/365;
			// Eccentricity correction factor
			$Eo[$i]=1.00011+(0.034221*cos($gamma)) + (0.001280*sin($gamma)) + (0.000719*cos(2*$gamma)) + (0.000077*sin(2*$gamma));
	} 
		
	return $Eo;
}


?>