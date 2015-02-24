<?php

function ExtraHorIrradiation($Eo, $ws, $delta, $SITE, $TIME) 
{
	//Latitude, rad
	$lat = $SITE['lat'];
	
	// Calculation
	for ($i = 0; $i < $TIME['Ndays']; $i++) 
	{
		$BOd0[$i]=24/pi() * 1367 * $Eo[$i] * ( (-1)*$ws[$i] * ( sin($delta[$i]) * sin($lat) ) - cos($delta[$i]) * cos($lat) * sin($ws[$i]));
	}

	return $BOd0;
}

?>