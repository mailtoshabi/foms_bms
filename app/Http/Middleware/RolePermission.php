<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RolePermission
{
    public function handle($request, Closure $next, string ...$roleKeys)
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

        // Allow access if staff has ANY of the specified roles (OR logic)
        foreach ($roleKeys as $roleKey) {
            $requiredRoleId = utility($roleKey);

            if (!$requiredRoleId) {
                abort(500, 'Role not configured in utilities: ' . $roleKey);
            }

            if ($staff->hasRoleId($requiredRoleId)) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized Department Access');
    }
}
