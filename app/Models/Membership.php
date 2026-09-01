<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membership extends Model
{
    use HasUuids;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['user_id','organization_id','role_id','joined_at','status'];
    protected function casts(): array { return ['joined_at' => 'datetime']; }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function role(): BelongsTo { return $this->belongsTo(Role::class); }
    public function projectMemberships(): HasMany { return $this->hasMany(ProjectMembership::class); }
    public function isActive(): bool { return $this->status === 'active'; }
    public function belongsToOrg(string $orgId): bool { return $this->organization_id === $orgId; }
}
