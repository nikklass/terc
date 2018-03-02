<?php

$api_domain_url = "/";
$api_version_url = "api/";
$api_path_url = $api_domain_url . $api_version_url;

//get main server ip address
$pendo_server_ip = env('PENDO_SERVER_IP');

$remote_pendo_api_url = "http://" . $pendo_server_ip . "/public/api/";
$remote_base_api_url = "http://" . $pendo_server_ip . "/api2/api/";

return [

    'account_settings' => [
        'default_group_cd' => env('DEFAULT_GROUP_CD'),
        'default_account_type_cd' => env('DEFAULT_ACCOUNT_TYPE_CD'),
        'savings_account_product_id' => env('SAVINGS_ACCOUNT_PRODUCT_ID'),
        'loan_repayment_account_product_id' => env('LOAN_REPAYMENT_ACCOUNT_PRODUCT_ID'),
    ],
    'routes' => [
        'get_users_url' => $api_path_url . 'users/index',
        'create_user_url' => $api_path_url . 'users/create',
        'create_message_url' => $api_path_url . 'smsoutbox/create',
        'fetch_savings_deposit_accounts_url' => $api_path_url . 'savings-deposit-accounts',
    ],
    'passport' => [
        'token_url' => $remote_pendo_api_url . 'login',
        'user_url' => $api_path_url . 'user',
        'username' => env('PENDOAPI_OAUTH_USERNAME'),
        'password' => env('PENDOAPI_OAUTH_PASSWORD'),
    ],

    'bulk_sms' => [ 
        'send_sms_url' => $remote_pendo_api_url . "sms/sendSms",
        'company_id' => env('COMPANY_ID'),
        'usr' => env('BULK_SMS_USR'),
    ],
    'mpesa' => [
        'getpayments_url' => $remote_base_api_url . 'api/mpesa/getpayments',
    ]
];