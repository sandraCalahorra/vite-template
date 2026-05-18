<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Get the pagination parameters from the request.
     * Supports both top-level parameters and the 'pagination' array sent by the frontend.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $tableName
     * @return array
     */
    protected function getPaginationParams(Request $request, string $tableName)
    {
        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 10;

        // Check if the frontend sent the pagination array
        if ($request->has('pagination') && is_array($request->pagination)) {
            foreach ($request->pagination as $p) {
                if (isset($p['name']) && $p['name'] === "{$tableName}_1") {
                    $page = $p['pagination']['page'] ?? $page;
                    $perPage = $p['pagination']['per_page'] ?? $perPage;
                    break;
                }
            }
        }

        return [
            'page' => (int) $page,
            'per_page' => (int) $perPage
        ];
    }
}
