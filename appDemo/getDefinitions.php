<?php
declare(strict_types=1);

use WScore\Deca\Controllers\Messages;
use WScore\Deca\Definitions;
use WScore\Deca\Contracts\MailerInterface;
use WScore\Deca\Contracts\MessageInterface;
use WScore\Deca\Contracts\RoutingInterface;
use WScore\Deca\Contracts\SessionInterface;
use WScore\Deca\Contracts\ViewInterface;
use WScore\Deca\Services\PhpMailer;
use WScore\Deca\Services\Routing;
use WScore\Deca\Services\Session;
use WScore\Deca\Services\Setting;
use WScore\Deca\Views\Twig\ViewTwig;

if (!function_exists('getDefinitions')) {
    /**
     * Build DI definitions for appDemo: paths, {@see Setting}, and interface aliases.
     * Tests can override entries afterward (e.g. {@see Session::class}) via {@see Definitions::setValue()}.
     */
    function getDefinitions(Setting $setting): Definitions
    {
        $definitions = new Definitions();
        $definitions->setValue(Definitions::APP_DIR, __DIR__);
        $definitions->setValue(Definitions::VAR_DIR, dirname(__DIR__) . '/var');
        $definitions->setValue(Setting::class, $setting);
        $definitions->setAlias(RoutingInterface::class, Routing::class);
        $definitions->setAlias(SessionInterface::class, Session::class);
        $definitions->setAlias(MessageInterface::class, Messages::class);
        $definitions->setAlias(ViewInterface::class, ViewTwig::class);
        $definitions->setAlias(MailerInterface::class, PhpMailer::class);

        return $definitions;
    }
}
