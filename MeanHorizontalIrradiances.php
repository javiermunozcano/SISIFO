<?php

function MeanHorizontalIrradiances($w, $ws, $DHI, $TIME) {
	
	//Calculations
	for ($d = 0; $d < $TIME['Ndays']; $d++) 
		{
		//Liu and Jordan parameters
		$a2[$d]=0.409-0.5016*sin($ws[$d]+1.047);
		$b2[$d]=0.6609+0.4767*sin($ws[$d]+1.047);
		//Counters initialisation
		$sumrg=0;
		$sumrd=0;
		
		//Calculation of rd and rg
			for ($h = 0; $h < $TIME['Nsteps']; $h++) 
				{
				if ( abs($w[$d][$h]) >= abs($ws[$d]) )
					{
					$rd[$d][$h]=0;
					$rg[$d][$h]=0;
					}else
						{
						$rd[$d][$h]=pi()/24*( cos($w[$d][$h] )- cos($ws[$d]) )/ ($ws[$d]* cos($ws[$d]) -sin($ws[$d]) );
						$rg[$d][$h]= $rd[$d][$h]* ($a2[$d] +$b2[$d]* cos($w[$d][$h]) );
						$sumrd = $sumrd + $rd[$d][$h];
						$sumrg = $sumrg + $rg[$d][$h];
						}
					
				}
					
			//Correction to ensure that the sum of rd and rg is unity
			for ($h = 0; $h < $TIME['Nsteps']; $h++)
			{
				if ($sumrd == 0)
					{
					$rd[$d][$h]=0;
					$rg[$d][$h]=0;
					}else
						{
						$rd[$d][$h]=$rd[$d][$h]/$sumrd*$TIME['Stepph'];
						$rg[$d][$h]=$rg[$d][$h]/$sumrg*$TIME['Stepph'];
						}
			}
					
			//Irradiance components
			for ($h = 0; $h < $TIME['Nsteps']; $h++)
			{	
				if ( abs($w[$d][$h] ) >= abs($ws[$d]) )
					{
					$G0[$d][$h]=0;
					$D0[$d][$h]=0;
					$B0[$d][$h]=0;
					}else
						{
						$G0[$d][$h]=$DHI['Gd0'][$d]*$rg[$d][$h];
						$D0[$d][$h]=$DHI['Dd0'][$d]*$rd[$d][$h];
						$B0[$d][$h]=$G0[$d][$h]-$D0[$d][$h];
						}
						
			}//end for $h Nsteps
		}//end for $d Ndays
	
$HI= array(
		'G0' => $G0,
		'B0' => $B0,
		'D0' => $D0);

return $HI;
}

?>