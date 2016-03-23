<?php


class LocationIpObserver
{

    public function created($model)
    {
        Cache::forget(CheckinController::CACHE_KEY_STATUS);
    }

    public function updated($model)
    {
        Cache::forget(CheckinController::CACHE_KEY_STATUS);
    }
    public function saved($model)
    {
        Cache::forget(CheckinController::CACHE_KEY_STATUS);
    }
    public function deleted($model)
    {
        Cache::forget(CheckinController::CACHE_KEY_STATUS);
    }
}