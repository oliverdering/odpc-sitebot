"""Shared, dependency-free application lifecycle model."""
from dataclasses import dataclass, field
from datetime import datetime, timezone
from enum import Enum
from uuid import uuid4

class Status(str, Enum):
    DRAFT = "draft"
    APPROVED = "approved"
    SENT = "sent"
    ACKNOWLEDGED = "acknowledged"
    INTERVIEW = "interview"
    REJECTED = "rejected"
    OFFER = "offer"
    WITHDRAWN = "withdrawn"

ALLOWED = {
    Status.DRAFT: {Status.APPROVED, Status.WITHDRAWN},
    Status.APPROVED: {Status.SENT, Status.DRAFT, Status.WITHDRAWN},
    Status.SENT: {Status.ACKNOWLEDGED, Status.INTERVIEW, Status.REJECTED, Status.OFFER, Status.WITHDRAWN},
    Status.ACKNOWLEDGED: {Status.INTERVIEW, Status.REJECTED, Status.OFFER, Status.WITHDRAWN},
    Status.INTERVIEW: {Status.OFFER, Status.REJECTED, Status.WITHDRAWN},
    Status.REJECTED: set(),
    Status.OFFER: {Status.WITHDRAWN},
    Status.WITHDRAWN: set(),
}

@dataclass
class Application:
    job_id: str
    candidate_id: str
    id: str = field(default_factory=lambda: str(uuid4()))
    status: Status = Status.DRAFT
    gmail_thread_id: str | None = None
    events: list = field(default_factory=list)

    def transition(self, new_status: Status, reason: str):
        if new_status not in ALLOWED[self.status]:
            raise ValueError(f"Invalid transition: {self.status} -> {new_status}")
        if not reason.strip():
            raise ValueError("Reason required for audit trail")
        self.events.append({"at": datetime.now(timezone.utc).isoformat(),
                            "from": self.status.value, "to": new_status.value, "reason": reason})
        self.status = new_status
