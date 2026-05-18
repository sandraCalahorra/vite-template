<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\PermissionsActionsType;

class PermissionsActionsTypesController extends Controller
{
    public function filterPermissionsActionsTypes(Request $request)
    {
        $user_object = $request->attributes->get('user_object');
        $restrict = 'The requested action is unauthorized for the current user level';

        $PermissionsActionsType = PermissionsActionsType::select('permissions_actions_types.*');

        // restrict level access
        if ($user_object->level !== null) {
            if($user_object->level >= 10) {
                return response()->json(['error' => $restrict ], 401);
            }
        } else if (!$user_object) {
            // $PermissionsActionsType->whereRaw('1 = 0');
            return response()->json(['error' => $restrict ], 401);
        }

        /* Get all permissions, then order the collection */
        $allPermissions = $PermissionsActionsType->get();
        $orderedData = $allPermissions->sortBy('permissions_actions_types.name', SORT_NATURAL)->values();

        /* Set pagination parameters */
        $pagination = $this->getPaginationParams($request, 'permissions_actions_types');
        $page = $pagination['page'];
        $perPage = $request->show_all ? (($allPermissions->count() > 0) ? $allPermissions->count() : 10) : $pagination['per_page'];

        /* Manually create a paginator */
        $paginatedData = new LengthAwarePaginator(
            $orderedData->forPage($page, $perPage),
            $orderedData->count(),
            $perPage,
            $page
        );

        return $paginatedData;
    }

    public function getPermissionsActionsTypes(Request $request)
    {
        $PermissionsActionsType = PermissionsActionsType::select('permissions_actions_types.*');

        $user_object = $request->attributes->get('user_object');
        $restrict = 'The requested action is unauthorized for the current user level';

        // restrict level access
        if (!$user_object) {
            // $PermissionsActionsType->whereRaw('1 = 0');
            return response()->json(['error' => $restrict], 401);
        } else if ($user_object->level !== null) {
            if($user_object->level >= 10) {
                return response()->json(['error' => $restrict], 401);
            }
        }

        $allPermissions = $PermissionsActionsType->get();
        return $allPermissions->sortBy('permissions_actions_types.name', SORT_NATURAL)->values();
    }
    public function findPermissionsActionsTypes(Request $request, $id)
    {
        $PermissionsActionsType = PermissionsActionsType::find($id);

        $restrict = 'The requested action is unauthorized for the current user level';
        $user_object = $request->attributes->get('user_object');

        // restrict level access
        if ($user_object->level !== null) {
            if($user_object->level >= 10) {
                return response()->json(['error' => $restrict], 401);
            } else {
                return $PermissionsActionsType;
            }
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }
    }

    public function createPermissionsActionsTypes(Request $request)
    {
        $restrict = 'The requested action is unauthorized for the current user level';
        $user_object = $request->attributes->get('user_object');
        if ($user_object->level !== null) {
            if($user_object->level >= 10) {
                return response()->json(['error' => $restrict], 401);
            } else {
                $PermissionsActionsType = PermissionsActionsType::create($request->all());
                return $PermissionsActionsType;
            }
        } else {
            return response()->json(['error' => $restrict], 401);
        }
    }

    public function updatePermissionsActionsTypes(Request $request, $id)
    {
        $permissionsactionstype = PermissionsActionsType::find($id);

        $restrict = 'The requested action is unauthorized for the current user level';
        $user_object = $request->attributes->get('user_object');

        // restrict level access
        if ($user_object->level !== null) {
            if($user_object->level >= 10) {
                return response()->json(['error' => $restrict], 401);
            } else {
                return $permissionsactionstype->update($request->all());
            }
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }
    }

    public function patchPermissionsActionsTypes(Request $request, $id)
    {
        $permissionsactionstype = PermissionsActionsType::find($id);

        $restrict = 'The requested action is unauthorized for the current user level';
        $user_object = $request->attributes->get('user_object');

        // restrict level access
        if ($user_object->level !== null) {
            if($user_object->level >= 10) {
                return response()->json(['error' => $restrict], 401);
            } else {
                return $permissionsactionstype->update($request->all());
            }
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }

    }

    public function deletePermissionsActionsTypes(Request $request)
    {
        $permissionsactionstype = PermissionsActionsType::find($request->id);

        $restrict = 'The requested action is unauthorized for the current user level';
        $user_object = $request->attributes->get('user_object');

        // restrict level access
        if ($user_object->level !== null) {
            if($user_object->level >= 10) {
                return response()->json(['error' => $restrict], 401);
            } else {
                return $permissionsactionstype->delete();
            }
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }

    }
}
