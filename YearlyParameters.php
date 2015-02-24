<?php

include_once 'ConfidenceLevels.php';

function YearlyParameters($MONTHLY, $OPTIONS) {

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//1. Global irradiation, Wh
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$G0a=array_sum($MONTHLY['G0m']);
	$Ga=array_sum($MONTHLY['Gm']);
	$Gefa=array_sum($MONTHLY['Gefm']);
	$Gefsaa=array_sum($MONTHLY['Gefsam']);
	$Gefsaypa=array_sum($MONTHLY['Gefsaypm']);
	$Gefsaypcea=array_sum($MONTHLY['Gefsaypcem']);	
	
	$YEARLY= array(
			'G0a'	=> $G0a,
			'Ga'	=> $Ga,
			'Gefa'	=> $Gefa,
			'Gefsaa'=> $Gefsaa,
			'Gefsaypa'=> $Gefsaypa,
			'Gefsaypcea'=> $Gefsaypcea);
	
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//2. Beam irradiation, Wh
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$YEARLY['B0a']=array_sum($MONTHLY['B0m']);
	$YEARLY['Ba']=array_sum($MONTHLY['Bm']);
	$YEARLY['Befa']=array_sum($MONTHLY['Befm']);
	$YEARLY['Befsaa']=array_sum($MONTHLY['Befsam']);
	$YEARLY['Befsaypa']=array_sum($MONTHLY['Befsaypm']);
	$YEARLY['Befsaypcea']=array_sum($MONTHLY['Befsaypcem']);
	
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//3. Diffuse irradiation, Wh
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$YEARLY['D0a']=array_sum($MONTHLY['D0m']);
	$YEARLY['Da']=array_sum($MONTHLY['Dm']);
	$YEARLY['Defa']=array_sum($MONTHLY['Defm']);
	$YEARLY['Defsaa']=array_sum($MONTHLY['Defsam']);
	$YEARLY['Defsaypa']=array_sum($MONTHLY['Defsaypm']);
	$YEARLY['Defsaypcea']=array_sum($MONTHLY['Defsaypcem']);	
	
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//4. Electric Energy, equivalent hours
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$YEARLY['EDCSPa']=array_sum($MONTHLY['EDCSPm']);
	$YEARLY['EDCPPa']=array_sum($MONTHLY['EDCPPm']);
	$YEARLY['EDCPEa']=array_sum($MONTHLY['EDCPEm']);
	$YEARLY['EDCPTa']=array_sum($MONTHLY['EDCPTm']);
	$YEARLY['EDCBIa']=array_sum($MONTHLY['EDCBIm']);
	$YEARLY['EDCPCa']=array_sum($MONTHLY['EDCPCm']);
	$YEARLY['EDCa']=array_sum($MONTHLY['EDCm']);
	$YEARLY['EACACa']=array_sum($MONTHLY['EACACm']);
	$YEARLY['EACa']=array_sum($MONTHLY['EACm']);
	$YEARLY['EACMTa']=array_sum($MONTHLY['EACMTm']);	
	
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//5. PRs (ideal)
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$YEARLY['PRDCa']=$YEARLY['EDCa']/($YEARLY['Ga']/1000);
	$YEARLY['PRACa']=$YEARLY['EACa']/($YEARLY['Ga']/1000);
	$YEARLY['PRACMTa']=$YEARLY['EACMTa']/($YEARLY['Ga']/1000);

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//6. Efficiencies and losses
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	//7.1 Incidence (reflection, transmission and dust)
	$YEARLY['PER_INCa']=1-$YEARLY['Gefa']/$YEARLY['Ga'];
	//7.2 Shading
	$YEARLY['PER_SOMa']=1-$YEARLY['Gefsaypa']/$YEARLY['Gefa'];
	//7.3 Spectrum
	$YEARLY['PER_ESPa']=1-$YEARLY['Gefsaypcea']/$YEARLY['Gefsaypa'];
	//7.4 PV real power versus nominal
	$YEARLY['PER_POTa']=(1-$YEARLY['EDCPPa']/$YEARLY['EDCSPa'])*100;
	//7.5 Seasonal (a-Si)
	$YEARLY['PER_ESTa']=(1-$YEARLY['EDCPEa']/$YEARLY['EDCPPa'])*100;
	//7.6 Temperature
	$YEARLY['PER_TEMa']=(1-$YEARLY['EDCPTa']/$YEARLY['EDCPEa'])*100;
	//7.7 Low irradiances
	$YEARLY['PER_BIa']=(1-$YEARLY['EDCBIa']/$YEARLY['EDCPTa'])*100;
	//7.8 DC wiring
	$YEARLY['PER_CDCa']=(1-$YEARLY['EDCPCa']/$YEARLY['EDCBIa'])*100;
	//7.9 Minimum irradiance and inverter saturation
	$YEARLY['PER_UMBa']=(1-$YEARLY['EDCa']/$YEARLY['EDCPCa'])*100;
	//7.10 Inverter energy efficiency
	$YEARLY['etaia']=($YEARLY['EACACa']/$YEARLY['EDCa'])*100;
	//7.11 Inverter losses
	$YEARLY['PER_INVa']=100-$YEARLY['etaia'];
	//7.12 AC wiring
	$YEARLY['PER_CACa']=(1-$YEARLY['EACa']/$YEARLY['EACACa'])*100;
	//7.13 LV/MV conversion efficiency (transformer)
	$YEARLY['etamta']=($YEARLY['EACMTa']/$YEARLY['EACa'])*100;
	//7.14 LV/MV energy conversion losses
	$YEARLY['PER_MTa']=100-$YEARLY['etamta'];	
		
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//7. Experimental PRs
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$YEARLY['PRexpDCa']=($YEARLY['EDCa']/($YEARLY['Ga']*(1-$YEARLY['PER_INCa'])*(1-$YEARLY['PER_ESPa'])))*1000;
	$YEARLY['PRexpACa']=($YEARLY['EACa']/($YEARLY['Ga']*(1-$YEARLY['PER_INCa'])*(1-$YEARLY['PER_ESPa'])))*1000;
	$YEARLY['PRexpACMTa']=($YEARLY['EACMTa']/($YEARLY['Ga']*(1-$YEARLY['PER_INCa'])*(1-$YEARLY['PER_ESPa'])))*1000;	
	
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//8. Yields and losses
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	//Reference yield, equivalent hours
	$YEARLY['Yr']=$YEARLY['Ga']/1000;
	//Array yield, equivalent hours
	$YEARLY['Ya']=$YEARLY['EDCa'];
	//Final yield, equivalent hours
	$YEARLY['Yf']=$YEARLY['EACMTa'];
	//Capture losses, percentage of Yr
	$YEARLY['LCa']=100*($YEARLY['Yr']-$YEARLY['Ya'])/$YEARLY['Yr'];
	//System losses, percentage of Yr
	$YEARLY['LSa']=100*($YEARLY['Ya']-$YEARLY['Yf'])/$YEARLY['Yr'];	

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//9. Hybrid PV-Diesel...... FUTURE WORKS
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//10. Confidence levels, Confidence levels, new in v2.03
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%	
	//Calculation of the confidence levels
	$COLE = ConfidenceLevels($YEARLY['EACMTa'], $OPTIONS);
	//Combined uncertainty, %
	$YEARLY['sigmat'] = $COLE['SIGMA_TOTAL'];
	//P50 level, equivalent hours
	$YEARLY['P50'] = $COLE['P50'];
	//P75 level, equivalent hours
	$YEARLY['P75'] = $COLE['P75'];
	//P90 level, equivalent hours
	$YEARLY['P90'] = $COLE['P90'];
	
	
return $YEARLY;
}

?>