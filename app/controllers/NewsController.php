<?php

use \custom\helpers\Responder;

class NewsController extends BaseController 
{
    public function index() {
        
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function attachNewsFile()
    {
        $file = Input::file('file');        
        $rand = time();
        
        $newName = $rand . '.' . strtolower($file->getClientOriginalExtension());

        $file->move(public_path() . '/assets/news/images/', $newName);

        $img = public_path() . '/assets/news/images/' . $newName;

        $imgout = public_path() . '/assets/news/images/' . $newName;
		
		$imageUrl = asset('/assets/news/images/' . $newName);

        if (exif_imagetype($img) > 0) {
            $img = Image::make($img);
            $img->fit(500, 260);
            $img->save($imgout);

            $id = News::storeImage($imageUrl);
            return Response::json(["success" => true, "id" => $id]);
        }
        return Response::json(["success" => false]);
    }

    /**
     * Store News
     */
    public function store() {
        $news = new News();
        $news->setFromInput('news');
        $news->urlImage = Input::get('urlImage') ? Input::get('urlImage') : null;
        $news->image_id = Input::get('fileId');
        $news->user_id = Auth::user()->id;
        $news->save();

        $newsContents = News::stream()->with(
                array(
                    'user' => function ($query) {
                        $query->select('id', 'full_name');
                    },
                    'newsImage' => function($query) {
                        $query->select('id', 'image');
                    }
                )
            )->get();
        
        return Responder::json(true)->withDataTransform($newsContents, 'HomeNewsTransformer')->withAlert('success')->withMessage('done')->send();
    }

    /**
     * @param $newsId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($newsId)
    {
        return Responder::json(true)->withDataTransform($this->one($newsId), 'NewsTransformer')->send();
    }

    private function one($newsId)
    {
        $news = News::stream()->with(
            array(
                'user' => function ($query) {
                    $query->select('id', 'full_name');
                }
            )
        )->findOrFail($newsId);
        return $news;
    }
}