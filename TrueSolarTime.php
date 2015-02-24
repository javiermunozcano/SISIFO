<?php
					
function TrueSolarTime($Hours, $ET, $SITE, $TIME) {
	
//Calculations
	if($TIME['LocalTime'] == 1) //Matrix Hours is Solar time
		{	
		//True solar time, radian
		for ($i = 0; $i < $TIME['Ndays']; $i++) 
			{
			for ($j = 0; $j < $TIME['Nsteps']; $j++) 
				{
				$w[$i][$j]=($Hours[$i][$j]-12)*15*pi()/180;
				}
			}
		
		}elseif($TIME['LocalTime'] == 2)	//Matrix Hours is Standard Time
			{
			//Daily loop
			for ($d = 0; $d < $TIME['Ndays']; $d++)
			//foreach ($TIME['SimulationDays'] as $d) // AMP: En Matlab estaba puesto así. Revisaremos si está mal.
				{
				//Daylight Saving Time, DST
				if (($d < $TIME['DOCS']) || ($d >= $TIME['DOCW'])) 
					{
					$DST[$d]=$TIME['DSTW'][$d];
					}else
						{
						$DST[$d]=$TIME['DSTS'][$d];			
						} 
				//Hourly loop
				for ($h = 0; $h < $TIME['Nsteps']; $h++) 
					{
					//True solar time, radian
					$w[$d][$h]=($Hours[$d][$h]-(12+$DST[$d][$h]))*15*pi/180 + ($SITE['Longitude']-$SITE['StandardLongitude'])*pi/180 + $ET[$d];
					}
				}//end foreach
			}//end elseif
		
return $w;	
}

?>