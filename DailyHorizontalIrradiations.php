<?php

function DailyHorizontalIrradiations($Gd0, $KDd, $TIME) 
{
	// Estimation of the Direct and Diffuse Components of Horizontal Radiation
	for ($d = 0; $d < $TIME['Ndays']; $d++) 
	{
		// Horizontal diffuse irradiation
		$Dd0[$d] = $Gd0[$d] * $KDd[$d];
		
		// Horizontal beam irradiation
		$Bd0[$d] = $Gd0[$d] * (1-$KDd[$d]);
	}
	
	// Output
	return $DHI = array(
					'Gd0' => $Gd0,
					'Bd0' => $Bd0,
					'Dd0' => $Dd0);
}

?>