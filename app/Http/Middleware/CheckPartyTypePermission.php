<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPartyTypePermission
{
    protected $actionPermissionMap = [
        'create' => 'create',
        'edit' => 'edit',
        'list' => 'view',
        'datatableList' => 'view',
        'delete' => 'delete',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $partyType = $request->route('partyType');
        $action = $request->route()->getActionMethod();

        $partyType = $request->route('partyType');
        $action = $request->route()->getActionMethod();

        if (isset($this->actionPermissionMap[$action])) {
            if($partyType == 'customer' || $partyType == 'supplier'){
                $permission = $partyType . '.' . $this->actionPermissionMap[$action];
                if (!optional(auth()->user())->can($permission)) {
                    abort(403, 'Unauthorized action.');
                }
            }
        }
        return $next($request);
    }
}