<?php

include_once 'PerezCoefficients90.php';

function AnisotropicIndices($Eo, $ws, $w, $SUNPOS, $AMI, $HI, $OPTIONS, $TIME) {

	//Diffuse model
	$model=$OPTIONS['DiffuseRadiationModeling'];	
	
	//Variables
	$B0=$HI['B0'];
	$D0=$HI['D0'];
	$costetazs=$SUNPOS['costetazs'];
	$tetazs=$SUNPOS['tetazs'];

	//Calculations
	for ($d = 0; $d < $TIME['Ndays']; $d++)
	//foreach ($TIME['SimulationDays'] as $dm)
		{
		//$d = $dm - 1;	
		//Isotropic
		if ($model == 1)
			{
			for ($h = 0; $h < $TIME['Nsteps']; $h++) 
				{
				//Anisotropy index
				$k1[$d][$h]=0;
				
				//Anulation of the diffuse horizontal component (Perez Model)
				$k2[$d][$h]=0;
				}
			}
		
		//Anisotropic (Hay)
		if ($model == 2)
			{
			for ($h = 0; $h < $TIME['Nsteps']; $h++) 
				{
				//Anisotropy index
				if ( abs($w[$d][$h] ) >= abs($ws[$d]) )
					{				
					$k1[$d][$h]=0;
					}else
						{
						$k1[$d][$h]= $B0[$d][$h] / (1367 *$Eo[$d] *$costetazs[$d][$h]);
						}
				//Anulation of the diffuse horizontal component (Perez Model)
				$k2[$d][$h]=0;
				}
			}
		
			//Anisotropic (Perez)
			if ($model == 3)
				{
				for ($h = 0; $h < $TIME['Nsteps']; $h++)
					{
					//Calculation of parameters F1 and F2 of the Perez model (Solar Energy 44,
					//1990), which are called here as k1 and k2, respectively
					if (abs($w[$d][$h]) >= abs($ws[$d]) )
						{
						$epsilon[$d][$h]=0;
						$delta[$d][$h]=0;
						$k1[$d][$h]=0;
						$k2[$d][$h]=0;
						}else
							{
							//Sky clearness, epsilon
							if ($D0[$d][$h] == 0)
								{
								$epsilon[$d][$h]=0;
								}else
									{
									//Simplified equation
									//Solar Energy 39, 1987
									$epsilon[$d][$h]=($D0[$d][$h]+$B0[$d][$h]/ $costetazs[$d][$h]) / $D0[$d][$h];
							
									//Equation including dependence with tetazs
									//Solar Energy 44, 1990
									//$epsilon[$d][$h]=(($D0[$d][$h] + $B0[$d][$h]/ $costetazs[$d][$h]) / $D0[$d][$h] + 1.041 *pow($tetazs[$d][$h],3) ) / (1 + 1.041 * pow($tetazs[$d][$h],3));
									}

								//Sky brightness, delta
								$delta[$d][$h]=$D0[$d][$h] * $AMI[$d][$h]/(1367*$Eo[$d]);
								
								//Model coefficients
								//Perez et al., Solar energy 44, 1990
								$PC=PerezCoefficients90($epsilon[$d][$h]);
								
								//Circumsolar coefficient
								$k1[$d][$h]=max(0, $PC['k31'] + $PC['k32']*$delta[$d][$h] + $PC['k33']*$tetazs[$d][$h]);
								
								//Horizont brightness coefficient
								$k2[$d][$h]=$PC['k41'] + $PC['k42'] *$delta[$d][$h] + $PC['k43'] *$tetazs[$d][$h];
							}
					
					}
				}
			
			
		}//end FOR $d SimulationDays
$ANISO= array(
			'k1' => $k1,
			'k2' => $k2);

return $ANISO;

}

?>