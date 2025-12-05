<?php

namespace App\Services;

use App\Models\User;

class RoleRedirect
{
    protected $map = [
        'kpp-operator' => 'security.kpp',
        'personal-control' => 'personal.control',
        'otdel-kadrov' => 'cabinet.employees.index',
        'kt-operator' => 'kt.kt_operator',
        'kt-priemosdatchik' => 'kt.kt_operator',
        'kt-crane' => 'kt.kt_crane',
        'ashana' => 'kitchen.index',
        'mark-manager' => 'mark.index',
        'mark-dispatcher' => 'mark.manager',
        'kpp-security' => 'white.car.lists',
    ];

    public function redirectFor(User $user): \Illuminate\Http\RedirectResponse
    {
        foreach ($this->map as $role => $route) {
            if ($user->hasRole($role)) {
                return redirect()->route($route);
            }
        }

        return redirect()->route('cabinet');
    }
}
