<?php

function ConfidenceLevels($P50, $OPTIONS)
{

	//Uncertainties: standard deviations, %
	$SIGMA_BD = $OPTIONS['UNCERTAINTY']['SIGMA_BD'];
	$SIGMA_VA = $OPTIONS['UNCERTAINTY']['SIGMA_VA'];
	$SIGMA_DL = $OPTIONS['UNCERTAINTY']['SIGMA_DL'];
	$SIGMA_TR = $OPTIONS['UNCERTAINTY']['SIGMA_TR'];
	$SIGMA_RP = $OPTIONS['UNCERTAINTY']['SIGMA_RP'];
	$SIGMA_PI = $OPTIONS['UNCERTAINTY']['SIGMA_PI'];
	$SIGMA_EN = $OPTIONS['UNCERTAINTY']['SIGMA_EN'];

	//Combined uncertainty, %
	$SIGMA_TOTAL = sqrt(pow($SIGMA_BD, 2) + pow($SIGMA_VA, 2) + pow($SIGMA_DL, 2) + pow($SIGMA_TR, 2) + pow($SIGMA_RP, 2) + pow($SIGMA_PI, 2) + pow($SIGMA_EN, 2));

	//P75 level, equivalent hours
	$P75 = $P50*(1-0.675*$SIGMA_TOTAL/100);

	//P90 level, equivalent hours
	$P90 = $P50*(1-1.2816*$SIGMA_TOTAL/100);

	//Output
	return $out = array(
			'SIGMA_TOTAL'  	=> $SIGMA_TOTAL,
			'P50'	=> $P50,
			'P75'	=> $P75,
			'P90'	=> $P90);


}

?>