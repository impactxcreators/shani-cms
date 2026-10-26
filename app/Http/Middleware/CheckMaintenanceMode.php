<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        // Logged-in admins always see the real site.
        if (Auth::check()) {
            return $next($request);
        }

        // Never block the admin panel / login or asset routes.
        $path = $request->path();
        $adminPath = config('cms.filament_path', 'admin');
        if ($path === $adminPath || str_starts_with($path, $adminPath . '/')
            || str_starts_with($path, 'livewire')
            || str_starts_with($path, 'css') || str_starts_with($path, 'js')
            || str_starts_with($path, 'storage') || str_starts_with($path, 'build')) {
            return $next($request);
        }

        if (Setting::get('maintenance_mode')) {
            return response()->view('coming-soon', [
                'message' => Setting::get('maintenance_message', 'We will be back soon.'),
            ], 503);
        }

        return $next($request);
    }
}
