<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
    	'/USSD',
    	'/saveGpData',
        '/USSDGP/long-code-recharge',
        '/partner/reporting/iframe/login',
        '/partner/reporting/iframe/logout',
        '/saveATGSOfferPackageData',
        '/nid-verification-api',
        '/category/get-service-list',
        '/service/get-sub-service-list'
    ];
}
