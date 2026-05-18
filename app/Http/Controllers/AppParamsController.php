<?php

namespace App\Http\Controllers;

use App\Models\AppParams;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\JsonResponse;

class AppParamsController extends Controller
{
    public function getAppParams(Request $request)
    {
        $user = auth()->user();

        // if (!$user) {
        //     return response()->json([
        //         'message' => 'User not authenticated',
        //         'logout' => true,
        //         'actions' => [
        //             'remove_local_storage' => ['user', 'token'],
        //             'redirect' => 'login'
        //         ]
        //     ], 403);
        // }

        if ($user) {
            if ($user?->is_enabled === 0) {
                try {
                    JWTAuth::invalidate(JWTAuth::getToken());
                } catch (\Exception $e) {
                    // Log error si quieres
                }

                return response()->json([
                    'message' => 'User disabled',
                    'logout' => true,
                    'actions' => [
                        'remove_local_storage' => ['user', 'token'],
                        'redirect' => 'login'
                    ]
                ], 403);
            }
            $appParams = AppParams::select('app_params.*')->first();


            // $user = auth()->user();

            // return response()->json([
            //     'app_params' => $appParams,
            //     'permissions' => [
            //         'can_access_services_expansion' => $user->can_access_services_expansion,
            //     ],
            // ]);
            return $appParams;
        }
    }

    public function retrieveAppParams(Request $request)
    {
        $appParams = $this->getAppParams($request);
        $permissionsController = new PermissionsController();
        // $sectionsController = new SectionsController();
        $changelogController = new ChangelogController();
        $announcementsController = new AnnouncementsController();

        $result['permissions'] = $permissionsController->getUserPermissions($request);
        $result['changelog'] = $changelogController->getChangelog($request);
        $result['announcements'] = $announcementsController->getActiveAnnouncements($request);
        $result['app_params'] = $appParams;

        return $result;

    }
}

