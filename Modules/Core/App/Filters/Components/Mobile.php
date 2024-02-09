<?php
declare(strict_types=1);
namespace Modules\Core\App\Filters\Components;

use Closure;
class Mobile implements ComponentInterface
{
    public function handle(array $content, Closure $next): mixed
    {
        if(isset($content['params']['mobile'])){
            $value = $content['params']['mobile'];
            $content['builder']->where('mobile', 'like', '%' . $value . '%');
        }
        return $next($content);
    }
}
