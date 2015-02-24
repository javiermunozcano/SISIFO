<?php

// Include function files
include_once 'MonthlyAverageToAll.php';
include_once 'RayleighConstant.php';


function CloudyHorizontalIrradiances($Eo, $ws, $w, $SUNPOS, $AMI, $METEO, $TIME) 
{
	//Model coefficients
	$trd0 = -1.5843e-2;
	$trd1 = 3.0543e-2;
	$trd2 = 3.797e-4;
	$a00 = 2.6463e-1;
	$a01 = -6.1581e-2;
	$a02 = 3.1408e-3;
	$a10 = 2.0402;
	$a11 = 1.8945e-2;
	$a12 = -1.1161e-2;
	$a20 = -1.3025;
	$a21 = 3.9231e-2;
	$a22 = 8.5079e-3;
	
	// Solar variables
	$gammas = $SUNPOS['gammas'];
	$costetazs = $SUNPOS['costetazs'];
	
	// Daily Linke Turbidity
	$Tlkd = MonthlyAverageToAll($METEO['Tlk']);
	
	// Calculations
	foreach ($TIME['SimulationDays'] as $dm) 
	{
		$d = $dm-1;
		// Diffuse transmission function at zenith, Trd(d)
		$Trd[$d] = $trd0 + $trd1 * $Tlkd[$d] + $trd2 * pow($Tlkd[$d], 2);
		// A0 coefficient
		$A0[$d] = $a00 + $a01 * $Tlkd[$d] + $a02 * pow($Tlkd[$d], 2);
		
		if ($A0[$d] * $Trd[$d] < 2e-3) 
		{
			$A0[$d] = 2e-3 / $Trd[$d];
		}
	
		// A1 coefficient
		$A1[$d] = $a10 + $a11 * $Tlkd[$d] + $a12 * pow($Tlkd[$d], 2);
		// A2 coefficient
		$A2[$d] = $a20 + $a21 * $Tlkd[$d] + $a22 * pow($Tlkd[$d], 2);
		
		for ($h = 0; $h < $TIME['Nsteps']; $h++) 
		{
			if ( abs($w[$d][$h]) >= abs($ws[$d]) ) 
			{
				$G0[$d][$h] = 0;
				$B0[$d][$h] = 0;
				$D0[$d][$h] = 0;
			}
			else 
			{
				// Rayleigh constant, deltaR
				$deltaR[$d][$h] = RayleighConstant($AMI[$d][$h]);
				// Beam horizontal clear irradiance
				//////////////////////////////////////////////////
				$B0[$d][$h] = 0; //(*)
				//////////////////////////////////////////////////
				// Diffuse angular function, Fd(h,d)
				$Fd[$d][$h] = $A0[$d] + $A1[$d] * sin($gammas[$d][$h]) + $A2[$d] * pow(sin($gammas[$d][$h]),2);
				// Diffuse horizontal clear irradiance
				$D0[$d][$h] = 1367 * $Eo[$d] * $Trd[$d] * $Fd[$d][$h];
				// Global horizontal clear irradiance
				$G0[$d][$h] = $B0[$d][$h] + $D0[$d][$h];
			}
		}
	}
	
	return $HI = array(
					'G0' => $G0,
					'B0' => $B0,
					'D0' => $D0);
}

?>