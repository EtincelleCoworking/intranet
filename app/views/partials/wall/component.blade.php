<?php


$isSuperAdmin = Auth::user()->isSuperAdmin();
$cacheKey = sprintf('wall.%d', (bool)$isSuperAdmin);
$wallContent = Cache::get($cacheKey);
if (empty($wallContent)) {
    $wallContent = View::make('partials.wall.inner', array('isSuperAdmin' => $isSuperAdmin))->render();
    Cache::forever($cacheKey, $wallContent);
}
echo $wallContent;
?>