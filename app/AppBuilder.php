<?php
declare(strict_types=1);

namespace App;

use App\Application\Container\BootContainer;
use App\Application\Container\Setting;
use DI\Container;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Factory\AppFactory;

class AppBuilder
{
    /**
     * @var string
     */
    private $root;

    /**
     * @var string
     */
    private $cache;

    /**
     * @var Setting
     */
    private $setting;

    /**
     * @var Container|ContainerInterface
     */
    private $container;

    public function __construct(string $root, string $cache)
    {
        $this->root = $root;
        $this->cache = $cache;
    }

    public static function forge(string $root, string $cache = null): self
    {
        $cache = $cache ?? $root . '/var/cache/';
        return new self($root, $cache);
    }

    /**
     * @param ServerRequestInterface|null $request
     * @return App
     */
    public function build(ServerRequestInterface $request = null): App
    {
        $app = $this->makeApp();

        $this->middleware($app, $request);
        $this->routes($app, $request);
        $this->setup($app, $request);

        return $app;
    }

    /** @noinspection PhpUnusedParameterInspection */
    private function middleware(App $app, ServerRequestInterface $request = null)
    {
        require __DIR__ . '/middleware.php';
    }

    /** @noinspection PhpUnusedParameterInspection */
    private function routes(App $app, ServerRequestInterface $request = null)
    {
        require __DIR__ . '/routes.php';
    }

    /** @noinspection PhpUnusedParameterInspection */
    private function setup(App $app, ServerRequestInterface $request = null)
    {
        require __DIR__ . '/setup.php';
    }

    public function loadSettings(?string $iniPath = null): AppBuilder
    {
        if ($iniPath === null) {
            $iniPath = $this->root . '/settings.ini';
        }
        $this->setting = Setting::forge($iniPath, $_ENV);

        return $this;
    }

    /**
     * @param bool $useCache
     * @return $this
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function loadContainer(bool $useCache = false): AppBuilder
    {
        $this->setting->addSettings([
            'projectRoot' => $this->root,
            'cacheDirectory' => $this->cache,
        ]);

        // Build PHP-DI Container instance

        $this->container = BootContainer::forge($this->setting, $this->cache)
            ->build($useCache);

        return $this;
    }

    /**
     * @return App
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function makeApp(): App
    {
        AppFactory::setContainer($this->container);
        AppFactory::setResponseFactory($this->container->get(ResponseFactoryInterface::class));

        $app = AppFactory::create();
        $this->container->set(App::class, $app); // register $app self.

        return $app;
    }
}