<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasUuids;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['organization_id','membership_id','title','message','is_read'];
    protected function casts(): array { return ['is_read'=>'boolean']; }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function membership(): BelongsTo { return $this->belongsTo(Membership::class); }
}
