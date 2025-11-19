<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  array<int, string>  $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (! $user) {
            throw new HttpResponseException(response()->json([
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED));
        }

        $allowedRoles = $this->normalizeRoles($roles);

        if (empty($allowedRoles) || $this->userHasRole($user, $allowedRoles)) {
            return $next($request);
        }

        throw new HttpResponseException(response()->json([
            'message' => 'This action is unauthorized.',
        ], Response::HTTP_FORBIDDEN));
    }

    /**
     * @param  array<int, string>  $roles
     * @return array<int, UserRole>
     */
    private function normalizeRoles(array $roles): array
    {
        $normalized = [];

        foreach ($roles as $role) {
            foreach (explode('|', $role) as $segment) {
                $roleEnum = UserRole::tryFrom(trim(strtolower($segment)));

                if ($roleEnum) {
                    $normalized[$roleEnum->value] = $roleEnum;
                }
            }
        }

        return array_values($normalized);
    }

    /**
     * @param  array<int, UserRole>  $roles
     */
    private function userHasRole(User $user, array $roles): bool
    {
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}
