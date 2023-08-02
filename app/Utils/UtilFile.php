<?php

namespace App\Utils;

use Carbon\Carbon;
use App\Models\Photo;
use App\Models\File;
use App\Models\Article;
use App\Models\NoticePhoto;

class UtilFile 
{

    public function updatePhotos($newFiles, $article, $principalPhoto, $is_article = true) {
        $error = true;

        // quita todas las fotos actuales
        foreach($article->photos as $photo) {
            $photo->delete();
        }

        // carga todas las fotos nuevamente (en BD, no en archivos)
        if($newFiles != null) {
            $files = json_decode($newFiles, true);


            if(!config('config.PHOTO_REQUIRED') || count($files) > 0) {
                $error = false;
            }

            foreach ($files as $file) {
                if($is_article){
                    $createdFile = new Photo();
                    $createdFile->article_id = $article->id;
                } else{
                    $createdFile = new NoticePhoto();
                    $createdFile->notice_id = $article->id;
                }
                if($file['filename'] == $principalPhoto) {
                    $createdFile->principal = true;
                } else {
                    $createdFile->principal = false;
                }
                $createdFile->name = $file['originalName'];
                $createdFile->path = $file['filename'];
                
                $createdFile->save();

                try {
                    if(!file_exists(public_path() . '/storage/thumb_' . $file['filename'])) {
                        $im = new \Imagick(public_path() . '/storage/' . $file['filename']);
                        // $im->setImageCompressionQuality(95);
                        $im->thumbnailImage(300,300,true);
                        $im->writeImage(public_path() . '/storage/thumb_' . $file['filename']);
                        $im->destroy();
                    }
                } catch(\Exception $e) {
                    $error = true;
                }
            }
        }

        return $error;
    }

    public function updateVideos($newFiles, $existentFiles, $article) {

        // actualiza lista de fotos existentes (formulario de edición)
        if($existentFiles != null) {
            $newPhotosArray = $existentFiles;

            foreach($article->videos as $video) {
                if(!in_array($video->id, $newPhotosArray)) {
                    $video->delete();
                }
            }
        } else {
            if($newFiles != null) {
                $files = json_decode($newFiles, true);

                foreach($article->videos as $video) {
                    $video->delete();
                }
            }
        }

        // sube fotos nuevas 
        if($newFiles != null) {
            $files = json_decode($newFiles, true);

            foreach ($files as $file) {
                $createdFile = new Video();
                $createdFile->name = $file['originalName'];
                $createdFile->path = $file['filename'];
                $createdFile->article_id = $article->id;
                $createdFile->save();
            }
        }
    }

    public function updatePhotosBulk($newFiles) {
        $error = true;

        try{

            // carga todas las fotos nuevamente (en BD, no en archivos)
            if($newFiles != null) {                

                $files = json_decode($newFiles, true);


                if(count($files) > 0) {
                    $error = false;
                }

                foreach ($files as $file) {
                    

                    $idArticle = explode("_", $file['originalName']);
                    

                    if(count($idArticle)){
                        $id_to_search = ltrim($idArticle[0], "0");
                        $articles = Article::where('sku', $id_to_search)->get();
                        if(count($articles)){
                            foreach ($articles as $article) {                                

                                $createdFile = new Photo();
                                $createdFile->article_id = $article->id;
                                    
                                $createdFile->principal = false;


                            
                                $createdFile->name = $file['originalName'];
                                $createdFile->path = $file['filename'];
                                
                                
                                $createdFile->save();

                                try {
                                    if(!file_exists(public_path() . '/storage/thumb_' . $file['filename'])) {
                                        $im = new \Imagick(public_path() . '/storage/' . $file['filename']);
                                        // $im->setImageCompressionQuality(95);
                                        $im->thumbnailImage(300,300,true);
                                        $im->writeImage(public_path() . '/storage/thumb_' . $file['filename']);
                                        $im->destroy();
                                    }
                                } catch(\Exception $e) {
                                    $error = true;
                                }
                            }
                            
                        }
                    }
                    
                }
            }
        } catch(Exception $e){
             \Log::debug(json_encode($e));
        }

        return $error;
    }

    public function updateFilesBulk($newFiles) {
        $error = true;

        try{
            // carga todas las fotos nuevamente (en BD, no en archivos)
            if($newFiles != null) {                

                $files = json_decode($newFiles, true);


                if(count($files) > 0) {
                    $error = false;
                }

                foreach ($files as $file) {
                    

                    $idArticle = explode("_", $file['originalName']);
                    

                    if(count($idArticle)){
                        $id_to_search = ltrim($idArticle[0], "0");
                        $articles = Article::where('sku', $id_to_search)->get();
                        if(count($articles)){
                            // crea nuevos archivos
                            $created_ids = [];
                            
                                $f = File::create([
                                    'path' => $file['filename'],
                                    'original_name' => $file['originalName']
                                ]);
                                
                                $created_ids[] = $f->id;                        
                            // asocia archivos al articulo
                            foreach ($articles as $article) {
                                $article->files()->attach($created_ids);
                            }
                            
                        }
                    }
                    
                }
            }
        } catch(Exception $e){
             \Log::debug(json_encode($e));
        }

        return $error;
    }
    

    
}
