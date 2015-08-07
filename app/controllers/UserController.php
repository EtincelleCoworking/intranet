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

        $totalMonth = DB::table('invoices_items')->join('invoices', function ($join) {
            if (Auth::user()->role == 'superadmin') {
                $join->on('invoices_items.invoice_id', '=', 'invoices.id')
                    ->where('invoices.type', '=', 'F')
                    ->where('invoices.days', '=', date('Ym'));
            } else {
                $join->on('invoices_items.invoice_id', '=', 'invoices.id')
                    ->where('invoices.type', '=', 'F')
                    ->where('invoices.user_id', '=', Auth::user()->id)
                    ->where('invoices.days', '=', date('Ym'));
            }
        })->select(DB::raw('SUM(amount) as total'))->groupBy('invoices.days')->first();

        if (Auth::user()->role == 'superadmin') {
            $chargesMonth = DB::table('charges_items')->join('charges', function ($join) {
                $join->on('charges_items.charge_id', '=', 'charges.id')
                    ->where(DB::raw('MONTH(charges.date_charge)'), '=', date('n'))
                    ->where(DB::raw('MONTH(charges.deadline)'), '=', date('n'));
            })->join('vat_types', function ($join) {
                $join->on('charges_items.vat_types_id', '=', 'vat_types.id');
            })->select(DB::raw('SUM(amount) as total, SUM(((amount * vat_types.value) / 100)) as mtva'))->first();

            $chargesMonthToPay = DB::table('charges_items')->join('charges', function ($join) {
                $join->on('charges_items.charge_id', '=', 'charges.id')
                    ->where(DB::raw('MONTH(charges.date_charge)'), '=', date('n'))
                    ->where(DB::raw('MONTH(charges.deadline)'), '=', date('n'))
                    ->whereNull('charges.date_payment');
            })->join('vat_types', function ($join) {
                $join->on('charges_items.vat_types_id', '=', 'vat_types.id');
            })->select(DB::raw('SUM(amount) as total, SUM(((amount * vat_types.value) / 100)) as mtva'))->first();

            // Temps passés
            $date_pt_filtre_start = date('Y-m') . '-01';
            $date_pt_filtre_end = date('Y-m') . '-' . date('t', Session::get('filtre_pasttime.month'));
            $pasttimes = PastTime::Recap(false, $date_pt_filtre_start, $date_pt_filtre_end);
            $pending = InvoiceItem::Pending();
            $on_hold = InvoiceItem::OnHold();
        } else {
            $chargesMonth = false;
            $chargesMonthToPay = false;
            $tva_collectee = false;
            $tva_deductible = false;
            $pending = false;
            $on_hold = false;

            // Temps passés
            $date_pt_filtre_start = date('Y-m') . '-01';
            $date_pt_filtre_end = date('Y-m') . '-' . date('t', Session::get('filtre_pasttime.month'));
            $pasttimes = PastTime::Recap(Auth::user()->id, $date_pt_filtre_start, $date_pt_filtre_end);
        }

        $chooseMember = User::where('is_member', true)->orderByRaw("RAND()")->first();

        /* En travaux pour les stats annu.
        $annualTotal = DB::table('invoices_items')->join('invoices', function($join)
        {
            $join->on('invoices_items.invoice_id', '=', 'invoices.id')
                 ->where('invoices.type', '=', 'F')->where(DB::raw("LEFT(invoices.days, 4)"), '=', date('Y'));
        })->select(DB::raw('SUM(amount) as total'), DB::raw('RIGHT(days, 2) as month'))->groupBy('invoices.days')->get();

        $valsAnnual = '';
        foreach ($annualTotal as $k => $annual) {
            if ($k > 0) {
                $valsAnnual .= ', ';
            }
            $valsAnnual .= $annual->total;
        }
        */

        $params = array(
            'totalMonth' => $totalMonth,
            'chargesMonth' => $chargesMonth,
            'chargesMonthToPay' => $chargesMonthToPay,
            'pasttimes' => $pasttimes,
            'chooseMember' => $chooseMember,
            'pending' => $pending,
            'on_hold' => $on_hold,
        );

        $params['messages'] = WallPost::where('level', 0)->orderBy('created_at', 'DESC')->limit(5)->get();
        $params['birthdays'] = User::where('birthday', '<>', '0000-00-00')
            ->whereRaw('DATE_ADD(birthday,
                INTERVAL YEAR(CURDATE())-YEAR(birthday)
                         + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(birthday),1,0)
                YEAR)
            BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)')
            ->whereIsMember(true)
            ->orderByRaw('DATE_FORMAT(birthday, "%m-%d") ASC')
            ->limit(5)->get();

        $params = array_merge($params, Subscription::getActiveSubscriptionInfos());

        return View::make('user.dashboard', $params);
    }

    /**
     * List users
     */
    public function liste()
    {
        $users = User::where('is_member', true)
            ->orderBy('lastname', 'asc')
            ->get();

        return View::make('user.liste', array('users' => $users));
    }

    /**
     * Modify user
     */
    public function modify($id)
    {
        $user = User::find($id);
        $skills = Skill::findSkillsForUser($id);
        if (!$user) {
            return Redirect::route('user_list')->with('mError', 'Cet utilisateur est introuvable !');
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
            return Redirect::route('user_list')->with('mError', 'Cet utilisateur est introuvable !');
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
                $user->is_member = Input::get('is_member');
                $user->twitter = Input::get('twitter');
                $user->website = Input::get('website');
                $user->phone = Input::get('phone');

                $birthday = explode('/', Input::get('birthday'));
                if ($birthday) {
                    $user->birthday = $birthday[2] . '-' . $birthday[1] . '-' . $birthday[0];
                }
                $user->role = 'member';

                if (count(Input::get('modif')) > 0) {
                    $save = false;
                    foreach (Input::get('modif') as $key => $skillId) {
                        $skillExist = Skill::find($skillId);
                        if (Input::get('deleteExist.' . $skillExist->id)) {
                            Skill::destroy($skillExist->id);
                        } else {
                            if ($skillExist->name != Input::get('nameExist.' . $skillExist->id)) {
                                $skillExist->name = Input::get('nameExist.' . $skillExist->id);
                                $save = true;
                            }
                            if ($skillExist->value != Input::get('valueExist.' . $skillExist->id)) {
                                $skillExist->value = Input::get('valueExist.' . $skillExist->id);
                                $save = true;
                            }
                            if ($save) {
                                $skillExist->save();
                            }
                        }
                    }
                }

                if (Input::get('skill_name') && count(Input::get('skill_name')) > 0) {
                    foreach (Input::get('skill_name') as $key => $skillname) {
                        if ($skillname != null) {
                            $skill = new Skill();
                            $skill->user_id = $user->id;
                            $skill->name = $skillname;
                            if (Input::get('skill_value.' . $key)) {
                                $skill->value = Input::get('skill_value.' . $key);
                            }
                            $skill->save();
                        }
                    }
                }

                if ($user->save()) {
                    return Redirect::route('user_modify', $user->id)->with('mSuccess', 'Cet utilisateur a bien été modifié');
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
            $birthday = explode('/', Input::get('birthday'));
            if ($birthday) {
                $params['birthday'] = $birthday[2] . '-' . $birthday[1] . '-' . $birthday[0];
            }
            Input::merge($params);
            $user = new User(Input::all());
            $user->role = 'member';

            if ($user->save()) {
                return Redirect::route('user_list')->with('mSuccess', 'Cet utilisateur a bien été ajouté');
            } else {
                return Redirect::route('user_list')->with('mError', 'Impossible d\'ajouter cet utilisateur');
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
            return Redirect::route('user_list')->with('mError', 'Cet utilisateur est introuvable !');
        }

        return View::make('user.profile', array('user' => $user));
    }

    /**
     * Edit profile
     */
    public function edit()
    {
        $profile = User::find(Auth::user()->id);
        $skills = Skill::findSkillsForUser(Auth::user()->id);

        return View::make('user.edit', array('user' => $profile, 'skills' => $skills));
    }

    /**
     * Edit user (form)
     */
    public function edit_check()
    {
        $user = User::find(Auth::user()->id);
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

//                if (count(Input::get('modif')) > 0) {
//                    $save = false;
//                    foreach (Input::get('modif') as $key => $skillId) {
//                        $skillExist = Skill::find($skillId);
//                        if (Input::get('deleteExist.' . $skillExist->id)) {
//                            Skill::destroy($skillExist->id);
//                        } else {
//                            if ($skillExist->name != Input::get('nameExist.' . $skillExist->id)) {
//                                $skillExist->name = Input::get('nameExist.' . $skillExist->id);
//                                $save = true;
//                            }
//                            if ($skillExist->value != Input::get('valueExist.' . $skillExist->id)) {
//                                $skillExist->value = Input::get('valueExist.' . $skillExist->id);
//                                $save = true;
//                            }
//                            if ($save) {
//                                $skillExist->save();
//                            }
//                        }
//                    }
//                }
//
//                if (Input::get('name') && count(Input::get('name')) > 1) {
//                    foreach (Input::get('name') as $key => $skillname) {
//                        if ($skillname != null) {
//                            $skill = new Skill();
//                            $skill->user_id = $user->id;
//                            $skill->name = $skillname;
//                            if (Input::get('value.' . $key)) {
//                                $skill->value = Input::get('value.' . $key);
//                            }
//                            $skill->save();
//                        }
//                    }
//                } elseif (Input::get('name') && count(Input::get('name')) == 1) {
//                    if (Input::get('name') != '') {
//                        $skill = new Skill();
//                        $skill->user_id = $user->id;
//                        $skill->name = Input::get('name');
//                        if (Input::get('value')) {
//                            $skill->value = Input::get('value');
//                        }
//                        $skill->save();
//                    }
//                }
//
//                if (Input::file('avatar')) {
//                    $avatar = $user->id . '.' . Input::file('avatar')->guessClientExtension();
//                    if ($user->avatar) {
//                        unlink(public_path() . '/uploads/avatars/' . $user->avatar);
//                    }
//                    if (Input::file('avatar')->move('uploads/avatars', $avatar)) {
//                        $user->avatar = $avatar;
//                    }
//                }

                if ($user->save()) {
                    return Redirect::route('user_profile', $user->id)->with('mSuccess', 'Votre profil a bien été modifié');
                } else {
                    return Redirect::route('user_edit')->with('mError', 'Impossible de modifier votre profil');
                }
            }
        } else {
            return Redirect::route('user_edit')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }


    /**
     * Get organisations list of an user (JSON)
     */
    public function json_organisations($id)
    {
        $user = User::find($id);
        return Response::json($user->organisations->lists('name', 'id'));
    }
}
