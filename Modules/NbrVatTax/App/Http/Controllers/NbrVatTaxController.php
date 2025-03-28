<?php

namespace Modules\NbrVatTax\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\NbrVatTax\App\Models\NbrTaxTariff;

class NbrVatTaxController extends Controller
{
    public function tariffDropdown(){
        $dropdown = NbrTaxTariff::getNbrTaxTariffDropdown();
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'data' => $dropdown
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('nbrvattax::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('nbrvattax::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('nbrvattax::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('nbrvattax::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
