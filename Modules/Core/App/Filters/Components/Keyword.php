<?php

declare(strict_types=1);
namespace Modules\Core\App\Filters\Components;

use Closure;
class Keyword implements ComponentInterface
{
    public function handle(array $content, Closure $next): mixed
    {
        if(isset($content['params']['keyword'])){
            $value = $content['params']['keyword'];
            $content['builder']
                ->orWhere('name', 'like', '%' . $value . '%')
                ->orWhere('mobile', 'like', '%' . $value . '%');
        }
        return $next($content);
    }
}
