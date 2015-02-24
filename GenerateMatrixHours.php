<?php

function GenerateMatrixHours($TIME) 
{
	// Hourly step, hours
	$Step = 24/ $TIME['Nsteps'];
	
	// Hourly row vector
	$ColVector = range($Step, 24, $Step);
	
	// Creates the matrix hours with all the rows equal to the previous one, Ndays rows (Ndays x Nteps)	
	for ($i = 0; $i < $TIME['Ndays']; $i++) 
	{
		$Hours[$i] = $ColVector;	
	}

	return $Hours;
}

?>