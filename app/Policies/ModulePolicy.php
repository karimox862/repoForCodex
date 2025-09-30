<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User;

class ModulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isProfessor();
    }

    public function view(User $user, Module $module): bool
    {
        return $user->isProfessor()
            && $user->professor !== null
            && $module->professor_id === $user->professor->id;
    }

    public function grade(User $user, Module $module): bool
    {
        return $this->view($user, $module);
    }
}
