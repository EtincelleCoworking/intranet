<?php

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Response;

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
        if (Auth::id()) {
            return Redirect::route('dashboard');
        }
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
        $result = Auth::attempt($user, Input::get('remember'));
        if ($result) {
            if (Auth::user()->is_enabled) {
                return Redirect::intended(URL::route('dashboard'));
            }
            return Redirect::route('user_login')->with('mError', 'Connexion impossible, merci de contacter un administrateur')->withInput();
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
        if (Auth::guest()) {
            Auth::logout();
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
            ->where('is_enabled', true)
//            ->where('default_location_id', '=', Auth::user()->default_location_id)
            ->orderBy('last_seen_at', 'desc')
            ->orderBy('lastname', 'asc')
            ->with('organisations', 'hashtags')
            ->get();

        $tags = DB::select('SELECT hashtags.* 
            FROM hashtags 
              JOIN user_hashtag ON hashtags.id = user_hashtag.hashtag_id
            WHERE is_highlighted = true 
              GROUP BY hashtags.id 
              ORDER BY hashtags.name ASC');


        return View::make('user.members', array(
            'users' => $users,
            'tags' => $tags));
    }

    /**
     * Modify user
     */
    public function modify($id)
    {
        $user = User::find($id);
        //$skills = Skill::findSkillsForUser($id);
        if (!$user) {
            return Redirect::route('members')->with('mError', 'Cet utilisateur est introuvable !');
        }

        $subscription_stats = DB::select(DB::raw(sprintf('SELECT 
invoices_items.id as invoices_items_id, invoices_items.subscription_overuse_managed,
round(((sum(time_to_sec(timediff(time_end, time_start )) / 3600) / invoices_items.`subscription_hours_quota`) - 1) * 100) as overuse,
round((sum(time_to_sec(timediff(time_end, time_start )) / 3600) / invoices_items.`subscription_hours_quota`) * 100) as ratio,
invoices.date_invoice, sum(time_to_sec(timediff(time_end, time_start )) / 3600) as used, invoices_items.`subscription_hours_quota` as ordered
, invoices.id as invoice_id, invoices_items.`subscription_from`, invoices_items.`subscription_to`
from past_times 
join invoices on invoices.id = past_times.invoice_id
join invoices_items on invoices.id = invoices_items.invoice_id AND invoices_items.subscription_user_id = %1$d 
where past_times.user_id = %1$d
# and past_times.user_id = invoices_items.subscription_user_id
# and past_times.time_start > "2017-01-01"
and past_times.is_free = 0
AND invoices_items.`subscription_from` != "0000-00-00 00:00:00"
AND invoices_items.`subscription_to` != "0000-00-00 00:00:00"
group by invoices.id
order by invoices.date_invoice desc
', $id)));

        foreach ($subscription_stats as $index => $data) {
            $subscription_stats[$index]->hours = floor($data->used);
            $subscription_stats[$index]->minutes = round(($data->used - floor($data->used)) * 60);
        }

        //var_dump($subscription_stats);exit;
        return View::make('user.modify', array(
            'user' => $user,
            'subscription_stats' => $subscription_stats));
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
                if (Input::get('gender')) {
                    $user->gender = Input::get('gender');
                }
                $user->bio_short = Input::get('bio_short');
                $user->bio_long = Input::get('bio_long');

                $tags = array();
                $hashtags = Input::get('hashtags');
                if (!empty($hashtags) && is_array($hashtags)) {
                    foreach ($hashtags as $hashtag) {
                        if (ctype_digit($hashtag)) {
                            $tags[] = $hashtag;
                        } else {
                            $newTag = Hashtag::where('name', '=', $hashtag)->first();
                            if (null == $newTag) {
                                $newTag = new Hashtag();
                                $newTag->name = $hashtag;
                                $newTag->slug = Str::slug($hashtag);
                                $newTag->save();
                                $tags[] = $newTag->id;
                            }
                        }
                    }
                }
                $user->hashtags()->sync($tags);

                $user->twitter = Input::get('twitter');
                $user->website = Input::get('website');
                $user->phone = Input::get('phone');

                $user->social_github = Input::get('social_github');
                $user->social_instagram = Input::get('social_instagram');
                $user->social_linkedin = Input::get('social_linkedin');
                $user->social_facebook = Input::get('social_facebook');
                if (Auth::user()->isSuperAdmin()) {
                    $user->is_member = Input::get('is_member', false);
                    $user->is_student = Input::get('is_student', false);
                    $user->free_coworking_time = Input::get('free_coworking_time', false);
                    $user->default_location_id = Input::get('default_location_id');
                    $user->is_enabled = Input::get('is_enabled');
                    $user->is_hidden_member = Input::get('is_hidden_member');
                    $user->affiliate_user_id = Input::get('affiliate_user_id') ? Input::get('affiliate_user_id') : null;
                }
                $user->slack_id = Input::get('slack_id');
                $user->rocks_status = Input::get('rocks_status');

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
                    return Redirect::route('user_profile', $user->id)->with('mSuccess', 'Les modifications ont été enregistrées');
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

    public function add_raw()
    {
        $data = User::SplitNameEmail(Input::get('content'));
        if (!$data) {
            return Redirect::route('user_add');
        }
        $email = strtolower($data['email']);
        $user = User::where('email', '=', $email)->first();
        if ($user) {
            return Redirect::route('user_modify', $user->id)->with('mError', 'Un utilisateur existe déjà avec cette email');
        }
        $user = new User();
        $user->firstname = $data['firstname'];
        $user->lastname = $data['lastname'];
        $user->email = $data['email'];
        $user->password = Hash::make('etincelle');
        $user->default_location_id = Auth::user()->default_location_id;
        $user->save();
        return Redirect::route('user_modify', $user->id)->with('mSuccess',
            sprintf('L\'utilisateur %s &lt;%s&gt; a été créé. <a href="%s" class="btn btn-primary">Envoyer l\'email de bienvenue</a>',
                $user->fullname, $user->email, URL::route('user_send_welcome_email', $user->id)));
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
            $cell1->addText(htmlspecialchars(sprintf('Tél: %s', $user->phoneFmt)), 'defaultText', array('align' => 'right'));
        }
        $cell1->addText(htmlspecialchars(sprintf('Email: %s', $user->email)), 'defaultText', array('align' => 'right'));
        if ($user->website) {
            $cell1->addText(htmlspecialchars($user->website), 'defaultText', array('align' => 'right'));
        }

        $cell2 = $table->addCell(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(5));
        $image_url = $user->largeAvatarUrl;
        $image_url = preg_replace('!^(.+)\?.+$!', '$1', $image_url);
        if (false === strpos($image_url, 'http')) {
            $image_url = public_path() . $image_url;
        }
        $cell2->addImage($image_url, array('width' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(5)));

        $filename = sprintf('%s.docx', Str::slug($user->fullname));

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
            if (Input::has('filtre_free_coworking_time')) {
                Session::put('filtre_user.free_coworking_time', Input::get('filtre_free_coworking_time'));
            } else {
                Session::put('filtre_user.free_coworking_time', false);
            }
            if (Input::has('filtre_is_hidden_member')) {
                Session::put('filtre_user.is_hidden_member', Input::get('filtre_is_hidden_member'));
            } else {
                Session::put('filtre_user.is_hidden_member', false);
            }

            if (Input::has('filtre_subscription')) {
                Session::put('filtre_user.subscription', Input::get('filtre_subscription'));
            } else {
                Session::put('filtre_user.subscription', false);
            }
        }


        $users = User::with('organisations')
            ->with('devices')
            ->orderBy('created_at', 'desc');
        if (Session::get('filtre_user.user_id')) {
            $users->where('users.id', '=', Session::get('filtre_user.user_id'));
        }
        if (Session::get('filtre_user.member')) {
            $users->where('users.is_member', '=', true);
        }
        if (Session::get('filtre_user.free_coworking_time')) {
            $users->where('users.free_coworking_time', '=', true);
        }
        if (Session::get('filtre_user.is_hidden_member')) {
            $users->where('users.is_hidden_member', '=', true);
        }
        if (Session::get('filtre_user.subscription')) {
            $users->join('invoices_items', 'invoices_items.subscription_user_id', '=', 'users.id')
                ->where('subscription_from', '<>', '0000-00-00 00:00:00')
                ->where('subscription_from', '<', date('Y-m-d'))
                ->where('subscription_to', '>', date('Y-m-d'))
                ->where('ressource_id', '=', Ressource::TYPE_COWORKING);
        }

        return View::make('user.liste', array('users' => $users->paginate(15, array('users.*'))));
    }

    public function ChangeLocation()
    {
        $user = Auth::user();
        $user->default_location_id = Input::get('location_id');
        $user->save();

        return Redirect::back();

    }

    public function cancelFilter()
    {
        Session::forget('filtre_user.user_id');
        Session::forget('filtre_user.subscription');
        Session::forget('filtre_user.member');
        Session::forget('filtre_user.free_coworking_time');
        Session::forget('filtre_user.is_hidden_member');
        return Redirect::route('user_list');
    }

    public function slackInvite($id)
    {
        $user = User::find($id);
        if (!$user) {
            App::abort(404);
        }


        $fields = array(
            'email' => $user->email,
            'first_name' => $user->firstname,
            'last_name' => $user->lastname,
            'resend' => 'true'
        );

        $fields_string = '';
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');


        $slack = new Slack($_ENV['slack_token']);
        $json = $slack->call('users.admin.invite', $fields);

        if (false == $json['ok']) {
            return sprintf('Erreur: %s', $json['error']);
        }

        $user->slack_invite_sent_at = new \DateTime();
        $user->save();
        return $user->slack_invite_sent_at->format('d/m/Y');
    }

    public function birthday()
    {
        $users = User::join('locations', 'users.default_location_id', '=', 'locations.id')
            ->where('birthday', '!=', '0000-00-00')
            ->where('birthday', '!=', '1970-01-01')
            ->where('locations.city_id', '=', Auth::user()->location->city_id)
//            ->where('users.id', '!=', Auth::id())
            ->orderBy('users.is_member', 'DESC')
            ->orderBy(DB::raw('DAY(birthday)'), 'ASC')
            ->distinct()
            //->select('users.id', 'users.birthday', 'users.firstname', 'users.lastname', 'users.email')
            ->get(array('users.*'));
        $items = array();
        //var_dump($users);exit;
        foreach ($users as $user) {
            $items[date('m', strtotime($user->birthday))][] = $user;
        }

        ksort($items);

        $months = array_keys($items);
        $current_month = date('m');
        $month = 1;
        while ($month < $current_month) {
            $month = array_shift($months);
            $months[] = $month;
        }

        $others = User::join('locations', 'users.default_location_id', '=', 'locations.id')
            ->whereIn('birthday', ['0000-00-00', '1970-01-01'])
            ->where('locations.city_id', '=', Auth::user()->location->city_id)
            ->orderBy('users.is_member', 'DESC')
            ->distinct()
            ->get(array('users.*'));

        return View::make('user.birthday', array(
            'months' => $months,
            'users' => $items,
            'others' => $others
        ));
    }


    public function affiliate($id = null)
    {
        if ($id && Auth::user()->isSuperAdmin()) {
            $godfather = User::find($id);
        } else {
            $godfather = Auth::user();
        }
        $users = array();
        foreach (User::where('affiliate_user_id', '=', $godfather->id)->orderBy('created_at', 'DESC')->get() as $user) {
            $users[$user->id] = $user;
        }

        $sql = sprintf('SELECT distinct(past_times.invoice_id) as id 
             FROM past_times 
               JOIN ressources on ressources.id = past_times.ressource_id
               JOIN users ON past_times.user_id = users.id
           WHERE ressources.ressource_kind_id = %1$d
             AND users.affiliate_user_id = %2$d
             AND past_times.invoice_id > 0
          ', RessourceKind::TYPE_MEETING_ROOM, $godfather->id);
        $invoice_ids = array();
        foreach (DB::select(DB::raw($sql)) as $data) {
            $invoice_ids[] = $data->id;
        }

        $items = array();
        if (count($invoice_ids)) {
            $sql = sprintf('SELECT invoices.date_invoice, DATE_FORMAT(invoices.date_invoice, "%%Y") as y, 
              DATE_FORMAT(invoices.date_invoice, "%%m") as m, 
              invoices_items.amount as amount,
              invoices.user_id
        FROM invoices_items
          JOIN invoices ON invoices.id = invoices_items.invoice_id
          JOIN ressources on ressources.id = invoices_items.ressource_id
        WHERE ressources.ressource_kind_id = %1$d
          AND invoices.date_invoice > "2017-10-01"
          AND invoices.id IN (%2$s)', RessourceKind::TYPE_MEETING_ROOM, implode(', ', $invoice_ids));

            foreach (DB::select(DB::raw($sql)) as $data) {
                $user = $users[$data->user_id];
                $concerned_period_start = $user->created_at;
                $concerned_period_end = $concerned_period_start->add(DateInterval::createFromDateString(sprintf('%d month', $godfather->affiliation_duration)))->format('Y-m-d');

                $concerned = $data->date_invoice < $concerned_period_end;
                if (!isset($items[$data->y][$user->id][(int)$data->m])) {
                    $items[$data->y][$user->id][(int)$data->m] = array(
                        'sales' => 0,
                        'concerned' => false,
                        'fees' => 0
                    );
                }
                $items[$data->y][$user->id][(int)$data->m]['sales'] += $data->amount;
                $items[$data->y][$user->id][(int)$data->m]['concerned'] += $concerned;
                if ($concerned) {
                    $items[$data->y][$user->id][(int)$data->m]['fees'] += $godfather->affiliation_fees / 100 * $data->amount;
                }
            }
            krsort($items);
        }
        return View::make('user.affiliate', array(
            'godfather' => $godfather,
            'items' => $items,
            'users' => $users,
        ));
    }

    public function refreshPersonnalCode()
    {
        $sql = 'SELECT personnal_code
FROM (
  SELECT FLOOR(RAND() * 999999) AS personnal_code 
  UNION
  SELECT FLOOR(RAND() * 999999) AS personnal_code
) AS personnal_code_plus_1
WHERE `personnal_code` NOT IN (SELECT distinct(personnal_code) FROM users where personnal_code is not null)
LIMIT 1';

        $items = DB::select(DB::raw($sql));
        $item = $items[0];

        $user = Auth::user();
        $user->personnal_code = str_pad($item->personnal_code, 6, "0", STR_PAD_LEFT);
        $user->save();

        $data = array();
        $data['status'] = 'success';
        $data['code'] = $user->personnal_code;

        return Response::json($data);
        /*
                $result = new Response();
                $result->headers->set('Content-Type', 'application/json');
                $result->setContent(json_encode($data));
                return $result;
        */
    }

    public function send_welcome_email($user_id)
    {
        $user = User::find($user_id);
        if (!$user) {
            return Redirect::route('user_list');
        }
        $location = $user->location;

        $content = $location->welcome_email_content;
        if (empty($content)) {
            return Redirect::route('user_list')->with('mError', sprintf('L\'espace %s ne contient pas de modèle d\'email de bienvenue.', $location->fullname));

        }
        $admin = Auth::user();
        $macros = array();
        $macros['%User.Firstname%'] = $user->firstname;
        $macros['%User.Lastname%'] = $user->lastname;
        $macros['%User.Email%'] = $user->email;
        $macros['%Admin.Firstname%'] = $admin->firstname;
        $macros['%Admin.Lastname%'] = $admin->lastname;

        $content = str_replace(array_keys($macros), array_values($macros), $content);

        Mail::send('emails.welcome_email', array('content' => $content), function ($message) use ($user, $admin) {
            $message->from($admin->email, $admin->fullname)
                ->cc($admin->email, $admin->fullname)
                ->bcc($_ENV['mail_bcc']);

            $admins = User::where('is_staff', '=', true)
                ->join('locations', 'users.default_location_id', '=', 'locations.id')
                ->where('locations.city_id', '=', $user->location->city_id)
                ->get();
            foreach ($admins as $a) {
                $message->cc($a->email, $a->fullname);
            }

            $message->to($user->email, $user->fullname);
            $message->subject(sprintf('Bienvenue à %s !', $_ENV['organisation_name']));
        });
        $user->welcome_email_sent_at = date('Y-m-d H:i:s');
        $user->save();
        return Redirect::route('user_list')->with('mSuccess', sprintf('L\'email de bienvenue a été envoyé à %s &lt;%s&gt;', $user->fullname, $user->email));
    }

    private function extractExtension($url)
    {
        $url_parts = parse_url($url);
        $parts = explode('.', basename($url_parts ['path']));
        return $parts[count($parts) - 1];
    }

    public function signature($user_id)
    {
        $user = User::find($user_id);
        switch ($user->location->city_id) {
            case 3: // Albi
                $twitter = 'etincelle_albi';
                $facebook = 'https://www.facebook.com/EtincelleAlbi';
                $url = 'http://www.coworking-albi.com';
                break;
            case 1: // Toulouse
            default:
                $twitter = 'etincelle_tls';
                $facebook = 'https://www.facebook.com/EtincelleCoworking';
                $url = 'http://www.etincelle-coworking.com';
                break;
        }

        $picture_url = $user->getAvatarUrl(100);
        $signature = View::make('user._signature', array(
            'user' => $user,
            'picture_url' => $picture_url,
            'extension' => $this->extractExtension($picture_url),
            'twitter' => $twitter,
            'facebook' => $facebook,
            'url' => $url,
        ));

        if (Input::get('download')) {
            $headers = array(
                "Content-Description" => "File Transfer",
                "Content-Transfer-Encoding" => "binary",
                "Content-type" => "text/html",
                "Content-Disposition" => "attachment; filename=signature.html"
            );
            return Response::make($signature, 200, $headers);
        }


        return View::make('user.signature', array(
            'user' => $user,
            'signature' => $signature,
        ));
    }


}

