<?php

// Include function files
include_once 'DustDegreeParameters.php';
include_once 'ShadingModelParameters.php';

function TrackerOneAxisAzimutal($SUNPOS, $w, $ws, $HI, $ANISO, $SITE, $PVGEN, $OPTIONS, $TIME)
{
	///////////////////////////////////////
	// INITIAL CALCULATIONS
	///////////////////////////////////////

	// INPUTS
	
	// $SUNPOS
	$costetazs = $SUNPOS['costetazs'];
	$cosfis = $SUNPOS['cosfis'];
	$gammas = $SUNPOS['gammas'];
	$tetazs = $SUNPOS['tetazs'];
	$fis = $SUNPOS['fis'];
	
	// $HI
	$G0 = $HI['G0'];
	$B0 = $HI['B0'];
	$D0 = $HI['D0'];
	
	// $ANISO
	$k1 = $ANISO['k1'];
	$k2 = $ANISO['k2'];
	
	// $PVGEN
	// Common
	$NBGH = $PVGEN['NBGH'];
	$NBGV = $PVGEN['NBGV'];
	$NBT = $PVGEN['NBT'];
	
	// This tracker
	$Inclination_surface = $PVGEN['Track1V']['Inclination_surface'];
	$LEO = $PVGEN['Track1V']['LEO'];
	$LNS = $PVGEN['Track1V']['LNS'];
	$ALARG = $PVGEN['Track1V']['ALARG'];
	$RSEV = $PVGEN['Track1V']['RSEV'];

	// OTHER PARAMETERS
	// Degree of dust, model parameters
	$DDP = DustDegreeParameters($OPTIONS['DustDegree']);
	$ar = $DDP['ar'];
	$c2 = $DDP['c2'];
	$Transm = $DDP['Transm'];
	// Shading, model coefficients
	$SMP = ShadingModelParameters($OPTIONS['ShadingModel']);
	$MSP = $SMP['MSP'];
	$MSO = $SMP['MSO'];
	$MSC = $SMP['MSC'];
	// Ground reflectance
	$GroundReflectance = $OPTIONS['GroundReflectance'];



	///////////////////////////////////
	// CALCULATIONS
	///////////////////////////////////
	for ($d = 0; $d < $TIME['Ndays']; $d++)
	{
		for ($h = 0; $h < $TIME['Nsteps']; $h++)
		{
			// Angles to the Sun's surface following an azimuth axis begins the ideal value,
			// and then considers whether it should be corrected
			$beta[$d][$h] = $Inclination_surface*pi()/180;
			$alfa[$d][$h] = $fis[$d][$h];

			// Find the length of the shadow.
			if ( abs($w[$d][$h]) >= abs($ws[$d]) )
			{
				$S1[$d][$h] = 0.0000001;
				$S2[$d][$h] = 0;
			}
			else
			{
				$S1[$d][$h] = $ALARG * cos($beta[$d][$h]);
				$S2[$d][$h] = $ALARG * sin($beta[$d][$h]) * tan($tetazs[$d][$h]);
			}

			$SP[$d][$h] = $S1[$d][$h] + $S2[$d][$h];
			
			// Consideration of the shadows projected by a tracker located at E.
			// geometric:
			// Inicial reset to zero (the program need if no shade)
			$FSGVE[$d][$h] = 0;
			$FSGHE[$d][$h] = 0;

			// Factor calculation when have shadow
			if ($w[$d][$h] <= 0)
			{
				if ($fis[$d][$h] < (-pi()/2) )
				{			
					$FSGHE[$d][$h] = max( 0, (1 - $LEO*cos(pi()-($fis[$d][$h]))) );
				}
				else
				{
					$FSGHE[$d][$h] = max( 0,(1-$LEO*cos($fis[$d][$h])) );
				}
				if ($gammas[$d][$h] > 0.000001)
				{
					$FSGVE[$d][$h] = max(0, (1+$LEO / $SP[$d][$h] * sin($fis[$d][$h])) );
				}
			}

			$FSGTE[$d][$h] = $FSGVE[$d][$h] * $FSGHE[$d][$h];

			// Martinez shading model
			$NBSVE[$d][$h] = 0;
			$NBSHE[$d][$h] = 0;
			$NBSTE[$d][$h] = 0;
			$FSEVE[$d][$h] = 0;
			$FSEHE[$d][$h] = 0;
			$FSETE[$d][$h] = 0;
			
			// Effective values:
			if ($FSGTE[$d][$h] > 0)
			{
				$NBSVE[$d][$h] = RoundToZero($FSGVE[$d][$h] * $NBGV + 0.999);
				$NBSHE[$d][$h] = RoundToZero($FSGHE[$d][$h] * $NBGH + 0.999);
				$NBSTE[$d][$h] = $NBSVE[$d][$h] * $NBSHE[$d][$h];
				$FSEVE[$d][$h] = $NBSVE[$d][$h] / $NBGV;
				$FSEHE[$d][$h] = $NBSHE[$d][$h] / $NBGH;
				if ($MSC == 1)
				{
					$FSETE[$d][$h] = $FSEVE[$d][$h] * $FSEHE[$d][$h];
				}
				else
				{
					$FSETE[$d][$h] = 1 - (1-$FSGTE[$d][$h]) * ( 1 - (1-$MSO) * $NBSTE[$d][$h] / ($NBT+1) ) * (1-$MSP);
				}
			}

			//Consideration of the shadows projected by a tracker located at W.
			// Geometric:
			$FSGVO[$d][$h] = 0;
			$FSGHO[$d][$h] = 0;

			if ($w[$d][$h] >= 0)
			{
				if ($fis[$d][$h] > pi()/2)  // To calculate the shade when the sun rises from behind
				{
					$FSGHO[$d][$h] = max( 0, (1-$LEO*cos(pi() - $fis[$d][$h])) );
				}
				else
				{
					$FSGHO[$d][$h] = max( 0, (1-$LEO*cos($fis[$d][$h])) );
				}
				if ($gammas[$d][$h] > 0.0000001)
				{
					$FSGVO[$d][$h] = max( 0, (1-$LEO/$SP[$d][$h] * sin($fis[$d][$h])) );
				}
			}

			$FSGTO[$d][$h] = $FSGVO[$d][$h] * $FSGHO[$d][$h];

			// Martinez shading model
			$NBSVO[$d][$h] = 0;
			$NBSHO[$d][$h] = 0;
			$NBSTO[$d][$h] = 0;
			$FSEVO[$d][$h] = 0;
			$FSEHO[$d][$h] = 0;
			$FSETO[$d][$h] = 0;
			
			// Effective values:
			if ($FSGTO[$d][$h] > 0)
			{
				$NBSVO[$d][$h] = RoundToZero($FSGVO[$d][$h] * $NBGV + 0.999);
				$NBSHO[$d][$h] = RoundToZero($FSGHO[$d][$h] * $NBGH + 0.999);
				$NBSTO[$d][$h] = $NBSVO[$d][$h] * $NBSHO[$d][$h];
				$FSEVO[$d][$h] = $NBSVO[$d][$h] / $NBGV;
				$FSEHO[$d][$h] = $NBSHO[$d][$h] / $NBGH;
				if ($MSC == 1)
				{
					$FSETO[$d][$h] = $FSEVO[$d][$h] * $FSEHO[$d][$h];
				}
				else
				{
					$FSETO[$d][$h] = 1 - (1-$FSGTO[$d][$h]) * ( 1 - (1-$MSO) * $NBSTO[$d][$h] / ($NBT+1) ) * (1-$MSP);
				}
			}
			//Consideration of the shadows projected by a tracker located at the SE.
			//Geometric:
			//Azimuth at the beginning of the shade
			
			$tanfiscsse = -($LEO+cos($fis[$d][$h])) / ($LNS+sin($fis[$d][$h]));
			// Azimuth at the final of the shade
			$tanfisfsse = -($LEO-cos($fis[$d][$h])) / ($LNS-sin($fis[$d][$h]));
			// Azimuth of horizontal shade unity
			$tanfiss1se = - $LEO/$LNS;
			// Initial reset to zero
			$FSGHSE[$d][$h] = 0;
			$FSGVSE[$d][$h] = 0;
			
			if (tan($fis[$d][$h]) >= $tanfiscsse)
			{	
				if (tan($fis[$d][$h]) <= $tanfiss1se)
				{			
					$FSGHSE[$d][$h] = 1-($tanfiss1se-tan($fis[$d][$h])) / ($tanfiss1se-$tanfiscsse);
					$FSGVSE[$d][$h] = max( 0, (1-(($LNS * cos($fis[$d][$h]) - $LEO * sin($fis[$d][$h]))/$SP[$d][$h])) );
				}
			}
			
			if (tan($fis[$d][$h]) >= $tanfiss1se)
			{
				if (tan($fis[$d][$h]) <= $tanfisfsse)
				{			
					$FSGHSE[$d][$h] = 1- (tan($fis[$d][$h]) - $tanfiss1se) / ($tanfisfsse-$tanfiss1se);
					$FSGVSE[$d][$h] = max( 0, (1-(($LNS*cos($fis[$d][$h])- $LEO * sin($fis[$d][$h]))/$SP[$d][$h])) );
				}
			}

			$FSGTSE[$d][$h] = $FSGVSE[$d][$h] * $FSGHSE[$d][$h];

			// Martinez shading model
			$NBSVSE[$d][$h] = 0;
			$NBSHSE[$d][$h] = 0;
			$NBSTSE[$d][$h] = 0;
			$FSEVSE[$d][$h] = 0;
			$FSEHSE[$d][$h] = 0;
			$FSETSE[$d][$h] = 0;

			// Effective values:
			if ($FSGTSE[$d][$h] > 0)
			{
				$NBSVSE[$d][$h] = RoundToZero($FSGVSE[$d][$h] * $NBGV + 0.999);
				$NBSHSE[$d][$h] = RoundToZero($FSGHSE[$d][$h] * $NBGH + 0.999);
				$NBSTSE[$d][$h] = $NBSVSE[$d][$h] * $NBSHSE[$d][$h];
				$FSEVSE[$d][$h] = $NBSVSE[$d][$h] / $NBGV;
				$FSEHSE[$d][$h] = $NBSHSE[$d][$h] / $NBGH;
				if ($MSC == 1)
				{
					$FSETSE[$d][$h] = $FSEVSE[$d][$h] * $FSEHSE[$d][$h];
				}
				else
				{
					$FSETSE[$d][$h] = 1 - (1-$FSGTSE[$d][$h]) * (1-(1-$MSO) * $NBSTSE[$d][$h] / ($NBT+1)) * (1-$MSP);
				}
			}
			// Consideration of the shadows cast by a fan located at the SW.
			// geometric:
			// Azimuth at the beginning of the shade
			$tanfiscsso = ($LEO-cos($fis[$d][$h])) / ($LNS+sin($fis[$d][$h]));
			// Azimuth at the end of the shade
			$tanfisfsso = ($LEO+cos($fis[$d][$h])) / ($LNS-sin($fis[$d][$h]));
			// Azimuth of the horizontal shade unity
			$tanfiss1so= $LEO/$LNS;
			//Initial reset to zero
			$FSGHSO[$d][$h] = 0;
			$FSGVSO[$d][$h] = 0;
			
			if (tan($fis[$d][$h]) >= $tanfiscsso)
			{
				if (tan($fis[$d][$h]) <= $tanfiss1so)
				{				
					$FSGHSO[$d][$h] = 1 -($tanfiss1so-tan($fis[$d][$h])) / ($tanfiss1so-$tanfiscsso);
					$FSGVSO[$d][$h] = max( 0, (1-(($LNS*cos($fis[$d][$h])+$LEO*sin($fis[$d][$h]))/$SP[$d][$h])) );
				}
			}

			if (tan($fis[$d][$h]) >= $tanfiss1so)
			{
				if (tan($fis[$d][$h]) <= $tanfisfsso)
				{				
					$FSGHSO[$d][$h] = 1-(tan($fis[$d][$h])-$tanfiss1so) / ($tanfisfsso-$tanfiss1so);
					$FSGVSO[$d][$h] = max( 0, (1-(($LNS*cos($fis[$d][$h])+$LEO*sin($fis[$d][$h]))/$SP[$d][$h])) );
				}
			}

			$FSGTSO[$d][$h] = $FSGVSO[$d][$h] * $FSGHSO[$d][$h];

			// Martinez shading model
			$NBSVSO[$d][$h] = 0;
			$NBSHSO[$d][$h] = 0;
			$NBSTSO[$d][$h] = 0;
			$FSEVSO[$d][$h] = 0;
			$FSEHSO[$d][$h] = 0;
			$FSETSO[$d][$h] = 0;
			
			// Effective values:
			if ($FSGTSO[$d][$h] > 0)
			{
				$NBSVSO[$d][$h] = RoundToZero($FSGVSO[$d][$h] * $NBGV + 0.999);
				$NBSHSO[$d][$h] = RoundToZero($FSGHSO[$d][$h] * $NBGH + 0.999);
				$NBSTSO[$d][$h] = $NBSVSO[$d][$h] * $NBSHSO[$d][$h];
				$FSEVSO[$d][$h] = $NBSVSO[$d][$h] / $NBGV;
				$FSEHSO[$d][$h] = $NBSHSO[$d][$h] / $NBGH;
				if ($MSC == 1)
				{
					$FSETSO[$d][$h] = $FSEVSO[$d][$h] * $FSEHSO[$d][$h];
				}
				else
				{
					$FSETSO[$d][$h] = 1 - (1-$FSGTSO[$d][$h]) * ( 1 - (1-$MSO) * $NBSTSO[$d][$h] / ($NBT+1) ) * (1-$MSP);
				}
			}
			// Consideration of the shadows cast by a fan located at the S.
			// geometric:
			// Azimuth at the beginning of the shade
			$tanfiscss = -1/$LNS;
			// Azimuth at the final of the shade
			$tanfisfss = 1/$LNS;
			// Azimuth of the horizontal shade unity
			$tanfiss1s = 0;
			// Initial reset to zero
			$FSGHS[$d][$h]=0;
			$FSGVS[$d][$h]=0;

			if (abs($w[$d][$h]) < abs($ws[$d]))
			{
				if (tan($fis[$d][$h]) >= $tanfiscss)
				{				
					if (tan($fis[$d][$h]) <= $tanfiss1s)
					{
						$FSGHS[$d][$h] = 1-($tanfiss1s-tan($fis[$d][$h])) / ($tanfiss1s-$tanfiscss);
						$FSGVS[$d][$h] = max( 0, (1-(($LNS*cos($fis[$d][$h])) /$SP[$d][$h])) );
					}
				}
				if (tan($fis[$d][$h]) >= $tanfiss1s)
				{
					if (tan($fis[$d][$h]) <= $tanfisfss)
					{					
						$FSGHS[$d][$h] = 1 - (tan($fis[$d][$h])-$tanfiss1s) / ($tanfisfss-$tanfiss1s);
						$FSGVS[$d][$h] = max( 0, (1-(($LNS*cos($fis[$d][$h]))/$SP[$d][$h])) );
					}
				}
			}
	
			$FSGTS[$d][$h]= $FSGVS[$d][$h] * $FSGHS[$d][$h];

			// Martinez shading model
			$NBSVS[$d][$h] = 0;
			$NBSHS[$d][$h] = 0;
			$NBSTS[$d][$h] = 0;
			$FSEVS[$d][$h] = 0;
			$FSEHS[$d][$h] = 0;
			$FSETS[$d][$h] = 0;
				
			// Effective values:
			if ($FSGTS[$d][$h] > 0)
			{
				$NBSVS[$d][$h] = RoundToZero($FSGVS[$d][$h] * $NBGV + 0.999);
				$NBSHS[$d][$h] = RoundToZero($FSGHS[$d][$h] * $NBGH + 0.999);
				$NBSTS[$d][$h] = $NBSVS[$d][$h] * $NBSHS[$d][$h];
				$FSEVS[$d][$h] = $NBSVS[$d][$h] / $NBGV;
				$FSEHS[$d][$h] = $NBSHS[$d][$h] / $NBGH;
				if (MSC == 1)
				{
					$FSETS[$d][$h] = $FSEVS[$d][$h] * $FSEHS[$d][$h];
				}
				else
				{
					$FSETS[$d][$h] = 1 - (1-$FSGTS[$d][$h]) * (1-(1-$MSO) * $NBSTS[$d][$h] / ($NBT+1)) * (1-$MSP);
				}
			}
				
			// Total shadows (sum of the above)
			// Geometric:
			$FSGTT[$d][$h] = $FSGTE[$d][$h] + $FSGTO[$d][$h] + $FSGTSE[$d][$h] + $FSGTSO[$d][$h] + $FSGTS[$d][$h];
			// Effective
			$FSETT[$d][$h] = $FSETE[$d][$h] + $FSETO[$d][$h] + $FSETSE[$d][$h] + $FSETSO[$d][$h] + $FSETS[$d][$h];
			// Limits the effective factor shade 1 (only relevant with the pessimistic model shadows)
			if ($FSETT[$d][$h] > 1)
			{
				$FSETT[$d][$h] = 1;
			}

			$alfa[$d][$h] = $fis[$d][$h];

			// Correction of the backtracking azimuthal angle:
			// To avoid the projected shadows by a tracker located at E
			if ( ($w[$d][$h] <= 0) && ($FSGTE[$d][$h] > 0) )
			{
				if ($fis[$d][$h] <- pi()/2)
				{				
					$alfa[$d][$h] = $fis[$d][$h] + $RSEV * acos( min(1, $LEO*cos(pi()-$fis[$d][$h])) );
				}
				else
				{
					$alfa[$d][$h] = $fis[$d][$h] + $RSEV * acos( min(1, $LEO*cos($fis[$d][$h])) );
				}
			}

			// To avoid the projected shadows by a tracker located at W
			if ( ($w[$d][$h] >= 0) && ($FSGTO[$d][$h]>0) )
			{
				if ( $fis[$d][$h] > pi()/2 )
				{
					$alfa[$d][$h] = $fis[$d][$h]- $RSEV * acos(min(1, $LEO*cos(pi()-$fis[$d][$h])) );
				}
				else
				{
					$alfa[$d][$h] = $fis[$d][$h]- $RSEV * acos(min(1, $LEO*cos($fis[$d][$h])) );
				}
			}
			
			// Incidence angle. Just before dawn, set the value to zero for the cosine of the angle of incidence.
			// It is a way to eliminate radiaciónen in that period.
			// Coordinates of unit radius vector of the sun in a system of coordinates Oxyz solidarity with the place and 
			// the x axis X, Y, Z pointing respectively to the west, south and the zenith
			$xsol[$d][$h] = cos($gammas[$d][$h]) * sin($fis[$d][$h]);
			$ysol[$d][$h] = cos($gammas[$d][$h]) * cos($fis[$d][$h]);
			$zsol[$d][$h] = sin($gammas[$d][$h]);
				
			// Coordinates of the normal to the surface in the same previous coordinate system
			$xsup[$d][$h] = sin($beta[$d][$h]) * sin($alfa[$d][$h]);
			$ysup[$d][$h] = sin($beta[$d][$h]) * cos($alfa[$d][$h]);
			$zsup[$d][$h] = cos($beta[$d][$h]);
				
			// Incidence angle
			if ( abs($w[$d][$h]) >= abs($ws[$d]) )
			{
				$costetas[$d][$h] = 1;
			}
			else
			{
				$costetas[$d][$h] = $xsol[$d][$h] * $xsup[$d][$h] + $ysol[$d][$h] * $ysup[$d][$h] + $zsol[$d][$h] * $zsup[$d][$h];
			}
				
			// Direct component
			// Resets direct predawn (redundant with the previous statement) and rear incidence
			if ( abs($w[$d][$h]) >= abs($ws[$d]) )
			{
				$B[$d][$h] = 0;
			}
			else
			{
				$B[$d][$h] = $B0[$d][$h] * max(0, $costetas[$d][$h]) / $costetazs[$d][$h];
			}
				 
			// Isotropic, cincumsolar and horizon diffuse irradiance components
			if ( abs($w[$d][$h]) >= abs($ws[$d]) )
			{
				$Diso[$d][$h] = 0;
				$Dcir[$d][$h] = 0;
				$Dhor[$d][$h] = 0;
				$D[$d][$h] = 0;
			}
			else
			{
				$Diso[$d][$h] = $D0[$d][$h] * ( 1 - $k1[$d][$h] ) * ( 1 + cos($beta[$d][$h]) )/2;
				$Dcir[$d][$h] = $D0[$d][$h] * $k1[$d][$h] * max(0, $costetas[$d][$h]) / $costetazs[$d][$h];
				$Dhor[$d][$h] = $D0[$d][$h] * $k2[$d][$h] * sin($beta[$d][$h]);
				$D[$d][$h] = $Diso[$d][$h] + $Dcir[$d][$h] + $Dhor[$d][$h];
			}
				
			// Albedo components
			if ( abs($w[$d][$h]) >= abs($ws[$d]) )
			{
				$R[$d][$h] = 0;
			}
			else
			{
				$R[$d][$h] = $GroundReflectance * $G0[$d][$h] * ( 1-cos($beta[$d][$h]) )/2;
			}
				
			// Global irradiance
			$G[$d][$h] = $B[$d][$h] + $D[$d][$h] + $R[$d][$h];
			
			//Consideration of the effects of the incidence angle
			// Direct correction factor 
			$FCB[$d][$h] = (1 - exp(-$costetas[$d][$h] / $ar) ) / (1 - exp(-1/$ar));
				
			// Diffuse correction factor
			$FCD[$d][$h] = 1 - exp(-1/$ar * ( ( sin($beta[$d][$h]) + (pi() - $beta[$d][$h] - sin($beta[$d][$h])) / (1+cos($beta[$d][$h])) ) * 4/3/pi() + $c2 * pow( ( sin($beta[$d][$h]) + (pi() - $beta[$d][$h] - sin($beta[$d][$h])) / (1+cos($beta[$d][$h])) ), 2) ) );
				
			// Albedo correction factor
			if ($beta[$d][$h] == 0)
			{
				$FCR[$d][$h] = 0;
			}
			else
			{
				$FCR[$d][$h] = 1 - exp(-1/$ar * ( ( sin($beta[$d][$h]) + ( $beta[$d][$h] - sin($beta[$d][$h])) / (1-cos($beta[$d][$h])) )* 4/3/pi() + $c2 * pow( ( sin($beta[$d][$h]) + ( $beta[$d][$h] - sin($beta[$d][$h])) / (1-cos($beta[$d][$h])) ), 2) ) );
			}
				
			// Effective irradiance components
			$Bef[$d][$h] = $B[$d][$h] * $FCB[$d][$h] * $Transm;
			$Def[$d][$h] = ( ($Diso[$d][$h] + $Dhor[$d][$h]) * $FCD[$d][$h] + $Dcir[$d][$h] * $FCB[$d][$h] ) * $Transm;
			$Ref[$d][$h] = $R[$d][$h] * $FCR[$d][$h] * $Transm;
				
			// Effective irradiance
			$Gef[$d][$h] = $Bef[$d][$h] + $Def[$d][$h] + $Ref[$d][$h];
				
			// Effective irradiance after adjacent shadows (E + W)
			// Peak limiting factor to unity shadows
			$FSET[$d][$h] = min(1, $FSETE[$d][$h] + $FSETO[$d][$h]);
			$Befsa[$d][$h] = (1-$FSET[$d][$h]*(1-$RSEV)) * $Bef[$d][$h];
			$Defsa[$d][$h] = ( ($Diso[$d][$h]+$Dhor[$d][$h]) * $FCD[$d][$h] + $Dcir[$d][$h] * $FCB[$d][$h] * (1-$FSET[$d][$h]*(1-$RSEV)) ) * $Transm;
			//$Befsa[$d][$h] = (1-($FSETE[$d][$h] + $FSETO[$d][$h]) * (1-$RSEV)) * $Bef[$d][$h];
			//$Defsa[$d][$h] = (($Diso[$d][$h] + $Dhor[$d][$h]) * $FCD[$d][$h] + $Dcir[$d][$h] * $FCB[$d][$h] * (1- ($FSETE[$d][$h] + $FSETO[$d][$h]) * (1-$RSEV)) ) * $Transm;
			$Gefsa[$d][$h] = $Befsa[$d][$h] + $Defsa[$d][$h] + $Ref[$d][$h];

			// Effective irradiances after total shadows (E+W+SE+SW)
			// Peak limiting factor to unity shadows
			$FSETT[$d][$h] = min(1, $FSETE[$d][$h]+$FSETO[$d][$h]+$FSETSE[$d][$h]+$FSETSO[$d][$h]);
			$Befsayp[$d][$h] = (1-$FSETT[$d][$h]*(1-$RSEV)) * $Bef[$d][$h];
			// $Befsayp[$d][$h] = (1-($FSETE[$d][$h]+$FSETO[$d][$h]+$FSETSE[$d][$h]+$FSETSO[$d][$h])*(1-$RSEV))*$Bef[$d][$h];
			$Defsayp[$d][$h] = ( ($Diso[$d][$h]+$Dhor[$d][$h]) * $FCD[$d][$h] + $Dcir[$d][$h] * $FCB[$d][$h] * (1-$FSETT[$d][$h]*(1-$RSEV)) ) * $Transm;
			// $Defsayp[$d][$h] = ( ($Diso[$d][$h]+$Dhor[$d][$h]) * $FCD[$d][$h] + $Dcir[$d][$h] * $FCB[$d][$h] * ( 1-($FSETE[$d][$h]+$FSETO[$d][$h]+$FSETSE[$d][$h]+$FSETSO[$d][$h]) * (1-$RSEV)) ) * $Transm;
			$Gefsayp[$d][$h] = $Befsayp[$d][$h] + $Defsayp[$d][$h] + $Ref[$d][$h];

		}
	}
	
	///////////////////////////////////////////////////////////////////////////////
	// OUTPUT
	///////////////////////////////////////////////////////////////////////////////

	return $ISI = array(
			'G'	      => $G,
			'B'       => $B,
			'D'       => $D,
			'R'       => $R,
	
			'Gef'     => $Gef,
			'Bef'     => $Bef,
			'Def'     => $Def,
			'Ref'     => $Ref,
	
			'Gefsa'   => $Gefsa,
			'Befsa'   => $Befsa,
			'Defsa'   => $Defsa,
	
			'Gefsayp' => $Gefsayp,
			'Befsayp' => $Befsayp,
			'Defsayp' => $Defsayp);
	
	}

?>			
			