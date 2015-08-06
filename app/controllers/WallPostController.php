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
            $post->user_id = Auth::user()->id;
            $post->message = Input::get('message');

            if ($post->save()) {
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
                return Response::json(array('status' => 'OK', 'created' => $post->created));
            } else {
                return Response::json(array('status' => 'KO'));
            }
        } else {
            return Response::json(array('status' => 'KO', 'message' => $validator->messages()));
//            return Redirect::route('dashboard')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

}