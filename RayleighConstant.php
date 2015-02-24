<?php

function RayleighConstant($AMI) {
//This function ...	

	//Model coefficients
	$dr0 = 6.6296;
	$dr1 = 1.7513;
	$dr2 = -0.1202;
	$dr3 = 0.0065;
	$dr4 = -0.00013;
	$dr5 = 10.4;
	$dr6 = 0.718;

	//Calculation of Rayleigh constant as a function of the Air Mass (AMI)
	if ($AMI <=0) 
	{
		$deltaR=0;
	}
	elseif ($AMI <=20)
	{
		$deltaR= 1/($dr0 + $dr1*$AMI + $dr2*pow($AMI,2) + $dr3*pow($AMI,3) + $dr4*pow($AMI,4));
	}
	else
	{
		$deltaR= 1/($dr5 + $dr6*$AMI);
	}
return $deltaR;
}

?>