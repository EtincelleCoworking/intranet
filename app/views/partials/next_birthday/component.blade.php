<?php


$cacheKey = 'birthday';
$cacheContent = Cache::get($cacheKey);
if (empty($cacheContent)) {
    $users = User::where('birthday', '<>', '0000-00-00')
            ->whereRaw('DAYOFYEAR(birthday) BETWEEN DAYOFYEAR(CURDATE())-1 AND DAYOFYEAR(CURDATE()) + 60')
            ->whereIsMember(true)
            ->orderByRaw('DAYOFYEAR(birthday) ASC')
            ->limit(5)->get();
    if (count($users) > 0) {
        $cacheContent = View::make('partials.next_birthday.inner', array('users' => $users))->render();
        Cache::put($cacheKey, $cacheContent, new \dateTime(date('Y-m-d 00:00:00', strtotime('tomorrow'))));
    }
}
echo $cacheContent;
?>
