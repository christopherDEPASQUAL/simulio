from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from .models import SimulationInput, SimulationResult
from .services import run_simulation

app = FastAPI(title="Simulio Simulator API")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:5173", "http://localhost:8000"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.post("/simulate", response_model=SimulationResult)
def simulate(payload: SimulationInput):
    try:
        return run_simulation(payload)
    except ValueError as e:
        raise HTTPException(status_code=422, detail={"type": "DOMAIN_ERROR", "message": str(e)})
    except Exception:
        raise HTTPException(status_code=500, detail={"type": "INTERNAL_ERROR", "message": "Unexpected error"})
