<?php

Event::subscribe('UserEventHandler');

PastTime::observe(new PastTimeObserver);
LocationIp::observe(new LocationIpObserver);