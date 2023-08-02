<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FilesController extends Controller
{
    public function upload(Request $request, $file) {
		$fileparts = explode('.', $file['name']);
		$extension = $fileparts[count($fileparts)-1];
		
        $filename = md5(uniqid()) . '.' . $extension;
		$targetfile = public_path() . '/storage/' . $filename;

		if(move_uploaded_file($file['tmp_name'], $targetfile)) {
            $preview = $config = $thumbtags = [];
            $preview[] = asset('storage/' . $filename);
            $config[] = [
                'caption' => $file['name'],
                'size' => $file['size'],
                'url' => route("admin.deletephoto"), // server api to delete the file based on key
            ];
            $thumbtags[] = [
                '{dataKey}' => $filename,
                '{isPrincipal}' => ''
            ];

            $out = ['filename' => $filename, 'initialPreview' => $preview, 'initialPreviewConfig' => $config, 'initialPreviewAsData' => true, 'initialPreviewFileType' => 'image', 'initialPreviewThumbTags' => $thumbtags];

			return json_encode($out);
		} else {
			abort(500);
		}
    }

    public function uploadPhoto(Request $request) {
        if(count($_FILES) > 0) {
            foreach($_FILES as $file) {
                if($file['size'] <= (25*1024*1024)) {
                    return $this->upload($request, $file);
                } else {
                    abort(400);
                }
            }
        }
    }

    public function uploadVideo(Request $request) {
        if(isset($_FILES['videos'])) {
            if($_FILES['videos']['size'] <= (100*1024*1024)) { // 20MB
                return $this->upload($request, $_FILES['videos']);
            } else {
                abort(400);
            }
        } else {
            abort(500);
        }
    }

    // ruta necesaria para poder eliminar una initial preview del bootstrap fileinput
    public function destroy() {
        return json_encode("OK");
    }
}
