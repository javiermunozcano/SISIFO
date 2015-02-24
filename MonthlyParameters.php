<?php
function MonthlyParameters($DAILY) {
include_once 'MonthlySum.php';	
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//1. Global irradiation, Wh
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$G0m=MonthlySum($DAILY['G0d']);
	$Gm=MonthlySum($DAILY['Gd']);
	$Gefm=MonthlySum($DAILY['Gefd']);
	$Gefsam=MonthlySum($DAILY['Gefsad']);
	$Gefsaypm=MonthlySum($DAILY['Gefsaypd']);
	$Gefsaypcem=MonthlySum($DAILY['Gefsaypced']);	
	
	$MONTHLY=array(
		'G0m'	=> $G0m,
		'Gm'	=> $Gm,
		'Gefm'	=> $Gefm,
		'Gefsam'=> $Gefsam,
		'Gefsaypm'=> $Gefsaypm,
		'Gefsaypcem'=> $Gefsaypcem);
	
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//2. Beam irradiation, Wh
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$MONTHLY['B0m']=MonthlySum($DAILY['B0d']);
	$MONTHLY['Bm']=MonthlySum($DAILY['Bd']);
	$MONTHLY['Befm']=MonthlySum($DAILY['Befd']);
	$MONTHLY['Befsam']=MonthlySum($DAILY['Befsad']);
	$MONTHLY['Befsaypm']=MonthlySum($DAILY['Befsaypd']);
	$MONTHLY['Befsaypcem']=MonthlySum($DAILY['Befsaypced']);
	
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//3. Diffuse irradiation, Wh
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$MONTHLY['D0m']=MonthlySum($DAILY['D0d']);
	$MONTHLY['Dm']=MonthlySum($DAILY['Dd']);
	$MONTHLY['Defm']=MonthlySum($DAILY['Defd']);
	$MONTHLY['Defsam']=MonthlySum($DAILY['Defsad']);
	$MONTHLY['Defsaypm']=MonthlySum($DAILY['Defsaypd']);
	$MONTHLY['Defsaypcem']=MonthlySum($DAILY['Defsaypced']);

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//4. Energies, equivalent hours
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$MONTHLY['EDCSPm']=MonthlySum($DAILY['EDCSPd']);
	$MONTHLY['EDCPPm']=MonthlySum($DAILY['EDCPPd']);
	$MONTHLY['EDCPEm']=MonthlySum($DAILY['EDCPEd']);
	$MONTHLY['EDCPTm']=MonthlySum($DAILY['EDCPTd']);
	$MONTHLY['EDCBIm']=MonthlySum($DAILY['EDCBId']);
	$MONTHLY['EDCPCm']=MonthlySum($DAILY['EDCPCd']);
	$MONTHLY['EDCm']=MonthlySum($DAILY['EDCd']);
	$MONTHLY['EACACm']=MonthlySum($DAILY['EACACd']);
	$MONTHLY['EACm']=MonthlySum($DAILY['EACd']);
	$MONTHLY['EACMTm']=MonthlySum($DAILY['EACMTd']);	
	
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//5. Monthly PRs (ideal)
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$fin=count($MONTHLY['EDCm']);
for ($i = 0; $i < $fin; $i++) 
	{
	$MONTHLY['PRDCm'][$i]=$MONTHLY['EDCm'][$i]/($MONTHLY['Gm'][$i]/1000);
	$MONTHLY['PRACm'][$i]=$MONTHLY['EACm'][$i]/($MONTHLY['Gm'][$i]/1000);
	$MONTHLY['PRACMTm'][$i]=$MONTHLY['EACMTm'][$i]/($MONTHLY['Gm'][$i]/1000);	
	}

	
return $MONTHLY;	
}

?>