<?php

function MonthlyAverageToAll($MonthlyAverages) 
{
	// This function assignates the monthly average of a given parameter to all
	// the days of that month.
	
	// Initial day of each month
	$MonthInitialDay = [1, 32, 60, 91, 121, 152, 182, 213, 244, 274, 305, 335];
	
	// Final day of each month
	$MonthFinalDay = [31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334, 365];
	
	// Assignation of the 12 monthly averages to all the days of that month
	for ($i = 0; $i < 12; $i++) 
	{
		for ($d = 0; $d < 365; $d++) 
		{
			if ( ($d >= $MonthInitialDay[$i]-1) &&  ($d <= $MonthFinalDay[$i]-1) )
			{
				$DailyValues[$d] = $MonthlyAverages[$i];
			}
		}
	}
	
	return $DailyValues;
}

?>