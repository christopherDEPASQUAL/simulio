from __future__ import annotations
import io, sys
from contextlib import contextmanager
from typing import Any, Callable, Tuple

@contextmanager
def _silence_stdout() -> None:
    saved = sys.stdout
    try:
        sys.stdout = io.StringIO()
        yield
    finally:
        sys.stdout = saved

with _silence_stdout():
    from . import provided_calculator as _provided  # unchanged file

_CANDIDATE_NAMES = [
    "CalculerMensualité39_bis2_ANCIEN",  # with accent (official)
    "CalculerMensualite39_bis2_ANCIEN",  # without accent
    "CalculerMensualiteAncien",
]

_TARGET: Callable[..., Tuple[Any, ...]] | None = None
for _name in _CANDIDATE_NAMES:
    if hasattr(_provided, _name):
        _TARGET = getattr(_provided, _name)
        break

if _TARGET is None:
    raise ImportError(
        "Calculator function not found in provided_calculator.py. "
        f"Tried: {', '.join(_CANDIDATE_NAMES)}."
    )

def CalculerMensualiteAncien(
    N: int,
    C2: float,
    T: float,
    ASSU: float,
    apport: float,
    mois: str,
    annee: str,
    fraisAgence: float,
    fraisNotaire: float,
    TRAVAUX: float,
    revalorisationBien: float,
):
    return _TARGET(N, C2, T, ASSU, apport, mois, annee, fraisAgence, fraisNotaire, TRAVAUX, revalorisationBien)

__all__ = ["CalculerMensualiteAncien"]
