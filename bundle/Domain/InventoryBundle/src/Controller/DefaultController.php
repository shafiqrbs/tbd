<?php

namespace Domain\InventoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/api/terminalbd/invoice', name:'terminalbd_invoice_index')]
    public function index() : JsonResponse
    {
        return new JsonResponse([
            'name' => 'invoice'
        ]);
    }

    #[Route('/api/terminalbd/invoice/edit', name:'terminalbd_invoice_edit')]
    public function edit() : JsonResponse
    {
        return new JsonResponse([
            'name' => 'invoice edit'
        ]);
    }
}