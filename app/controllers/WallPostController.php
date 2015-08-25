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
                    'created_at' => $post->created_at->format('c'),
//                    'created' => $post->created,
                    'content' => \Michelf\Markdown::defaultTransform($post->message),
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
        $page_count = ceil(WallPost::count() / WallPost::ITEM_PER_PAGE);
        foreach (array(0, 1) as $key) {
            for ($page_index = 0; $page_index < $page_count; $page_index++) {
                Cache::forget(sprintf('wall.%d.%d', $key, $page_index));
            }
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

    public function page($page_index)
    {
        $isSuperAdmin = Auth::user()->isSuperAdmin();
        $cacheKey = sprintf('wall.%d.%d', (bool)$isSuperAdmin, (int)$page_index);
        $wallContent = Cache::get($cacheKey);
        if (empty($wallContent)) {
            $wallContent = View::make('partials.wall.page', array('isSuperAdmin' => $isSuperAdmin, 'page_index' => $page_index))->render();
            Cache::forever($cacheKey, $wallContent);
        }

        return $wallContent;
    }

}