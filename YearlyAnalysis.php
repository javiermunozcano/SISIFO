<?php

include_once 'MainProgram.php';
include_once 'DailyParameters.php';
include_once 'MonthlyParameters.php';
include_once 'YearlyParameters.php';
include_once 'ClearPostProcessing.php';
include_once 'ClearCloudyPostProcessing.php';
include_once 'MathFuncs.php';


function YearlyAnalysis($SITE,$METEO,$PVMOD,$PVGEN,$BOS,$OPTIONS,$TIME) {
	
	if (($METEO['Data'] == 1)||($METEO['Data'] == 3)) 
	{//MONTHLY AVERAGES
		switch ($METEO['Sky']) {
			case '1':	//Mean sky
        		$MATRICES = MainProgram($SITE, $METEO, $PVMOD, $PVGEN, $BOS, $OPTIONS, $TIME);				//Matrices
				$DAILY=DailyParameters($MATRICES['HI'],$MATRICES['ISI'],$MATRICES['POWER'],$TIME);	//Daily parameters
				$MONTHLY=MonthlyParameters($DAILY);	//Monthly parameters
				$YEARLY=YearlyParameters($MONTHLY, $OPTIONS);	//Yearly parameters
				break;
				
			case '2':	//Clear sky
				$MATRICESClear=MainProgram($SITE,$METEO,$PVMOD,$PVGEN,$BOS,$OPTIONS,$TIME);		//Matrices
				$DAILYClear=DailyParameters($MATRICESClear['HI'],$MATRICESClear['ISI'],$MATRICESClear['POWER'],$TIME);	//Daily parameters
				$MONTHLYClear=MonthlyParameters($DAILYClear);	//Monthly parameters
				
				$MONTHLY=ClearPostProcessing($MONTHLYClear,$METEO['Gdm0']);	//Post-processing of monthly parameters
				$YEARLY=YearlyParameters($MONTHLY, $OPTIONS);	//Yearly parameters
				
				$DAILY=$DAILYClear;	//Output matrices are those of the Clear Sky
				$MATRICES=$MATRICESClear;
				break;
			
			case '3':	//Clear and cloudy sky
				//First, clear sky
				$METEO['Sky']=2;
				
				$MATRICESClear=MainProgram($SITE,$METEO,$PVMOD,$PVGEN,$BOS,$OPTIONS,$TIME);	//Matrices clear
				$DAILYClear=DailyParameters($MATRICESClear['HI'],$MATRICESClear['ISI'],$MATRICESClear['POWER'], $TIME);	//Daily parameters
				$MONTHLYClear=MonthlyParameters($DAILYClear);	//Monthly parameters
				
				//Second, cloudy sky
				$METEO['Sky']=3;
				
				$MATRICESCloudy=MainProgram($SITE,$METEO,$PVMOD,$PVGEN,$BOS,$OPTIONS,$TIME);	//Matrices cloudy
				$DAILYCloudy=DailyParameters($MATRICESCloudy['HI'], $MATRICESCloudy['ISI'], $MATRICESCloudy['POWER'],$TIME);	//Daily parameters
				$MONTHLYCloudy=MonthlyParameters($DAILYCloudy);	//Monthly parameters
				
				$MONTHLY=ClearCloudyPostProcessing($MONTHLYClear,$MONTHLYCloudy,$METEO['Gdm0']);		//Post-processing of monthly parameters
				$YEARLY=YearlyParameters($MONTHLY, $OPTIONS);	//Yearly parameters
				
				$DAILY=$DAILYClear;	//Output matrices are those of the Clear Sky
				$MATRICES=$MATRICESClear;
				break;
		}
	}elseif ($METEO['Data'] == 2)
	{
		$MATRICES=MainProgram($SITE,$METEO,$PVMOD,$PVGEN,$BOS,$OPTIONS,$TIME);	//Matrices
		
		$DAILY=DailyParameters($MATRICES['HI'],$MATRICES['ISI'],$MATRICES['POWER'],$TIME);	//Daily parameters
		$MONTHLY=MonthlyParameters($DAILY);	//Monthly parameters
		$YEARLY=YearlyParameters($MONTHLY, $OPTIONS);	//Yearly parameters
	}

$RESULTS= array(
		'MATRICES' 	=> $MATRICES,
		'DAILY' 	=> $DAILY,
		'MONTHLY'	=> $MONTHLY,
		'YEARLY'	=> $YEARLY);

return $RESULTS;

}

?>