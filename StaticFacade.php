<?php

// Include function files
include_once 'DustDegreeParameters.php';
include_once 'ShadingModelParameters.php';

function StaticFacade($SUNPOS, $w, $ws, $HI, $ANISO, $SITE, $PVGEN, $OPTIONS, $TIME) 
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
	// Facade
	$Inclination_generator = $PVGEN['Facade']['Inclination_generator'];
	$Orientation_facade = $PVGEN['Facade']['Orientation_facade'];
	$Inclination_facade = $PVGEN['Facade']['Inclination_facade'];
	$LCN = $PVGEN['Facade']['LCN'];
	$AEO = $PVGEN['Facade']['AEO'];
	$DEO = $PVGEN['Facade']['DEO'];
	//Conversion to radian
	$beta_facade = $Inclination_facade*pi()/180;

	// OTHER PARAMETERS
	// OptimumSlope
	if ($OPTIONS['OptimumSlope'] == 1) 
	{
		$Inclination_generator= 3.7 + 0.69*$SITE['Latitude']*valueSign($SITE['Latitude']);
		$Orientation_facade = 0;
		$Inclination_facade = 0;
	}
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
			// Conversion to radian
			$beta[$d][$h] = $Inclination_generator * pi()/180;
			$alfa[$d][$h] = $Orientation_facade * pi()/180;
	
	  	    // Coordinates of unit radius vector of the sun in a system of coordinates Oxyz 
	  	    // solidarity with the place and the x axis X, Y, Z pointing respectively to the
	  	    // west, south and the zenith
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
			if ( abs($w[$d][$h]) >= abs($ws[$d]) ) 
			{
				$B[$d][$h] = 0;
			}
			else
			{
				$B[$d][$h] = $B0[$d][$h] * max(0, $costetas[$d][$h]) / $costetazs[$d][$h];
			}

			// Isotropic, circumsolar and horizon diffuse irradiance components
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

			// Global irradiance
			$G[$d][$h] = $B[$d][$h] + $D[$d][$h] + $R[$d][$h];

			// Consideration of the effects of the incidence angle
			// Direct correction factor
			$FCB[$d][$h] = (1 - exp(-$costetas[$d][$h] / $ar) ) / (1 - exp(-1/$ar));

			// Diffuse correction factor (Isotropic component)
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
	
			// Effective irradiation components
			$Bef[$d][$h] = $B[$d][$h] * $FCB[$d][$h] * $Transm;
			$Def[$d][$h] = ( ($Diso[$d][$h] + $Dhor[$d][$h]) * $FCD[$d][$h] + $Dcir[$d][$h] * $FCB[$d][$h] ) * $Transm;
			$Ref[$d][$h] = $R[$d][$h] * $FCR[$d][$h] * $Transm;

			// Global effective irradiance
			$Gef[$d][$h] = $Bef[$d][$h] + $Def[$d][$h] + $Ref[$d][$h];

			// Calculation of the geometric shadow cast by a row
			// Located in the South, FSGTS.
			// Reset the factors
			$FSGHC[$d][$h] = 0;
			$FSGVC[$d][$h] = 0;
			$FSGTC[$d][$h] = 0;
			$FSGF[$d][$h] = 0;
			$FSEF[$d][$h] = 0;
			$LVS[$d][$h] = 0;
			$LHS[$d][$h] = 0;

	
			// Sun coordinates, in the coordinate system resulting from turning the primal alpha = angle (facade orientation) around the z axis
			$xprimasol[$d][$h] = $xsol[$d][$h] * cos($alfa[$d][$h]) - $ysol[$d][$h] * sin($alfa[$d][$h]);
			$yprimasol[$d][$h] = $xsol[$d][$h] * sin($alfa[$d][$h]) + $ysol[$d][$h] * cos($alfa[$d][$h]);
			$zprimasol[$d][$h] = $zsol[$d][$h];

			// Rotated coordinate system resulting from previous coordinate one -beta_facade angle around the axis xprima
			$x2primasol[$d][$h] = $xprimasol[$d][$h];
			$y2primasol[$d][$h] = $yprimasol[$d][$h] * cos(-$beta_facade) - $zprimasol[$d][$h] * sin(-$beta_facade);
			$z2primasol[$d][$h] = $yprimasol[$d][$h] * sin(-$beta_facade) + $zprimasol[$d][$h] * cos(-$beta_facade);

	
			// Shadows caused by the own facade (Geometric Shadow Factor caused by the facade, FSGF). The condition is that either day and the sun 
			// is at the back of the facade.
			if ( $zsol[$d][$h] > 0 && $y2primasol[$d][$h] <= 0.0035 ) 
			{
				$FSGF[$d][$h] = 1;
				$FSEF[$d][$h] = 1;
			}
			
			// Shadows caused by the row located over the zenith
			// Geometric shadow factor horizontally. Calculate strictly when it is day and the sun is in front of the facade
			if ( $zsol[$d][$h] > 0 && $y2primasol[$d][$h] >= 0.0035 ) 
			{
				if ( $x2primasol[$d][$h] < 0 )
				{
					$FSGHC[$d][$h] = max( 0 , 1 - $DEO / $AEO - ($LCN - sin($beta[$d][$h])) / $AEO * $x2primasol[$d][$h] / $z2primasol[$d][$h] );
				}
		        else
		        {
					$FSGHC[$d][$h] = max( 0 , 1 + $DEO / $AEO + ($LCN - sin($beta[$d][$h])) / $AEO * $x2primasol[$d][$h] / $z2primasol[$d][$h] );
		        }
		    } 
	
			// Separation reversion to vertical façade LCN_FV
			$LCN_FV = $LCN * ( cos($beta_facade) - sin($beta_facade) * tan($beta[$d][$h]) );
			// Vertical shadow reversion factor to vertical façade
			$FSGVC_FV[$d][$h] = max( 0 , 1 - $LCN_FV / ( sin($beta[$d][$h]) + cos($beta[$d][$h]) * $zprimasol[$d][$h] / $yprimasol[$d][$h] ) );
			// Vertical shadow factor on the actual facade
			$FSGVC[$d][$h] = $FSGVC_FV[$d][$h] - $LCN * sin($beta_facade) / cos($beta[$d][$h]);

	
	
			// Total geometric shadow factor caused by the row located at the zenith
			$FSGTC[$d][$h] = $FSGHC[$d][$h] * $FSGVC[$d][$h];

			// Martinez shading Model
			$NBSVC[$d][$h] = 0;
			$NBSHC[$d][$h] = 0;
			$NBSTC[$d][$h] = 0;
			$FSEVC[$d][$h] = 0;
			$FSEHC[$d][$h] = 0;
			$FSETC[$d][$h] = 0;

			// effective values
			if ($FSGTC[$d][$h] > 0) 
			{
				$NBSVC[$d][$h] = RoundToZero($FSGVC[$d][$h] * $NBGV + 0.999);
	            $NBSHC[$d][$h] = RoundToZero($FSGHC[$d][$h] * $NBGH + 0.999);
				$NBSTC[$d][$h] = $NBSVC[$d][$h] * $NBSHC[$d][$h];
				$FSEVC[$d][$h] = $NBSVC[$d][$h] / $NBGV;
				$FSEHC[$d][$h] = $NBSHC[$d][$h] / $NBGH;
				if ($MSC == 1)
				{	
					$FSETC[$d][$h] = $FSEVC[$d][$h] * $FSEHC[$d][$h];
				}
				else
				{
					$FSETC[$d][$h] = 1 - (1 - $FSGTC[$d][$h]) * (1 - (1-$MSO) * $NBSTC[$d][$h] / ($NBT+1)) * (1-$MSP);
				}
	    	}   
	
			// Total shadow = caused by the facade + projected by the row
			// located at the zenith

			$FSETT[$d][$h] = $FSEF[$d][$h] + $FSETC[$d][$h];

			// Irradiances
			// Peak limiting factor to unity shadows
			$FSETT[$d][$h] = min(1, $FSETT[$d][$h]);

			$Befsayp[$d][$h] = (1-$FSETT[$d][$h]) * $Bef[$d][$h];
			$Defsayp[$d][$h] = ( ($Diso[$d][$h] + $Dhor[$d][$h]) * $FCD[$d][$h] + $Dcir[$d][$h] * $FCB[$d][$h] * (1-$FSETT[$d][$h]) ) * $Transm;
			$Gefsayp[$d][$h] = $Befsayp[$d][$h] + $Defsayp[$d][$h] + $Ref[$d][$h];

			// Agreement to maintain variables in other parts of the program
			$Befsa[$d][$h] = $Befsayp[$d][$h];
			$Defsa[$d][$h] = $Defsayp[$d][$h];
			$Gefsa[$d][$h] = $Gefsayp[$d][$h];
		} 
	} 
	
	
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