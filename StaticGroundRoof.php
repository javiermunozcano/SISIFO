<?php

include_once 'MathFuncs.php';
include_once 'DustDegreeParameters.php';
include_once 'ShadingModelParameters.php';

function StaticGroundRoof($SUNPOS, $w, $ws, $HI, $ANISO, $SITE, $PVGEN, $OPTIONS, $TIME) {

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
	//GroundRoof
	$Inclination_roof=$PVGEN['GroundRoof']['Inclination_roof'];
	$Orientation_roof=$PVGEN['GroundRoof']['Orientation_roof'];
	$Inclination_generator=$PVGEN['GroundRoof']['Inclination_generator'];
	$Orientation_generator=$PVGEN['GroundRoof']['Orientation_generator'];
	$LNS=$PVGEN['GroundRoof']['LNS'];
	$AEO=$PVGEN['GroundRoof']['AEO'];
	$DEO=$PVGEN['GroundRoof']['DEO'];

//OTHER PARAMETERS
	//OptimumSlope
	if($OPTIONS['OptimumSlope'] == 1)
		{
		$Inclination_roof=0;
		$Orientation_roof=0;
		$Inclination_generator = 3.7 +0.69 *$SITE['Latitude'] *valueSign($SITE['Latitude']);
		$Orientation_generator=0;
		}
	
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
			//Conversion from degree to radian
			//Roof position
			$beta_roof = $Inclination_roof *pi()/180;
			$alfa_roof = $Orientation_roof *pi()/180;
			//PV generator position
			$beta_generator = $Inclination_generator *pi()/180;
			$alfa_generator = $Orientation_generator *pi()/180;
				
			//Coordinates of unit vector of the Sun in a Cartesian coordinate system
			//0XYZ, whose origen (0) is placed in the location and with the axis
			//X, Y, Z pointing to West, South and Zenith, respectively.
			$xsol[$d][$h]=cos($gammas[$d][$h]) *sin($fis[$d][$h]);
			$ysol[$d][$h]=cos($gammas[$d][$h]) *cos($fis[$d][$h]);
			$zsol[$d][$h]=sin($gammas[$d][$h]);
			
			//Coordinates of the unit vector of the Sun in a coordinate system 0X'Y'Z'
			//that results of rotating the original system an angle "alfa_roof" around the Z
			//axis.
			$xprimasol[$d][$h] = $xsol[$d][$h] * cos($alfa_roof) - $ysol[$d][$h] * sin($alfa_roof);
			$yprimasol[$d][$h] = $xsol[$d][$h] * sin($alfa_roof) + $ysol[$d][$h] * cos($alfa_roof);
			$zprimasol[$d][$h] = $zsol[$d][$h];
			
			//Coordinates of the unit vector of the Sun in a coordinate system 0X2'XY'XZ'
        	//that results of rotating the original system an angle "alfa_roof" around the Z
        	//axis and and angle "beta_roof" around the X' axis.
			$x2primasol[$d][$h] = $xprimasol[$d][$h];
			$y2primasol[$d][$h] = $yprimasol[$d][$h] * cos($beta_roof) - $zprimasol[$d][$h] * sin($beta_roof);
			$z2primasol[$d][$h] = $yprimasol[$d][$h] * sin($beta_roof) + $zprimasol[$d][$h] * cos($beta_roof);
				
			//Coordinates of the normal unit vector of the surface, in the previous
			//coordinate system
			$x2primasup[$d][$h] = sin($beta_generator) * sin($alfa_generator);
			$y2primasup[$d][$h] = sin($beta_generator) * cos($alfa_generator);
			$z2primasup[$d][$h] = cos($beta_generator);
			
			//Coordinates of the normal unit vector of the surface, in the
			//coordinate system that results of rotating the previous one an angle
			//"beta_roof" (negative) around X2'
			$xprimasup[$d][$h] =  $x2primasup[$d][$h];
			$yprimasup[$d][$h] =  $y2primasup[$d][$h] * cos($beta_roof) + $z2primasup[$d][$h] * sin($beta_roof);
			$zprimasup[$d][$h] = -$y2primasup[$d][$h] * sin($beta_roof) + $z2primasup[$d][$h] * cos($beta_roof);
			
			//Coordinates of the normal unit vector of the surface in 0XYZ coordinate
			//system
			$xsup[$d][$h] =  $xprimasup[$d][$h] * cos($alfa_roof) + $yprimasup[$d][$h] * sin($alfa_roof);
			$ysup[$d][$h] = -$xprimasup[$d][$h] * sin($alfa_roof) + $yprimasup[$d][$h] * cos($alfa_roof);
			$zsup[$d][$h] =  $zprimasup[$d][$h];
			
			//Alfa y beta angles of the PV generator in OXYZ coordinate system
			$beta[$d][$h] = acos($zsup[$d][$h]);
			if ($ysup[$d][$h] == 0)
				{
				$alfa[$d][$h] = 0;
				}else
					{
					$alfa[$d][$h]= atan($xsup[$d][$h] / $ysup[$d][$h] );
					}
			
			//Incidence angle
			if ( abs($w[$d][$h]) >= abs($ws[$d]))
				{	
				$costetas[$d][$h] = 1;
				}else
					{
					$costetas[$d][$h] = $xsol[$d][$h] *$xsup[$d][$h] +$ysol[$d][$h] *$ysup[$d][$h] +$zsol[$d][$h] *$zsup[$d][$h];
					}
			
			//Beam irradiance component
			if ( abs($w[$d][$h] ) >= abs($ws[$d]) )
				{
				$B[$d][$h]=0;
				}else
					{	
					$B[$d][$h]=$B0[$d][$h] *max(0,$costetas[$d][$h]) / $costetazs[$d][$h];
					}
			
			//Diffuse irradiance components: isotropic (Diso), circumsolar (Dcir) and the
			//horizon (Dhor)
			if ( abs($w[$d][$h]) >= abs($ws[$d]) )
				{
				$Diso[$d][$h] =0;
				$Dcir[$d][$h] =0;
				$Dhor[$d][$h] =0;
				$D[$d][$h] =0;
				}else
					{
					$Diso[$d][$h]=$D0[$d][$h] *(1-$k1[$d][$h]) *(1+cos($beta[$d][$h])) /2;
					$Dcir[$d][$h]=$D0[$d][$h] *$k1[$d][$h] *max(0,$costetas[$d][$h]) / $costetazs[$d][$h];
					$Dhor[$d][$h]=$D0[$d][$h] *$k2[$d][$h] *sin($beta[$d][$h]);
					$D[$d][$h] = $Diso[$d][$h]+$Dcir[$d][$h]+ $Dhor[$d][$h];
					}
			
			//Albedo irradiance component
			if ( abs($w[$d][$h]) >= abs($ws[$d]) )
				{
				$R[$d][$h]=0;
				}else
					{
					$R[$d][$h]=$GroundReflectance*$G0[$d][$h]* ( 1-cos($beta[$d][$h]) )/2;
					}
					
			//Global irradiance
			$G[$d][$h]=$B[$d][$h] +$D[$d][$h] +$R[$d][$h];
			
			//Effects of incidence angles
			//Correction of the beam irradiance
			$FCB[$d][$h]=(1-exp(-$costetas[$d][$h]/$ar))/(1-exp(-1/$ar));
			//Correction of the diffuse irradiance (isotropic component)
			$FCD[$d][$h]=1-exp(-1/$ar*((sin($beta[$d][$h])+(pi()-$beta[$d][$h]-sin($beta[$d][$h]))/(1+cos($beta[$d][$h])))*4/3/pi()+$c2*pow(sin($beta[$d][$h])+(pi()-$beta[$d][$h]-sin($beta[$d][$h]))/(1+cos($beta[$d][$h])),2)));
			//Correction of the albedo irradiance
			if ( $beta[$d][$h] == 0 )
				{
				$FCR[$d][$h] = 0;
				}else 
					{
					$FCR[$d][$h]=1-exp(-1/$ar*((sin($beta[$d][$h])+($beta[$d][$h]-sin($beta[$d][$h]))/(1-cos($beta[$d][$h])))*4/3/pi()+$c2*pow(sin($beta[$d][$h])+($beta[$d][$h]-sin($beta[$d][$h]))/(1-cos($beta[$d][$h])),2)));
					}
			
			//Effective irradiance components
			$Bef[$d][$h]=$B[$d][$h] *$FCB [$d][$h] *$Transm;
			$Def[$d][$h]=(($Diso[$d][$h]+$Dhor[$d][$h])*$FCD[$d][$h]+$Dcir[$d][$h]*$FCB[$d][$h])*$Transm;
			$Ref[$d][$h]=$R[$d][$h]*$FCR[$d][$h]*$Transm;
			
			//Effective global irradiance
			$Gef[$d][$h]=$Bef[$d][$h]+$Def[$d][$h]+$Ref[$d][$h];
			
			//Coordinates of the Sun that results of rotating the original an angle
			//"alfa" around the Z axis
			$x3primasol[$d][$h] = $x2primasol[$d][$h] * cos($alfa_generator) - $y2primasol[$d][$h] * sin($alfa_generator);
			$y3primasol[$d][$h] = $x2primasol[$d][$h] * sin($alfa_generator) + $y2primasol[$d][$h] * cos($alfa_generator);
			$z3primasol[$d][$h] = $z2primasol[$d][$h];
			
			//Geometric shading factor caused by the roof and by a row placed
			//towards the South, FSGTS.
			//Initialisation
			$FSGHS[$d][$h]=0;
			$FSGVS[$d][$h]=0;
			$FSGTS[$d][$h]=0;
			$FSGT[$d][$h] =0;
			$FSET[$d][$h]=0;
			//Geometric shading factor caused by the roof itself (FSGT)
			if ( ( $zsol[$d][$h] > 0 ) && ( $z3primasol[$d][$h] <= 0 ) )
				{
				$FSGT[$d][$h] =1;
				$FSET[$d][$h] =1;
				}
			//Shading caused by the a row placed towards the south
			//Shading factor in horizontal direction
			if ( ($y3primasol[$d][$h] > 0) && ($z3primasol[$d][$h] > 0) )
				{
				if ( $x3primasol[$d][$h] < 0 )
					{
					$FSGHS[$d][$h] = max(0 , 1 + $DEO/$AEO + (($LNS-cos($beta_generator))/$AEO)*($x3primasol[$d][$h]/$y3primasol[$d][$h]));
					}else
						{	
						$FSGHS[$d][$h]=max(0 , 1 - $DEO/$AEO -(($LNS-cos($beta_generator))/$AEO)*($x3primasol[$d][$h]/$y3primasol[$d][$h]));
						}
				//Shading factor in the vertical direction
				$FSGVS[$d][$h]=max(0 , 1 - $LNS/(cos($beta_generator) + sin($beta_generator)*($y3primasol[$d][$h]/$z3primasol[$d][$h])));
				}
			//Total geometric shading factor
			$FSGTS[$d][$h]=$FSGHS[$d][$h]*$FSGVS[$d][$h];
			
			//Martinez Shading model
			$NBSVS[$d][$h]=0;
			$NBSHS[$d][$h]=0;
			$NBSTS[$d][$h]=0;
			$FSEVS[$d][$h]=0;
			$FSEHS[$d][$h]=0;
			$FSETS[$d][$h]=0;
			
			//Effective values
			if ( $FSGTS[$d][$h] > 0 )
				{
				$NBSVS[$d][$h] = RoundToZero($FSGVS[$d][$h] * $NBGV + 0.999);
				$NBSHS[$d][$h] = RoundToZero($FSGHS[$d][$h]* $NBGH+ 0.999);
				$NBSTS[$d][$h] = $NBSVS[$d][$h] * $NBSHS[$d][$h];
				$FSEVS[$d][$h] = $NBSVS[$d][$h]/$NBGV;
				$FSEHS[$d][$h] = $NBSHS[$d][$h]/$NBGH;
				
				if ($MSC == 1)
					{
					$FSETS[$d][$h]=$FSEVS[$d][$h]*$FSEHS[$d][$h];
					}else
						{
						$FSETS[$d][$h]=min(1,(1-(1-$FSGTS[$d][$h])*(1-(1-$MSO)*$NBSTS[$d][$h]/($NBT+1))*(1-$MSP)));
						}
				}
			
			//Total shading, caused by the roof and row placed towards the south
			$FSETT[$d][$h]=$FSET[$d][$h]+ $FSETS[$d][$h];
				
			//Irradiances
			$Befsayp[$d][$h]=(1-$FSETT[$d][$h])*$Bef[$d][$h];
			$Defsayp[$d][$h]=(($Diso[$d][$h]+$Dhor[$d][$h])*$FCD[$d][$h]+$Dcir[$d][$h]*$FCB[$d][$h]*(1-$FSETT[$d][$h]))*$Transm;
			$Gefsayp[$d][$h]=$Befsayp[$d][$h]+$Defsayp[$d][$h]+$Ref[$d][$h];
			
			//Other calculations
			$Befsa[$d][$h]=$Befsayp[$d][$h];
			$Defsa[$d][$h]=$Defsayp[$d][$h];
			$Gefsa[$d][$h]=$Gefsayp[$d][$h];
			
				
			}//end FOR $h Nsteps
		}//end FOR $d Ndays

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//OUTPUT
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
		
$ISI= array(
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
