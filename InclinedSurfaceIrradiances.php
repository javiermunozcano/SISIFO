<?php

// Include function files

include_once 'StaticGroundRoof.php';
include_once 'StaticFacade.php';
include_once 'TrackerOneAxisHorizontal.php';
include_once 'TrackerOneAxisAzimutal.php';
include_once 'TrackerTwoAxisVerticalHorizontal.php';
include_once 'TrackerTwoAxisVenetianBlind.php';
include_once 'TrackerTwoAxisHorizontalPerpendicular.php';
include_once 'TrackerTwoAxisConcentrator.php';
include_once 'SpectralResponse.php';

function InclinedSurfaceIrradiances($SUNMOT, $SUNPOS, $AMI, $HI, $ANISO, $SITE, $PVMOD, $PVGEN, $OPTIONS, $TIME) 
{
	
	switch ($PVGEN['Struct']) {
		case 1:
			// Ground or roof
		 	$ISI = StaticGroundRoof($SUNPOS, $SUNMOT['w'], $SUNMOT['ws'], $HI, $ANISO, $SITE, $PVGEN, $OPTIONS, $TIME);
			break;
		case 2:
			// Façade
			$ISI = StaticFacade($SUNPOS, $SUNMOT['w'], $SUNMOT['ws'], $HI, $ANISO, $SITE, $PVGEN, $OPTIONS, $TIME);
			break;
		case 3:
			// One axis horizontal or inclined
			$ISI = TrackerOneAxisHorizontal($SUNPOS, $SUNMOT['w'], $SUNMOT['ws'], $HI, $ANISO, $SITE, $PVGEN, $OPTIONS, $TIME);
			break;
		case 4:
			// One axis vertical (azimuthal)
			$ISI = TrackerOneAxisAzimutal($SUNPOS, $SUNMOT['w'], $SUNMOT['ws'], $HI, $ANISO, $SITE, $PVGEN, $OPTIONS, $TIME);
			break;
		case 5:
			// Two axis (primary vertical/secondary horizontal)
			$ISI = TrackerTwoAxisVerticalHorizontal($SUNPOS, $SUNMOT['w'], $SUNMOT['ws'], $HI, $ANISO, $SITE, $PVGEN, $OPTIONS, $TIME);
			break;
		case 6:
			// Two axis (primary vertical/secondary horizontal, Venetian blind)
			$ISI = TrackerTwoAxisVenetianBlind($SUNPOS, $SUNMOT['w'], $SUNMOT['ws'], $HI, $ANISO, $SITE, $PVGEN, $OPTIONS, $TIME);
			break;
		case 7:
			// Two axis (primary horizontal, secondary perpendicular)
			$ISI = TrackerTwoAxisHorizontalPerpendicular($SUNPOS, $SUNMOT['w'], $SUNMOT['ws'], $HI, $ANISO, $SITE, $PVGEN, $OPTIONS, $TIME);
			break;
		case 8:
			// Concentrator
			$ISI = TrackerTwoAxisConcentrator($SUNPOS, $SUNMOT['w'], $SUNMOT['ws'], $HI, $ANISO, $SITE, $PVGEN, $OPTIONS, $TIME);
			break;
		default:
			echo 'Invalid configuration, please select between 1 and 8';
		break;
	}
	
	// Spectral response
	$ISI = SpectralResponse($SUNMOT['Eo'], $SUNPOS, $AMI, $HI, $ISI, $PVMOD, $OPTIONS, $TIME);
	
	return $ISI;
}

?>