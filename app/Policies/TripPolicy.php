<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TripPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $authUser): bool
    {
        return $authUser->hasAnyRole(['super_admin', 'admin'])
            || $authUser->hasRole('driver')
            || $authUser->can('ViewAny:Trip');
    }

    public function view(User $authUser, Trip $trip): bool
    {
        if ($authUser->hasAnyRole(['super_admin', 'admin']) || $authUser->can('View:Trip')) {
            return true;
        }

        if ($authUser->hasRole('driver')) {
            return $trip->driver_id !== null && $trip->driver_id === $authUser->driver?->id;
        }

        return false;
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasAnyRole(['super_admin', 'admin']) || $authUser->can('Create:Trip');
    }

    public function update(User $authUser, Trip $trip): bool
    {
        if ($trip->isImmutable()) {
            return false;
        }

        return $authUser->hasAnyRole(['super_admin', 'admin']) || $authUser->can('Update:Trip');
    }

    public function delete(User $authUser, Trip $trip): bool
    {
        if ($trip->isImmutable() || $trip->isInProgress() || $trip->isCompleted()) {
            return false;
        }

        return $authUser->hasAnyRole(['super_admin', 'admin']) || $authUser->can('Delete:Trip');
    }

    public function assignResources(User $authUser, Trip $trip): bool
    {
        if ($trip->isImmutable() || $trip->isInProgress() || $trip->isCompleted()) {
            return false;
        }

        return $authUser->hasAnyRole(['super_admin', 'admin']) || $authUser->can('Update:Trip');
    }

    public function start(User $authUser, Trip $trip): bool
    {
        if (! $trip->isAssigned()) {
            return false;
        }

        if ($authUser->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        if ($authUser->hasRole('driver')) {
            return $trip->driver_id !== null && $trip->driver_id === $authUser->driver?->id;
        }

        return false;
    }

    public function cancel(User $authUser, Trip $trip): bool
    {
        if ($trip->isImmutable() || $trip->isInProgress() || $trip->isCompleted()) {
            return false;
        }

        return $authUser->hasAnyRole(['super_admin', 'admin']) || $authUser->can('Update:Trip');
    }
}
