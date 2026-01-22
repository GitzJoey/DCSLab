---
alwaysApply: false
description: 
---
# Standardisasi Resource API

Aturan ini mengatur standar penulisan API Resource (`app/Http/Resources`) untuk menjaga konsistensi format response dan performa aplikasi.

## 1. Struktur Dasar
- Class harus extends `Illuminate\Http\Resources\Json\JsonResource`.
- Method utama adalah `toArray($request)`.

## 2. ID Encoding
Semua field `id` (primary key) **WAJIB** di-encode menggunakan `Hashids`.
```php
use Vinkla\Hashids\Facades\Hashids;

'id' => Hashids::encode($this->id),
```

## 3. Handling Status (Soft Deletes)
Jika model menggunakan Soft Deletes, resource harus menangani status `DELETED` secara eksplisit. Gunakan helper method private `setStatus` di dalam class resource.

```php
use App\Enums\RecordStatusEnum;

public function toArray($request)
{
    return [
        // ... field lainnya
        'status' => $this->setStatus($this->status, $this->deleted_at),
    ];
}

private function setStatus($status, $deleted_at)
{
    if (! is_null($deleted_at)) {
        return RecordStatusEnum::DELETED->name;
    } else {
        return $status->name;
    }
}
```

## 4. Handling Relationships (Eager Loading)
Untuk mencegah N+1 Query problem, jangan akses relationship secara langsung. Gunakan `whenLoaded`.

### Single Relation (BelongsTo/HasOne)
Gunakan `new Resource(...)`.
```php
'company' => new CompanyResource($this->whenLoaded('company')),
// Atau jika ingin di-merge ke root level:
$this->mergeWhen($this->relationLoaded('company'), [
    'company' => new CompanyResource($this->whenLoaded('company')),
]),
```

### Collection Relation (HasMany)
Gunakan `Resource::collection(...)`.
```php
'branches' => BranchResource::collection($this->whenLoaded('branches')),
```

## 5. Format Data Lainnya
- **ULID**: Sertakan jika ada kolom `ulid`.
- **Enum**: Return `->name` atau `->value` sesuai kebutuhan (biasanya `->name` untuk status).
- **Boolean**: Pastikan return tipe boolean asli (`true`/`false`) atau `1`/`0` sesuai konvensi database project (Project ini tampaknya menggunakan boolean asli di response JSON).

## Contoh Lengkap
```php
namespace App\Http\Resources;

use App\Enums\RecordStatusEnum;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class BranchResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'code' => $this->code,
            'name' => $this->name,
            
            // Relationship handling
            'company' => new CompanyResource($this->whenLoaded('company')),
            
            // Status handling
            'status' => $this->setStatus($this->status, $this->deleted_at),
        ];
    }

    private function setStatus($status, $deleted_at)
    {
        if (! is_null($deleted_at)) {
            return RecordStatusEnum::DELETED->name;
        } else {
            return $status->name;
        }
    }
}
```
