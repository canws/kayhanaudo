<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Files;
use Auth;
use Image;

class FilesController extends Controller
{
    public function save(Request $request)
    {

        if($request->hasfile('file')) {

            $user_id = Auth::user()->id;

            $imageExtensionArr = ['png', 'jpg', 'jpeg'];
            $destinationPath = public_path('uploads');

            $file = $request->file('file');
            $orignal_name = $file->getClientOriginalName();
            $file_type = $file->getClientMimeType();
            $fileTypeArr = explode('/', $file_type);
            $fileName = time() . '_' . $orignal_name;

           
            $extension = $file->getClientOriginalExtension();
            
            if(in_array($extension, $imageExtensionArr)){
                $thumbnail = time() . '_450X600_' . $orignal_name;
                $imgFile = Image::make($file->getRealPath());
                $imgFile->resize(450, 600, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath.'/thumbnail/'.$thumbnail);
                $file->move($destinationPath, $fileName);
            }

            Files::saveFile($user_id, $fileName, $fileTypeArr[0], $thumbnail);
            return response()->json([
                'sts' => true,
                'msg' => ''
            ], 200);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'File not uploaded.'
            ], 400);
        }
        
        
    }

    public function fetchFiles(Request $request){
        $limit = $request->limit;
        $offset = $request->offset;

        $files = Files::select('id', 'file_name', 'thumbnail', 'type')->orderBy('id', 'desc')->offset($offset)->limit($limit)->get()->toArray();
        return response()->json([
            'sts' => true,
            'files' => $files
        ], 200);
    }

    public function fetchFilesDetails(Request $request){
      
        $files = Files::select('id', 'file_name', 'thumbnail', 'type')->whereIn('id', $request->ids)->get()->toArray();
        return response()->json([
            'sts' => true,
            'files' => $files
        ], 200);
    }
}
