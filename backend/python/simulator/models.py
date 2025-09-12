from typing import List
from pydantic import BaseModel, Field, conint, confloat

class SimulationInput(BaseModel):
    years: conint(ge=2, le=35)
    purchase_price: conint(ge=0)
    down_payment: conint(ge=0)
    works: conint(ge=0)
    agency_fee_rate_percent: confloat(ge=0, le=10)
    notary_fee_rate_percent: confloat(ge=0, le=15)
    interest_rate_percent: confloat(ge=0, le=100)
    insurance_rate_percent: confloat(ge=0, le=100)
    appreciation_rate_percent: confloat(ge=0, le=20)
    acquisition_month: conint(ge=1, le=12)
    acquisition_year: conint(ge=1990, le=2100)

class AmortizationRow(BaseModel):
    period_index: conint(ge=1)
    date_iso: str
    monthly_payment_eur: float
    principal_component_eur: float
    interest_component_eur: float
    insurance_component_eur: float
    remaining_principal_eur: float

class YearAggregateRow(BaseModel):
    year_index: conint(ge=1)
    sum_principal_eur: float
    sum_interest_eur: float
    sum_insurance_eur: float
    end_of_year_remaining_principal_eur: float

class Schedules(BaseModel):
    monthly: List[AmortizationRow]
    yearly: List[YearAggregateRow]

class SimulationResult(BaseModel):
    currency: str = Field(default="EUR")
    monthly_payment_eur: float
    total_interest_eur: float
    total_insurance_eur: float
    notary_fee_eur: float
    bank_guarantee_eur: float
    min_monthly_income_eur: int
    agency_fee_eur: float
    loan_amount_eur: float
    appreciation_rate_percent: float
    schedules: Schedules
