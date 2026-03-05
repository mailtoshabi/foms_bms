<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RolePermission
{
    public function handle($request, Closure $next, $roleKey)
    {
        $staff = Auth::guard('staff')->user();

        if (!$staff) {
            return redirect()->route('staff.login');
        }

        // Operation department full access
        $operationRoleId = utility('id_operation_dept');

        if ($staff->hasRoleId($operationRoleId)) {
            return $next($request);
        }

        $requiredRoleId = utility($roleKey);

        if (!$requiredRoleId) {
            abort(500, 'Role not configured in utilities.');
        }

        if (!$staff->hasRoleId($requiredRoleId)) {
            abort(403, 'Unauthorized Department Access');
        }

        return $next($request);
    }
}
