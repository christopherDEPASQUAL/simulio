import pandas as pd
from simulator.models import SimulationInput
from simulator import services

def test_wrapper_builds_schedules_and_rounds(monkeypatch):
    # Fake DF with one month
    df = pd.DataFrame([{
        "Mensualité": 1000.0,         # includes insurance in provided function
        "Capital Amorti": 700.004,
        "Intérêts": 299.994,
        "Capital restant dû": 180000.001,
    }])

    # Fake raw function result tuple (only the first 9 elements used by our wrapper)
    fake_tuple = (
        1000.0,   # M
        500.0,    # I
        50.0,     # A
        4000.0,   # fraisNotaire
        3000.0,   # garantieBancaire
        2857.1,   # salaireMinimum
        df,       # df
        2000.0,   # fraisAgence2
        180000.0, # C2 (loan amount)
    )

    def fake_calc(*args, **kwargs):
        return fake_tuple + (None, None, None, None, 0.0)

    # Patch the call used by services
    monkeypatch.setattr(services, "CalculerMensualiteAncien", fake_calc)

    p = SimulationInput(
        years=25, purchase_price=200000, down_payment=0, works=0,
        agency_fee_rate_percent=3.0, notary_fee_rate_percent=2.5,
        interest_rate_percent=4.0, insurance_rate_percent=0.3,
        appreciation_rate_percent=1.0, acquisition_month=2, acquisition_year=2025
    )

    result = services.run_simulation(p)

    # Rounded top-level
    assert result.monthly_payment_eur == 1000.00
    assert result.total_interest_eur == 500.00
    assert result.total_insurance_eur == 50.00
    assert result.notary_fee_eur == 4000.00
    assert result.bank_guarantee_eur == 3000.00
    assert result.min_monthly_income_eur == 2857  # int
    assert result.agency_fee_eur == 2000.00
    assert result.loan_amount_eur == 180000.00

    # One monthly row with clean rounding & ISO date
    m = result.schedules.monthly[0]
    assert m.date_iso.endswith("-01")
    assert m.monthly_payment_eur == 1000.00
    # insurance = 1000 - (700.00 + 299.99) = 0.01 after rounding tolerance
    assert abs((m.principal_component_eur + m.interest_component_eur + m.insurance_component_eur) - m.monthly_payment_eur) <= 0.02
    assert m.remaining_principal_eur == 180000.00
