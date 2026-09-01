<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMembership extends Model
{
    public $incrementing = false;
    protected $primaryKey = null;
    protected $keyType = 'string';
    protected $fillable = ['project_id','organization_id','membership_id'];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function membership(): BelongsTo { return $this->belongsTo(Membership::class); }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
}
