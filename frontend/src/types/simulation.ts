export type SimulationInput = {
  years: number;                    
  purchase_price: number;           
  down_payment: number;             
  works: number;                    
  agency_fee_rate_percent: number;  
  notary_fee_rate_percent: number;  
  interest_rate_percent: number;    
  insurance_rate_percent: number;   
  appreciation_rate_percent: number;
  acquisition_month: number;        
  acquisition_year: number;         
};

export type AmortizationRow = {
  period_index: number;             
  date_iso: string;                 
  monthly_payment_eur: number;      
  principal_component_eur: number;  
  interest_component_eur: number;   // intérêts
  insurance_component_eur: number;  // assurance (si distincte)
  remaining_principal_eur: number;  // capital restant dû
};

export type YearAggregateRow = {
  year_index: number;               
  sum_principal_eur: number;
  sum_interest_eur: number;
  sum_insurance_eur: number;
  end_of_year_remaining_principal_eur: number;
};

export type SimulationResult = {
  currency: 'EUR';
  monthly_payment_eur: number;
  total_interest_eur: number;
  total_insurance_eur: number;
  notary_fee_eur: number;
  bank_guarantee_eur: number;       // e.g. 1.5% du prêt (règle actuelle)
  min_monthly_income_eur: number;   // arrondi à l’entier
  agency_fee_eur: number;
  loan_amount_eur: number;          // montant financé net
  appreciation_rate_percent: number;
  schedules: {
    monthly: AmortizationRow[];
    yearly: YearAggregateRow[];
  };
};
