<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnused */

namespace App\Application\Container;

use App\Application\Interfaces\ProviderInterface;
use App\Application\Interfaces\SessionInterface;
use App\Application\Services\ViewTwig;
use Psr\Container\ContainerInterface;
use Slim\App;
use DI;

class ProviderTest implements ProviderInterface
{

    public static function getDefinitions(): array
    {
        return [
            ViewTwig::class => DI\factory([self::class, 'getViewTwig']),
        ];
    }

    public static function getViewTwig(ContainerInterface $c): ViewTwig
    {
        /** @var Setting $settings */
        $settings = $c->get(Setting::class);

        $tempDir = $settings->projectRoot . '/app/templates';

        $view = new ViewTwig($tempDir, [
            'auto_reload' => false,
        ], [
            SessionInterface::class => $c->get(SessionInterface::class),
            App::class => $c->get(App::class),
        ]);
        $view->add('settings', $settings);

        return $view;
    }
}