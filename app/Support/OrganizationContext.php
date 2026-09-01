<?php

namespace App\Support;

use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;

class OrganizationContext
{
    public ?User $user = null;
    public ?Organization $organization = null;
    public ?Membership $membership = null;
    public array $permissions = [];

    public function set(User $user, Organization $org, Membership $membership, array $permissions): void
    {
        $this->user = $user;
        $this->organization = $org;
        $this->membership = $membership;
        $this->permissions = $permissions;
    }

    public function clear(): void
    {
        $this->user = null;
        $this->organization = null;
        $this->membership = null;
        $this->permissions = [];
    }

    public function hasPermission(string $perm): bool { return in_array($perm, $this->permissions, true); }
    public function currentOrganizationId(): ?string { return $this->organization?->id; }
    public function currentMembershipId(): ?string { return $this->membership?->id; }
    public function isActive(): bool { return $this->membership?->status === 'active'; }
}
