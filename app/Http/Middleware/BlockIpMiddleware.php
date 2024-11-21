<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BlockIpMiddleware
{
  /**
   * Lista de IPs bloqueados.
   */
  protected $blockedIps = [];

  /**
   * Handle an incoming request.
   */
  public function handle(Request $request, Closure $next)
  {
    if (in_array($request->ip(), $this->blockedIps)) {
      return response()->json(['message' => 'Access Denied'], 403);
    }

    return $next($request);
  }
}
