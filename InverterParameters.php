<?php
//INVERTER PARAMETERS

function InverterParameters($p0, $Efficiency)
{
	
	/* NOTE: In this version only 3 pairs of p-eta data are supported by the user and resolves
	 * the system of 3 equations.
	 */

	// Equations solved P10%, P50% y P100%
	$Effic0=$Efficiency[0];
	$Effic1=$Efficiency[1];
	$Effic2=$Efficiency[2];
	$Ki0=100*( (5/36)*(1/$Effic0)+(-1/4)*(1/$Effic1)+(1/9)*(1/$Effic2) );
	$Ki1=100*( (-5/12)*(1/$Effic0)+(33/12)*(1/$Effic1)+(-4/3)*(1/$Effic2) )-1;
	$Ki2=100*( (5/18)*(1/$Effic0)+(-5/2)*(1/$Effic1)+(20/9)*(1/$Effic2) );
		
	return $out = array($Ki0, $Ki1, $Ki2);
}

?>