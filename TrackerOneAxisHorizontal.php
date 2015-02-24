<?php

// Include function files
include_once 'DustDegreeParameters.php';
include_once 'ShadingModelParameters.php';

function TrackerOneAxisHorizontal($SUNPOS, $w, $ws, $HI, $ANISO, $SITE, $PVGEN, $OPTIONS, $TIME)
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
	$LEO = $PVGEN['Track1H']['LEO'];
	$Rotation_MAX = $PVGEN['Track1H']['Rotation_MAX'];
	$Axis_orientation = $PVGEN['Track1H']['Axis_orientation'];
	$Axis_inclination = $PVGEN['Track1H']['Axis_inclination'];
	$LNS = $PVGEN['Track1H']['LNS'];
	$Inclination_module = $PVGEN['Track1H']['Inclination_module'];
	$RSEH = $PVGEN['Track1H']['RSEH'];
	// Conversion to radian
	$rotMAX = $Rotation_MAX * pi()/180;
	$alfa_eje = $Axis_orientation * pi()/180;
	$beta_eje = $Axis_inclination * pi()/180;
	$beta_mod = $Inclination_module * pi()/180;
	
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
			
			//Sets the coordinates of the unit radius vector of the sun in a system 
			//of coordinates Oxyz solidarity with the place and the x axis X, Y, Z 
			//pointing respectively to the west, south and the zenith
			$xsol[$d][$h] = cos($gammas[$d][$h]) * sin($fis[$d][$h]);
			$ysol[$d][$h] = cos($gammas[$d][$h]) * cos($fis[$d][$h]);
			$zsol[$d][$h] = sin($gammas[$d][$h]);
			
			//SOl coordinates in a supportive system with the surface of the generator 
			//at noon, ie when the edge of the generator is horizontal. This system is 
			//to turn an alfa_eje angle around the Z axis, and another beta_eje + beta_mod, 
			//about the x-axis'
			$x2primasol[$d][$h] = $xsol[$d][$h] * cos($alfa_eje) - $ysol[$d][$h] * sin($alfa_eje) * cos($beta_eje + $beta_mod) + $zsol[$d][$h] * sin($alfa_eje) * sin($beta_eje + $beta_mod);
			$y2primasol[$d][$h] = $xsol[$d][$h] * sin($alfa_eje) + $ysol[$d][$h] * cos($alfa_eje) * cos($beta_eje + $beta_mod) - $zsol[$d][$h] * cos($alfa_eje) * sin($beta_eje + $beta_mod);
			$z2primasol[$d][$h] = $ysol[$d][$h] * sin($beta_eje + $beta_mod) + $zsol[$d][$h] * cos($beta_eje + $beta_mod);

			// Rotating axis angle (rotNS)
			if ( $z2primasol[$d][$h] == 0)
			{
				$rotNS[$d][$h] = pi()/2 * valueSign($x2primasol[$d][$h]);
			}
			else
			{
				$rotNS[$d][$h] = atan($x2primasol[$d][$h]/$z2primasol[$d][$h]);
			}
			
			// Backtracking correction on the horizontal axis.
			// $RSEH. The result is called RNSCBT
			$rotNSCBT[$d][$h] = $rotNS[$d][$h] - $RSEH * acos( min(1, $LEO * cos($rotNS[$d][$h])) ) * valueSign($x2primasol[$d][$h]);
			
			// Limits the rotation angle to 90 degrees or constructive value.
			// The result is called rotNSCBTyC.
			$rotNSCBTyC[$d][$h] = $rotNSCBT[$d][$h];
			// Limit to 90 degrees
			if (abs($rotNSCBTyC[$d][$h]) > pi()/2)
			{
				$rotNSCBTyC[$d][$h] = pi()/2 * valueSign($x2primasol[$d][$h]);
			}
			// Limit to constructive value
			if (abs($rotNSCBTyC[$d][$h]) > $rotMAX)
			{	
				$rotNSCBTyC[$d][$h] = $rotMAX * valueSign($x2primasol[$d][$h]);
			}
	
			// Surface coordinate system in solidarity with the generator at noon
			$x2primasup[$d][$h] = sin($rotNSCBTyC[$d][$h]);
			$y2primasup[$d][$h] = 0;
			$z2primasup[$d][$h] = cos($rotNSCBTyC[$d][$h]);

			// %Incidence angle
	        if ( abs($w[$d][$h]) >= abs($ws[$d]) )
	        {
	        	$costetas[$d][$h] = 0;
	        }
	        else
			{	
	        	$costetas[$d][$h] = $x2primasol[$d][$h] * $x2primasup[$d][$h] + $y2primasol[$d][$h] * $y2primasup[$d][$h] + $z2primasol[$d][$h] * $z2primasup[$d][$h];
	        }
	       
	        // Surface coordinate system in solidarity with the place.
	        // Result of rotating the OX''Y''Z 'angle - (+ beta_mod beta_eje) in
	        // around the X axis''
			$xprimasup[$d][$h] = $x2primasup[$d][$h];
			$yprimasup[$d][$h] = $y2primasup[$d][$h] * cos($beta_eje + $beta_mod) + $z2primasup[$d][$h] * sin($beta_eje + $beta_mod);
			$zprimasup[$d][$h] = -$y2primasup[$d][$h] * sin($beta_eje + $beta_mod) + $z2primasup[$d][$h] * cos($beta_eje + $beta_mod);
			// Followed by rotating -alfa_eje an angle around the axis Z '
			$xsup[$d][$h] = $xprimasup[$d][$h] * cos($alfa_eje) + $yprimasup[$d][$h] * sin($alfa_eje);
			$ysup[$d][$h] = -$xprimasup[$d][$h] * sin($alfa_eje) + $zprimasup[$d][$h] * cos($alfa_eje);
			$zsup[$d][$h] = $zprimasup[$d][$h];

			// Inclination of the receiving surface
			$beta[$d][$h] = acos($zsup[$d][$h]);
			// Azimuth of the receiving surface
			if ($ysup[$d][$h] == 0)
			{
				$alfa[$d][$h] = pi()/2 * valueSign($w[$d][$h]);
			}
			else
			{
				$alfa[$d][$h] = atan($xsup[$d][$h] / $ysup[$d][$h]);
			}

			// Components of the global irradiance incident on the receiving surface
			// Direct irradiance
			if ( abs($w[$d][$h]) >= abs($ws[$d]) )
			{
				$B[$d][$h] = 0;
			}
			else
			{
				$B[$d][$h] = $B0[$d][$h] * max(0, $costetas[$d][$h]) / $costetazs[$d][$h];
			}

			// Diffuse irradiance (isotropic and circumsolar components)
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
				
			// Albedo component
			if ( abs($w[$d][$h]) >= abs($ws[$d]) )
			{
				$R[$d][$h] = 0;
			}
			else
			{
				$R[$d][$h] = $GroundReflectance * $G0[$d][$h] * ( 1-cos($beta[$d][$h]) )/2;
			}
			
			// Glogal incident irradiation
			$G[$d][$h] = $B[$d][$h] + $D[$d][$h] + $R[$d][$h];
				
			// Consideration of the effects of the incidence angle
			// Direct irradiation correction factor
			if ( abs($w[$d][$h]) >= abs($ws[$d]) )
			{
				$FCB[$d][$h] = 0;
			}
			else
			{
				$FCB[$d][$h] = (1-exp(-$costetas[$d][$h]/$ar)) / (1-exp(-1/$ar));
			}

			// Diffuse irradiation correction factor
			if ( abs($w[$d][$h]) >= abs($ws[$d]) )
			{
				$FCD[$d][$h] = 0;
			}
			else
			{
				$FCD[$d][$h] = 1 - exp(-1/$ar * ( ( sin($beta[$d][$h]) + (pi()-$beta[$d][$h]-sin($beta[$d][$h])) / (1+cos($beta[$d][$h])) ) *4/3/pi() + $c2 * pow( ( sin($beta[$d][$h]) + (pi()-$beta[$d][$h]-sin($beta[$d][$h])) / (1+cos($beta[$d][$h]))), 2) ) );
			}

			// Albedo irradiation correction factor
			if ( abs($w[$d][$h]) >= abs($ws[$d]) )
			{
				$FCR[$d][$h] = 0;
			}
			elseif ($beta[$d][$h] == 0)
			{
				$FCR[$d][$h] = 0;
			}
			else
			{
				$FCR[$d][$h] = 1 - exp( -1/$ar * ( ( sin($beta[$d][$h]) + ($beta[$d][$h]-sin($beta[$d][$h])) / (1-cos($beta[$d][$h])) ) *4/3/pi() + $c2 * pow( ( sin($beta[$d][$h]) + ($beta[$d][$h]-sin($beta[$d][$h])) / (1-cos($beta[$d][$h])) ), 2 ) ) );
			}
			//Effective irradiance components
			$Bef[$d][$h] = $B[$d][$h] * $FCB[$d][$h] * $Transm;
			$Def[$d][$h] = ( ($Diso[$d][$h] + $Dhor[$d][$h]) * $FCD[$d][$h] + $Dcir[$d][$h] * $FCB[$d][$h] ) * $Transm;
			$Ref[$d][$h] = $R[$d][$h] * $FCR[$d][$h] * $Transm;
			
			// Effective global irradiance
			$Gef[$d][$h] = $Bef[$d][$h] + $Def[$d][$h] + $Ref[$d][$h];
				
			//Shadows EW (can if there are no back-tracking and monitoring is no ideal or is limited by construction)
			//Length of the shadow on the x-axis ''
			$sombra[$d][$h] = 0.00000001;
			if ($z2primasol[$d][$h] == 0)
			{
				$z2primasol[$d][$h] = 0.000001;
			}
			else
			{
				$sombra[$d][$h] = cos($rotNSCBTyC[$d][$h]) + sin($rotNSCBTyC[$d][$h]) * $x2primasol[$d][$h] / $z2primasol[$d][$h];
			}

			// Components of effective irradiance, CONSIDERING THE SHADOWS PLANNED BY THE ADJACENT TRACKERS,

			// Value unit to the horizontal dimension of the shadow
			$FSGHE[$d][$h] = 1;
			$FSGHO[$d][$h] = 1;
			$FSGHS[$d][$h] = 1;

			// Reset to zero the shadow factor vertically
			$FSGVE[$d][$h] = 0;
			$FSGVO[$d][$h] = 0;
			$FSGVS[$d][$h] = 0;

			// Vertical dimension shaded tracker located east, FSGVE, which is relevant only in the morning
			if ($w[$d][$h] <= 0)
			{
				// $FSGVE[$d][$h] = 0;
				if ( abs($rotNSCBTyC[$d][$h]) == pi()/2)
				{
					$FSGVE[$d][$h] = 1;
				}
				else
				{
					$FSGVE[$d][$h] = (1-$RSEH) * max( 0, (1-$LEO/$sombra[$d][$h]) );
				}
				
				if (abs($w[$d][$h]) >= abs($ws[$d]))
				{                 
				$FSGVE[$d][$h] = 0;
				}
			}
			// Total shadow factor projected by the tracker located east
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


			// Vertical dimension shaded tracker located West, FSGVO
			// Only relevant in the afternoon
			if ($w[$d][$h] > 0)
			{
				//$FSGVO[$d][$h]=0;
				if (abs($rotNSCBTyC[$d][$h]) == pi()/2)
				{
					$FSGVO[$d][$h] = 1;
				}
				else	
				{
					$FSGVO[$d][$h] = (1-$RSEH) * max(0,(1-$LEO/$sombra[$d][$h]));
				}
				if (abs($w[$d][$h]) >= abs($ws[$d]))
				{
					$FSGVO[$d][$h] = 0;
				}
			}

			// Total shadows factor  projected by the tracker located west
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

			// Total geometric factor shadow projected by adjacent trackers
			$FSGTEO[$d][$h] = $FSGTE[$d][$h] + $FSGTO[$d][$h];
			
			// Effective shadow factor, projected by adjacent trakcers
			$FSETEO[$d][$h] = $FSETE[$d][$h] + $FSETO[$d][$h];

			$Befsa[$d][$h] = (1-$FSETEO[$d][$h]) * $Bef[$d][$h];
			$Defsa[$d][$h] = ( ($Diso[$d][$h] + $Dhor[$d][$h]) * $FCD[$d][$h] + $Dcir[$d][$h] * $FCB[$d][$h] * (1-$FSETEO[$d][$h]) ) * $Transm;
			$Gefsa[$d][$h] = $Befsa[$d][$h] + $Defsa[$d][$h] + $Ref[$d][$h];

			// The following is from the previous version, and can be generalized in future versions
			// Consideration of shadows projected by ROWS LOCATED SOUTH. Strictly, the code implemented here
			// is valid only when there is no back-tracking, and when there is only a deviation from the
			// horizontal, either module on the horizontal axis, or axis on horizontal ground. That is, 
			// when only Bgen angle or Beje are nonzero angles. The angle of the sun with the horizontal, 
			// measured on the normal to the surface of the receiving plane is called B. The length of the 
			// shadow in the posterior direction, is called sombrapos (h, d); To consider the two deviations 
			//in a single package instructions, you must create a new parameter, resulting from adding inclieje
			// and inclimod, called inclihorizonte. Shadow factor is called FSGTS (h, d). An "if" is added to 
			//avoid the hassle of divisions by zero.
	
			$tanB[$d][$h] = (sqrt( (pow(($xsol[$d][$h]),2)) + (pow(($zsol[$d][$h]),2)) ) ) / $ysol[$d][$h];

			if ($tanB[$d][$h] <= 0)
			{
				$FSGVS[$d][$h] = 0;
			}
			else
			{
				$inclihorizonte = ($beta_eje + $beta_mod);
				$sombrapos[$d][$h] = cos($inclihorizonte) + (sin($inclihorizonte)) / $tanB[$d][$h];
				$FSGVS[$d][$h] = max( 0, (1-($LNS/$sombrapos[$d][$h])) );
	        }

	        $FSGTS[$d][$h] = $FSGVS[$d][$h] * $FSGHS[$d][$h];

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

			// Irradiations:
			// Peak limiting shadows factor to unity
			$FSETS[$d][$h] = min(1, $FSETS[$d][$h]);
			$FSETEO[$d][$h] = min(1, $FSETEO[$d][$h]);

			$Befsayp[$d][$h] = (1-$FSETS[$d][$h]) * $Befsa[$d][$h];
			$Defsayp[$d][$h] = ( ($Diso[$d][$h]+$Dhor[$d][$h]) * $FCD[$d][$h] + $Dcir[$d][$h] * $FCB[$d][$h] * (1-$FSETEO[$d][$h]) * (1-$FSETS[$d][$h]) ) * $Transm;
			$Gefsayp[$d][$h] = $Befsayp[$d][$h] + $Defsayp[$d][$h] + $Ref[$d][$h];

		}
	}
	/////////////////////////////////////////////////////////////////////////////////
	// OUTPUT
	/////////////////////////////////////////////////////////////////////////////////
	
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
