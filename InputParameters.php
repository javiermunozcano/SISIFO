<?php
//INPUT PARAMETERS

//Include Function Files
include_once 'LowIrradianceParameters.php';
include_once 'ReadTMY.php';
include_once 'InverterParameters.php';

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%   SITE   %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

$Latitude = 40.43;              //Latitude of the location, positive in the Northern Hemisphere and negative in the Southern Hemisphere.
$lat = $Latitude*pi()/180;      //Latitude, radians. Internal calculation.
$Altitude = 667;                //Altitude of the location over sea level
$Longitude = -3.7;              //Longitude of the location, negative towards West and positive towards East.
$StandardLongitude = 0;         //StandardLongitude of the local meridian (multiple of 15), negative towards West an positive towards East.
$Location = 'MADRID';
$Project = 'Project_Name';

$SITE= array(
		'Latitude'  => $Latitude,
		'lat'       => $lat,
		'Altitude'  => $Altitude,
		'Longitude' => $Longitude,
		'StandardLongitude' => $StandardLongitude,
		'Location'  => $Location,
		'Project'	=> $Project);


//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%   METEO   %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//Input Data
// 1.Monthly averages
// 2.Time series
$Data=1;

if ($Data==2)//Read input file. Exammple: ExampleTMY.xls
{
	$METEO_ReadTMY=ReadTMY($METEO);
	$Day=$METEO_ReadTMY[0];
	$Time_Hours=$METEO_ReadTMY[1];
	$G0=$METEO_ReadTMY[2];
	$D0=$METEO_ReadTMY[3];
	$B0=$METEO_ReadTMY[4];
	$Ta=$METEO_ReadTMY[5];

$METEO= array(
			'Data'  => $Data,
			'Sky'   => NULL,
			'Gdm0'  => NULL,
			'TMm'   => NULL,
			'Tmm'   => NULL,
			'Hours' => $Time_Hours,
			'G0'	=> $G0,
			'B0'	=> $B0,
			'D0'	=> $D0,
			'Ta'	=> $Ta);

}

if ($Data==1)   //If METEO.Data=1:
{
	//Sky: 1.Meam
	//     2.Clear
	//     3.Clear/Cloudy
	$Sky=2;

	//Mean daily irradiation, monthly average, Wh/m2.
	$Gdm0=[1980,2680,4430,5080,6480,7210,7320,6430,4970,3350,2130,1600];

	//Maximum daily temperature, monthly average, ºC.
	$TMm=[10,12.7,16.3,17.8,22,29,31.7,31.2,26.1,20.2,13.3,10];

	//Minimum daily temperature, monthly average, ºC.
	$Tmm=[3,3.1,5.3,6.2,10.2,15.1,17,16.5,13.3,10.4,6.1,3.4];

	if (($Sky==2)||($Sky==3))//If METEO.Data=1 and Sky=2 or 3
	{
		$Tlk=[3,3.2,2.9,3.3,3.4,3.9,3.8,4.2,3.9,3.9,3.7,3.1];   //Linke Turbidity, dimensionless.
	}
	else
	{
		$Tlk = NULL;
	}

$METEO= array(
			'Data' => $Data,
			'Sky'  => $Sky,
			'Gdm0' => $Gdm0,
			'TMm'  => $TMm,
			'Tmm'  => $Tmm,
			'Tlk'  => $Tlk);
}


//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%   PVMOD   %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//Material of the solar cell, dimensionless.
//  1.Si-c
//  2.Te-Cd
//  3.Si-a
//  4.III-V(concentrators)
//  5.CIS
$CellMaterial=1;
//Power model, dimensionless.
//  1.Only temperature effect.
//  2.Irradiance and temperature effects.
//  3.Others
$PowerModel=1;
//Coefficient of Variation of module Power with Temperature (absolute value)
$CVPT=0.5;
//Nominal Operation Cell Temperature, ºC
$NOCT=48;
//Power of a single PV module at different irradiances
//Power of a single PV module at different irradiances
if ($PowerModel==1) 
{
	$P1000=NULL;
	$P600=NULL;
	$P200=NULL;
	$LowGParam =NULL;
}
elseif ($PowerModel==2)
{
	//Power of the PV module under Standard Test Conditions (STC), %
	$P1000=$_POST['P1000'];
	//Power of the PV module at 600 W/m2 and 25ºC cell temperature, %
	$P600=$_POST['P600'];
	//Power of the PV module at 200 W/m2 and 25ºC cell temperature, %
	$P200=$_POST['P200'];
//Parameters a, b, c of the irradiance efficiency model (power model 2) (Internal calculation)
$LowGParam = LowIrradianceParamerters($P1000, $P600, $P200);
}

if ($CellMaterial==3)
{
	//Maximum variation of the efficiency respect to the nominal value during the year, absolute value, in %.
	$SeasonalVariation=20;
	//Day of the year (from 1 to 365) when the efficiency reaches its maximum value, dimensionless.
	$SeasonalPhase=196;
}
else
{
	$SeasonalVariation= NULL;
	$SeasonalPhase= NULL;
}

 
$PVMOD= array(
		'CellMaterial'=> $CellMaterial,			
		'PowerModel'  => $PowerModel, 		
		'CVPT'		  => $CVPT,		
		'NOCT'		  => $NOCT,		
		'P1000'	 	  => $P1000,		
		'P600'	 	  => $P600,		
		'P200'	 	  => $P200,		
	    'LowGParam' => $LowGParam, 
		'AmorphousSi' => array(	'SeasonalVariation'=> $SeasonalVariation,	
								'SeasonalPhase'    => $SeasonalPhase)); 


//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%   PVGEN   %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//Type of structure, dimensionless.
//	1.Ground or roof
//	2.Facade
//	3.One axis horizontal or inclined
//	4.One axis vertical (azimuthal)
//	5.Two axis (primary vertical/secondary horizontal)
//	6.Two axis (primary vertical/secondary horizontal, Venetian blind)
//	7.Two axis (primary horizontal, secondary perpendicular)
//	8.Concentrator
$Struct=1;
//Nominal power of all the PV generators of the system, kWp.
$PowNom_Total=1000;
//Nominal power connected to a single inverter, kWp.
$PowNom_PerInverter=100;
//Nominal power connected to each LV/MV transformer, kWp.
$PowNom_PerTransformer=1000;
//Ratio real power to nominal power, dimensionless.
$PRVPN=1;
//Real power of all the PV generators of the system, kWp.
//Internal calculation
$PowReal_Total=$PowNom_Total*$PRVPN;
//Number of bypass diodes in the horizontal dimension, dimensionless.
$NBGH=10;
//Number of bypass diodes in the vertical dimension, dimensionless (for the Venetian Blind Tracker this parameters is calculated below).
if ($Struct == 6) 
{
	$NBGV=NULL;
	$NBT=NULL;
}
else 
{
$NBGV=10;
//Total number of bypass diodes
$NBT=$NBGV*$NBGH;
}
//PVGEN array, adding to the previous each structure's specific parameters
$PVGEN= array(
		'Struct'	=> $Struct,		
		'PowNom_Total'=> $PowNom_Total, 	
		'PowNom_PerInverter' => $PowNom_PerInverter,	
		'PowNom_PerTransformer'=> $PowNom_PerTransformer,
		'PRVPN'	=> $PRVPN,
		'PowReal_Total' => $PowReal_Total,
		'NBGH'	=> $NBGH,			
		'NBGV'	=> $NBGV,
		'NBT'	=> $NBT);

switch ($PVGEN['Struct'])
{
	case 1: 
		$PVGEN['GroundRoof'] = array (				// Ground/Roof: PVGEN['Struct'] = 1
			'Inclination_roof'	=> 0,
			'Orientation_roof'	=> 0,
			'Inclination_generator'	=> 35,
			'Orientation_generator'	=> 0,
			'LNS'	=> 1.5,
			'AEO'	=> 5,
			'DEO'	=> 0);
		break;		
	case 2:
		$PVGEN['Facade'] = array (					// Façade: PVGEN['Struct'] = 2
			'Inclination_generator'	=> 35,
			'Orientation_facade'	=> 0,
			'Inclination_facade'	=> 0,
			'LCN'	=> 3,
			'AEO'	=> 5,
			'DEO'	=> 1);
		break;		
	case 3:
		$PVGEN['Track1H'] = array (					// One axis horizontal or inclined: PVGEN['Struct'] = 3
			'LEO'				=> 2.5,
			'Rotation_MAX'		=> 60,
			'Axis_orientation'	=> 0,
			'Axis_inclination'	=> 0,
			'LNS'				=> 2,
			'Inclination_module'=> 0,
			'RSEH'				=> 0);
		break;
	case 4:
		$PVGEN['Track1V'] = array (					// One axis vertical (azimuthal): PVGEN['Struct'] = 4
				'Inclination_surface'=> 45,
				'LEO'	=> 2.45,
				'LNS'	=> 2,
				'ALARG'	=> 0.827,
				'RSEV'	=> 0);
		break;
	case 5:
		$PVGEN['Track2VH'] = array (				// Two axis (primary vertical/secondary horizontal): PVGEN['Struct'] = 5
				'Azimut_MAX'	=> 110,
				'Inclination_MAX'	=> 90,
				'LEO'	=> 1.94,
				'LNS'	=> 1.16,
				'ALARG'	=> 0.414,
				'RSEV'	=> 0,
				'RSEH'	=> 1);
		break;
	case 6:
		$PVGEN['Track2VE'] = array (				// Two axis (primary vertical/secondary horizontal, Venetian blind): PVGEN['Struct'] = 6
				'Inclination_rack'	=> 45,
				'LEO'	=> 2.45,
				'LNS'	=> 2.03,
				'ALARG'	=> 0.827,
				'RSEV'	=> 0,
				'RSEH'	=> 0,
				'LNS_rack'	=> 1.2,
				'ALARGF'=> 0.15,
				'NF'	=> 4,
				'NBFV'	=> 1);
		$PVGEN['NBGV'] = $PVGEN['Track2VE']['NF']*$PVGEN['Track2VE']['NBFV']; // In Venetian Blind, number of bypass diodes is recalculated
		$PVGEN['NBT'] = $PVGEN['NBGV']*$PVGEN['NBGH'];
		break;
	case 7:
		$PVGEN['Track2HP'] = array (				// Two axis (primary horizontal/secondary perpendicular): PVGEN['Struct'] = 7
				'LEO'	=> 2.36,
				'LNS'	=> 1.5,
				'ALARG'	=> 1,
				'Rotation_MAX'	=> 60,
				'Axis_inclination'	=> 0,
				'Inclination_MAX'	=> 60,
				'RSES'	=> 0,
				'RSEH'	=> 0);
		break;
	case 8:
		$PVGEN['Track2CO'] = array (				// Two axis concentrator: PVGEN['Struct'] = 8 
				'Azimut_MAX'=> 110,
				'Inclination_MAX' => 90,
				'LEO' 	=> 1.94,
				'LNS'	=> 1.16,
				'ALARG' => 0.414);
		break;
};


//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%   BOS  %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%		

$PowNom = 120;                      	//Nominal output power, kW
$PowMax = 120;                      	//Maximum output power, kW
$DRi = $PowNom/$PowNom_PerInverter; 	//Normalised size

//Inverter Option
//1. Predefined Ki0,Ki1,Ki2
//2. Graphic method (only interface)
$InverterOption=1;

if ($InverterOption==1)
{
	$Ki0 = 0.0115;                      	//Power efficiency curve parameters
	$Ki1 = 0.0015;
	$Ki2 = 0.0438;
}
else
{
	$Ki0 = NULL;
	$Ki1 = NULL;
	$Ki2 = NULL;
}

$BOS= array(
        'INVERTER'  =>   array(
                'PowNom'=> $PowNom,    
                'PowMax'=> $PowMax,    
                'DRi'   => $DRi,
                'Ki0'   => $Ki0,            
                'Ki1'   => $Ki1,
                'Ki2'   => $Ki2));
  
 if (!isset($BOS['INVERTER']['Ki0']) || !isset($BOS['INVERTER']['Ki1']) || !isset($BOS['INVERTER']['Ki2']))
         {
  
        $BOS['INVERTER']['p0']= [0.1, 0.5, 1]; 
        $BOS['INVERTER']['Efficiency']=[94.2, 96.7, 96.2];  //Inverter parameters (calculated)
  
        $Inverter_Param=InverterParameters($BOS['INVERTER']['p0'], $BOS['INVERTER']['Efficiency']);
        $BOS['INVERTER']['Ki0']= $Inverter_Param[0];            									//Power efficiency curve parameters
        $BOS['INVERTER']['Ki1']= $Inverter_Param[1];
        $BOS['INVERTER']['Ki2']= $Inverter_Param[2];
        }
  
        
$PowNom_Transformer = 1000;     									//Nominal power, kW
$OpenLosses_Transformer = 1.3;  									//No-load losses, kW
$CopperLosses_Transformer = 1.5; 									//Copper losses, kW

//Normalised parameters. Internal calculations
$DRt = $PowNom_Transformer/$PowNom_PerTransformer;          		//Normalised size, dimensionless
$pervacrg = $OpenLosses_Transformer/$PowNom_PerTransformer;     		//Normalised no-load losses, dimensionless
$percurg = $CopperLosses_Transformer/$PowNom_PerTransformer; 		//Normalised copper losses, dimensionless
        
        
$BOS['TRANSFORMER']=array(
				'PowNom'        => $PowNom_Transformer,
				'OpenLosses'    => $OpenLosses_Transformer,
				'CopperLosses'  => $CopperLosses_Transformer,
				'DRt'           => $DRt,
				'pervacrg'      => $pervacrg,
				'percurg'       => $percurg);
				
		
$BOS['WIRING']  =   array(
				'DCLosses'  => 0,    //DC losses, %
				'ACLosses'  => 0);   //AC losses, %

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%   OPTIONS  %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

//PV application
//  1.Grid connection
$Application = 1;

//Analysis type
//  1.Yearly
$Analysis = 1;

//Optimize slope
//  0.No
//  1.Yes
$OptimumSlope = 0;

//Degree of dust
//  1.Clean (0%)
//  2.Low (2%)
//  3.Medium (3%)
//  4.High (8%)
$DustDegree = 1;

//Spectral response of solar cells
//  0.No
//  1.Yes
$SpectralResponse = 1;

//Model of diffuse radiation
//  1.Isotropic
//  2.Anisotropic (Hay)
//  3.Anisotropic (Pérez)
$DiffuseRadiationModeling = 1;

//Daily correlation between the fraction of diffuse and clearness index
//  1.Page
//  2.Erbs
//  3.Macagnan
$DailyDiffuseCorrelation = 1;
if ($METEO['Data']==1)
{
	//Daily correlation between the fraction of diffuse and clearness index
	//  1.Page
	//  2.Erbs
	//  3.Macagnan
	$DailyDiffuseCorrelation = 1;
	$HourlyDiffuseCorrelation = 1;

}
elseif ($METEO['Data']==2)
{
	//Hourly cprreñatopm betweem the fraction of diffuse and the clearness index
	//  1.Orgill-Hollands
	//  2.Erbs
	$HourlyDiffuseCorrelation = 2;
	$DailyDiffuseCorrelation = 1;
}

//Shading model
//  1.Pessimistic
//  2.Optimistic
//  3.Classic
//  4.Martinez
$ShadingModel = 4;

//Other parameters
$Gth = 0;                   //Minimum irradiance required to injecting power in the grid, W/m2
$GroundReflectance = 0.2;   //Ground reflectance

//UNCERTAINTY
//Input database
$SIGMA_BD = 5;  		//Standard deviation of global horizontal irradiation according to the input database, %
//Inter-annual variability
$SIGMA_VA = 1;			//Standard deviation of inter-annual variability of the global horizontal irradiation, %
//Long-term drift
$SIGMA_DL = 0;			//Standard deviation of long-term drift of solar radiation, %
//Transposition models and cell temperature
$SIGMA_TR = 4;			//Standard deviation of transposition solar radiation models and cell temperature, %
//Power response
$SIGMA_RP = 2;			//Standard deviation of power response of the PV system, %
//Initial PV power
$SIGMA_PI = 2;			//Standard deviation of the difference between the real and the nominal power of the PV generators, %
//PV power degradation
$SIGMA_EN = 0;			//Standard deviation of PV power degradation, %

$UNCERTAINTY = array(
		'SIGMA_BD'	=> $SIGMA_BD,
		'SIGMA_VA'	=> $SIGMA_VA,
		'SIGMA_DL'	=> $SIGMA_DL,
		'SIGMA_TR'	=> $SIGMA_TR,
		'SIGMA_RP'	=> $SIGMA_RP,
		'SIGMA_PI'	=> $SIGMA_PI,
		'SIGMA_EN'	=> $SIGMA_EN);

$OPTIONS = array(
		'Application'   => $Application,
		'Analysis'      => $Analysis,
		'OptimumSlope'  => $OptimumSlope,
		'DustDegree'    => $DustDegree,
		'SpectralResponse' => $SpectralResponse,
		'DiffuseRadiationModeling' => $DiffuseRadiationModeling,
		'DailyDiffuseCorrelation' => $DailyDiffuseCorrelation,
		'HourlyDiffuseCorrelation'=> $HourlyDiffuseCorrelation,
		'ShadingModel'  => $ShadingModel,
		'Gth' => $Gth,
		'GroundReflectance' => $GroundReflectance,
		'UNCERTAINTY'	=> $UNCERTAINTY);

								
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%   TIME    %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//Simulation days:
//365 days= 365
//12 characteristic days= 12

$SimDays=365;

if($SimDays==365)
{	
	//Simulation days, all the year
	for ($i = 0; $i < 365; ++$i)
	{
		$SimulationDays[$i]=$i+1;
	}
}
elseif ($SimDays==12)
{
	//Characteristic days, 12 days
	$SimulationDays = array(17,45,74,105,135,161,199,230,261,292,322,347);
}

//Number of simulated days.
$Ndays = count($SimulationDays);

//Simulation step, seconds
$SimulationStep =3600;

//Number of simulation points per day
$Nsteps = floor((24*60*60)/$SimulationStep);

//Number of simulation points per hour
$Stepph = 3600/$SimulationStep;

//Local time
// 1-Solar time
// 2-Standard time
$LocalTime = 1;

if ($LocalTime == 2)
{
	//Day of change of winter to summer
	$DOCS = 90;
	//Daylight saving time in summer, hours
	$DSTS = 2;
	//Day of change of summer to winter
	$DOCW = 300;
	//Daylight saving time in winter, hours
	$DSTW = 1;
}
else
{
	$DOCS = NULL;
	$DSTS = NULL;
	$DOCW = NULL;
	$DSTW = NULL;

}

$TIME = array(
		'SimulationDays' => $SimulationDays,
		'Ndays'          => $Ndays,
		'SimulationStep' => $SimulationStep,
		'Nsteps'         => $Nsteps,
		'Stepph'         => $Stepph,
		'LocalTime'      => $LocalTime,
		'DOCS'           => $DOCS,
		'DSTS'           => $DSTS,
		'DOCW'           => $DOCW,
		'DSTW'           => $DSTW);

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%   ECONOMIC ANALYSIS    %%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

//General economic input data
$LifetimeFIT = 20;
$Degradation =0.2;
$IRR = 15;

// Capital Expenditure
$TotalCost = 1000000;

// Operating Expenditure
$CfOM = 0;
$cvOM = 10;
$deltaCfOM = 0;
$deltaCvOM = 0;

// Electricity transmission costs
$Ctransm = 0;
$deltaCtransm = 0;

// Financial data
$Inflation = 2;
$IncTax = 30;
$LoanPerc = 80;
$LoanTerm = 15;
$LoanRate = 8;

// Feed-in tariff
$FIT = 24.6669592;
$deltaFIT = 2;

$ECO = array (
		'LifetimeFIT' => $LifetimeFIT,
		'Degradation' => $Degradation,
		'IRR' => $IRR,
		'TotalCost' => $TotalCost,
		'CfOM' => $CfOM,
		'cvOM' => $cvOM,
		'deltaCfOM' => $deltaCfOM,
		'deltaCvOM' => $deltaCvOM,
		'Ctransm' => $Ctransm,
		'deltaCtransm' => $deltaCtransm,
		'Inflation' => $Inflation,
		'IncTax' => $IncTax,
		'LoanPerc' => $LoanPerc,
		'LoanTerm' => $LoanTerm,
		'LoanRate' => $LoanRate,
		'FIT' => $FIT,
		'deltaFIT' => $deltaFIT);

?>

