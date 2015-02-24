<?php

include_once 'BandGap.php';

function SpectralResponse($Eo,$SUNPOS,$AMI,$HI,$ISI,$PVMOD,$OPTIONS,$TIME) {

	//Model coefficients
	//Spectral response
	//Energy band gap, EG, in eV
	$EG=BandGap($PVMOD['CellMaterial']);
	
	//Model coefficients
	$CB=1.029+(1.024-1.029)/(1.7-1.12)*($EG-1.12);
	$CD=0.764+(0.840-0.764)/(1.7-1.12)*($EG-1.12);
	$CAL=0.970+(0.989-0.970)/(1.7-1.12)*($EG-1.12);
	$AB=-0.313+(-0.222+0.313)/(1.7-1.12)*($EG-1.12);
	$AD=-0.882+(-0.728+0.882)/(1.7-1.12)*($EG-1.12);
	$AAL=-0.244+(-0.219+0.244)/(1.7-1.12)*($EG-1.12);
	$BB=0.00524+(0.0092-0.00524)/(1.7-1.12)*($EG-1.12);
	$BD=-0.0204+(-0.0183+0.0204)/(1.7-1.12)*($EG-1.12);
	$BAL=0.0129+(0.0179-0.0129)/(1.7-1.12)*($EG-1.12);	
	
	//Calculations
	for ($d = 0; $d < $TIME['Ndays']; $d++) 
		{
		for ($h = 0; $h < $TIME['Nsteps']; $h++) 
			{
			//Instantaneous Clearness Index, KTI
			$KTI[$d][$h]=$HI['G0'][$d][$h]/(1367*$Eo[$d]*$SUNPOS['costetazs'][$d][$h]+0.000001);
	
			//Spectral corrections factors for beam (FCEB), diffuse (FCED) and
			//reflected (FCER).
			if ($OPTIONS['SpectralResponse'] == 1)
				{
				$FCEB[$d][$h]=$CB*exp($AB*($KTI[$d][$h]-0.74)+$BB*($AMI[$d][$h]-1.5));
				$FCED[$d][$h]=$CD*exp($AD*($KTI[$d][$h]-0.74)+$BD*($AMI[$d][$h]-1.5));
				$FCER[$d][$h]=$CAL*exp($AAL*($KTI[$d][$h]-0.74)+$BAL*($AMI[$d][$h]-1.5));
				}else
					{
					$FCEB[$d][$h]=1;
					$FCED[$d][$h]=1;
					$FCER[$d][$h]=1;
					}
	
			//Spectral corrections of the irradiance components
			$Befsaypce[$d][$h]=$ISI['Befsayp'][$d][$h]*$FCEB[$d][$h];
			$Defsaypce[$d][$h]=$ISI['Defsayp'][$d][$h]*$FCED[$d][$h];
			$Refce[$d][$h]=$ISI['Ref'][$d][$h]*$FCER[$d][$h];
	
			//Global irradiance
			$Gefsaypce[$d][$h]=$Befsaypce[$d][$h]+$Defsaypce[$d][$h]+$Refce[$d][$h];
						
			}//end FOR $h Nsteps
		}//end FOR $d Ndays
	
//Output
		
$ISI['Gefsaypce'] = $Gefsaypce;
$ISI['Befsaypce'] = $Befsaypce;
$ISI['Defsaypce'] = $Defsaypce;
$ISI['Refce']     = $Refce;
		
return $ISI;

}

?>