<?php

function AirMass($SUNPOS,$SITE,$TIME) {

	//Model coefficients;
	$h0  = 8434.5;
	$cr1 = 0.061359;
	$cr2 = 0.1594;
	$cr3 = 1.123;
	$cr4 = 0.065656;
	$cr5 = 28.9344;
	$cr6 = 277.3971;
	$ma1 = 0.50572;
	$ma2 = 6.07995;
	$ma3 = -1.6364;
	
	$Altitude=$SITE['Altitude'];	//Altitude
	$gammas=$SUNPOS['gammas'];		//Sun elevation
	
	//Calculations
for ($d = 0; $d < $TIME['Ndays']; $d++) 
	{
	for ($h = 0; $h < $TIME['Nsteps']; $h++) 
		{
		//Altitude correction
		$altitude_correction=exp(-$Altitude/$h0);
		//Refraction correction
		$delta_gammas[$d][$h] = $cr1 * ($cr2 + $cr3* $gammas[$d][$h] + $cr4* pow($gammas[$d][$h],2) )/ ( 1 + $cr5* $gammas[$d][$h] + $cr6* pow($gammas[$d][$h],2) );
		$gammas_corrected[$d][$h]=$gammas[$d][$h] + $delta_gammas[$d][$h];
		
		//Air Mass calculation
		if ($gammas[$d][$h] < 0) 
			{
			$AMI[$d][$h]=0;
			}elseif ($gammas[$d][$h] > pi()/2)
				{
				$AMI[$d][$h]=1;	
				}else
					{
					$AMI[$d][$h] = $altitude_correction / ( sin($gammas_corrected[$d][$h]) + $ma1* pow($gammas_corrected[$d][$h]*180/pi() + $ma2,$ma3));
					}
		}//end FOR $h
	}//end FOR $d
	
return $AMI;	
}

?>