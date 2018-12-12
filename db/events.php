<?php

/**
 * Users wallet plugin
 *
 * @package    local_wallet
 * @author     Ekaterina Staneva
 */

defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname' => '\folder\plugin_name\event_name',
        'callback' => 'local_wallet_observer::update_wallet',
    ),
);
