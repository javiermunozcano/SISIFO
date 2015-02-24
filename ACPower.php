<?php

function ACPower($POWER,$Gref,$INVERTER,$WIRING,$OPTIONS,$TIME) {

	//Parameters
	$DRi=$INVERTER['DRi'];
	$Ki0=$INVERTER['Ki0'];
	$Ki1=$INVERTER['Ki1'];
	$Ki2=$INVERTER['Ki2'];
	
	//Maximum inverter power, normalised to the nominal one
	$pacmax=$INVERTER['PowMax']/$INVERTER['PowNom'];	
	
	//Calculations
	for ($d = 0; $d < $TIME['Ndays']; $d++) 
		{
		for ($h = 0; $h < $TIME['Nsteps']; $h++) 
			{
			if ( $Gref[$d][$h] < $OPTIONS['Gth'] )
				{
				//PV system is OFF
				//Anulates AC power
				$PAC[$d][$h]=0;
				$PACAC[$d][$h]=0;
				}else
					{
					//Power delivered by the PV generator at the inverter input,
					//normalised to the nominal inverter power.
					$PDCN[$d][$h]=$POWER['PDC'][$d][$h]/$DRi;
					$A=$Ki2;
					$BI=$Ki1+1;
					$C=$Ki0-$PDCN[$d][$h];
					//Power delivered by the inverter at its output, normalised to the
					//nominal inverter power.
					$PACi[$d][$h]=(-$BI+sqrt(pow($BI,2)-4*$A*$C))/(2*$A);
				
					//Special case: if PDCN(h,d)<Ki0, PACi(h,d) would be negative.
					if($PACi[$d][$h] < 0)
						{
						//Anulates AC and DC powers
						$PACi[$d][$h]=0;
						$POWER['PDCSP'][$d][$h]=0;
						$POWER['PDCPP'][$d][$h]=0;
						$POWER['PDCPE'][$d][$h]=0;
						$POWER['PDCPT'][$d][$h]=0;
						$POWER['PDCBI'][$d][$h]=0;
						$POWER['PDCPC'][$d][$h]=0;
						$POWER['PDC'][$d][$h]=0;
						}
	
					if ( $PACi[$d][$h] > $pacmax )
						{
						//Inverter saturation
						//AC power is limited to the nominal inverter power (normalised
						//to the nominal PV generator power)
						$PACAC[$d][$h]=$DRi*$pacmax;
						//DC power recalculated as the nominal inverter power
						//divided by the effiency in that operating point
						$PDC[$d][$h]=$DRi*($pacmax+$Ki0+$Ki1*$pacmax+$Ki2*pow($pacmax,2));
						}else
							{
							//Power delivered by the inverter at its output,
							//normalised to the nominal PV power.
							$PACAC[$d][$h]=$PACi[$d][$h]*$DRi;
							}
	
					//AC power injected by the inverter in LV, normalised to the
					//nominal PV power after discounting AC wiring losses.
					$PAC[$d][$h]=$PACAC[$d][$h]*(1-$WIRING['ACLosses']/100*pow($PACAC[$d][$h],2));
					}
				
			}//end FOR $h Nsteps
		}//end FOR $d Ndays
$POWER['PACAC']=$PACAC;
$POWER['PAC']=$PAC;

return $POWER;

}

?>