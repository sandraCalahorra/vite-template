<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;

class AnnouncementsController extends Controller
{
    public function getActiveAnnouncements() {
         $announcements = Announcement::where('is_active', true)->orderBy('created_at', 'desc')->get();

         return $announcements;
    }
    public function filterAnnouncements(Request $request)
    {
        $query = Announcement::select('announcements.*');

        /* Apply the search filters */
        if ($request->filled('stringSearch')) {
            $query->where(function ($q) use ($request) {
                $q->where('announcements.id', 'LIKE', "%$request->stringSearch%")
                  ->orWhere('announcements.title', 'LIKE', "%$request->stringSearch%");
            });
        }

        /* Set sorting */
        if ($request->filled('order_by') && in_array($request->order, ['ascending', 'descending'])) {
            $order = ($request->order === 'descending') ? 'desc' : 'asc';
            $query->orderBy($request->order_by, $order);
        } else {
            $query->orderBy('is_active', 'desc')->orderBy('created_at', 'desc');
        }

        /* Pagination */
        $pagination = $this->getPaginationParams($request, 'anuncios_settings');
        $page = $pagination['page'];
        $perPage = $request->show_all ? ($query->count() ?: 10) : $pagination['per_page'];

        return $query->paginate($perPage, ['*'], 'page', $page);
    }
    public function getAnnouncements (Request $request)
    {
        $announcement= Announcement::select('announcements.*');

        $user_object = $request->attributes->get('user_object');

        // restrict level access
        if ($user_object->level !== null) {
        } else if (!$user_object) {
            $announcement->whereRaw('1 = 0');
        }

        $allAnnouncements = $announcement->get();
        return $allAnnouncements->sortBy('announcements.id', SORT_NATURAL)->values();
    }
    public function createAnnouncements(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user_object = $request->attributes->get('user_object');
        if (!$user_object || ($user_object->level && $user_object->level >= 20)) {
            return response()->json(['error' => 'Not allowed, nice try :)'], 401);
        }

        if($request->expires_on){
            $request->merge([
                'expires_on' => date('Y-m-d', strtotime($request->expires_on))
            ]);
        }

        $announcement = Announcement::create($request->all());

        return $announcement;
    }

    public function updateAnnouncements(Request $request, $id)
    {
        $restrict = 'Not allowed, nice try :)';
        $user_object = $request->attributes->get('user_object');

        if ($user_object->level && $user_object->level >= 20) {
            return response()->json(['error' => $restrict], 401);
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }

        $announcement = Announcement::find($id);

        if (!$announcement) {
            return response()->json(['error' => 'announcement not found'], 404);
        }

        $data = $request->all();

        $validator = Validator::make($data, [
            'title' => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if($request->expires_on){
            $request->merge([
                'expires_on' => date('Y-m-d', strtotime($request->expires_on))
            ]);
        }

        $announcement->update($request->all());

        return $announcement;
    }

    public function deleteAnnouncements(Request $request)
    {
        $user_object = $request->attributes->get('user_object');
        if (!$user_object || ($user_object->level && $user_object->level >= 20)) {
            return response()->json(['error' => 'Not allowed, nice try :)'], 401);
        }

        $announcement = Announcement::find($request->id);

        if ($announcement->delete()) {
            return 'El registro se ha borrado correctamente.';
        } else {
            return 'No se encontró ningún registro que borrar.';
        }
    }
}
