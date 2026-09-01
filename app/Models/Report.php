<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasUuids;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['organization_id','type','period','generated_at'];
    protected function casts(): array { return ['generated_at'=>'datetime']; }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
}
