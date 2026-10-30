<?php

namespace App\Http\Middleware;

use App\Models\Device;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The pilgrim API accepts device tokens only, not staff accounts.
 */
class EnsureDeviceToken
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var mixed $device */
        $device = $request->user();

        abort_unless($device instanceof Device && $device->pilgrim()->exists(), 403, 'This endpoint requires a paired device token.');

        return $next($request);
    }
}
