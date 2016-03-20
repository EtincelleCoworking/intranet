<?php


class PastTimeObserver
{

    public function created($model)
    {
        Cache::forget(CheckinController::CACHE_KEY);
    }

    public function updated($model)
    {
        Cache::forget(CheckinController::CACHE_KEY);
    }
    public function saved($model)
    {
        Cache::forget(CheckinController::CACHE_KEY);
    }
    public function deleted($model)
    {
        Cache::forget(CheckinController::CACHE_KEY);
    }
}