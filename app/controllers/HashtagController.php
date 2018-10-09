<?php

/**
 * Tag Controller
 */
class HashtagController extends BaseController
{

    public function index()
    {
        return View::make('hashtag.index', array(
            'items' => Hashtag::orderBy('name', 'ASC')->get()
        ));
    }

    public function add()
    {
        $tags = array();
        foreach (explode("\n", Input::get('content')) as $caption) {
            $tag = Hashtag::where('name', '=', $caption)->first();
            if (null == $tag) {
                $tag = new Hashtag();
                $tag->name = $caption;
                $tag->slug = Str::slug($tag->name);
                $tag->is_highlighted = (bool)Input::get('is_highlighted');
                $tag->save();
                $tags[] = $tag;
            }
        }

        return Redirect::route('hashtags')->with('mSuccess', sprintf('%d tags ont été ajoutés', count($tags)));
    }
}