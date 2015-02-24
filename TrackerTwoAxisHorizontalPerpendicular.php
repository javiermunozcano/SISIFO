<?php

include_once 'MathFuncs.php';

function TrackerTwoAxisHorizontalPerpendicular($SUNPOS, $w, $ws, $HI, $ANISO, $SITE, $PVGEN, $OPTIONS, $TIME) {

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
	$D0=$HI['D0'];
	
	//ANISO
	$k1=$ANISO['k1'];
	$k2=$ANISO['k2'];
	//PVGEN
		//Common
		$NBGH=$PVGEN['NBGH'];
		$NBGV=$PVGEN['NBGV'];
		$NBT=$PVGEN['NBT'];
		//This tracker
		$LEO=$PVGEN['Track2HP']['LEO'];
		$LNS=$PVGEN['Track2HP']['LNS'];
		$ALARG=$PVGEN['Track2HP']['ALARG'];
		$Rotation_MAX=$PVGEN['Track2HP']['Rotation_MAX'];
		$Axis_inclination=$PVGEN['Track2HP']['Axis_inclination'];
		$Inclination_MAX=$PVGEN['Track2HP']['Inclination_MAX'];
		$RSES=$PVGEN['Track2HP']['RSES'];
		$RSEH=$PVGEN['Track2HP']['RSEH'];
		//Conversion to radian
		$rotMAX=$Rotation_MAX*pi()/180;
		$inclieje=$Axis_inclination*pi()/180;
		$incliMAX=$Inclination_MAX*pi()/180;
	
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
		
		//Sets the coordinates of the unit radius vector of the sun in a system of coordinates Oxyz 
		//solidarity with the place and the x axis X, Y, Z pointing respectively to the west, south 
		//and the zenith
		$xsol[$d][$h]=cos($gammas[$d][$h])*sin($fis[$d][$h]);
		$ysol[$d][$h]=cos($gammas[$d][$h])*cos($fis[$d][$h]);
		$zsol[$d][$h]=sin($gammas[$d][$h]);
		
		//Sets the coordinates of the unit radius vector of the sun in a coordinate system Ox'y'z'
		//solidarity with the place, but with an axis inclined at an angle inclieje and, therefore, 
		//with the x axis X', Y ', Z' pointing respectively west (x '), integral with the axis of 
		//rotation (y') and plane normal x'y '(z')
		$rosol[$d][$h]=sqrt( pow($ysol[$d][$h],2)+ pow($zsol[$d][$h],2) );
		$tetasol[$d][$h]=atan($zsol[$d][$h]/$ysol[$d][$h]);
		
		$xprimasol[$d][$h]=$xsol[$d][$h];
		$yprimasol[$d][$h]=$rosol[$d][$h]*cos($tetasol[$d][$h]+$inclieje);
		$zprimasol[$d][$h]=$rosol[$d][$h]*sin($tetasol[$d][$h]+$inclieje);
		
		//CONSIDERATION PRINCIPAL AXIS MOTION, +
		//// Angle of rotation axis (rotNS)
		if ( $zprimasol[$d][$h] == 0 )
			{
			$rotNS[$d][$h]=pi()/2*valueSign($fis[$d][$h]);
			}else
				{
				$rotNS[$d][$h]=abs(atan($xprimasol[$d][$h]/$zprimasol[$d][$h]))*valueSign($fis[$d][$h]);
				}
		
		//Limits the rotation angle to a maximum of 90 degrees
		if ( abs($rotNS[$d][$h]) > pi()/2 )
			{
			$rotNS[$d][$h]=pi()/2*valueSign($fis[$d][$h]);
			}
		
		//Limits the rotation angle to the constructive value, the result is called rotNSC
		$rotNSC[$d][$h]=$rotNS[$d][$h];
		if ( abs($rotNS[$d][$h]) > $rotMAX )
			{
			$rotNSC[$d][$h]= $rotMAX*valueSign($fis[$d][$h]);
			}
		
		//Shadow testing and, if necessary, corrected by "back-tracking" on the horizontal axis, RSEH.
		$sombra[$d][$h]=1/cos($rotNS[$d][$h]);
		$rotNSBTC[$d][$h]=$rotNS[$d][$h]-$RSEH*acos(min(1,$LEO*cos($rotNS[$d][$h])))*valueSign($w[$d][$h]);
		
		//CONSIDERATION OF SECOND AXIS MOTION
		//Coordinates of the Sun in a supportive system with the surface associated to the principal axis, ie,
		//the normal points south at noon, and rotates with the shaft throughout the day, including the possibility
		//of backtracking (RSEH = 1)
		$x2primasol[$d][$h]=$xprimasol[$d][$h]*cos($rotNSBTC[$d][$h])- $zprimasol[$d][$h]*sin($rotNSBTC[$d][$h]);
		$y2primasol[$d][$h]=$yprimasol[$d][$h];
		$z2primasol[$d][$h]=$xprimasol[$d][$h]*sin($rotNSBTC[$d][$h])+$zprimasol[$d][$h]*cos($rotNSBTC[$d][$h]);
		
		//Angle of inclination of the surface (rotation about the second axis) perpendicular to the projection of 
		//the Sun on the plane Z''Y '
		if ( $z2primasol[$d][$h] > 0 )
			{
			$betasupideal[$d][$h]=atan($y2primasol[$d][$h]/$z2primasol[$d][$h]);
			}else
				{
				$betasupideal[$d][$h]=0;
				}
		// Reset overnight, to facilitate the presentation
		if ( $zsol[$d][$h] < 0 )
			{
			$betasupideal[$d][$h]=0;
			}
			
		// Set initial zero shade factors and correction for backtracking
		$FSGHS[$d][$h]=0;
		$FSGVS[$d][$h]=0;
		$CBTP[$d][$h]=0;
		
		//Analysis shadows between rows, ie, the row that is the South:
		//Coordinates of the Sun in a solidary system with the receiving surface (equation 3.77)
		$x3primasol[$d][$h]= $x2primasol[$d][$h];
		$y3primasol[$d][$h]= $y2primasol[$d][$h]*cos($betasupideal[$d][$h])-$z2primasol[$d][$h]*sin($betasupideal[$d][$h]);
	
		//Avoid division by zero
		if ( $x3primasol[$d][$h] == 0 )
			{
			$x3primasol[$d][$h] = 0.000001;
			}
		
		//There are shadows if (equation 3.78)
		if ( ($LNS*cos($betasupideal[$d][$h])- $ALARG) < abs($y3primasol[$d][$h]/$x3primasol[$d][$h]) )
			{
			//Lengths and shade factors
			$sombrapos[$d][$h]=$ALARG*1/cos($betasupideal[$d][$h]);
			$sombraposv[$d][$h]=$ALARG + abs($y3primasol[$d][$h]/$x3primasol[$d][$h]);
			$FSGHS[$d][$h] = max(0,(1-$LNS/$sombrapos[$d][$h]));
			$FSGVS[$d][$h] = max(0,1-$LNS*cos($betasupideal[$d][$h])/$sombraposv[$d][$h]);
			//Angle correction
			$CBTP[$d][$h]=acos(min(1,($LNS/$ALARG*cos($betasupideal[$d][$h]))));
			}
			
			//Tilt angle after backtracking
			$betasupBT[$d][$h]=(abs($betasupideal[$d][$h])-$CBTP[$d][$h]*$RSES)*valueSign($betasupideal[$d][$h]);
			//Removing shadows if backtracking
			if ($RSES == 1)
				{
				$FSGHS[$d][$h]=0;
				}
			
			//Limit angle to constructive value
			if ( abs($betasupBT[$d][$h]) > $incliMAX )
				{
				$betasupBTC[$d][$h]=$incliMAX*valueSign($betasupBT[$d][$h]);
				}else
					{
					$betasupBTC[$d][$h]=$betasupBT[$d][$h];
					}
			
			// Scan the shadows again:
			// Coordinates of the Sun in a solidary system with the receptor surface
			$y3primasol[$d][$h]= $y2primasol[$d][$h]*cos($betasupBTC[$d][$h])-$z2primasol[$d][$h]*sin($betasupBTC[$d][$h]);
			//There are shadows if
			if ( ($LNS*cos($betasupBTC[$d][$h])- $ALARG) < abs($y3primasol[$d][$h]/$x3primasol[$d][$h]) )
				{
				//Lengths and shade factors
				$sombrapos[$d][$h]= $ALARG*1/cos($betasupBTC[$d][$h]);
				$sombraposv[$d][$h]=$ALARG + abs($y3primasol[$d][$h]/$x3primasol[$d][$h]);
				$FSGHS[$d][$h] = max(0,(1-$LNS/$sombrapos[$d][$h]));
				$FSGVS[$d][$h] = max(0,1-$LNS*cos($betasupBTC[$d][$h])/$sombraposv[$d][$h]);
				}
			
			//Full and effective shadow projected by the south row
			$FSGTS[$d][$h]=$FSGHS[$d][$h]*$FSGVS[$d][$h];
			
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
			
		//Limit angle to constructive value
		if ( abs($betasupBT[$d][$h]) > $incliMAX )
			{
			$betasupBTC[$d][$h]=$incliMAX*valueSign($betasupBT[$d][$h]);
			}else
				{
				$betasupBTC[$d][$h]=$betasupBT[$d][$h];
				}
			
		//Surface coordinates in the XYZ system
		$xsup[$d][$h]= cos($betasupBTC[$d][$h])*sin($rotNSBTC[$d][$h]);
		$ysup[$d][$h]= sin($betasupBTC[$d][$h]);
		$zsup[$d][$h]= cos($betasupBTC[$d][$h])*cos($rotNSBTC[$d][$h]);
			
		//Inclination of the receptor surface
		$beta[$d][$h]=acos($zsup[$d][$h]);
			
		//Azimuth of the receptor surface
		if ( $ysup[$d][$h] == 0 )
			{
			$alfa[$d][$h]=pi()/2*valueSign($w[$d][$h]);
			}else
				{
				$alfa[$d][$h]=atan($xsup[$d][$h]/$ysup[$d][$h]);
				}
			
		//Incidence angle
		if ( abs($w[$d][$h]) >= abs($ws[$d]))
			{
			$costetas[$d][$h]=1;
			}else
				{
				$costetas[$d][$h]=$xsol[$d][$h]*$xsup[$d][$h]+$ysol[$d][$h]*$ysup[$d][$h]+$zsol[$d][$h]*$zsup[$d][$h];
				}
	
		//Direct component
		if ( abs($w[$d][$h]) >= abs($ws[$d]))
			{
			$B[$d][$h]=0;
			}else
				{
				$B[$d][$h]=$B0[$d][$h]*max(0,$costetas[$d][$h])/$costetazs[$d][$h];
				}
	
		//Isotropic, circumsolar and horizon components of the diffuse irradiance
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
				$R[$d][$h]=$GroundReflectance*$G0[$d][$h]*(1-cos($beta[$d][$h]))/2;
				}
	
		//Global irradiation
		$G[$d][$h]=$B[$d][$h]+$D[$d][$h]+$R[$d][$h];
	
		//Consideration of the effects of the incidence angle
		// Angle correction factor of direct irradiance
		if ( abs($w[$d][$h]) >= abs($ws[$d]) )
			{
			$FCB[$d][$h]=0;
			}else
				{
				$FCB[$d][$h]= (1-exp(-$costetas[$d][$h]/$ar))/(1-exp(-1/$ar));
				}
		
		//Angle correction factor of the diffuse irradiance
		if ( abs($w[$d][$h]) >= abs($ws[$d]) )
			{
			$FCD[$d][$h]=0;
			}else
				{
				$FCD[$d][$h]=1-exp(-1/$ar*((sin($beta[$d][$h])+(pi()-$beta[$d][$h]-sin($beta[$d][$h]))/(1+cos($beta[$d][$h])))*4/3/pi()+$c2*pow(sin($beta[$d][$h])+(pi()-$beta[$d][$h]-sin($beta[$d][$h]))/(1+cos($beta[$d][$h])),2)));
				}
			
		//Angular correction albedo factor
		if ( abs($w[$d][$h]) >= abs($ws[$d]))
			{
			$FCR[$d][$h]=0;
			}else
				{
				if ( $beta[$d][$h] == 0)
					{
					$FCR[$d][$h]=0;
					}else
						{
						$FCR[$d][$h]=1-exp(-1/$ar*((sin($beta[$d][$h])+($beta[$d][$h]-sin($beta[$d][$h]))/(1-cos($beta[$d][$h])))*4/3/pi()+$c2*pow(sin($beta[$d][$h])+($beta[$d][$h]-sin($beta[$d][$h]))/(1-cos($beta[$d][$h])),2)));
						}
				}
		
		//Effective irradiance components
		$Bef[$d][$h]=$B[$d][$h]*$FCB[$d][$h]*$Transm;
		$Def[$d][$h]=(($Diso[$d][$h]+$Dhor[$d][$h])*$FCD[$d][$h]+$Dcir[$d][$h]*$FCB[$d][$h])*$Transm;
		$Ref[$d][$h]=$R[$d][$h]*$FCR[$d][$h]*$Transm;
		
		//Effective irradiation
		$Gef[$d][$h]=$Bef[$d][$h]+$Def[$d][$h]+$Ref[$d][$h];
		
		// Consideration of shadows projected by the "axes" to E and W
		// Value unit to the horizontal dimension of the shadow
		$FSGHE[$d][$h]=1;
		$FSGHO[$d][$h]=1;
		// Vertical dimension shaded by tracker located east, FSGVE, which is relevant only in the morning
		if ( $w[$d][$h] <= 0 )
			{
			$FSGVO[$d][$h]=0;
			if ( abs($rotNS[$d][$h]) == pi()/2 )
				{
				$FSGVE[$d][$h]=1;
				}else
					{
					$FSGVE[$d][$h]=(1-$RSEH)*max(0,(1-$LEO/$sombra[$d][$h]));
					}
					if ( abs($w[$d][$h]) >= abs($ws[$d]))
						{					
						$FSGVE[$d][$h]=0;
						}
			}
		
		// Vertical dimension shaded by tracker located West FSGVO, which is only relevant in the afternoon
		if ( $w[$d][$h] > 0 )
			{
			$FSGVE[$d][$h]=0;
			if ( abs($rotNS[$d][$h]) == pi()/2 )
				{
				$FSGVO[$d][$h]=1;
				}else
					{
					$FSGVO[$d][$h]=(1-$RSEH)*max(0,(1-$LEO/$sombra[$d][$h]));
					}
			if ( abs($w[$d][$h]) >= abs($ws[$d]))
				{
				$FSGVO[$d][$h]=0;
				}
			}

		// Total geometric shadow Factor E and W
		$FSGTE[$d][$h]=$FSGVE[$d][$h]*$FSGHE[$d][$h];
		$FSGTO[$d][$h]=$FSGVO[$d][$h]*$FSGHO[$d][$h];
		
		//Martinez shading model
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
			$SEHE[$d][$h] = $NBSHE[$d][$h]/$NBGH;
			if ( $MSC == 1 )
				{
				$FSETE[$d][$h]=$FSEVE[$d][$h]*$FSEHE[$d][$h];
				}else
					{
					$FSETE[$d][$h]=1-(1-$FSGTE[$d][$h])*(1-(1-$MSO)*$NBSTE[$d][$h]/($NBT+1))*(1-$MSP);
					}
			}
		
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
			
		$FSGTEO[$d][$h]=$FSGTE[$d][$h]+$FSGTO[$d][$h];
			
		//Calculation of the effective shadow
			
		$FSETEO[$d][$h] =$FSETE[$d][$h]+$FSETO[$d][$h];
			
		$Befsa[$d][$h]=(1-$FSETEO[$d][$h])*$Bef[$d][$h];
		$Defsa[$d][$h]=(($Diso[$d][$h]+$Dhor[$d][$h])*$FCD[$d][$h]+$Dcir[$d][$h]*$FCB[$d][$h]*(1-$FSETEO[$d][$h]))*$Transm;
		$Gefsa[$d][$h]=$Befsa[$d][$h]+$Defsa[$d][$h]+$Ref[$d][$h];
		
		// For total shadow, we agree that if there are simultaneous shadows, ie, once projected to the south row and one of the axes,
		// is null the generation corresponding to direct irradiance and diffuse circumsolar component
		$Befsayp[$d][$h]=(1-$FSETS[$d][$h])*$Befsa[$d][$h];
		$Defsayp[$d][$h]=(($Diso[$d][$h]+$Dhor[$d][$h])*$FCD[$d][$h]+$Dcir[$d][$h]*$FCB[$d][$h]*(1-$FSETEO[$d][$h])*(1-$FSETS[$d][$h]))*$Transm;			
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