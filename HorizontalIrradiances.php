<?php
	
	// Include function files
	include_once 'ExtraHorIrradiation.php';
	include_once 'MonthlyAverageToAll.php';
	include_once 'DailyClearnessIndex.php';
	include_once 'DailyDiffuseFraction.php';
	include_once 'DailyHorizontalIrradiations.php';
	include_once 'MeanHorizontalIrradiances.php';
	include_once 'ClearHorizontalIrradiances.php';
	include_once 'CloudyHorizontalIrradiances.php';
	

//HORIZONTAL IRRADIANCES

function HorizontalIrradiances($SUNMOT, $SUNPOS, $AMI, $SITE, $METEO, $OPTIONS, $TIME) {

if (($METEO['Data'] == 1)||($METEO['Data'] == 3) )
{
	if (($METEO['Sky'] == 1) || ($METEO['Sky'] == 3) ) 
	{
		//Mean sky
		//Daily Extraterrestial Horizontal Irradiation, Wh/m2
		$BOd0=ExtraHorIrradiation($SUNMOT['Eo'], $SUNMOT['ws'], $SUNMOT['delta'], $SITE, $TIME);
		//Daily Global Horizontal Irradiation, Wh/m2
		if($TIME['Ndays'] == 365)
		{
			//All year
			$Gd0=MonthlyAverageToAll($METEO['Gdm0']);
		}
		elseif($TIME['Ndays'] == 12)
		{	
			//Only characteristic days
			$Gd0=$METEO['Gdm0'];
		}
				
		//Daily Clearness Index
		$KTd=DailyClearnessIndex($Gd0, $BOd0, $TIME);
		//Daily Diffuse fraction
		$KDd=DailyDiffuseFraction($KTd, $SUNMOT['ws'], $OPTIONS, $TIME);
		//Daily horizontal irradiations, Wh/m2
		$DHI=DailyHorizontalIrradiations($Gd0, $KDd, $TIME);
		//Horizontal irradiances, W/m2
		$HI=MeanHorizontalIrradiances($SUNMOT['w'], $SUNMOT['ws'], $DHI, $TIME);
		if($METEO['Sky'] == 3)
		{
			//Anulates the beam component
			$HI['B0']=array_fill(0 , $TIME['Ndays'], array_fill(0 , $TIME['Nsteps'], 0));
			//And global matches the diffuse
			$HI['G0']=$HI['D0'];
		}
	} 
	elseif ($METEO['Sky'] == 2)
	{
       	//Clear sky
       	$HI=ClearHorizontalIrradiances($SUNMOT['Eo'], $SUNMOT['ws'], $SUNMOT['w'], $SUNPOS, $AMI, $METEO, $TIME);
	}
	//elseif ($METEO['Sky'] == 3)
	//{
    	//Cloudy sky
   // 	$HI=CloudyHorizontalIrradiances($SUNMOT['Eo'], $SUNMOT['ws'], $SUNMOT['w'], $SUNPOS, $AMI, $METEO, $TIME); 
   // } 
			
		
}
else //$METEO['Data']=2
{
	$HI['G0']=$METEO['G0'];
	$HI['B0']=$METEO['B0'];
	$HI['D0']=$METEO['D0'];
}
		
		
			
return $HI;
}

?>

