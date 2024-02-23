<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Entities\Core;

class CoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('core::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function user(Request $request,EntityManagerInterface $em)
    {

        $term = $request['term'];
        $entities = [];
        $service = new JsonRequestResponse();
        if ($term) {
            $go = 64;
            $entities = $em->getRepository(Core::class)->userAutoComplete($go,$term);
        }
        $data = $service->returnJosnResponse($entities);
        return $data;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function executive(Request $request,EntityManagerInterface $em)
    {

        $term = $request['term'];
        $entities = [];
        $service = new JsonRequestResponse();
        $go = 64;
        $entities = $em->getRepository(Core::class)->userAutoComplete( $go,$term);
        $data = $service->returnJosnResponse($entities);
        return $data;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function customer(Request $request,EntityManagerInterface $em)
    {

        $term = $request['term'];
        $entities = [];
        $service = new JsonRequestResponse();
        if ($term) {
            // $go = $this->getUser()->getGlobalOption();
            $go = 64;
            $entities = $em->getRepository(Core::class)->customerAutoComplete($go,$term);
        }
        $data = $service->returnJosnResponse($entities);
        return $data;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function vendor(Request $request,EntityManagerInterface $em)
    {

        $term = $request['term'];
        $entities = [];
        $service = new JsonRequestResponse();
        if ($term) {
            // $go = $this->getUser()->getGlobalOption();
            $go = 65;
            $entities = $em->getRepository(Core::class)->vendorAutoComplete($go,$term);
        }
        $data = $service->returnJosnResponse($entities);
        return $data;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function location(Request $request,EntityManagerInterface $em)
    {

        $term = $request['term'];
        $entities = [];
        $service = new JsonRequestResponse();
        $entities = $em->getRepository(Core::class)->locationAutoComplete($term);
        $data = $service->returnJosnResponse($entities);
        return $data;
    }

}
