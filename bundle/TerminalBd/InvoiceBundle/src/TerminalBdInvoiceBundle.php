<?php

namespace TerminalBd\InvoiceBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use TerminalBd\InvoiceBundle\DependencyInjection\TerminalBdInvoiceExtension;

class TerminalBdInvoiceBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new TerminalBdInvoiceExtension();
    }
}