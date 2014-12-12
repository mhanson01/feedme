<?php

require 'vendor/autoload.php';

use Feedme\FeedMe;

$feed = new FeedMe();
$collection =  $feed->getCollection();
print_r($collection->first());

$feed = new FeedMe('https://laracasts.com/feed');
$collection =  $feed->getCollection();
print_r($collection->first());