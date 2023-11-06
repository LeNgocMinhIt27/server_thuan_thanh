<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Class ApiRouteNeedsPermission.
 */
class ApiRouteNeedsPermission
{
    /**
     * @param $request
     * @param Closure $next
     * @param $permission
     * @param bool $needsAll
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $role, $needsAll = false)
    {

        /*
         * role array
         */
        $user = $request->user();
        $access = false;
        if (!empty($user)) {
            if (strpos($role, ';') !== false) {
                $roles = explode(';', $role);
                $access = $user->allowMultiple($roles, ($needsAll === 'true' ? true : false));
            } else {
                $access = $user->allow($role);
            }
        }

        if (!$access) {
            return response()
                ->json([
                    'status_code' => 403,
                    'message' => trans('auth.general_error')
                ], 403);
        }

        return $next($request);
    }
}
