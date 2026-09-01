<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasUuids;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['organization_id','creator_membership_id','reviewer_membership_id','approver_membership_id','description','amount','status'];
    protected function casts(): array { return ['amount'=>'decimal:2']; }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function creator(): BelongsTo { return $this->belongsTo(Membership::class, 'creator_membership_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(Membership::class, 'reviewer_membership_id'); }
    public function approver(): BelongsTo { return $this->belongsTo(Membership::class, 'approver_membership_id'); }

    public function canTransitionTo(string $to): bool {
        return match($this->status.'->'.$to) {
            'pending->approved','pending->rejected','approved->paid' => true,
            default => false,
        };
    }
}
