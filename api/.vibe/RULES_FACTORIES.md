# Global rules when creating Factory files

1. Always create functions for modifying each columns stated in the definition
2. For `status` columns, always use the `App\Enums\RecordStatus` enum (e.g. `RecordStatus::ACTIVE`), never a raw integer.
