<?php

namespace App\Http\Middleware;

use App\Models\Redirect as RedirectModel;
use Closure;
use Illuminate\Http\Request;

class HandleRedirects
{
    public function handle(Request $request, Closure $next)
    {
        $path = '/' . ltrim($request->path(), '/');
        $redirect = RedirectModel::where('source', $path)->where('is_active', true)->first();

        if ($redirect) {
            $redirect->increment('hit_count');
            $redirect->forceFill(['last_hit_at' => now()])->save();

            if ((int) $redirect->status_code === 410) {
                abort(410);
            }
            return redirect($redirect->destination, $redirect->status_code);
        }

        return $next($request);
    }
}
