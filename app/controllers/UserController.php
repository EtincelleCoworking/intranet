<?php

/**
 * UserController Class
 */
class UserController extends BaseController
{
    /**
     * Login page for users
     */
    public function login()
    {
        return View::make('user.login');
    }

    /**
     * Login check after post form
     */
    public function login_check()
    {
        $user = array(
            'email' => Input::get('email'),
            'password' => Input::get('password')
        );

        if (Auth::attempt($user, Input::get('remember'))) {
            return Redirect::intended(URL::route('dashboard'));
        } else {
            return Redirect::route('user_login')->with('mError', 'Connexion impossible, merci de vérifier vos informations')->withInput();
        }
    }

    /**
     * Disconnect user
     */
    public function logout()
    {
        Auth::logout();
        return Redirect::route('user_login')->with('mSuccess', 'Votre déconnexion a bien été effectuée !');
    }

    /**
     * Dashboard
     */
    public function dashboard()
    {
        if (!Auth::user()) {
            return Redirect::route('user_login');
        }

        return View::make('user.dashboard');
    }

    /**
     * List users
     */
    public function members()
    {
        $users = User::where('is_member', true)
            ->orderBy('lastname', 'asc')
            ->get();

        return View::make('user.members', array('users' => $users));
    }

    /**
     * Modify user
     */
    public function modify($id)
    {
        $user = User::find($id);
        $skills = Skill::findSkillsForUser($id);
        if (!$user) {
            return Redirect::route('members')->with('mError', 'Cet utilisateur est introuvable !');
        }

        return View::make('user.modify', array('user' => $user, 'skills' => $skills));
    }

    /**
     * Modify user (form)
     */
    public function modify_check($id)
    {
        $user = User::find($id);
        if (!$user) {
            return Redirect::route('members')->with('mError', 'Cet utilisateur est introuvable !');
        }

        if (!Auth::user()->isSuperAdmin() && $id <> Auth::id()) {
            App::abort(403, 'Unauthorized action.');
            return false;
        }

        $validator = Validator::make(Input::all(), User::$rules);
        if (!$validator->fails()) {
            // Vérifier que l'adresse email soit unique (peut-être à améliorer... directement dans l'entity ?)
            $check = User::where('email', '=', $user->email)->where('id', '!=', $user->id)->first();
            if (!$check) {
                $user->email = Input::get('email');
                $user->firstname = Input::get('firstname');
                $user->lastname = Input::get('lastname');
                if (Input::get('password')) {
                    $user->password = Hash::make(Input::get('password'));
                }
                $user->bio_short = Input::get('bio_short');
                $user->bio_long = Input::get('bio_long');
                $user->twitter = Input::get('twitter');
                $user->website = Input::get('website');
                $user->phone = Input::get('phone');

                $user->social_github = Input::get('social_github');
                $user->social_instagram = Input::get('social_instagram');
                $user->social_linkedin = Input::get('social_linkedin');
                $user->social_facebook = Input::get('social_facebook');
                if (Auth::user()->isSuperAdmin()) {
                    $user->is_member = Input::get('is_member', false);
                }

                if (Input::get('birthday')) {
                    $birthday = explode('/', Input::get('birthday'));
                    $user->birthday = $birthday[2] . '-' . $birthday[1] . '-' . $birthday[0];
                } else {
                    $user->birthday = '';
                }
                if (!$user->role) {
                    $user->role = 'member';
                }

                $need_to_move_file = false;
                if (Input::hasFile('avatar')) {
                    // remove previous
                    if ($user->avatar) {
                        try {
                            Croppa::delete(sprintf('/uploads/users/%d/%s', $user->id, $user->avatar));
                        } catch (\League\Flysystem\FileNotFoundException $e) {

                        }
                    }

                    $filename = Str::random(8) . '.' . File::extension(Input::file('avatar')->getClientOriginalName());
                    $user->avatar = $filename;
                    $need_to_move_file = true;
                }

                if ($user->save()) {
                    if ($need_to_move_file) {
                        $target_folder = public_path() . '/uploads/users/' . $user->id;
                        if (!is_dir($target_folder)) {
                            mkdir($target_folder);
                        }
                        Input::file('avatar')->move($target_folder, $filename);
                    }
                    return Redirect::route('user_profile', $user->id)->with('mSuccess', 'Cet utilisateur a bien été modifié');
                } else {
                    return Redirect::route('user_modify', $user->id)->with('mError', 'Impossible de modifier cet utilisateur');
                }
            }
        } else {
            return Redirect::route('user_modify', $user->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    /**
     * Add User
     */
    public function add()
    {
        return View::make('user.add');
    }

    /**
     * Add User check
     */
    public function add_check()
    {
        $validator = Validator::make(Input::all(), User::$rulesAdd);
        if (!$validator->fails()) {
            $params = array('password' => Hash::make(Input::get('password')), 'role' => 'member');
            if (Input::get('birthday')) {
                $birthday = explode('/', Input::get('birthday'));
                $params['birthday'] = $birthday[2] . '-' . $birthday[1] . '-' . $birthday[0];
            }
            Input::merge($params);
            $user = new User(Input::all());
            $user->role = 'member';

            if ($user->save()) {
                return Redirect::route('members')->with('mSuccess', 'Cet utilisateur a bien été ajouté');
            } else {
                return Redirect::route('members')->with('mError', 'Impossible d\'ajouter cet utilisateur');
            }
        } else {
            return Redirect::route('user_add')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    /**
     * User Profile
     */
    public function profile($id)
    {
        $user = User::find($id);
        if (!$user) {
            return Redirect::route('members')->with('mError', 'Cet utilisateur est introuvable !');
        }

        return View::make('user.profile', array('user' => $user));
    }

    /**
     *
     */
    public function exportMemberProfile($id)
    {
        $user = User::find($id);
        if (!$user) {
            return Redirect::route('members')->with('mError', 'Cet utilisateur est introuvable !');
        }

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->addTitleStyle(1, array('name' => 'Tahoma', 'size' => 30, 'bold' => true), array('align' => 'center', 'spaceBefore' => true,
            'spaceAfter' => true));
        $phpWord->addTitleStyle(2, array('name' => 'Tahoma', 'size' => 14, 'color' => '666666', 'bold' => true), array('align' => 'center', 'spaceBefore' => true,
            'spaceAfter' => true));
        $phpWord->addFontStyle(
            'defaultText',
            array(
                'name' => 'Tahoma',
                'size' => 12,
                'spaceBefore' => true,
                'spaceAfter' => true
            )
        );


        $section = $phpWord->addSection(array(
            'marginTop' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1),
            'marginLeft' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1),
            'marginRight' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1),
        ));
        $textrun = $section->addTextRun('Heading1');
        $textrun->addText($user->fullname);
        $section->addTextBreak();

        $textrun = $section->addTextRun('Heading2');
        $textrun->addText(htmlspecialchars($user->bio_short));
        $section->addTextBreak();

        $table = $section->addTable();
        $table->addRow();
        $cell1 = $table->addCell(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(14));
        foreach (explode("\n", htmlspecialchars($user->bio_long)) as $line) {
            $cell1->addText($line, 'defaultText');
        }
        $cell1->addTextBreak();
        if ($user->phone) {
            $cell1->addText(htmlspecialchars(sprintf('Tél: %s', $user->phone)), 'defaultText', array('align' => 'right'));
        }
        $cell1->addText(htmlspecialchars(sprintf('Email: %s', $user->email)), 'defaultText', array('align' => 'right'));
        if ($user->website) {
            $cell1->addText(htmlspecialchars($user->website), 'defaultText', array('align' => 'right'));
        }

        $cell2 = $table->addCell(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(5));
        $cell2->addImage($user->getAvatarUrl(800), array('width' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(5)));

        $filename = sprintf('%d.docx', $user->id);

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($filename);
        $content = file_get_contents($filename);
        unlink($filename);
        $headers = array(
            "Content-Description" => "File Transfer",
            "Content-Transfer-Encoding" => "binary",
            "Content-type" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "Content-Disposition" => "attachment; filename=" . $filename
        );
        return Response::make($content, 200, $headers);
    }

    /**
     * Edit profile
     */
    public function edit()
    {
        return View::make('user.modify', array('user' => Auth::user()));
    }
//
//    /**
//     * Edit user (form)
//     */
//    public function edit_check()
//    {
//        $user = User::find(Auth::user()->id);
//        $validator = Validator::make(Input::all(), User::$rules);
//        if (!$validator->fails()) {
//            // Vérifier que l'adresse email soit unique (peut-être à améliorer... directement dans l'entity ?)
//            $check = User::where('email', '=', $user->email)->where('id', '!=', $user->id)->first();
//            if (!$check) {
//                $user->email = Input::get('email');
//                $user->firstname = Input::get('firstname');
//                $user->lastname = Input::get('lastname');
//                if (Input::get('password')) {
//                    $user->password = Hash::make(Input::get('password'));
//                }
//                $user->bio_short = Input::get('bio_short');
//                $user->bio_long = Input::get('bio_long');
//                $user->twitter = Input::get('twitter');
//                $user->website = Input::get('website');
//                $user->phone = Input::get('phone');
//
////                if (count(Input::get('modif')) > 0) {
////                    $save = false;
////                    foreach (Input::get('modif') as $key => $skillId) {
////                        $skillExist = Skill::find($skillId);
////                        if (Input::get('deleteExist.' . $skillExist->id)) {
////                            Skill::destroy($skillExist->id);
////                        } else {
////                            if ($skillExist->name != Input::get('nameExist.' . $skillExist->id)) {
////                                $skillExist->name = Input::get('nameExist.' . $skillExist->id);
////                                $save = true;
////                            }
////                            if ($skillExist->value != Input::get('valueExist.' . $skillExist->id)) {
////                                $skillExist->value = Input::get('valueExist.' . $skillExist->id);
////                                $save = true;
////                            }
////                            if ($save) {
////                                $skillExist->save();
////                            }
////                        }
////                    }
////                }
////
////                if (Input::get('name') && count(Input::get('name')) > 1) {
////                    foreach (Input::get('name') as $key => $skillname) {
////                        if ($skillname != null) {
////                            $skill = new Skill();
////                            $skill->user_id = $user->id;
////                            $skill->name = $skillname;
////                            if (Input::get('value.' . $key)) {
////                                $skill->value = Input::get('value.' . $key);
////                            }
////                            $skill->save();
////                        }
////                    }
////                } elseif (Input::get('name') && count(Input::get('name')) == 1) {
////                    if (Input::get('name') != '') {
////                        $skill = new Skill();
////                        $skill->user_id = $user->id;
////                        $skill->name = Input::get('name');
////                        if (Input::get('value')) {
////                            $skill->value = Input::get('value');
////                        }
////                        $skill->save();
////                    }
////                }
////
////                if (Input::file('avatar')) {
////                    $avatar = $user->id . '.' . Input::file('avatar')->guessClientExtension();
////                    if ($user->avatar) {
////                        unlink(public_path() . '/uploads/avatars/' . $user->avatar);
////                    }
////                    if (Input::file('avatar')->move('uploads/avatars', $avatar)) {
////                        $user->avatar = $avatar;
////                    }
////                }
//
//                if ($user->save()) {
//                    return Redirect::route('user_profile', $user->id)->with('mSuccess', 'Votre profil a bien été modifié');
//                } else {
//                    return Redirect::route('user_edit')->with('mError', 'Impossible de modifier votre profil');
//                }
//            }
//        } else {
//            return Redirect::route('user_edit')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
//        }
//    }


    /**
     * Get organisations list of an user (JSON)
     */
    public function json_organisations($id)
    {
        $user = User::find($id);
        return Response::json($user->organisations->lists('name', 'id'));
    }

    public function login_as($id)
    {
        $user = User::find($id);
        if (!$user) {
            App::abort(404);
        }
        Auth::loginUsingId($user->id);
        return Redirect::route('dashboard')->with('mSuccess', sprintf('Vous avez été connecté en tant que %s', $user->fullname));
    }

    public function liste()
    {
        if (Input::has('filtre_submitted')) {
            if (Input::has('filtre_user_id')) {
                Session::put('filtre_user.user_id', Input::get('filtre_user_id'));
            } else {
                Session::put('filtre_user.user_id', false);
            }
            if (Input::has('filtre_member')) {
                Session::put('filtre_user.member', Input::get('filtre_member'));
            } else {
                Session::put('filtre_user.member', false);
            }
            if (Input::has('filtre_subscription')) {
                Session::put('filtre_user.subscription', Input::get('filtre_subscription'));
            } else {
                Session::put('filtre_user.subscription', false);
            }
        }


        $users = User::with('organisations')
            ->orderBy('lastname', 'asc');
        if (Session::get('filtre_user.user_id')) {
            $users->where('users.id', '=', Session::get('filtre_user.user_id'));
        }
        if (Session::get('filtre_user.member')) {
            $users->where('users.is_member', '=', true);
        }
        if (Session::get('filtre_user.subscription')) {
            $users->join('invoices', 'invoices.user_id', '=', 'users.id')
                ->join('invoices_items', 'invoices_items.invoice_id', '=', 'invoices.id')
                ->where('subscription_from', '<>', '0000-00-00 00:00:00')
                ->where('subscription_from', '<', date('Y-m-d'))
                ->where('subscription_to', '>', date('Y-m-d'))
                ->where('ressource_id', '=', Ressource::TYPE_COWORKING);
        }

        return View::make('user.liste', array('users' => $users->paginate(15, array('users.*'))));
    }
}
