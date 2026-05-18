<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleController extends Controller
{
    public function filterRoles(Request $request)
    {
        $role = Role::select([ 'level', 'name' ]);

        /* Apply the search filters */
        if ($request->has('stringSearch')) {

            $role->where(function ($query) use ($request) {
                $query->where('roles.name', 'LIKE', "%$request->stringSearch%")
                    ->orWhere('roles.level', 'LIKE', "%$request->stringSearch%");
            });
        }

        $user_object = $request->attributes->get('user_object');

        // restrict level access
        if ($user_object->level !== null) {
            if($user_object->level >= 10) {
                $role->whereRaw('1 = 0');
            } else {
                $role->where('level', ">=", $user_object->level);
            }
        } else if (!$user_object) {
            $role->whereRaw('1 = 0');
        }

        /* Get all roles, then order the collection */
        $allRoles = $role->get();

        $orderBy = $request->input('order_by', 'level');
        $order = $request->input('order', 'ascending');

        if ($order === 'descending') {
            $orderedData = $allRoles->sortByDesc($orderBy, SORT_NATURAL)->values();
        } else {
            $orderedData = $allRoles->sortBy($orderBy, SORT_NATURAL)->values();
        }

        /* Set pagination parameters */
        $pagination = $this->getPaginationParams($request, 'roles');
        $page = $pagination['page'];
        $perPage = $request->show_all ? (($allRoles->count() > 0) ? $allRoles->count() : 10) : $pagination['per_page'];

        /* Manually create a paginator */
        $paginatedData = new LengthAwarePaginator(
            $orderedData->forPage($page, $perPage),
            $orderedData->count(),
            $perPage,
            $page
        );

        return $paginatedData;
    }
    public function getRoles(Request $request)
    {
        $role = Role::select([ 'level', 'name' ]);

        $user_object = $request->attributes->get('user_object');


        $allRoles = $role->get();
        return $allRoles->sortBy('level', SORT_NATURAL)->values();
    }
    public function findRoles(Request $request, $id)
    {
        $role = Role::find($id);

        $restrict = 'The requested action is unauthorized for the current user level';
        $user_object = $request->attributes->get('user_object');

        // restrict level access
        if ($user_object->level !== null) {
            if($user_object->level >= 10) {
                return response()->json(['error' => $restrict], 401);
            } else {
                return $role;
            }
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }
    }

    public function createRoles(Request $request)
    {
        $restrict = 'The requested action is unauthorized for the current user level';
        $user_object = $request->attributes->get('user_object');
        if ($user_object->level !== null) {
            if($user_object->level >= 10) {
                return response()->json(['error' => $restrict], 401);
            } else {
                $role = Role::create($request->all());
                return $role->level;
            }
        } else {
            return response()->json(['error' => $restrict], 401);
        }
    }

    public function updateRoles(Request $request, $id)
    {
        $role = Role::find($id);

        $restrict = 'The requested action is unauthorized for the current user level';
        $user_object = $request->attributes->get('user_object');

        // restrict level access
        if ($user_object->level !== null) {
            if($user_object->level >= 10) {
                return response()->json(['error' => $restrict], 401);
            } else {
                return $role->update($request->all());
            }
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }
    }

    public function patchRoles(Request $request, $id)
    {
        $role = Role::find($id);

        $restrict = 'The requested action is unauthorized for the current user level';
        $user_object = $request->attributes->get('user_object');

        // restrict level access
        if ($user_object->level !== null) {
            if($user_object->level >= 10) {
                return response()->json(['error' => $restrict], 401);
            } else {
                return $role->update($request->all());
            }
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }

    }

    public function deleteRoles(Request $request)
    {
        $role = Role::find($request->id);

        if (!$role) {
            return response()->json(['error' => 'Role not found.'], 404);
        }

        $restrict = 'The requested action is unauthorized for the current user level';
        $user_object = $request->attributes->get('user_object');

        // restrict level access
        if ($user_object->level !== null) {
            if($user_object->level >= 10) {
                return response()->json(['error' => $restrict], 401);
            } else {
                return $role->delete();
            }
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }

    }
}
