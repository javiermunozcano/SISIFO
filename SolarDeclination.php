<?php

function SolarDeclination($TIME) 
{
	//Initialization to zero
	$delta = array_fill(0 , $TIME['Ndays']-1, 0);
	
	for ($i = 0; $i < $TIME['Ndays']; $i++) {
		// Simulation day
		$d = $TIME['SimulationDays'][$i];
		// Spencer equations
		// Gamma
		$gamma = ($d-1)*2*pi()/365;
		// Eccentricity correction factor
		$Eo[$i]=1.00011+(0.034221*cos($gamma)) + (0.001280*sin($gamma)) + (0.000719*cos(2*$gamma)) + (0.000077*sin(2*$gamma));
		// Solar declination, degree
		$delta[$i] = (0.006918-(0.399912*cos($gamma))+(0.070257*sin($gamma))-(0.006758*cos(2*$gamma))+(0.000907*sin(2*$gamma))-(0.002697*cos(3*$gamma))+(0.00148*sin(3*$gamma)))*180/pi();
		//Conversion from degree to radian
		$delta[$i] = $delta[$i]*pi()/180;
	}
	
	return $delta;
}

?>