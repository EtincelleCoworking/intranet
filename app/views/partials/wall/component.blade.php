<?php

if(empty($page_index)){
    $page_index = 0;
}
$isSuperAdmin = Auth::user()->isSuperAdmin();
$cacheKey = sprintf('wall.%d.%d', (bool)$isSuperAdmin, (int)$page_index);
$wallContent = Cache::get($cacheKey);
if (empty($wallContent)) {
    $wallContent = View::make('partials.wall.inner', array('isSuperAdmin' => $isSuperAdmin, 'page_index' => $page_index))->render();
    Cache::forever($cacheKey, $wallContent);
}
echo $wallContent;
?>