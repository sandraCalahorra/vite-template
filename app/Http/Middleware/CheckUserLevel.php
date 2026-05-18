<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserLevel
{
    public function handle(Request $request, Closure $next, ...$allowedLevels)
    {
        $allowedLevels = str_replace(' ', '', $allowedLevels);

        $user_object = $request->attributes->get('user_object') ?? null;
        $isUserExplicitlyAllowed = $request->attributes->get('isUserExplicitlyAllowed') ?? false;

        // Check if the value in $user_object->level matches any of the values in $allowedLevels
        if ($user_object && ($isUserExplicitlyAllowed || $this->isLevelAllowed($user_object->level, $allowedLevels))) {
            return $next($request);
        } else {
            return response()->json(['error' => 'MW: The requested action is unauthorized for the current user level'], Response::HTTP_UNAUTHORIZED);
        }

    }

    private function isLevelAllowed($level, $allowedLevels) {

        // Rearrange the allowed levels array to ensure that the explicitly excluded values (e.g., "!20") are checked first
        usort($allowedLevels, function($a, $b) {
            return substr($a, 0, 1) === '!' ? -1 : 1;
        });

        foreach ($allowedLevels as $rule) {
            // Check for explicitly excluded values (e.g., "!20")
            if (preg_match('/^!(\d+)$/', $rule, $matches)) {
                $excludedLevel = (int)$matches[1];

                if ($level == $excludedLevel) {
                    return false;
                }
            }

            // Check for explicitly excluded range (e.g., "!12-14")
            if (preg_match('/^!(\d+)-(\d+)$/', $rule, $matches)) {
                $excludedMin = (int)$matches[1];
                $excludedMax = (int)$matches[2];

                if ($level >= $excludedMin && $level <= $excludedMax) {
                    return false;
                }
            }

            // Check for explicitly excluded inequalities (!<, !<=, !>, !>=)
            if (preg_match('/^!(<|>|<=|>=)(\d+)$/', $rule, $matches)) {
                $operator = $matches[1];
                $threshold = (int)$matches[2];

                if (eval("return \$level $operator \$threshold;")) {
                    return false;
                }
            }

            // Check for specific number
            if (is_numeric($rule) && $level == $rule) {
                return true;
            }

            // Check for inequalities (<, <=, >, >=)
            if (preg_match('/^(<|>|<=|>=)(\d+)$/', $rule, $matches)) {
                $operator = $matches[1];
                $threshold = (int)$matches[2];

                if (eval("return \$level $operator \$threshold;")) {
                    return true;
                }
            }

            // Check for range (e.g., "12-14")
            if (preg_match('/^(\d+)-(\d+)$/', $rule, $matches)) {
                $min = (int)$matches[1];
                $max = (int)$matches[2];

                if ($level >= $min && $level <= $max) {
                    return true;
                }
            }
        }

        return false;
    }
}
