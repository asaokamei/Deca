<?php
declare(strict_types=1);

namespace App;

use App\Application\Container\Provider;
use App\Application\Container\Setting;
use App\Application\Interfaces\ProviderInterface;
use DI\ContainerBuilder;
use PHPUnit\Framework\MockObject\RuntimeException;
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
     * @var ContainerBuilder
     */
    private $containerBuilder;

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
     * @param string[] $extraFiles
     * @return App
     */
    public function build(ServerRequestInterface $request = null, array $extraFiles = []): App
    {
        $app = $this->makeApp();
        $files = [
            __DIR__ . '/Application/middleware.php',
            __DIR__ . '/routes.php',
            __DIR__ . '/Application/setup.php',
        ];
        $files += $extraFiles;
        foreach ($files as $file) {
            $this->loadFile($file, $app, $request);
        }

        return $app;
    }

    /** @noinspection PhpUnusedParameterInspection */
    private function loadFile(string $file, App $app, ServerRequestInterface $request = null)
    {
        try {
            require $file;
        } catch (\Throwable $e) {
            throw new RuntimeException('failed to load a file: ' . $file, $e->getCode(), $e);
        }
    }

    public function loadSettings(?string $iniPath = null): AppBuilder
    {
        if ($iniPath === null) {
            $iniPath = $this->root . '/settings.ini';
        }
        $this->setting = Setting::forge($iniPath, $_ENV);
        $this->setting->addSettings([
            'projectRoot' => $this->root,
            'cacheDirectory' => $this->cache,
        ]);

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
        $this->prepareContainer($useCache);
        $this->containerBuilder->addDefinitions(Provider::getDefinitions());

        return $this;
    }

    /**
     * @param string|ProviderInterface $provider
     * @return $this
     */
    public function loadProvider(string $provider): AppBuilder
    {
        $this->containerBuilder->addDefinitions($provider::getDefinitions());
        return $this;
    }

    private function prepareContainer(bool $useCache)
    {
        if ($this->containerBuilder) return;

        $this->containerBuilder = new ContainerBuilder();
        if ($useCache && $this->cache) { // compilation not working, yet
            $this->containerBuilder->enableCompilation($this->cache);
        }
        $this->containerBuilder->addDefinitions([
            Setting::class => $this->setting,
        ]);
    }

    /**
     * @return App
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function makeApp(): App
    {
        $container = $this->containerBuilder->build();
        AppFactory::setContainer($container);
        AppFactory::setResponseFactory($container->get(ResponseFactoryInterface::class));

        $app = AppFactory::create();
        $container->set(App::class, $app); // register $app self.

        return $app;
    }
}