<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Contracts\UserTypeInterface;
use App\Models\Journal as OperationJournal;

class Journal
{
    /**
     * Handle an incoming request.
     *
     * @param Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, JsonResponse $response)
    {
        /**
         * @var UserTypeInterface $user
         */
        $user = auth()->user();
        if ($user && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $operation = OperationJournal::getOperation($request);

            $query = $request->query();
            $except = array_keys($request->query());
            if ($request->has('password')) {
                $except[] = 'password';
            }

            if ($request->has('access_token')) {
                $except[] = 'access_token';
            }

            OperationJournal::create([
                'user_id' => $user->id,
                'user_type' => $user->getUserType(),
                'action' => $operation['action'],
                'target' => $operation['target'],
                'method' => $request->method(),
                'path' => $request->path(),
                'query' => $query,
                'params' => $request->except($except),
                'ip' => $request->ip()
            ]);
        }
    }
}
