<?php

namespace Domain\DomainBundle;

use Domain\DomainBundle\DependencyInjection\DomainDomainExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class DomainDomainBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new DomainDomainExtension();
    }
}