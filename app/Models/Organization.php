<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['name'];

    public function memberships(): HasMany { return $this->hasMany(Membership::class); }
    public function projects(): HasMany { return $this->hasMany(Project::class); }
    public function expenses(): HasMany { return $this->hasMany(Expense::class); }
    public function auditLogs(): HasMany { return $this->hasMany(AuditLog::class); }
    public function notifications(): HasMany { return $this->hasMany(Notification::class); }
    public function reports(): HasMany { return $this->hasMany(Report::class); }
}
