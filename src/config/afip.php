<?php

return [

    'environment'               => env('AFIP_ENVIRONMENT', 'dev'), // dev prod
    
    'path_cer_dev'              => env('AFIP_PATH_CER_DEV', '/cert//x509v2.pem'),
    'path_key_dev'              => env('AFIP_PATH_KEY_DEV', '/cert/MiClavePrivada'),
    'passphrase_dev'            => env('AFIP_PASSPHRASE_DEV', ''),
    'cuit_representada_dev'     => env('AFIP_CUIT_REPRESENTADA_DEV', ''),

    'path_cer_prod'             => env('AFIP_PATH_CER_PROD', '/cert/x509v2.pem'),
    'path_key_prod'             => env('AFIP_PATH_KEY_PROD', '/cert/MiClavePrivada'),
    'passphrase_prod'           => env('AFIP_PASSPHRASE_PROD', ''),
    'cuit_representada_prod'    => env('AFIP_CUIT_REPRESENTADA_PROD', ''),
    
    'url_cms_dev'               => "https://wsaahomo.afip.gov.ar/ws/services/LoginCms",
    'url_cms_prod'              => "https://wsaa.afip.gov.ar/ws/services/LoginCms",

    'url_wsa4_dev'              => "https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA4",
    'url_wsa4_prod'             => "https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA4",
    
];
