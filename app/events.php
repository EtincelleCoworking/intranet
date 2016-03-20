<?php

Event::subscribe('UserEventHandler');

PastTime::observe(new PastTimeObserver);