<?php

declare(strict_types=1);
namespace Modules\Core\App\Filters\Components;

use Closure;
class Name implements ComponentInterface
{
    public function handle(array $content, Closure $next): mixed
    {
        if(isset($content['params']['name'])){
            $value = $content['params']['name'];
            $content['builder']->where('name', 'like', '%' . $value . '%');
        }
        return $next($content);
    }
}
