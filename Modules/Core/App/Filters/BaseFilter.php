<?php
namespace Modules\Core\App\Filters;
use Illuminate\Pipeline\Pipeline;

abstract class BaseFilter
{
    abstract protected function getFilters(): array;

    public function getResults($contents)
    {
        $limit = (isset($contents['limit']) and $contents['limit'])?$contents['limit']:50;
        $results = app(Pipeline::class)
            ->send($contents)
            ->through($this->getFilters())
            ->then(fn ($contents) => $contents['builder']);
        return $results->paginate($limit)->withQueryString();
    }
}
