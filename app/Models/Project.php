<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasUuids;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['organization_id','name','description','status','start_date','end_date'];
    protected function casts(): array { return ['start_date'=>'date','end_date'=>'date']; }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function tasks(): HasMany { return $this->hasMany(Task::class); }
    public function projectMemberships(): HasMany { return $this->hasMany(ProjectMembership::class); }
}
