import { z } from 'zod';

export const SimulationInputSchema = z.object({
  years: z.number().int().min(2).max(35),
  purchase_price: z.number().min(0),
  down_payment: z.number().min(0),
  works: z.number().min(0),
  agency_fee_rate_percent: z.number().min(0).max(10),
  notary_fee_rate_percent: z.number().min(0).max(15),
  interest_rate_percent: z.number().min(0).max(100),
  insurance_rate_percent: z.number().min(0).max(100),
  appreciation_rate_percent: z.number().min(0).max(20),
  acquisition_month: z.number().int().min(1).max(12),
  acquisition_year: z.number().int().min(1990).max(2100),
}).superRefine((v, ctx) => {
  // règle métier : prêt > 0
  const agency = v.purchase_price * v.agency_fee_rate_percent / 100;
  const notary = v.purchase_price * v.notary_fee_rate_percent / 100;
  const principalToFinance = v.purchase_price + v.works + agency + notary - v.down_payment;
  if (principalToFinance <= 0) {
    ctx.addIssue({ code: z.ZodIssueCode.custom, message: 'Loan amount must be positive after fees and down payment.' });
  }
});
