from fastapi.testclient import TestClient
from simulator.app import app

client = TestClient(app)

BASE = {
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

def test_years_too_small_is_422():
    payload = {**BASE, "years": 1}
    resp = client.post("/simulate", json=payload)
    assert resp.status_code == 422

def test_agency_fee_out_of_range_is_422():
    payload = {**BASE, "agency_fee_rate_percent": 99}
    resp = client.post("/simulate", json=payload)
    assert resp.status_code == 422

def test_zero_price_returns_422():
    payload = {**BASE, "purchase_price": 0, "down_payment": 0, "works": 0}
    resp = client.post("/simulate", json=payload)
    assert resp.status_code == 422

