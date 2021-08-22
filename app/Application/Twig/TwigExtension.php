<?php
/**
 * Slim Framework (http://slimframework.com)
 *
 * @license   https://github.com/slimphp/Twig-View/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace App\Application\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'slim';
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('url_for', [TwigFunctions::class, 'urlFor']),
            new TwigFunction('path', [TwigFunctions::class, 'urlFor']),
            new TwigFunction('full_url_for', [TwigFunctions::class, 'fullUrlFor']),
            new TwigFunction('url', [TwigFunctions::class, 'fullUrlFor']),
            new TwigFunction('is_current_url', [TwigFunctions::class, 'isCurrentUrl']),
            new TwigFunction('current_url', [TwigFunctions::class, 'getCurrentUrl']),
            new TwigFunction('get_uri', [TwigFunctions::class, 'getUri']),
            new TwigFunction('base_path', [TwigFunctions::class, 'getBasePath']),
            /**
             * new functions
             */
            new TwigFunction('hidden_csrf_token', [TwigFunctions::class, 'getCsrfTokens']),
            new TwigFunction('csrf_token', [TwigFunctions::class, 'getCsrfTokens']),
            new TwigFunction('flashMessages', [TwigFunctions::class, 'getFlashMessages']),
            new TwigFunction('flashNotices', [TwigFunctions::class, 'getFlashNotices']),
        ];
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('arrayToString', [TwigFilters::class, 'filterArrayToString'])
        ];
    }
}
