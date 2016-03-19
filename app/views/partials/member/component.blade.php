<?php


$cacheKey = 'member:'.Auth::user()->default_location_id;
$boxContent = Cache::get($cacheKey);
if (empty($boxContent)) {
    $boxContent = View::make('partials.member.inner')->render();
    Cache::put($cacheKey, $boxContent, 60);
}
echo $boxContent;
?>