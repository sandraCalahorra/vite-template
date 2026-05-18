<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Permission;
use App\Http\Controllers\PermissionsActionsTypesController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\DB;

class PermissionsController extends Controller
{

    public function filterPermissions(Request $request)
    {
        $permission = Permission::select(
            'permissions.entity',
            'permissions.level',
            'permissions.action_id',
            'permissions_actions_types.name as permissionName',
            'permissions_actions_types.color as actionColor',
            'roles.name as roleName'
        )
        ->leftJoin('permissions_actions_types', 'permissions_actions_types.id', '=', 'permissions.action_id')
        ->leftJoin('users', 'users.level', '=', 'permissions.level')
        ->leftJoin('roles', 'roles.level', '=', 'permissions.level')
        ->distinct();

        // Filters
        if ($request->has('stringSearch')) {
            $permission->where('permissions.entity', 'LIKE', "%{$request->stringSearch}%");
        }

        if ($request->has('role')) {
            $permission->where('users.level', $request->role);
        }

        if ($request->has('actions')) {
            $permission->whereIn('permissions.action_id', $request->actions);
        }
        // Fetch all
        $rawPermissions = $permission->get();

        // Group by entity and level
        $grouped = $rawPermissions->groupBy(fn ($item) => $item->entity . '_' . $item->level);

        // Map to desired format
        $transformed = $grouped->map(function ($group) {
            $first = $group->first();

            return [
                'entity'   => $first->entity,
                'roleName'     => $first->roleName,
                'level'    => $first->level,
                'actions'  => $group
                                ->map(fn ($item) => [
                                    'name' => $item->permissionName,
                                    'color' => $item->actionColor,
                                ])
                                ->unique('name')
                                ->values(),
            ];
        })->values();

        // Sort
        $orderBy = $request->input('order_by', 'entity');
        $order = $request->input('order', 'ascending');

        if ($order === 'descending') {
            $orderedData = $transformed->sortByDesc($orderBy, SORT_NATURAL)->values();
        } else {
            $orderedData = $transformed->sortBy($orderBy, SORT_NATURAL)->values();
        }

        /* Set pagination parameters */
        $pagination = $this->getPaginationParams($request, 'permissions');
        $page = $pagination['page'];
        $perPage = $request->show_all ? (($orderedData->count() > 0) ? $orderedData->count() : 10) : $pagination['per_page'];

        /* Manually create a paginator */
        $paginated = new LengthAwarePaginator(
            $orderedData->forPage($page, $perPage),
            $orderedData->count(),
            $perPage,
            $page
        );

        return $paginated;
    }

    public function retrievePermissionsData(Request $request)
    {
        $permissionsActionsTypesController = new PermissionsActionsTypesController();
        $roleController = new RoleController();

        $mergedArray = [];
        $mergedArray['action_types'] = $permissionsActionsTypesController->getPermissionsActionsTypes($request);
        $mergedArray['roles'] = $roleController->getRoles($request);

        return $mergedArray;

    }

    public function getPermissions(Request $request)
    {
        $allPermissions = Permission::where('level', $request->level)
            ->join('permissions_actions_types', 'permissions.action_id', '=', 'permissions_actions_types.id')
            ->select('entity', DB::raw('GROUP_CONCAT(permissions_actions_types.name) as action'))
            ->groupBy('entity')
            ->get();

        return $allPermissions->sortBy('entity', SORT_NATURAL)->values();
    }

    public function getUserPermissions(Request $request)
    {
        $permission = Permission::where('level', $request->level)
            ->join('permissions_actions_types', 'permissions.action_id', '=', 'permissions_actions_types.id')
            ->select('entity', 'permissions_actions_types.name as action')
            ->get()
            ->groupBy('entity')
            ->map(fn ($actions) => $actions->pluck('action')->unique()->values());

        return $permission;
    }
    public function findPermissions(Request $request, $id)
    {
        $permission = Permission::find($id);

        $restrict = 'The requested action is unauthorized for the current user level';
        $user_object = $request->attributes->get('user_object');

        // restrict level access
        if ($user_object->level !== null) {
            if($user_object->level >= 10) {
                return response()->json(['error' => $restrict], 401);
            } else {
                return $permission;
            }
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }
    }

    public function createPermissions(Request $request)
    {
        $restrict = 'The requested action is unauthorized for the current user level';
        $user_object = $request->attributes->get('user_object');
        if ($user_object->level !== null) {
            if($user_object->level >= 10) {
                return response()->json(['error' => $restrict], 401);
            } else {
                if ($request->has('actionsArray')) {
                    return $this->handlePermissionsActions($request);
                } else {
                    return response()->json(['error' => 'actionsArray is required'], 401);
                }
            }
        } else {
            return response()->json(['error' => $restrict], 401);
        }
    }

    public function updatePermissions(Request $request, $id)
    {

        $restrict = 'The requested action is unauthorized for the current user level';
        $user_object = $request->attributes->get('user_object');

        // restrict level access
        if ($user_object->level !== null) {
            if($user_object->level >= 10) {
                return response()->json(['error' => $restrict], 401);
            } else {
                if ($request->has('actionsArray')) {
                    return $this->handlePermissionsActions($request);
                } else {
                    return response()->json(['error' => 'actionsArray is required'], 401);
                }
            }
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }
    }

    public function patchPermissions(Request $request, $id)
    {
        $restrict = 'The requested action is unauthorized for the current user level';
        $user_object = $request->attributes->get('user_object');

        // restrict level access
        if ($user_object->level !== null) {
            if($user_object->level >= 10) {
                return response()->json(['error' => $restrict], 401);
            } else {
                if ($request->has('actionsArray')) {
                    return $this->handlePermissionsActions($request);
                } else {
                    return response()->json(['error' => 'actionsArray is required'], 401);
                }
            }
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }

    }

    public function deletePermissions(Request $request)
    {

        $restrict = 'The requested action is unauthorized for the current user level';
        $user_object = $request->attributes->get('user_object');

        // restrict level access
        if ($user_object->level !== null) {
            if($user_object->level >= 10) {
                return response()->json(['error' => $restrict], 401);
            } else {
                return $this->handlePermissionsActions($request);
            }
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }

    }

    public function handlePermissionsActions(Request $request)
    {

        $permissions = Permission::select('permissions.*')->where('entity', $request->entity)->where('level', $request->level)->get()->values();
        if ($permissions && count($permissions) > 0) {
            // delete all corresponding rows
            foreach ($permissions as $key => $value) {
                $row = Permission::where('entity', $value['entity'])->where('level', $value['level'])->first();
                $row->delete();
            }
        }

        // ----> UPDATE OR CREATE <-----
        // create new ones
        if ($request->has('actionsArray')) {
            foreach ($request->actionsArray as $key => $value) {
                Permission::create([
                    'entity' => $request->entity,
                    'level' => $request->level,
                    'action_id' => $value['id']
                ]);
            }
        }
        return $permissions;
    }
}
