from __future__ import annotations
from decimal import Decimal, ROUND_HALF_UP
from typing import Any, Dict, List
from datetime import date

import pandas as pd
from .calculator import CalculerMensualiteAncien
from .models import SimulationInput, SimulationResult, Schedules, AmortizationRow, YearAggregateRow

DEC2 = Decimal("0.01")
def round2(x: float | int) -> float:
    return float(Decimal(str(x)).quantize(DEC2, rounding=ROUND_HALF_UP))

def _precheck_positive_loan(p: SimulationInput) -> None:
    agency = p.purchase_price * (p.agency_fee_rate_percent / 100.0)
    notary = p.purchase_price * (p.notary_fee_rate_percent / 100.0)
    prelim_loan = p.purchase_price + p.works + agency + notary - p.down_payment
    if prelim_loan <= 0:
        raise ValueError("Loan amount must be positive after fees and down payment.")


def run_simulation(p: SimulationInput) -> SimulationResult:
    _precheck_positive_loan(p)
    tup = CalculerMensualiteAncien(
        p.years, p.purchase_price, p.interest_rate_percent, p.insurance_rate_percent,
        p.down_payment, f"{int(p.acquisition_month):02d}", f"{int(p.acquisition_year):04d}",
        p.agency_fee_rate_percent, p.notary_fee_rate_percent, p.works, p.appreciation_rate_percent
    )
    # Unpack (function returns a big tuple with DataFrame in position 6)
    (M, I, A, fraisNotaire, garantieBancaire, salaireMinimum, df, fraisAgence2, C2, *_rest) = tup

    if not isinstance(df, pd.DataFrame):
        raise RuntimeError("Calculator did not return a pandas DataFrame.")

    # Build monthly schedule with ISO dates
    # Assume first payment the month after acquisition
    start_y = int(p.acquisition_year)
    start_m = int(p.acquisition_month) + 1
    dates: List[str] = []
    for idx in range(len(df)):
        m = start_m + idx
        y = start_y + (m - 1) // 12
        mm = ((m - 1) % 12) + 1
        dates.append(f"{y:04d}-{mm:02d}-01")

    # Try to find expected french columns
    def pick(*cands: str) -> str:
        for c in cands:
            if c in df.columns: return c
        lower = {c.lower(): c for c in df.columns}
        for c in cands:
            if c.lower() in lower: return lower[c.lower()]
        raise KeyError(f"Missing expected columns among: {cands}")

    col_payment = pick("Mensualité", "Mensualite")
    col_principal = pick("Capital Amorti")
    col_interest = pick("Intérêts", "Interets")
    col_crd = pick("Capital restant dû", "Capital restant du", "CRD")

    monthly_rows: List[AmortizationRow] = []
    for i, (_, row) in enumerate(df.iterrows(), start=1):
        base_payment = float(row[col_payment])           # souvent assurance incluse
        principal = float(row[col_principal])
        interest = float(row[col_interest])
        insurance = max(0.0, base_payment - (principal + interest))  # par différence
        crd = max(0.0, float(row[col_crd]))
        monthly_rows.append(
            AmortizationRow(
                period_index=i,
                date_iso=dates[i-1],
                monthly_payment_eur=round2(base_payment),
                principal_component_eur=round2(principal),
                interest_component_eur=round2(interest),
                insurance_component_eur=round2(insurance),
                remaining_principal_eur=round2(crd),
            )
        )

    # Yearly aggregates
    by_year: Dict[int, List[AmortizationRow]] = {}
    for r in monthly_rows:
        y = int(r.date_iso[:4])
        by_year.setdefault(y, []).append(r)

    yearly: List[YearAggregateRow] = []
    for idx, y in enumerate(sorted(by_year.keys()), start=1):
        rows = by_year[y]
        yearly.append(YearAggregateRow(
            year_index=idx,
            sum_principal_eur=round2(sum(r.principal_component_eur for r in rows)),
            sum_interest_eur=round2(sum(r.interest_component_eur for r in rows)),
            sum_insurance_eur=round2(sum(r.insurance_component_eur for r in rows)),
            end_of_year_remaining_principal_eur=round2(rows[-1].remaining_principal_eur),
        ))

    return SimulationResult(
        monthly_payment_eur=round2(float(M)),
        total_interest_eur=round2(float(I)),
        total_insurance_eur=round2(float(A)),
        notary_fee_eur=round2(float(fraisNotaire)),
        bank_guarantee_eur=round2(float(garantieBancaire)),
        min_monthly_income_eur=int(round(float(salaireMinimum))),
        agency_fee_eur=round2(float(fraisAgence2)),
        loan_amount_eur=round2(float(C2)),
        appreciation_rate_percent=float(p.appreciation_rate_percent),
        schedules=Schedules(monthly=monthly_rows, yearly=yearly),
    )
