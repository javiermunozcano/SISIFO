<?php

function SunPosition($delta, $w, $SITE, $TIME) 
{
	// Latitute, rad.
	$lat = $SITE['lat'];
	
	// Calculations
	for ($d = 0; $d < $TIME['Ndays']; $d++) 
	{
		for ($h = 0; $h < $TIME['Nsteps']; $h++) 
		{
			// Cosines of the solar zenith angle
			$costetazs[$d][$h] = sin($delta[$d]) * sin($lat) + cos($delta[$d]) * cos($lat) * cos($w[$d][$h]);
			
			// Limitation of costetazs to positive values
			if ($costetazs[$d][$h] < 0) 
			{
				$costetazs[$d][$h] = 0;
			}
			
			// Solar altitude angle
			$gammas[$d][$h] = asin($costetazs[$d][$h]);
			
			// Solar zenith angle
			$tetazs[$d][$h] = pi()/2 - $gammas[$d][$h];
			
			// Cosines of solar azimuth angle
			$cosfis[$d][$h] = ( $costetazs[$d][$h] * sin($lat) - sin($delta[$d]) ) * valueSign($lat) / ( cos($gammas[$d][$h]) * cos($lat) );
			
			// Limitation of cosfis(h,d) to real values
			if ($cosfis[$d][$h] > 1) 
			{
				$cosfis[$d][$h] = 1;
			}
			elseif ($cosfis[$d][$h] < -1)
			{
				$cosfis[$d][$h] = -1;
			}
			
			// Solar azimuth angle (negative towards the east, in the morning, and positive
			// towards the west, in the evening.
			if ($w[$d][$h] < 0) 
			{
				$fis[$d][$h] = -acos($cosfis[$d][$h]);
			}
			else
			{
				$fis[$d][$h] = acos($cosfis[$d][$h]);
			}
						
		}	
	}
	
	
	
	return $SUNPOS = array(
						'costetazs' => $costetazs, 
						'cosfis'	=> $cosfis,
						'gammas'	=> $gammas, 
						'tetazs'	=> $tetazs, 
						'fis'		=> $fis); 

}

?>