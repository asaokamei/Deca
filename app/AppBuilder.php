<?php
declare(strict_types=1);

namespace App;

use App\Application\Container\BootContainer;
use App\Application\Container\Setting;
use DI\Container;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Factory\AppFactory;

class AppBuilder
{
    /**
     * @var bool
     */
    private $useCache = false;

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

    public function setUseCache($useCache): AppBuilder
    {
        $this->useCache = $useCache;
        return $this;
    }

    /**
     * @param ServerRequestInterface|null $request
     * @return App
     * @throws Exception
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

    public function loadEnv(): AppBuilder
    {
        $this->setting = Setting::forge($this->root . '/settings.ini');

        return $this;
    }

    public function loadContainer(?bool $useCache = null): AppBuilder
    {
        if ($useCache === null) {
            $useCache = $this->useCache;
        }
        $this->setting->addSettings([
            'projectRoot' => $this->root,
            'cacheDirectory' => $this->cache,
        ]);

        // Build PHP-DI Container instance

        $this->container = BootContainer::forge($this->setting, $this->cache)
            ->setUseCache($useCache)
            ->build();

        return $this;
    }

    /**
     * @return App
     * @throws Exception
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