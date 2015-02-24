<?php
/**
* Tag Controller
*/
class TagController extends BaseController
{
    /**
     * Verify if exist
     */
    private function dataExist($id)
    {
        $data = Tag::find($id);
        if (!$data) {
            return Redirect::route('tag_list')->with('mError', 'Ce tag est introuvable !');
        } else {
            return $data;
        }
    }

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
        $tag = $this->dataExist($id);

        return View::make('tag.modify', array('tag' => $tag));
    }

    /**
     * Modify tag (form)
     */
    public function modify_check($id)
    {
        $tag = $this->dataExist($id);

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
            $q = Tag::where('name', 'LIKE', '%'.Input::get('term').'%');
            if (Input::get('olds')) {
                $tags = explode(',', Input::get('olds'));
                $q->whereNotIn('id', $tags);
            }
            $list = $q->lists('name', 'id');
        } else {
            $list = array();
        }

        $ajaxArray = array();
        foreach ($list as $key => $value) {
            $ajaxArray[] = array(
                "name" => $value
            );
        }

        return Response::json($ajaxArray);
    }
}