<?php

declare(strict_types=1);
namespace Modules\Core\App\Filters\Components;

use Closure;

interface ComponentInterface
{
    public function handle(array $content, Closure $next): mixed;
}
