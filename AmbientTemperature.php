<?php

include_once 'MonthlyAverageToAll.php';

function AmbientTemperature($w, $ws, $METEO, $TIME) {
	
	//Daily maximum and minimum temperatures
	if($TIME['Ndays'] == 365)
		{
		//All year
		$TM=MonthlyAverageToAll($METEO['TMm']);
		$Tm=MonthlyAverageToAll($METEO['Tmm']);
		}elseif($TIME['Ndays'] == 12)
			{
			//Only characteristic days
			$TM=$METEO['TMm'];
			$Tm=$METEO['Tmm'];
			}
	
	//Calculations
	for ($d = 0; $d < $TIME['Ndays']; $d++) 	
		{
		//Daily coefficients
		$a3[$d]= -pi() / ($ws[$d] +2 *pi() -pi()/6);
		$b3[$d]= -$a3[$d] *$ws[$d];
		$a4[$d]= pi() / ($ws[$d] -pi() /6);
		$b4[$d]= -$a4[$d] *pi()/6;
		$a5[$d]= pi()/(2*pi()+ $ws[$d] -pi()/6);
		$b5[$d]= -(pi() +$a5[$d] *pi()/6);
	
		for ($h = 0; $h < $TIME['Nsteps']; $h++) 
			{
			if ( (-pi() < $w[$d][$h]) && ($w[$d][$h] < $ws[$d]) )
				{
				$Ta[$d][$h]=$TM[$d]-($TM[$d] -$Tm[$d]) / 2*(1+ cos($a3[$d] *$w[$d][$h] +$b3[$d]) );
				}
				elseif ( ( $ws[$d] < $w[$d][$h]) && ( $w[$d][$h] < pi()/6 ) )
				{
					$Ta[$d][$h]=$Tm[$d] +($TM[$d] -$Tm[$d]) / 2*(1+cos($a4[$d] * $w[$d][$h] +$b4[$d]) );
				}else
						{
						$Ta[$d][$h]=$TM[$d]-( $TM[$d]-$Tm[$d] )/ 2*(1+cos( $a5[$d] *$w[$d][$h]+ $b5[$d]) );
						}
			}//end FOR $h
	
		}//end FOREACH $d

return $Ta;
		
}

?>