<?php
define('DEFAULT_CONTROLLER', 'Api\\Welcome');
define('API_ROUTE', 'api');
define('ROUTE_NOT_FOUND_CONTROLLER', 'Api\\RouteNotFound');
define('DEFAULT_METHOD', 'index');
define('NAMESPACE_CONTROLLER', 'App\\Controllers\\');

define('HTTP_METHOD_GET', 'GET');
define('HTTP_METHOD_POST', 'POST');
define('HTTP_METHOD_PUT', 'PUT');
define('HTTP_METHOD_DELETE', 'DELETE');

define('URL_BASE', 'http://localhost:88');
define('MAINTENANCE', 0);

// Credenciais do Aplicativo Bling (OAuth2)
define('BLING_CLIENT_ID', getenv('BLING_CLIENT_ID'));
define('BLING_CLIENT_SECRET', getenv('BLING_CLIENT_SECRET'));
define('BLING_API_URL', getenv('BLING_API_URL') ?: 'https://www.bling.com.br/Api/v3');