<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasUuids;
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['organization_id','membership_id','action','entity_type','entity_id','timestamp'];
    protected function casts(): array { return ['timestamp'=>'datetime']; }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function membership(): BelongsTo { return $this->belongsTo(Membership::class); }
}
