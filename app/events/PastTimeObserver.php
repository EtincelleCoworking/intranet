<?php


class PastTimeObserver
{

    public function created($model)
    {
        Cache::forget(CheckinController::CACHE_KEY_AVAILABILITY);
    }

    public function updated($model)
    {
        Cache::forget(CheckinController::CACHE_KEY_AVAILABILITY);
    }
    public function saved($model)
    {
        Cache::forget(CheckinController::CACHE_KEY_AVAILABILITY);
    }
    public function deleted($model)
    {
        Cache::forget(CheckinController::CACHE_KEY_AVAILABILITY);
    }
}