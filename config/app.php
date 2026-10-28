<?php
return [
 'name'=>env('APP_NAME','PC Center'), 'env'=>env('APP_ENV','local'),
 'debug'=>(bool)env('APP_DEBUG',false), 'url'=>env('APP_URL','http://127.0.0.1:8000'),
 'timezone'=>'America/Lima', 'locale'=>'es', 'fallback_locale'=>'es', 'faker_locale'=>'es_ES',
 'demo'=>(bool)env('APP_DEMO',true),
 'key'=>env('APP_KEY'), 'cipher'=>'AES-256-CBC',
 'maintenance'=>['driver'=>env('APP_MAINTENANCE_DRIVER','file'),'store'=>env('APP_MAINTENANCE_STORE','database')],
];
