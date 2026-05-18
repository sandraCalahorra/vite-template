<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function filterUsers(Request $request)
    {
        // example query with the same model to refer another id from the same table in this case is the column managing_user referenced to the same table
        // $user = User::select('users.*', 'managers.name as managing_user_name', DB::raw("roles.name as role_name"))
        $user = User::select('users.*', DB::raw("roles.name as role_name"))
        ->leftJoin('roles','roles.level', 'users.level')
        // ->leftJoin('users as managers', 'users.managing_user', 'managers.id')
        ;

        /* Apply the search filters */
        if ($request->has('stringSearch')) {
            $user->where(function ($query) use ($request) {
                $query->where('users.name', 'LIKE', "%$request->stringSearch")
                    ->orWhere('users.email', 'LIKE', "%$request->stringSearch%");
            });
        }
        if ($request->has('role')) {
            $user->where("users.level", $request->role);
        }

        /* Get all users, then order the collection */
        $allUsers = $user->get();

        $orderBy = $request->input('order_by', 'name');
        $order = $request->input('order', 'ascending');

        if ($order === 'descending') {
            $orderedData = $allUsers->sortByDesc($orderBy, SORT_NATURAL)->values();
        } else {
            $orderedData = $allUsers->sortBy($orderBy, SORT_NATURAL)->values();
        }

        /* Set pagination parameters */
        $pagination = $this->getPaginationParams($request, 'usuarios');
        $page = $pagination['page'];
        $perPage = $request->show_all ? (($allUsers->count() > 0) ? $allUsers->count() : 10) : $pagination['per_page'];

        /* Manually create a paginator */
        $paginatedData = new LengthAwarePaginator(
            $orderedData->forPage($page, $perPage),
            $orderedData->count(),
            $perPage,
            $page
        );

        return $paginatedData;
    }


    public function getUsers(Request $request)
    {
        $allUsers = User::select("users.*", DB::raw("roles.name as role_name"))->leftJoin('roles','roles.level', 'users.level');

        $user_object = $request->attributes->get('user_object');

        $orderedData = $allUsers->get()->sortBy('name', SORT_NATURAL)->values();
        return $orderedData;
    }

    public function findUser(Request $request, $id)
    {
        $user_object = $request->attributes->get('user_object');
        $user = User::select("users.*")->find($id);
        $restrict = 'Not allowed, nice try :)';

        if ($user_object->level !== null) {
            if ($user_object->id != $id) {
                return response()->json(['error' => $restrict], 401);
            } else {
                if ($user_object->level >= 10 && $user->level < $user_object->level) return response()->json(['error' => $restrict], 401);
                return $user;
            }
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }
    }

    public function updateUser(Request $request, $id)
    {
        // Find the user to update.
        $user = User::find($id);

        $restrict = 'Not allowed, nice try :)';
        $user_object = $request->attributes->get('user_object');

        if ($user_object->level) {
            if ($user_object->level >= 10 && ($user->level < $user_object->level || $user_object->level >= 20 && $user->id != $user_object->id)){
                return response()->json(['error' => $restrict], 401);
            }
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }

        $request->validate([
            'name'  => 'required',
            'email' => 'required'
        ]);
        $request->merge([
            'password' => $request->password ? Hash::make($request->password) : $user->password
        ]);
        $user->update($request->all());
        return $user;
    }

    public function patchUser(Request $request, $id)
    {
        $user = User::find($id);

        $restrict = 'Not allowed, nice try :)';
        $user_object = $request->attributes->get('user_object');

        if ($user_object->level) {
            if ($user_object->level >= 10 && ($user->level < $user_object->level || $user_object->level >= 20 && $user->id != $user_object->id)){
                return response()->json(['error' => $restrict], 401);
            }
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }

        $userData = $request->all();
        $userData['password'] = $request->password ? Hash::make($request->password) : $user->password;

        $user->update($userData);
        return $user;
    }

    public function deleteUser(Request $request)
    {
        $ids = $request->ids ?: [$request->id];
        $user_object = $request->attributes->get('user_object');
        $restrict = 'Not allowed, nice try :)';

        if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }

        $deletedCount = 0;
        $errors = [];

        foreach ($ids as $id) {
            $user = User::find($id);
            if (!$user) {
                if ($id) $errors[] = "User ID $id not found";
                continue;
            }

            // Permission check
            if ($user_object->level !== null) {
                if ($user_object->level >= 20) {
                    $errors[] = "ID $id: $restrict";
                    continue;
                } else if ($user_object->level >= 10 && $user->level < $user_object->level) {
                    $errors[] = "ID $id: $restrict";
                    continue;
                }
            }

            $user->delete();
            $deletedCount++;
        }

        if ($deletedCount > 0) {
            $message = $deletedCount == 1 ? 'User deleted successfully' : "$deletedCount users deleted successfully";
            return response()->json([
                'message' => $message,
                'errors' => $errors
            ], 200);
        }

        return response()->json(['error' => 'No users were deleted', 'details' => $errors], 400);
    }
}
