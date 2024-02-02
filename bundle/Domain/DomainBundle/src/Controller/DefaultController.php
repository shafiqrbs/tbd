<?php

namespace Domain\DomainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/api/domain/medicine', name:'domain_medicine_index')]
    public function index() : JsonResponse
    {
        return new JsonResponse([
            'name' => 'domain_medicine_index'
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