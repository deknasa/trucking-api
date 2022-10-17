<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class Authorized
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = auth('api')->user()->id;
        $class = $this->getClass(Route::current()->uri);
        $method = $this->convertMethod($request->method());

        if ($this->hasPermission($userId, $class, $method)) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }

    public function hasPermission(int $userId, string $class, string $method): bool
    {
        $permission = false;

        $userRole = DB::table('userrole')
            ->where('user_id', $userId)
            ->first();

        $userRoleAcl = DB::table('useracl')
            ->select('acos.id')
            ->leftJoin('acos', 'useracl.aco_id', '=', 'acos.id')
            ->where('acos.class', '=', $class)
            ->where('acos.method', '=', $method)
            ->where('useracl.user_id', $userRole->user_id ?? null);

        $userAcl = DB::table('useracl')
            ->select('acos.id')
            ->leftJoin('acos', 'useracl.aco_id', '=', 'acos.id')
            ->where('acos.class', '=', $class)
            ->where('acos.method', '=', $method)
            ->where('useracl.user_id', $userId)
            ->unionAll($userRoleAcl)
            ->count();

        $permission = $userAcl > 0;

        return $permission;
    }

    public function convertMethod(string $httpMethod): string
    {
        $methods = [
            'GET' => 'index',
            'POST' => 'store',
            'PATCH' => 'update',
            'DELETE' => 'destroy'
        ];

        return $methods[$httpMethod];
    }

    public function getClass(string $uri): string
    {
        $uri = str_replace('api/', '', $uri);
        $class = explode('/', $uri)[0];

        return $class;
    }
}
