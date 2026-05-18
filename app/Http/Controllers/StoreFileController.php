<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\File;


class StoreFileController extends Controller
{
    public function storeFile(Request $request)
    {
        if ($file = $request->file('file')) {
            // project_id could be replaced by any id related to the file table
            $path = 'files/projects/' . $request->project_id;
            $fileName = $file->getClientOriginalName();
            $file->move($path, $fileName);

            File::create([
                'name' => $request->name,
                'project_id' => $request->project_id,
                'file_path' => $path . '/' . $fileName,
            ]);
        }
    }
    public function removeFile(Request $request)
    {
        $path = 'files/projects/' . $request->id;
        // Check if file exists to avoid error
        if ($request->fileName && file_exists($path . '/' . $request->fileName)) {
            unlink(public_path($path . '/' . $request->fileName));
        }
        $file = File::where('project_id', $request->id)->where('name', $request->name)->first();
        if ($file) $file->delete();
    }
    public function getFilesByProject($projectId)
    {
        $file = File::select('files.*')->where('project_id', $projectId)->get();
        return $file;
    }
}
