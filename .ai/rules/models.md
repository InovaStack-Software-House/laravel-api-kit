---
paths:
  - 'app/Models/*.php'
---

# Models

## API models use ULID primary keys
Every API-facing model uses the HasUlids trait and its migration declares $table->ulid('id')->primary(). Never $table->id(). Factories must be final, and HasFactory's @use docblock must sit directly above a dedicated `use HasFactory;` statement (Larastan level 10 fails otherwise).
