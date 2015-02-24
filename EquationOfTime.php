<?php

function EquationOfTime($TIME) 
{
	//Initialization to zero
	$ET = array_fill(0 , $TIME['Ndays']-1, 0);
	
	for ($i = 0; $i < $TIME['Ndays']; $i++) {
		// Simulation day
		$d = $TIME['SimulationDays'][$i];
		// Gamma
		$gamma = ($d-1)*2*pi()/365;
		// Equation of time
	    $ET[$i] = 0.000075 + 0.001868*cos($gamma) - 0.032077*sin($gamma) - 0.014615*cos(2*$gamma) - 0.04089*sin(2*$gamma);
	}
	
	return $ET;
}

?>