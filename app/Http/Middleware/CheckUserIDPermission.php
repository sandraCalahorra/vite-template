<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class checkUserIDPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissionsArray)
    {
        $permissionsArray = str_replace(' ', '', $permissionsArray);
        $user_object = $request->attributes->get('user_object') ?? null;

        $explicitlyAllowedArray = $this->getExplicitlyAllowedValues($permissionsArray);
        $explicitlyDeniedArray = $this->getExplicitlyDeniedValues($permissionsArray);

        $isUserExplicitlyAllowed = $this->isUserExplicitlyAllowed($user_object->level, $explicitlyAllowedArray);
        $isUserExplicitlyDenied = $this->isUserExplicitlyDenied($user_object->level, $explicitlyDeniedArray);

        if ($isUserExplicitlyDenied) {
            return response()->json(['error' => 'The requested action is unauthorized for the current user ID.'], Response::HTTP_UNAUTHORIZED);
        } else {
            $request->attributes->set('isUserExplicitlyAllowed', $isUserExplicitlyAllowed);
            return $next($request);
        }
    }

    private function getExplicitlyAllowedValues($permissionsArray) {
        // Receives an array of permissions and returns an array of explicitly allowed values (e.g., those not starting with "!")
        $filteredArray = array_filter($permissionsArray, function($rule) {
            return substr($rule, 0, 1) !== '!';
        });

        return $filteredArray;
    }

    private function getExplicitlyDeniedValues($permissionsArray) {
        // Receives an array of permissions and returns an array of explicitly denied values (e.g., those starting with "!")
        $filteredArray = array_filter($permissionsArray, function($rule) {
            return substr($rule, 0, 1) === '!';
        });

        return $filteredArray;
    }

    private function isValueIncluded($id, $array) {
        foreach ($array as $rule) {

            // Check for specific number
            if (is_numeric($rule) && $id == $rule) {
                return true;
            }

            // Check for inequalities (<, <=, >, >=)
            if (preg_match('/^(<|>|<=|>=)(\d+)$/', $rule, $matches)) {

                $operator = $matches[1];
                $threshold = (int)$matches[2];

                if (eval("return \$id $operator \$threshold;")) {
                    return true;
                }
            }

            // Check for range (e.g., "12-14")
            if (preg_match('/^(\d+)-(\d+)$/', $rule, $matches)) {

                $min = (int)$matches[1];
                $max = (int)$matches[2];

                if ($id >= $min && $id <= $max) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isUserExplicitlyAllowed($id, $permissionsArray) {
        return $this->isValueIncluded($id, $permissionsArray);
    }

    private function isUserExplicitlyDenied($id, $permissionsArray) {

        // Remove the "!" character from the array
        $permissionsArray = array_map(function($rule) {
            return ltrim($rule, '!');
        }, $permissionsArray);

        return $this->isValueIncluded($id, $permissionsArray);
    }
}
