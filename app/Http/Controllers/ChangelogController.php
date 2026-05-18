<?php

namespace App\Http\Controllers;

use App\Models\Changelog;

class ChangelogController extends Controller
{
    public function getChangelog()
    {
        $changelogs = Changelog::all();
        $latestChangelog = $changelogs->sortByDesc('id')->first();

        return $latestChangelog;
    }

    public function getChangelogs()
    {
        $changelogs = Changelog::all();
        $changelogs = $changelogs->sortByDesc('id')->values();
        return response()->json($changelogs);
    }
}
?>
