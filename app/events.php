<?php

Event::subscribe(new UserEventHandler);

PastTime::observe(new PastTimeObserver);
LocationIp::observe(new LocationIpObserver);