<?php

include_once 'DustDegreeParameters.php';
include_once 'ShadingModelParameters.php';
include_once 'MathFuncs.php';

function TrackerTwoAxisConcentrator($SUNPOS, $w, $ws, $HI, $ANISO, $SITE, $PVGEN, $OPTIONS, $TIME) {
	
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//INITIAL CALCULATIONS
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%	

//INPUTS
	//SUNPOS
	$costetazs=$SUNPOS['costetazs'];
	$cosfis=$SUNPOS['cosfis'];
	$gammas=$SUNPOS['gammas'];
	$tetazs=$SUNPOS['tetazs'];
	$fis=$SUNPOS['fis'];
	//HI
	$G0=$HI['G0'];
	$B0=$HI['B0'];
	for ($d = 0; $d < $TIME['Ndays']; $d++) 
		{
		for ($h = 0; $h < $TIME['Nsteps']; $h++) 
			{
			$D0[$d][$h]=0; //(*)
			}
		}
	//ANISO
	$k1=$ANISO['k1'];
	$k2=$ANISO['k2'];
//PVGEN
	//Common
	$NBGH=$PVGEN['NBGH'];
	$NBGV=$PVGEN['NBGV'];
	$NBT=$PVGEN['NBT'];
	//This tracker
	$Azimut_MAX=$PVGEN['Track2CO']['Azimut_MAX'];
	$Inclination_MAX=$PVGEN['Track2CO']['Inclination_MAX'];
	$LEO=$PVGEN['Track2CO']['LEO'];
	$LNS=$PVGEN['Track2CO']['LNS'];
	$ALARG=$PVGEN['Track2CO']['ALARG'];
	$RSEV=0;	//(*)
	$RSEH=0;	//(*)	
	
//OTHER PARAMETERS
	//Degree of dust, model parameters
	$DDP=DustDegreeParameters($OPTIONS['DustDegree']);
		$ar=$DDP['ar'];
		$c2=$DDP['c2'];
		$Transm=$DDP['Transm'];
	//Shading, model coefficients
	$SMP=ShadingModelParameters($OPTIONS['ShadingModel']);
		$MSP=$SMP['MSP'];
		$MSO=$SMP['MSO'];
		$MSC=$SMP['MSC'];
	//Ground reflectance
	$GroundReflectance=$OPTIONS['GroundReflectance'];

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//CALCULATIONS
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	
	for ($d = 0; $d < $TIME['Ndays']; $d++) 
		{
		for ($h = 0; $h < $TIME['Nsteps']; $h++) 
			{
			// SETTING TRACKER ANGLES
			// The analysis process is: ideal value, design limitations,
			// analysis of shadows, back-tracking corrections
				
			//Ideal values
			$beta[$d][$h] = $tetazs[$d][$h];
			$alfa[$d][$h] = $fis[$d][$h];
			
			//Limits the angle of inclination to constructive maximum value
			if ( $beta[$d][$h] > $Inclination_MAX*pi()/180 )
				{
				$beta[$d][$h]=$Inclination_MAX*pi()/180;
				}
			
			// Limits the azimuthal rotation angle of maximum constructive.
			if ( abs($alfa[$d][$h]) > $Azimut_MAX*pi()/180 )
				{
				$alfa[$d][$h]=$Azimut_MAX*pi()/180*valueSign($alfa[$d][$h]);
				}
			
			// Set horizontal overnight
			if ( abs($w[$d][$h]) > abs($ws[$d]) )
				{
				$beta[$d][$h]=0;
				}
			
			// Calculate the length of the shadow.
			if ( abs($w[$d][$h]) >= abs($ws[$d]) )
				{
				$S1[$d][$h]=0.0000001;
				$S2[$d][$h]=0;
				}else
					{
					$S1[$d][$h]=$ALARG*cos($beta[$d][$h]);
					$S2[$d][$h]=$ALARG*sin($beta[$d][$h])*tan($tetazs[$d][$h]);
					}
			
			$SP[$d][$h]=$S1[$d][$h]+$S2[$d][$h];
			
			// Consideration of the shadows projected by a tracker located at E.
			// geometric:
			// Reset to zero (needed for the program if no shade)
			$FSGVE[$d][$h]=0;
			$FSGHE[$d][$h]=0;
			
			// Factor calculation when shadow
			if ( $w[$d][$h] <= 0 )
				{
				if ( $fis[$d][$h] < (-pi()/2) )
					{
					$FSGHE[$d][$h]=max(0,(1-$LEO*cos(pi()-($fis[$d][$h]))));
					}else
						{
						$FSGHE[$d][$h]=max(0,(1-$LEO*cos($fis[$d][$h])));
						}
				if ( $gammas[$d][$h] > 0.000001 )
					{
					$FSGVE[$d][$h]=max(0,(1+$LEO/$SP[$d][$h]*sin($fis[$d][$h])));
					}
				}
			$FSGTE[$d][$h]= $FSGVE[$d][$h]*$FSGHE[$d][$h];
			
			// Martinez shading model
			$NBSVE[$d][$h]=0;
			$NBSHE[$d][$h]=0;
			$NBSTE[$d][$h]=0;
			$FSEVE[$d][$h]=0;
			$FSEHE[$d][$h]=0;
			$FSETE[$d][$h]=0;
			
			//Effective values:
			if ( $FSGTE[$d][$h] > 0 )
				{
				$NBSVE[$d][$h] = RoundToZero($FSGVE[$d][$h]* $NBGV+ 0.999);
				$NBSHE[$d][$h] = RoundToZero($FSGHE[$d][$h]* $NBGH+ 0.999);
				$NBSTE[$d][$h] = $NBSVE[$d][$h] * $NBSHE[$d][$h];
				$FSEVE[$d][$h] = $NBSVE[$d][$h]/$NBGV;
				$FSEHE[$d][$h] = $NBSHE[$d][$h]/$NBGH;
			
				if ( $MSC == 1 )
					{
					$FSETE[$d][$h]=$FSEVE[$d][$h]*$FSEHE[$d][$h];
					}else
						{	
						$FSETE[$d][$h]=1-(1-$FSGTE[$d][$h])*(1-(1-$MSO)*$NBSTE[$d][$h]/($NBT+1))*(1-$MSP);
						}
				}
			
			// Consideration of the shadows projected by a tracker located at W.
			// geometric:
			$FSGVO[$d][$h]=0;
			$FSGHO[$d][$h]=0;
			
			if ( $w[$d][$h] >= 0 )
				{
				if ( $fis[$d][$h] > pi()/2) //To calculate the shade when the sun rises from behind
					{
					$FSGHO[$d][$h]=max(0,(1-$LEO*cos(pi()-$fis[$d][$h])));
					}else
						{
						$FSGHO[$d][$h]=max(0,(1-$LEO*cos($fis[$d][$h])));
						}
				if ( $gammas[$d][$h] > 0.0000001 )
					{
					$FSGVO[$d][$h]=max(0,(1-$LEO/$SP[$d][$h]*sin($fis[$d][$h])));
					}
			}
			$FSGTO[$d][$h]= $FSGVO[$d][$h]*$FSGHO[$d][$h];
				
			//Martinez shading model
			$NBSVO[$d][$h]=0;
			$NBSHO[$d][$h]=0;
			$NBSTO[$d][$h]=0;
			$FSEVO[$d][$h]=0;
			$FSEHO[$d][$h]=0;
			$FSETO[$d][$h]=0;
			
			//Effective values:
			if ( $FSGTO[$d][$h] > 0 )
				{
				$NBSVO[$d][$h] = RoundToZero($FSGVO[$d][$h]* $NBGV+ 0.999);
				$NBSHO[$d][$h] = RoundToZero($FSGHO[$d][$h]* $NBGH+ 0.999);
				$NBSTO[$d][$h] = $NBSVO[$d][$h] * $NBSHO[$d][$h];
				$FSEVO[$d][$h] = $NBSVO[$d][$h]/$NBGV;
				$FSEHO[$d][$h] = $NBSHO[$d][$h]/$NBGH;
				if ( $MSC == 1 )
					{
					$FSETO[$d][$h]=$FSEVO[$d][$h]*$FSEHO[$d][$h];
					}else
						{
						$FSETO[$d][$h]=1-(1-$FSGTO[$d][$h])*(1-(1-$MSO)*$NBSTO[$d][$h]/($NBT+1))*(1-$MSP);
						}
				}
				
			//if ( $FSGTO[$d][$h] > 0 )
			//	{
			//  NBSVO[$d][$h] = RoundToZero($FSGVO[$d][$h]* $NBGV+ 0.999);
			//  NBSHO[$d][$h] = RoundToZero($FSGHO[$d][$h]* $NBGH+ 0.999);
			//  NBSTO[$d][$h] = $NBSVO[$d][$h] * $NBSHO[$d][$h];
			//	}
			
			//Effective values:
			//if ( $FSGTO[$d][$h] > 0 )
			//	{
			//  $FSETO[$d][$h]=1-(1-$FSGTO[$d][$h])*(1-(1-$MSO)*$NBSTO[$d][$h]/($NBT+1))*(1-$MSP);
			//	}else
			//    	{
			//	  	$FSETO[$d][$h]=0;
			//		}
			
			// Consideration of the shadows projected by a tracker located at SE.
			// geometric:
			// Azimuth at the beginning of the shadow
			$tanfiscsse= -($LEO+cos($fis[$d][$h]))/($LNS+sin($fis[$d][$h]));
			// Azimuth at the end of the shadow
			$tanfisfsse= -($LEO-cos($fis[$d][$h]))/($LNS-sin($fis[$d][$h]));
			//Azimuth of horizontal shade unity
			$tanfiss1se= - $LEO/$LNS;
			//Initial set to zero
			$FSGHSE[$d][$h]=0;
			$FSGVSE[$d][$h]=0;
				
			if ( tan($fis[$d][$h]) >= $tanfiscsse )
				{
				if ( tan($fis[$d][$h]) <= $tanfiss1se )
					{
					$FSGHSE[$d][$h]=1-($tanfiss1se-tan($fis[$d][$h]))/($tanfiss1se-$tanfiscsse);
					$FSGVSE[$d][$h]=max(0,(1-(($LNS*cos($fis[$d][$h])-$LEO*sin($fis[$d][$h]))/$SP[$d][$h])));
					}
				}
			if ( tan($fis[$d][$h]) >= $tanfiss1se )
				{
				if ( tan($fis[$d][$h]) <= $tanfisfsse )
					{
					$FSGHSE[$d][$h]=1-(tan($fis[$d][$h])-$tanfiss1se)/($tanfisfsse-$tanfiss1se);
					$FSGVSE[$d][$h]=max(0,(1-(($LNS*cos($fis[$d][$h])-$LEO*sin($fis[$d][$h]))/$SP[$d][$h])));
					}
				}
			
			$FSGTSE[$d][$h]= $FSGVSE[$d][$h]*$FSGHSE[$d][$h];
				
			//Martinez shading model
			$NBSVSE[$d][$h]=0;
			$NBSHSE[$d][$h]=0;
			$NBSTSE[$d][$h]=0;
			$FSEVSE[$d][$h]=0;
			$FSEHSE[$d][$h]=0;
			$FSETSE[$d][$h]=0;
			
			//Effective values:
			if ( $FSGTSE[$d][$h] > 0 )
				{
				$NBSVSE[$d][$h] = RoundToZero($FSGVSE[$d][$h]* $NBGV+ 0.999);
				$NBSHSE[$d][$h] = RoundToZero($FSGHSE[$d][$h]* $NBGH+ 0.999);
				$NBSTSE[$d][$h] = $NBSVSE[$d][$h] * $NBSHSE[$d][$h];
				$FSEVSE[$d][$h] = $NBSVSE[$d][$h]/$NBGV;
				$FSEHSE[$d][$h] = $NBSHSE[$d][$h]/$NBGH;
				if ( $MSC == 1 )
					{
					$FSETSE[$d][$h]= $FSEVSE[$d][$h]*$FSEHSE[$d][$h];
					}else
						{
						$FSETSE[$d][$h]=1-(1-$FSGTSE[$d][$h])*(1-(1-$MSO)*$NBSTSE[$d][$h]/($NBT+1))*(1-$MSP);
						}
				}

				//if ( $FSGTSE[$d][$h] > 0 )
				// 	{ 
				//	  $NBSVSE[$d][$h] = RoundToZero($FSGVSE[$d][$h]* $NBGV+ 0.999);
				//    $NBSHSE[$d][$h] = RoundToZero($FSGHSE[$d][$h]* $NBGH+ 0.999);
				//    $NBSTSE[$d][$h] = $NBSVSE[$d][$h] * $NBSHSE[$d][$h];
				//
				//	}
				
				//Effective values:
				//if ( $FSGTSE[$d][$h] > 0 )
				//	{
				//  $FSETSE[$d][$h]=1-(1-$FSGTSE[$d][$h])*(1-(1-$MSO)*$NBSTSE[$d][$h]/($NBT+1))*(1-$MSP);
				//	}else
				//		{
				//	 	$FSETSE[$d][$h]=0;
				//		}
				
			// Consideration of the shadows projected by a tracker located at SW.
			// geometric:
			// Azimuth at the beginning of the shadow
				$tanfiscsso= ($LEO-cos($fis[$d][$h]))/($LNS+sin($fis[$d][$h]));
				// Azimuth at the end of the shadow
				$tanfisfsso= ($LEO+cos($fis[$d][$h]))/($LNS-sin($fis[$d][$h]));
				//Azimuth of the horizontal shade unity
				$tanfiss1so= $LEO/$LNS;
				//Initial set to zero
				$FSGHSO[$d][$h]=0;
				$FSGVSO[$d][$h]=0;
				
				if ( tan($fis[$d][$h]) >= $tanfiscsso )
					{	
					if ( tan($fis[$d][$h]) <= $tanfiss1so )
						{
						$FSGHSO[$d][$h]=1-($tanfiss1so-tan($fis[$d][$h]))/($tanfiss1so-$tanfiscsso);
						$FSGVSO[$d][$h]=max(0,(1-(($LNS*cos($fis[$d][$h])+$LEO*sin($fis[$d][$h]))/$SP[$d][$h])));
						}
					}
				if ( tan($fis[$d][$h]) >= $tanfiss1so )
					{
					if ( tan($fis[$d][$h]) <= $tanfisfsso )
						{
						$FSGHSO[$d][$h]=1-(tan($fis[$d][$h])-$tanfiss1so)/($tanfisfsso-$tanfiss1so);
						$FSGVSO[$d][$h]=max(0,(1-(($LNS*cos($fis[$d][$h])+$LEO*sin($fis[$d][$h]))/$SP[$d][$h])));
						}
					}
				
				$FSGTSO[$d][$h]= $FSGVSO[$d][$h]*$FSGHSO[$d][$h];
				
				//Martinez shading model
				$NBSVSO[$d][$h]=0;
				$NBSHSO[$d][$h]=0;
				$NBSTSO[$d][$h]=0;
				$FSEVSO[$d][$h]=0;
				$FSEHSO[$d][$h]=0;
				$FSETSO[$d][$h]=0;
				
				//Effective values:
				if ( $FSGTSO[$d][$h] > 0 )
					{
					$NBSVSO[$d][$h] = RoundToZero($FSGVSO[$d][$h]* $NBGV+ 0.999);
					$NBSHSO[$d][$h] = RoundToZero($FSGHSO[$d][$h]* $NBGH+ 0.999);
					$NBSTSO[$d][$h] = $NBSVSO[$d][$h] * $NBSHSO[$d][$h];
					$FSEVSO[$d][$h] = $NBSVSO[$d][$h]/$NBGV;
					$FSEHSO[$d][$h] = $NBSHSO[$d][$h]/$NBGH;
					if ( $MSC == 1 )
						{
						$FSETSO[$d][$h]=$FSEVSO[$d][$h]*$FSEHSO[$d][$h];
						}else
							{
							$FSETSO[$d][$h]=1-(1-$FSGTSO[$d][$h])*(1-(1-$MSO)*$NBSTSO[$d][$h]/($NBT+1))*(1-$MSP);
							}
					}
				
				
				//if ( $FSGTSO[$d][$h] > 0 )
				//	{
				//  $NBSVSO[$d][$h] = RoundToZero($FSGVSO[$d][$h]* $NBGV+ 0.999);
				//  $NBSHSO[$d][$h] = RoundToZero($FSGHSO[$d][$h]* $NBGH+ 0.999);
				//  $NBSTSO[$d][$h] = $NBSVSO[$d][$h] * $NBSHSO[$d][$h];
				//	}
								
				//Effective values:
				//if ( $FSGTSO[$d][$h] > 0 )
				//	{
				//	$FSETSO[$d][$h]=1-(1-$FSGTSO[$d][$h])*(1-(1-$MSO)*$NBSTSO[$d][$h]/($NBT+1))*(1-$MSP);
				//	}else
				//		{
				//		$FSETSO[$d][$h]=0;
				//		}
				
				// Consideration of the shadows projected by a tracker located at S.
				// geometric:
				// Azimuth at the beginning of the shadow
				$tanfiscss= -1/$LNS;
				// Azimuth at the end of the shadow
				$tanfisfss= 1/$LNS;
				//Azimuth of the horizontal shade unity
				$tanfiss1s= 0;
				//Initial set to zero
				$FSGHS[$d][$h]=0;
				$FSGVS[$d][$h]=0;
				
				if ( abs($w[$d][$h]) < abs($ws[$d]) )
					{
					if ( tan($fis[$d][$h]) >= $tanfiscss )
						{
						if ( tan($fis[$d][$h]) <= $tanfiss1s )
							{
							$FSGHS[$d][$h]=1-($tanfiss1s-tan($fis[$d][$h]))/($tanfiss1s-$tanfiscss);
							$FSGVS[$d][$h]=max(0,(1-(($LNS*cos($fis[$d][$h]))/$SP[$d][$h])));
							}
						}
						if ( tan($fis[$d][$h]) >= $tanfiss1s )
							{
							if ( tan($fis[$d][$h]) <= $tanfisfss )
								{
								$FSGHS[$d][$h]=1-(tan($fis[$d][$h])-$tanfiss1s)/($tanfisfss-$tanfiss1s);
								$FSGVS[$d][$h]=max(0,(1-(($LNS*cos($fis[$d][$h]))/$SP[$d][$h])));
								}
							}
					}
				$FSGTS[$d][$h]= $FSGVS[$d][$h]*$FSGHS[$d][$h];
				
				//Martinez shading model
				$NBSVS[$d][$h]=0;
				$NBSHS[$d][$h]=0;
				$NBSTS[$d][$h]=0;
				$FSEVS[$d][$h]=0;
				$FSEHS[$d][$h]=0;
				$FSETS[$d][$h]=0;
				
				//Effective values:
				if ( $FSGTS[$d][$h] > 0 )
					{
					$NBSVS[$d][$h] = RoundToZero($FSGVS[$d][$h]* $NBGV+ 0.999);
					$NBSHS[$d][$h] = RoundToZero($FSGHS[$d][$h]* $NBGH+ 0.999);
					$NBSTS[$d][$h] = $NBSVS[$d][$h] * $NBSHS[$d][$h];
					$FSEVS[$d][$h] = $NBSVS[$d][$h]/$NBGV;
					$FSEHS[$d][$h] = $NBSHS[$d][$h]/$NBGH;
					if ( $MSC == 1 )
						{
						$FSETS[$d][$h]=$FSEVS[$d][$h]*$FSEHS[$d][$h];
						}else
							{
							$FSETS[$d][$h]=1-(1-$FSGTS[$d][$h])*(1-(1-$MSO)*$NBSTS[$d][$h]/($NBT+1))*(1-$MSP);
							}
					}
				
				//if ( $FSGTS[$d][$h] > 0 )
				//	{
				//   $NBSVS[$d][$h] = RoundToZero($FSGVS[$d][$h]* $NBGV+ 0.999);
				//   $NBSHS[$d][$h] = RoundToZero($FSGHS[$d][$h]* $NBGH+ 0.999);
				//   $NBSTS[$d][$h] = $NBSVS[$d][$h] * $NBSHS[$d][$h];
				//	}
				
				//Effective values:
				//if ( $FSGTS[$d][$h] > 0 )
				//	{
				//  $FSETS[$d][$h]=1-(1-$FSGTS[$d][$h])*(1-(1-$MSO)*$NBSTS[$d][$h]/($NBT+1))*(1-$MSP);
				//	}else
				//		{
				//		$FSETS[$d][$h]=0;
				//		}
				
				// Total Shadows (sum of above)
				// Geometric:
				$FSGTT[$d][$h]=$FSGTE[$d][$h]+$FSGTO[$d][$h]+$FSGTSE[$d][$h]+$FSGTSO[$d][$h]+$FSGTS[$d][$h];
				//Effective
				$FSETT[$d][$h]=$FSETE[$d][$h]+$FSETO[$d][$h]+$FSETSE[$d][$h]+$FSETSO[$d][$h]+$FSETS[$d][$h];
				
				//Correction of the azimuthal backtracking angle:
				//On one hand, this version is based on the conjecture (probably true for NBGV = NBGH) that 
				//the best option to back-tracking corresponds precisely to the lower angle correction.
				//On the other hand, note the choice: when beta, eleccionbeta = 1; when alpha, eleccionbeta = -1.
				//When it refers to the tracker shadow located southeast and southwest eleccionbetaseo use = 1 
				//and eleccionbetaseo= -1 
			
				//Initial set to zero
				$correcionalfae[$d][$h]=0;
				$correcionalfao[$d][$h]=0;
				$correcionalfas[$d][$h]=0;
				$correcionalfase[$d][$h]=0;
				$correcionalfaso[$d][$h]=0;
				
				$correcionalfafe[$d][$h]=0;
				$correcionalfafo[$d][$h]=0;
				$correcionalfafs=0;
				$correcionalfafse[$d][$h]=0;
				$correcionalfafso[$d][$h]=0;
				
				$correcionbetae[$d][$h]=0;
				$correcionbetao[$d][$h]=0;
				$correcionbetas[$d][$h]=0;
				$correcionbetase[$d][$h]=0;
				$correcionbetaso[$d][$h]=0;
				
				$correcionbetafe[$d][$h]=0;
				$correcionbetafo[$d][$h]=0;
				$correcionbetafs[$d][$h]=0;
				$correcionbetafse[$d][$h]=0;
				$correcionbetafso[$d][$h]=0;
				$eleccionbeta[$d][$h]=0;
				$eleccionbetaseo[$d][$h]=0;
			
				// To avoid shadows projected by a tracker located at E
				if ($w[$d][$h] <= 0 )
					{
					if ( ($FSGTE[$d][$h] > 0) && ($gammas[$d][$h] > 0) )
						{
						if ($fis[$d][$h] < -pi()/2)
							{
							$correcionalfae[$d][$h]= acos(min(1,$LEO*cos(pi()-$fis[$d][$h])));
							}else
								{
								$correcionalfae[$d][$h]= acos(min(1,$LEO*cos($fis[$d][$h])));
								}
						$correcionbetae[$d][$h]= acos(min(1,$LEO/$ALARG*sin($gammas[$d][$h])));
						if ($RSEV == 1)
							{
							if ($RSEH == 1)
								{
								if ( $correcionalfae[$d][$h] > $correcionbetae[$d][$h] )
									{
									$correcionalfafe[$d][$h]=0;
									$correcionbetafe[$d][$h]=$correcionbetae[$d][$h];
									$eleccionbeta[$d][$h]=1;
									}else
										{
										$correcionalfafe[$d][$h]=$correcionalfae[$d][$h];
										$correcionbetafe[$d][$h]=0;
										$eleccionbeta[$d][$h]=-1;
										}
								}
							if ( $RSEH == 0 )
								{
								$correcionalfafe[$d][$h]=$correcionalfae[$d][$h];
								$correcionbetafe[$d][$h]=0;
								$eleccionbeta[$d][$h]=-1;
								}
							}
						if ( $RSEV == 0 )
							{
							if ( $RSEH == 1 )
								{
								$correcionalfafe[$d][$h]=0;
								$correcionbetafe[$d][$h]=$correcionbetae[$d][$h];
								$eleccionbeta[$d][$h]=1;
								}
							}
						$alfa[$d][$h]=$fis[$d][$h]+ $correcionalfafe[$d][$h];
						$beta[$d][$h]=$tetazs[$d][$h]- $correcionbetafe[$d][$h];
						}
					}
			
				// To avoid shadows projected by a tracker located at W
				if ( $w[$d][$h] >= 0 )
					{
					if ( ($FSGTO[$d][$h] > 0) && ($gammas[$d][$h] > 0) )
						{
						if ( $fis[$d][$h] > pi()/2 )
							{
							$correcionalfao[$d][$h]= acos(min(1,$LEO*cos(pi()-$fis[$d][$h])));
							}else
								{
								$correcionalfao[$d][$h]= acos(min(1,$LEO*cos($fis[$d][$h])));
								}
						$correcionbetao[$d][$h]= acos(min(1,$LEO/$ALARG*sin($gammas[$d][$h])));
						if ( $RSEV == 1 )
							{
							if ( $RSEH == 1 )
								{
								if ( $correcionalfao[$d][$h] > $correcionbetao[$d][$h] )
									{
									$correcionalfafo[$d][$h]=0;
									$correcionbetafo[$d][$h]=$correcionbetao[$d][$h];
									$eleccionbeta[$d][$h]=1;
									}else
										{
										$correcionalfafo[$d][$h]=$correcionalfao[$d][$h];
										$correcionbetafo[$d][$h]=0;
										$eleccionbeta[$d][$h]=-1;
										}	
								}
							if ( $RSEH == 0 )
								{
								$correcionalfafo[$d][$h]=$correcionalfao[$d][$h];
								$correcionbetafo[$d][$h]=0;
								$eleccionbeta[$d][$h]=-1;
								}
							}
				
						if ( $RSEV == 0 )
							{
							if ( $RSEH == 1 )
								{
								$correcionalfafo[$d][$h]=0;
								$correcionbetafo[$d][$h]=$correcionbetao[$d][$h];
								$eleccionbeta[$d][$h]=1;
								}
							}
						$alfa[$d][$h]= $fis[$d][$h]- $correcionalfafo[$d][$h];
						$beta[$d][$h]= $tetazs[$d][$h]-$correcionbetafo[$d][$h];
						}
					}
				
				// To avoid shadows projected by a tracker located at SE
				// Are cancelled in azimuth because of implant there is a 180-degree turn, but maintain in inclination
				if ( ($RSEH == 1) && ($gammas[$d][$h] > 0) )
					{
					if ( tan($fis[$d][$h]) >= $tanfiscsse )
						{
						if ( tan($fis[$d][$h]) <= $tanfiss1se )
							{
							$correcionbetase[$d][$h]= acos(min(1,(-$LEO*sin($fis[$d][$h])+$LNS*cos($fis[$d][$h]))/$ALARG*sin($gammas[$d][$h])));
							$correcionbetafse[$d][$h]=$correcionbetase[$d][$h];
							$beta[$d][$h]=$tetazs[$d][$h]-$correcionbetafse[$d][$h];
							$eleccionbetaseo[$d][$h]=1;
							}
						}
					if ( tan($fis[$d][$h]) >= $tanfiss1se )
						{
						if ( tan($fis[$d][$h]) <= $tanfisfsse )
							{
							$correcionbetase[$d][$h]= acos(min(1,(-$LEO*sin($fis[$d][$h])+$LNS*cos($fis[$d][$h]))/$ALARG*sin($gammas[$d][$h])));
							$correcionbetafse[$d][$h]=$correcionbetase[$d][$h];
							$beta[$d][$h]=$tetazs[$d][$h]-$correcionbetafse[$d][$h];
							$eleccionbetaseo[$d][$h]=1;
							}
						}
					}
				
				// To avoid shadows projected by a tracker located at SW
				if ( ($RSEH == 1) && ($gammas[$d][$h] > 0) )
					{
					if ( tan($fis[$d][$h]) >= $tanfiscsso )
						{
						if ( tan($fis[$d][$h]) <= $tanfiss1so )
							{
							$correcionbetaso[$d][$h]= acos(min(1,($LEO*sin($fis[$d][$h])+$LNS*cos($fis[$d][$h]))/$ALARG*sin($gammas[$d][$h])));
							$correcionbetafso[$d][$h]=$correcionbetaso[$d][$h];
							$beta[$d][$h]=$tetazs[$d][$h]-$correcionbetafso[$d][$h];
							$eleccionbetaseo[$d][$h]=1;
							}
						}
					if ( tan($fis[$d][$h]) >= $tanfiss1so )
						{	
						if ( tan($fis[$d][$h]) <= $tanfisfsso )
							{
							$correcionbetaso[$d][$h]= acos(min(1,(LEO*sin(fis(h,d))+LNS*cos(fis(h,d)))/ALARG*sin(gammas(h,d))));
							$correcionbetafso[$d][$h]=$correcionbetaso[$d][$h];
							$beta[$d][$h]=$tetazs[$d][$h]-$correcionbetafso[$d][$h];
							$eleccionbetaseo[$d][$h]=1;
							}
						}
					}
					
				// Limits, again, the angle of inclination to constructive maximum value
				if ( $beta[$d][$h] > $Inclination_MAX*pi()/180 )
					{
					$beta[$d][$h]=$Inclination_MAX*pi()/180;
					}
				
				// Limits the azimuthal rotation angle of maximum constructive.
				if ( abs($alfa[$d][$h]) > $Azimut_MAX*pi()/180 )
					{
					$alfa[$d][$h]=$Azimut_MAX*pi()/180*valueSign($alfa[$d][$h]);
					}

				// Incidence angle. For just before dawn, the value of zero for the cosine of the angle of incidence is set. 
				// It is a way to eliminate radiation in that period.

				//Coordinates of unit radius vector of the sun in a system of coordinates Oxyz solidarity with the place and
				//the x axis X, Y, Z pointing respectively to the west, south and the zenith	
				$xsol[$d][$h]=cos($gammas[$d][$h])*sin($fis[$d][$h]);
				$ysol[$d][$h]=cos($gammas[$d][$h])*cos($fis[$d][$h]);
				$zsol[$d][$h]=sin($gammas[$d][$h]);
				
				// Coordinates of the normal to the surface in the same previous coordinate system
				$xsup[$d][$h]=sin($beta[$d][$h])*sin($alfa[$d][$h]);
				$ysup[$d][$h]=sin($beta[$d][$h])*cos($alfa[$d][$h]);
				$zsup[$d][$h]=cos($beta[$d][$h]);
					
				//Incidence angle
				if ( abs($w[$d][$h]) >= abs($ws[$d]) )
					{
					$costetas[$d][$h]=1;
					}else
						{
						$costetas[$d][$h]=$xsol[$d][$h]*$xsup[$d][$h]+$ysol[$d][$h]*$ysup[$d][$h]+$zsol[$d][$h]*$zsup[$d][$h];
						}
				
				//Direct component
				//Resets direct predawn (redundant with the previous statement) and posterior incidence
				if ( abs($w[$d][$h]) >= abs($ws[$d]) )
					{	
					$B[$d][$h]=0;
					}else
						{
						$B[$d][$h]=$B0[$d][$h]*max(0,$costetas[$d][$h])/$costetazs[$d][$h];
						}
			
				//Isotropic, circumsolar and horizon of the diffuse irradiance
				if ( abs($w[$d][$h]) >= abs($ws[$d]) )
					{
					$Diso[$d][$h]=0;
					$Dcir[$d][$h]=0;
					$Dhor[$d][$h]=0;
					$D[$d][$h]=0;
					}else
						{
						$Diso[$d][$h]=$D0[$d][$h]*(1-$k1[$d][$h])*(1+cos($beta[$d][$h]))/2;
						$Dcir[$d][$h]=$D0[$d][$h]*$k1[$d][$h]*max(0,$costetas[$d][$h])/$costetazs[$d][$h];
						$Dhor[$d][$h]=$D0[$d][$h]*$k2[$d][$h]*sin($beta[$d][$h]);
						$D[$d][$h]=$Diso[$d][$h]+$Dcir[$d][$h]+$Dhor[$d][$h];
						}
					
					//Albedo component
					if ( abs($w[$d][$h]) >= abs($ws[$d]) )
						{	
						$R[$d][$h]=0;
						}else
							{
							$R[$d][$h]=0; //(*)
							}
							
				//Global irradiance
				$G[$d][$h]=$B[$d][$h]+$D[$d][$h]+$R[$d][$h];
					
				//Consideration of the effects of the incidence angle
				//Direct irradiation correction factor 
				$FCB[$d][$h]=(1-exp(-$costetas[$d][$h]/$ar))/(1-exp(-1/$ar));
				
				//Diffuse irradiation correction factor
				$FCD[$d][$h]=1-exp(-1/$ar*((sin($beta[$d][$h])+(pi()-$beta[$d][$h]-sin($beta[$d][$h]))/(1+cos($beta[$d][$h])))*4/3/pi()+$c2*pow(sin($beta[$d][$h])+(pi()-$beta[$d][$h]-sin($beta[$d][$h]))/(1+cos($beta[$d][$h])),2)));
				
				//Albedo irradiation correction factor
				if ( $beta[$d][$h] == 0 )
					{
					$FCR[$d][$h]=0;
					}else
						{
						$FCR[$d][$h]=1-exp(-1/$ar*((sin($beta[$d][$h])+($beta[$d][$h]-sin($beta[$d][$h]))/(1-cos($beta[$d][$h])))*4/3/pi()+$c2*pow(sin($beta[$d][$h])+($beta[$d][$h]-sin($beta[$d][$h]))/(1-cos($beta[$d][$h])),2)));
						}
					
				//Effective irradiation components
				$Bef[$d][$h]=$B[$d][$h]*$FCB[$d][$h]*$Transm;
				$Def[$d][$h]=(($Diso[$d][$h]+$Dhor[$d][$h])*$FCD[$d][$h]+$Dcir[$d][$h]*$FCB[$d][$h])*$Transm;
				$Ref[$d][$h]=$R[$d][$h]*$FCR[$d][$h]*$Transm;
							
				//Effective irradiation
				$Gef[$d][$h]=$Bef[$d][$h]+$Def[$d][$h]+$Ref[$d][$h];

				// Effective irradiance after adjacent shadows (E + W)
				// Maximum limitation factor to unity shadows
				$FSET[$d][$h]=min(1, $FSETE[$d][$h]+$FSETO[$d][$h]);
				$Befsa[$d][$h]=(1-$FSET[$d][$h]*(1-$RSEV))*$Bef[$d][$h];
				//$Befsa[$d][$h]=(1-($FSETE[$d][$h]+$FSETO[$d][$h])*(1-$RSEV))*$Bef[$d][$h];
				$Defsa[$d][$h]=(($Diso[$d][$h]+$Dhor[$d][$h])*$FCD[$d][$h]+$Dcir[$d][$h]*$FCB[$d][$h]*(1-$FSET[$d][$h]*(1-$RSEV)))*$Transm;
				//$Defsa[$d][$h]=(($Diso[$d][$h]+$Dhor[$d][$h])*$FCD[$d][$h]+$Dcir[$d][$h]*$FCB[$d][$h]*(1-($FSETE[$d][$h]+$FSETO[$d][$h])*(1-$RSEV)))*$Transm;
				$Gefsa[$d][$h]=$Befsa[$d][$h]+$Defsa[$d][$h]+$Ref[$d][$h];
				
				// Total effective irradiance after shadows (E + W + SE + SW)
				// Maximum limitation factor to unity shadows
				$FSETT[$d][$h]=min(1, $FSETE[$d][$h]+$FSETO[$d][$h]+$FSETSE[$d][$h]+$FSETSO[$d][$h]);
				$Befsayp[$d][$h]=(1-$FSETT[$d][$h]*(1-$RSEV))*$Bef[$d][$h];
				//$Befsayp[$d][$h]=(1-($FSETE[$d][$h]+$FSETO[$d][$h]+$FSETSE[$d][$h]+$FSETSO[$d][$h])*(1-$RSEV))*$Bef[$d][$h];
				$Defsayp[$d][$h]=(($Diso[$d][$h]+$Dhor[$d][$h])*$FCD[$d][$h]+$Dcir[$d][$h]*$FCB[$d][$h]*(1-$FSETT[$d][$h]*(1-$RSEV)))*$Transm;
				//$Defsayp[$d][$h]=(($Diso[$d][$h]+$Dhor[$d][$h])*$FCD[$d][$h]+$Dcir[$d][$h]*$FCB[$d][$h]*(1-($FSETE[$d][$h]+$FSETO[$d][$h]+$FSETSE[$d][$h]+$FSETSO[$d][$h])*(1-$RSEV)))*$Transm;
				$Gefsayp[$d][$h]=$Befsayp[$d][$h]+$Defsayp[$d][$h]+$Ref[$d][$h];
		
			}//end FOR $h Nsteps
		}//end FOR $d Ndays

$ISI= array (
		'G' => $G,
		'B' => $B,
		'D' => $D,
		'R' => $R,
		'Gef' => $Gef,
		'Bef' => $Bef,
		'Def' => $Def,
		'Ref' => $Ref,
		'Gefsa' => $Gefsa,
		'Befsa' => $Befsa,
		'Defsa' => $Defsa,
		'Gefsayp' => $Gefsayp,
		'Befsayp' => $Befsayp,
		'Defsayp' => $Defsayp);
		
return $ISI;

}

?>