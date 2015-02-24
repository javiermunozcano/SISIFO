<?php

// Include function files

include_once 'Eccentricity.php';
include_once 'SolarDeclination.php';
include_once 'EquationOfTime.php';
include_once 'SunriseAngle.php';
include_once 'GenerateMatrixHours.php';
include_once 'TrueSolarTime.php';
include_once 'SunPosition.php';
include_once 'AirMass.php';
include_once 'HorizontalIrradiances.php';
include_once 'AnisotropicIndices.php';
include_once 'InclinedSurfaceIrradiances.php';
include_once 'AmbientTemperature.php';
include_once 'CellTemperature.php';
include_once 'DCPower.php';
include_once 'ACPower.php';
include_once 'ACPowerMT.php';

//MAIN PROGRAM

function MainProgram($SITE, $METEO, $PVMOD, $PVGEN, $BOS, $OPTIONS, $TIME ) 
{
	// Eccentricity correction factor, dimensionless
	$SUNMOT_Eo= Eccentricity($TIME);
	
	//Solar declination, radian
	$SUNMOT_delta=SolarDeclination($TIME);
			
	//Equation of time, radian
	$SUNMOT_ET=EquationOfTime($TIME);
	
	//Sunrise angle, radian
	$SUNMOT_ws=SunriseAngle($SUNMOT_delta, $SITE, $TIME);
	
	//Generation of the matrix Hours
	if (($METEO['Data'] == 1)||($METEO['Data'] == 3))
	{
		$Hours = GenerateMatrixHours($TIME);
	}
	else 
	{
		$Hours = $METEO['Hours'];
	}
	
	//True Solar Time, radian
	$SUNMOT_w=TrueSolarTime($Hours, $SUNMOT_ET, $SITE, $TIME);
	
	$SUNMOT = array(
				'Eo'	 => $SUNMOT_Eo,
				'delta'  => $SUNMOT_delta,
				'ET'	 => $SUNMOT_ET,
				'ws'	 => $SUNMOT_ws,
				'w'	     => $SUNMOT_w);
	
	// Sun Position, cosines and angles.
	$SUNPOS = SunPosition($SUNMOT['delta'], $SUNMOT['w'], $SITE, $TIME);
	
	// Air Mass
	$AMI = AirMass($SUNPOS, $SITE, $TIME);
	
	// Calculation of horizontal irradiances
	$HI = HorizontalIrradiances($SUNMOT, $SUNPOS, $AMI, $SITE, $METEO, $OPTIONS, $TIME);
	// Anisotropic indices
	$ANISO = AnisotropicIndices($SUNMOT['Eo'], $SUNMOT['ws'], $SUNMOT['w'], $SUNPOS, $AMI, $HI, $OPTIONS, $TIME);
	// Irradiances on the inclined surface
	$ISI = InclinedSurfaceIrradiances($SUNMOT, $SUNPOS, $AMI, $HI, $ANISO, $SITE, $PVMOD, $PVGEN, $OPTIONS, $TIME);
	
	// Ambient temperature
	if (($METEO['Data'] == 1)||($METEO['Data'] == 3))
	{
		$Ta = AmbientTemperature($SUNMOT['w'], $SUNMOT['ws'], $METEO, $TIME);
	}
	else
	{
		$Ta = $METEO['Ta'];
	}
	// Cell temperature
	$Tc = CellTemperature($Ta, $ISI['Gefsaypce'], $PVMOD, $TIME);
	
	// DC power
	$POWER = DCPower($ISI['Gefsaypce'], $Tc, $PVMOD, $PVGEN, $BOS['WIRING'], $OPTIONS, $TIME);
		
 	// AC power, low voltage
	$POWER = ACPower($POWER, $ISI['Gefsaypce'], $BOS['INVERTER'], $BOS['WIRING'], $OPTIONS, $TIME);
	
	// AC power, medium voltage
	$POWER = ACPowerMT($POWER, $BOS['TRANSFORMER'], $TIME);
		
	// OUTPUT
	
	return $out = array(
					'HI'  	=> $HI,
					'ISI'   => $ISI,
					'POWER' => $POWER);
}


?>