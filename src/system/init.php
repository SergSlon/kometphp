<?php
/**
 * 
 * This file is executed before app startup and module initialization:
 * 
 *    \Komet\K::app()->bind("app_call", function(&$response){
 *        // do something with the response object
 *    });
 * 
 */

// This example adds tags to the request before the router has been resolved:
\Komet\K::app()->bind("router_before_resolve", function(\Komet\HMVC\Router &$router){
    $ieclasses = explode(" ", msie_classes($router->request()->userAgent(), 11));
    foreach ($ieclasses as $iec) {
        $router->request()->addTag($iec);
    }
});