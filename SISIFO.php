 <?php
 /*
SISIFO: Simulador de Sistemas Fotovoltaicos.
(C) Grupo de Sistemas Fotovoltaicos. Instituto de Energía Solar. 
Universidad Politécnica de Madrid.
*/

include_once 'InputParameters.php';
include_once 'YearlyAnalysis.php';
include_once 'economics.php';
//include_once '../Output.php';
ini_set("memory_limit","1000M");

switch ($OPTIONS['Analysis'])
{
	case '1': //Yearly analysis
		$RESULTS=YearlyAnalysis($SITE,$METEO,$PVMOD,$PVGEN,$BOS,$OPTIONS,$TIME);
		break;
		
	case '2':	//DC sweep
   		//...
		break;
		
	case '3':	//Parametric
   		//...
		break;
		
	default:
		echo 'Insert a correct value (1,2,3)';
		break;
}

//Economic analysis and electricity cost calculations
$ECONOMICS = economic_analysis($RESULTS, $PVGEN, $ECO);

//Results output
//outputInterface($RESULTS, $SITE, $METEO, $PVMOD, $PVGEN, $BOS, $OPTIONS, $TIME, $ECONOMICS);

print_r($ECONOMICS);
//print_r($RESULTS['YEARLY']);
?>
