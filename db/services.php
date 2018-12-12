<?php


 //TODO: what is service array
$services = array(
'wallet_external' => array(
            'functions' => array (
                'purchase_product',
                'get_balance',
                'get_transactions_history',
                'get_purchase_history'
            ),
            'restrictedusers' => 0,
            'enabled'=>1,
            'shortname'=>'wallet_external'
    )
);

$functions = array(
        'purchase_product' => array(
                'classname'   => 'local_wallet_external',
                'methodname'  => 'purchase_product',
                'classpath'   => 'local/wallet/externallib.php',
                'description' => 'This function updates the number of credits in the wallet and writes in
                    transactions log and purchase history table.',
                'type'        => 'write',
                'ajax'        => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile')
        ),
        'get_balance' => array(
            'classname'   => 'local_wallet_external',
            'methodname'  => 'get_wallet_balance',
            'classpath'   => 'local/wallet/externallib.php',
            'description' => 'This function returns the credits in a given wallet ',
            'type'        => 'read',
            'ajax'        => true,
            'capabilities'  => '',
            'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile')
        ),
        'get_transactions_history' => array(
                'classname'   => 'local_wallet_external',
                'methodname'  => 'get_user_transactions',
                'classpath'   => 'local/wallet/externallib.php',
                'description' => 'This function returns the transaction history of a  given wallet ',
                'type'        => 'read',
                'ajax'        => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile')
        ),
        'get_purchase_history' => array(
            'classname'   => 'local_wallet_external',
            'methodname'  => 'get_purchase_history',
            'classpath'   => 'local/wallet/externallib.php',
            'description' => 'This function returns the purchase history of a given wallet ',
            'type'        => 'read',
            'ajax'        => true,
            'capabilities'  => '',
            'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile')
        )
);
