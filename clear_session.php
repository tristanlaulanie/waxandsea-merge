<?php
// clear_session.php

require 'vendor/autoload.php';

use Symfony\Component\HttpFoundation\Session\Session;

$session = new Session();
$session->start();
$session->clear();

echo "All sessions have been cleared.";
