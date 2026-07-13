<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyZkAgentKey
{
    public function handle(Request $request, Closure $next)
    {
        $key = (string) $request->header('X-Api-Key');
        $expected = (string) config('services.zkteco.agent_key');

        if ($expected === '' || !hash_equals($expected, $key)) {
            abort(401, 'Invalid API key.');
        }

        return $next($request);
    }
}
