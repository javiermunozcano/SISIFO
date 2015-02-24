<?php

function DCPower($Gref,$Tc,$PVMOD,$PVGEN,$WIRING,$OPTIONS,$TIME) {

	//Calculations
	for ($d = 0; $d < $TIME['Ndays']; $d++) 
		{
		//Daily variation of a-Si efficiency
		$SV=$PVMOD['AmorphousSi']['SeasonalVariation'];
		$SP=$PVMOD['AmorphousSi']['SeasonalPhase'];
		if ( $PVMOD['CellMaterial'] == 3 )
			{
			$EFESIA[$d]= 1+($SV/100)*sin(2*pi()*($d+1-$SP+91.25)/365);
			}else
				{
				//Other materials
				$EFESIA[$d]=1;
				}	

		//PV efficiencies
		for ($h = 0; $h < $TIME['Nsteps']; $h++) 
			{
			if ($PVMOD['PowerModel'] == 1 )
				{
				//Efficiency caused by the cell temperature
				$EFNG[$d][$h]=(1-($Tc[$d][$h]-25)*$PVMOD['CVPT']/100);
				//Efficiency caused by the level of irradiance
				$EGBI[$d][$h]=1;
				}elseif ($PVMOD['PowerModel'] == 2)
					{
					//Efficiency caused by the cell temperature
					$EFNG[$d][$h]=(1-($Tc[$d][$h]-25)*$PVMOD['CVPT']/100);
					//Efficiency caused by the level of irradiance
					//Model coefficients
					$a=$PVMOD['LowGParam']['a'];
					$b=$PVMOD['LowGParam']['b'];
					$c=$PVMOD['LowGParam']['c'];
					if($Gref[$d][$h] == 0)
						{
						$EGBI[$d][$h]=0;
						}else
							{
							$EGBI[$d][$h]=max(0, $a + $b*($Gref[$d][$h]/1000) + $c*log($Gref[$d][$h]/1000) );
							}
				}
			}//end FOR $h Nsteps
		
			//DC power
			for ($h = 0; $h < $TIME['Nsteps']; $h++) 
				{
				if ( $Gref[$d][$h] < $OPTIONS['Gth'])
					{
					//PV system is OFF
					//Anulates DC power
					$PDCSP[$d][$h]=0;
					$PDCPP[$d][$h]=0;
					$PDCPE[$d][$h]=0;
					$PDCPT[$d][$h]=0;
					$PDCBI[$d][$h]=0;
					$PDCPC[$d][$h]=0;
					$PDC[$d][$h]=0;
					}else
						{
						//Chain of DC power conversion, normalised to the nominal PV power.
						//Ideal, no losses
						$PDCSP[$d][$h]=$Gref[$d][$h]/1000;
						//Real power (mismatch, tolerance, etc.)
						$PDCPP[$d][$h]=$PDCSP[$d][$h]*$PVGEN['PRVPN'];
						//Discounting seasonal losses(a-Si)
						$PDCPE[$d][$h]=$PDCPP[$d][$h]*$EFESIA[$d];
						//Discounting temperature losses
						$PDCPT[$d][$h]=$PDCPE[$d][$h]*$EFNG[$d][$h];
						//Discounting low irradiance losses
						$PDCBI[$d][$h]=$PDCPT[$d][$h]*$EGBI[$d][$h];
						//Discounting wiring (PV generator to Inverter) losses
						$PDCPC[$d][$h]=$PDCBI[$d][$h]*(1-$WIRING['DCLosses']/100*pow($PDCBI[$d][$h],2));
						//Power delivered by the PV generator at the inverter input,
						//normalised to the nominal PV power.
						$PDC[$d][$h]=$PDCPC[$d][$h];
						}
				}//end FOR $h Nsteps
		}//end FOR $d Ndays

$POWER= array (
		'PDCSP' => $PDCSP,
		'PDCPP' => $PDCPP,
		'PDCPE' => $PDCPE,
		'PDCPT' => $PDCPT,
		'PDCBI' => $PDCBI,
		'PDCPC' => $PDCPC,
		'PDC' => $PDC);


return $POWER;

}

?>