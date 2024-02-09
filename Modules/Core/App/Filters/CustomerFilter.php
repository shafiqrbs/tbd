<?php


namespace Modules\Core\App\Filters;


use Illuminate\Pipeline\Pipeline;
use Modules\Core\App\Filters\Components\Keyword;
use Modules\Core\App\Filters\Components\Mobile;
use Modules\Core\App\Filters\Components\Name;

class CustomerFilter extends  BaseFilter
{
    protected function getFilters(): array
    {
        return [
            Name::class,
            Mobile::class,
            Keyword::class,
        ];
        // TODO: Implement getFIlters() method.
    }

}
