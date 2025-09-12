from pydantic import BaseModel
from typing import List, Optional

class ErrorDetail(BaseModel):
    field: Optional[str] = None
    code: Optional[str] = None
    hint: Optional[str] = None

class ErrorResponse(BaseModel):
    error: dict  # {"type": "...", "message": "...", "details": [ErrorDetail, ...]}
