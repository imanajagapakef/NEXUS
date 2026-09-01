<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasUuids;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['project_id','organization_id','assignee_membership_id','title','description','status','priority','due_date'];
    protected function casts(): array { return ['due_date'=>'date']; }

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(Membership::class, 'assignee_membership_id'); }
}
