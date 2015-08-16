<?php

class WallPostController extends BaseController
{
    public function add_check()
    {
        $validator = Validator::make(Input::all(), WallPost::$rules);
        if (!$validator->fails()) {

            if ($parent_id = Input::get('parent_id')) {
                $root = WallPost::find($parent_id);
                $post = with(new WallPost())->setChildOf($root);
            } else {
                $post = new WallPost();
                $post->setAsRoot();
            }
            $post->user_id = Auth::id();
            $post->message = Input::get('message');

            if ($post->save()) {
                $this->purgeCache();
                return Redirect::route('dashboard')->with('mSuccess', 'Le message a été ajouté');
            } else {
                return Redirect::route('dashboard')->with('mError', 'Impossible de créer le message')->withInput();
            }
        } else {
            return Redirect::route('dashboard')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    public function reply()
    {
        $validator = Validator::make(Input::all(), WallPost::$rules);
        if (!$validator->fails()) {

            if ($parent_id = Input::get('parent_id')) {
                $root = WallPost::find($parent_id);
                $post = with(new WallPost())->setChildOf($root);
            } else {
                $post = new WallPost();
                $post->setAsRoot();
            }
            $post->user_id = Auth::user()->id;
            $post->message = Input::get('message');

            if ($post->save()) {
                $this->purgeCache();
                return Response::json(array('status' => 'OK',
                    'created_at' => $post->created_at,
//                    'created' => $post->created,
                    'content' => nl2br($post->message),
                    'id' => $post->id));
            } else {
                return Response::json(array('status' => 'KO'));
            }
        } else {
            return Response::json(array('status' => 'KO', 'message' => $validator->messages()));
//            return Redirect::route('dashboard')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    protected function purgeCache()
    {
        foreach (array(0, 1) as $key) {
            Cache::forget(sprintf('wall.%s', $key));
        }

    }

    public function delete($id)
    {
        WallPost::where('path', 'LIKE', sprintf('%d/', $id))->delete();
        $this->purgeCache();
        return Redirect::route('dashboard')->with('mSuccess', 'Le message a été supprimé');
    }


    public function deleteReply($id)
    {
        WallPost::destroy($id);
        $this->purgeCache();
        if (Request::ajax()) {
            return Response::make();
        } else {
            return Redirect::route('dashboard');

        }
    }

}