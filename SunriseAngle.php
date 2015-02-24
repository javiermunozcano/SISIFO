<?php

function SunriseAngle($delta, $SITE, $TIME) 
{
	//Initialization to zero
	$ws = array_fill(0 , $TIME['Ndays']-1, 0);
	
	// Latitute, rad
	$lat = $SITE['lat'];
	
	// Calculation
	for ($i = 0; $i < $TIME['Ndays']; $i++) 
	{
		// Sunrise angle
		if (( -tan($delta[$i]) * tan($lat) ) > 1) 
		{
			$ws[$i] = 0;
		}
		elseif ( ( -tan($delta[$i]) * tan($lat) ) < (-1) )
		{
			$ws[$i] = -pi();
		}
		else 
		{
			$ws[$i] = -acos(-tan($delta[$i])*tan($lat));
		}	
	}

	return $ws;
}

?>