<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || !$this->canAccessAdmin($user)) {
            abort(403, 'Bạn không có quyền truy cập vào trang quản trị viên.');
        }

        return $next($request);
    }

    private function canAccessAdmin($user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (
            !Schema::hasTable(config('permission.table_names.permissions', 'permissions'))
            || !Schema::hasTable(config('permission.table_names.roles', 'roles'))
            || !Schema::hasTable(config('permission.table_names.model_has_roles', 'model_has_roles'))
        ) {
            return false;
        }

        return $user->hasPermissionTo('admin.access');
    }
}
