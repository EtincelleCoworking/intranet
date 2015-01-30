<?php
/**
* Tag Controller
*/
class TagController extends BaseController
{

    /**
     * List of tags
     */
    public function liste()
    {
        $tags = Tag::paginate(15);

        return View::make('tag.liste', array('tags' => $tags));
    }

    /**
     * Add a tag
     */
    public function add()
    {
        return View::make('tag.add');
    }

    /**
     * Add tag check
     */
    public function add_check()
    {
        $validator = Validator::make(Input::all(), Tag::$rulesAdd);
        if (!$validator->fails()) {
            $tag = new Tag;
            $tag->name = Input::get('name');

            if ($tag->save()) {
                return Redirect::route('tag_modify', $tag->id)->with('mSuccess', 'La tag a bien été ajouté');
            } else {
                return Redirect::route('tag_add')->with('mError', 'Impossible de créer ce tag')->withInput();
            }
        } else {
            return Redirect::route('tag_add')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    /**
     * Modify tag
     */
    public function modify($id)
    {
        $tag = Tag::find($id);
        if (!$tag) {
            return Redirect::route('tag_list')->with('mError', 'Ce tag est introuvable !');
        }

        return View::make('tag.modify', array('tag' => $tag));
    }

    /**
     * Modify tag (form)
     */
    public function modify_check($id)
    {
        $tag = Tag::find($id);
        if (!$tag) {
            return Redirect::route('vat_list')->with('mError', 'Ce tag est introuvable !');
        }

        $validator = Validator::make(Input::all(), Tag::$rules);
        if (!$validator->fails()) {
            $tag->name = Input::get('name');
            if ($tag->save()) {
                return Redirect::route('tag_modify', $tag->id)->with('mSuccess', 'Ce tag a bien été modifié');
            } else {
                return Redirect::route('tag_modify', $tag->id)->with('mError', 'Impossible de modifier ce tag')->withInput();
            }
        } else {
            return Redirect::route('tag_modify', $tag->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    /**
     * Json list
     */
    public function json_list()
    {
        if (strlen(Input::get('term')) >= 2) {
            if (Input::get('olds')) {
                $tags = explode(',', Input::get('olds'));
                $list = Tag::where('name', 'LIKE', '%'.Input::get('term').'%')->whereNotIn('id', $tags)->lists('name', 'id');
            } else {
                $list = Tag::where('name', 'LIKE', '%'.Input::get('term').'%')->lists('name', 'id');
            }
            //$list = Tag::where('name', 'LIKE', '%'.Input::get('term').'%')->lists('name', 'id');
        } else {
            $list = array();
        }

        return Response::json($list);
    }
}