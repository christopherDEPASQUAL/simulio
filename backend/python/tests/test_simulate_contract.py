from fastapi.testclient import TestClient
from simulator.app import app

client = TestClient(app)

def _payload():
    return {
        "years": 25,
        "purchase_price": 200000,
        "down_payment": 0,
        "works": 0,
        "agency_fee_rate_percent": 3.0,
        "notary_fee_rate_percent": 2.5,
        "interest_rate_percent": 4.0,
        "insurance_rate_percent": 0.3,
        "appreciation_rate_percent": 1.0,
        "acquisition_month": 2,
        "acquisition_year": 2025,
    }

def test_simulate_happy_path_contract():
    resp = client.post("/simulate", json=_payload())
    assert resp.status_code == 200, resp.text
    data = resp.json()

    # Top-level keys
    for k in [
        "currency","monthly_payment_eur","total_interest_eur","total_insurance_eur",
        "notary_fee_eur","bank_guarantee_eur","min_monthly_income_eur",
        "agency_fee_eur","loan_amount_eur","appreciation_rate_percent","schedules"
    ]:
        assert k in data, f"missing key: {k}"

    monthly = data["schedules"]["monthly"]
    yearly = data["schedules"]["yearly"]

    # Length & non-negativity
    assert isinstance(monthly, list) and isinstance(yearly, list)
    assert len(monthly) == _payload()["years"] * 12
    crds = [row["remaining_principal_eur"] for row in monthly]
    assert min(crds) >= 0
    assert crds[0] >= crds[-1]

    # Components sum ~ monthly payment (tolerance for rounding)
    sample = monthly[:12]  # check first year only
    for row in sample:
        total = row["principal_component_eur"] + row["interest_component_eur"] + row["insurance_component_eur"]
        assert abs(total - row["monthly_payment_eur"]) <= 0.02
