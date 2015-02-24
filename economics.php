<?php

function economic_analysis($RESULTS, $PVGEN, $ECO) 
{
	$LifetimeFIT = $ECO['LifetimeFIT'];
	$Degradation = $ECO['Degradation'] /100;
	$IRR = $ECO['IRR'] /100;
	
	$TotalCost = $ECO['TotalCost'];
	
	$CfOM = $ECO['CfOM'];
	$cvOM = $ECO['cvOM'] /1000;					//variable costs per kWh
	$deltaCfOM = $ECO['deltaCfOM'] /100;
	$deltaCvOM = $ECO['deltaCvOM'] /100;
	
	$Ctransm = $ECO['Ctransm'];
	$deltaCtransm = $ECO['deltaCtransm'] /100;
	
	$Inflation = $ECO['Inflation'] / 100;
	$IncTax = $ECO['IncTax'] /100;
	$LoanPerc = $ECO['LoanPerc'] /100;
	$LoanAmount = $LoanPerc * $TotalCost;
	$LoanTerm = $ECO['LoanTerm'];
	$LoanRate = $ECO['LoanRate'] /100;
	
	$Equity = $TotalCost - $LoanAmount;
	
	$FIT = $ECO['FIT'] / 100;
	$deltaFIT = $ECO['deltaFIT'] /100;
	
	// Weighted average cost of capital WACC.
	// Real. Considered the real discount rate used to determine present value of future cash flows.
	$WACC_nom = $IRR*(1-$LoanPerc) + $LoanRate*$LoanPerc*(1-$IncTax);	
	// Nominal, after inflation correction. Considered the nominal discount rate used to determine present value of future cash flows.
	$WACC_real = (1+$WACC_nom) / (1+$Inflation) - 1;
	
	// Electricity production in kWh for the first year of analysis
	$EACMT_year1 = round($PVGEN['PowNom_Total'] * $RESULTS['YEARLY']['EACMTa'], 0);
	
	// Cash flow analysis for every year of PV system lifetime
	for ($i = 0; $i < $LifetimeFIT; $i++) 
	{
		$year = $i+1;	
		
		// Electricity production in kWh for every year of analysis
		$E_output[$i] = $EACMT_year1 * pow(1-$Degradation, $year-1);
		
		// Updated feed-in tariff in c€/kWh for every year of analysis
		$FIT_updated[$i] = $FIT * pow(1+$deltaFIT, $year-1);
		
		// Annual incomes, related to the value of produced energy according to FIT
		$I_energy[$i] = $E_output[$i] * $FIT_updated[$i];
		
		// Annual fixed operating expenses, updated with inflation rate and extra increases
		$CfOM_updated[$i] = -$CfOM * pow(1+$Inflation+$deltaCfOM, $year-1);
		// Annual variable operating expenses, updated with inflation rate and extra increases
		$CvOM_updated[$i] = -$cvOM * $E_output[$i] * pow(1+$Inflation+$deltaCvOM, $year-1);
		
		// Annual electricity transmission costs
		$Ctransm_updated[$i] = -$Ctransm * $E_output[$i] * pow(1+$deltaCtransm, $year-1);
		
		// Total annual operating expenses, including energy transmission charge
		$COM[$i] = $CfOM_updated[$i] + $CvOM_updated[$i] + $Ctransm_updated[$i];
		
		// Financial analysis
		// Debt Interests & Capital payment and remaining unpaid debt for each period
		if ($year > $LoanTerm)
		{
			$Pay_Int[$i] = 0;
			$Pay_Cap[$i] = 0;
			$Unpaid[$i] = 0;
		}
		elseif ($year == 1) 
			{
				$Pay_Int[$i] = -($LoanRate * $LoanAmount);
				$Pay_Cap[$i] = -($LoanAmount / $LoanTerm);
				$Unpaid[$i] = $LoanAmount + $Pay_Cap[$i];
			}else {
				$Pay_Int[$i] = -($LoanRate * $Unpaid[$i-1]);
				$Pay_Cap[$i] = -($LoanAmount / $LoanTerm);
				$Unpaid[$i] = $Unpaid[$i-1] + $Pay_Cap[$i];
			}
		// Total debt payment
		$Pay_total[$i] = $Pay_Int[$i] + $Pay_Cap[$i];  
		
		// Taxes
		// Taxable income (benefits after interests payment)
		$I_taxable[$i] = $I_energy[$i] + $COM[$i] +$Pay_Int[$i];
		// Annual income taxes
		$Tax[$i] = -($IncTax*$I_taxable[$i]);
		// Corrected income taxes (if $I_taxable is negative due to losses, no tax is paid)
		if ($Tax[$i]>0) {
			$Tax[$i] = 0;
		}
		
		// Total periodical expenses with financing & without taxes
		$Exp_wo_tax[$i] = $COM[$i] + $Pay_total[$i];
		// Total periodical expenses with financing & with taxes
		$Exp_w_tax[$i] = $COM[$i] + $Pay_total[$i] + $Tax[$i];
		
		// Annual cashflow after debt & without taxes
		$CF_wo_tax[$i] = $I_energy[$i] + $Exp_wo_tax[$i];
		// Annual cashflow after debt & after taxes
		$CF_w_tax[$i] = $I_energy[$i] + $Exp_w_tax[$i];
		// Accumulated investment cashflow
		if ($year ==1) {
			$CF_Acc[$i] = (-$Equity) + $CF_w_tax[$i];
		}else {
			$CF_Acc[$i] = $CF_Acc[$i-1] + $CF_w_tax[$i];
		}
		
	}
	////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////// RESULTS ////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////
	
	// Incomes nominal Net Present Value NPV
	$NPV_inc = 0;
	for ($i = 0; $i < $LifetimeFIT; $i++)
	{
		$year=$i+1;
		$NPV_inc = $NPV_inc + $I_energy[$i]/pow(1+$WACC_nom,$year);
	}
	
	// Expenses with financing & without tax nominal NPV, including initial investment of equity
	$NPV1_exp_wo = 0;
	for ($i = 0; $i < $LifetimeFIT; $i++)
	{
		$year=$i+1;
		$NPV1_exp_wo = $NPV1_exp_wo + $Exp_wo_tax[$i]/pow(1+$WACC_nom,$year);
	}
	$NPV_exp_wo = $NPV1_exp_wo - $Equity;

	// Expenses with financing & with tax nominal NPV, including initial investment of equity
	$NPV1_exp = 0;
	for ($i = 0; $i < $LifetimeFIT; $i++)
	{
		$year=$i+1;
		$NPV1_exp = $NPV1_exp + $Exp_w_tax[$i]/pow(1+$WACC_nom,$year);
	}
	$NPV_exp = $NPV1_exp - $Equity;
	
	// Energy production nominal NPV
	$NPVn_elec = 0;
	for ($i = 0; $i < $LifetimeFIT; $i++)
	{
		$year=$i+1;
		$NPVn_elec = $NPVn_elec + $E_output[$i]/pow(1+$WACC_nom,$year);
	}
	
	// Energy production real NPV
	$NPVr_elec = 0;
	for ($i = 0; $i < $LifetimeFIT; $i++)
	{
		$year=$i+1;
		$NPVr_elec = $NPVr_elec + $E_output[$i]/pow(1+$WACC_real,$year);
	}
	
	// Lifetime levelized electricity generation cost (LCOE) calculation
	// LCOE, c€/kWh (with financing and without tax
	$LCOEn_wo = 100 * (-$NPV_exp_wo)/ $NPVn_elec;
	$LCOEr_wo = 100 * (-$NPV_exp_wo)/ $NPVr_elec;
	// LCOE, c€/kWh (with financing and with tax)
	$LCOEn = 100 * (-$NPV_exp)/ $NPVn_elec;
	$LCOEr = 100 * (-$NPV_exp)/ $NPVr_elec;
	
	return $ECONOMICS = array(
						'LifetimeFIT' => $LifetimeFIT,
						'Equity' => $Equity,
						'LoanAmount' => $LoanAmount,
						'E_output' => $E_output,
						'FIT_updated' => $FIT_updated,
						'I_energy' => $I_energy,
						'CfOM_updated' => $CfOM_updated,
						'CvOM_updated' => $CvOM_updated,
						'Ctransm_updated' => $Ctransm_updated,
						'COM' => $COM,
						'Unpaid' => $Unpaid,
						'Pay_Int' => $Pay_Int,
						'Pay_Cap' => $Pay_Cap,
						'Pay_total' => $Pay_total,
						'I_taxable' => $I_taxable,
						'Tax' => $Tax,
						'Exp_wo_tax' => $Exp_wo_tax,
						'Exp_w_tax' => $Exp_w_tax,
						'CF_wo_tax' => $CF_wo_tax,
						'CF_w_tax' => $CF_w_tax,
						'CF_Acc' => $CF_Acc,
						'NPV_inc' => $NPV_inc,
						'NPV_exp_wo' => $NPV_exp_wo,
						'NPV_exp' => $NPV_exp,
						'NPVn_elec' => $NPVn_elec,
						'NPVr_elec' => $NPVr_elec,
						'LCOEn_wo' => $LCOEn_wo,
						'LCOEr_wo' => $LCOEr_wo,
						'LCOEn' => $LCOEn,
						'LCOEr' => $LCOEr);
}

?>
