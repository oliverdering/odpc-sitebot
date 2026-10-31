"""D.R.O.L. offline bridge core. No networking, no hardware I/O."""
from dataclasses import dataclass
from enum import Enum
from typing import Callable

class State(str, Enum):
    DISCONNECTED = 'disconnected'
    READY = 'ready'
    ARMED = 'armed'
    SIMULATED_FLIGHT = 'simulated_flight'
    LINK_LOST = 'link_lost'

@dataclass(frozen=True)
class Capabilities:
    model: str
    protocol: str
    telemetry: bool = False
    verified_on_hardware: bool = False
    simulation_only: bool = True

class OfflineBridge:
    """Auditable simulation only; never infers real aircraft actions."""
    def __init__(self, adapter, clock: Callable[[], float], timeout_seconds=2.0):
        if timeout_seconds <= 0:
            raise ValueError('timeout_seconds must be positive')
        self.adapter = adapter
        self.clock = clock
        self.timeout_seconds = timeout_seconds
        self.state = State.DISCONNECTED
        self.last_heartbeat = None
        self.events = []

    def _event(self, name, **details):
        event = {'time': self.clock(), 'event': name, 'state': self.state.value, **details}
        self.events.append(event)
        return event

    def connect(self):
        if self.state != State.DISCONNECTED:
            raise RuntimeError('already connected')
        self.state = State.READY
        self.last_heartbeat = self.clock()
        return self._event('connected')

    def heartbeat(self):
        self.tick()  # Reject a stale heartbeat instead of reviving an expired link.
        if self.state in (State.DISCONNECTED, State.LINK_LOST):
            raise RuntimeError('link not active; reconnect explicitly')
        self.last_heartbeat = self.clock()
        return self._event('heartbeat')

    def arm(self):
        self._require(State.READY)
        self.state = State.ARMED
        return self._event('simulated_arm')

    def takeoff(self):
        self._require(State.ARMED)
        self.state = State.SIMULATED_FLIGHT
        return self._event('simulated_takeoff')

    def land(self):
        self._require(State.SIMULATED_FLIGHT)
        self.state = State.READY
        return self._event('simulated_landing_complete')

    def disconnect(self):
        self.state = State.DISCONNECTED
        self.last_heartbeat = None
        return self._event('disconnected')

    def tick(self):
        if self.state not in (State.DISCONNECTED, State.LINK_LOST) and self.last_heartbeat is not None:
            if self.clock() - self.last_heartbeat > self.timeout_seconds:
                previous = self.state.value
                self.state = State.LINK_LOST
                return self._event('link_timeout', previous_state=previous, aircraft_action='unknown')
        return None

    def _require(self, state):
        self.tick()
        if self.state != state:
            raise RuntimeError(f'expected {state.value}, got {self.state.value}')

    def capabilities(self):
        info = self.adapter.capabilities()
        return Capabilities(model=info['model'], protocol=info.get('transport', 'offline'))
