<?php

namespace Modules\AppsApi\App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\VendorModel;


class GeneratePatternCodeService
{

    public function customerCode($queryParams = [])
    {

        $prefix     = $queryParams['prefix'];
        $domain     = $queryParams['domain'];
        $table      = $queryParams['table'];

        $datetime = new \DateTime("now");
        $date = $datetime->format('Y-m-01');
        $entity = DB::table("{$table} as e")
            ->where('e.domain_id', $domain)
            ->whereBetween('e.created_at', [
                Carbon::parse($date),
                Carbon::parse($date)->endOfMonth()
            ])->count('id');
        $lastCode = $entity;
        $code = (int)$lastCode + 1;
        if(empty($prefix)) {
            $customerId = sprintf("%s%s", $datetime->format('my'), str_pad($code, 5, '0', STR_PAD_LEFT));
        }else{
            $customerId = sprintf("%s%s%s",$prefix, $datetime->format('my'), str_pad($code, 5, '0', STR_PAD_LEFT));
        }
        $data = array('code'=>$code,'generateId'=>$customerId);
        return $data;


    }

    public function invoiceNo($queryParams)
    {

        $prefix     = $queryParams['prefix'];
        $domain     = $queryParams['config'];
        $table      = $queryParams['table'];
        $datetime = new \DateTime("now");
        $date = $datetime->format('Y-m-01');
        $entity = DB::table("{$table} as e")
            ->where('e.config_id', $domain)
            ->whereBetween('e.created_at', [
                Carbon::parse($date),
                Carbon::parse($date)->endOfMonth()
            ])->count('id');
        $lastCode = $entity;
        $code = (int)$lastCode + 1;
        if(empty($prefix)) {
            $customerId = sprintf("%s%s", $datetime->format('my'), str_pad($code, 5, '0', STR_PAD_LEFT));
        }else{
            $customerId = sprintf("%s%s%s",$prefix, $datetime->format('my'), str_pad($code, 5, '0', STR_PAD_LEFT));
        }
        $data = array('code'=>$code,'generateId'=>$customerId);
        return $data;


    }

    public function productBatch($queryParams = [])
    {

        $prefix     = $queryParams['prefix'];
        $config     = $queryParams['config'];
        $table      = $queryParams['table'];

        $datetime = new \DateTime("now");
        $date = $datetime->format('Y-m-01');
        $entity = DB::table("{$table} as e")
            ->where('e.config_id', $config)
            ->whereBetween('e.created_at',[
                Carbon::parse($date),
                Carbon::parse($date)->endOfMonth()
            ])->count('id');
        $lastCode = $entity;
        $code = (int)$lastCode + 1;
        if(empty($prefix)) {
            $generateId = sprintf("%s%s", $datetime->format('my'), str_pad($lastCode, 5, '0', STR_PAD_LEFT));
        }else{
            $generateId = sprintf("%s%s%s",$prefix, $datetime->format('my'), str_pad($lastCode, 5, '0', STR_PAD_LEFT));
        }
        $data = array('code' => $code,'generateId' => $generateId);
        return $data;


    }

    public function categoryCode($queryParams = [])
    {

        $config     = $queryParams['config'];
        $table      = $queryParams['table'];
        $entity = DB::table("{$table} as e")
            ->where('e.config_id', $config)
            ->count('id');
        $lastCode = $entity;
        $code = (int)$lastCode + 1;
        $generateId = sprintf("%s", str_pad($code, 3, '0', STR_PAD_LEFT));
        $data = array('code' => $code,'generateId' => $generateId);
        return $data;


    }

    public function productBarcodeCode($queryParams = [])
    {

        $config     = $queryParams['config'];
        $category      = $queryParams['category'];
        $table      = $queryParams['table'];
        $entity = DB::table("{$table} as e")
            ->where('e.config_id', $config)
            ->count('id');
        $lastCode = $entity;
        $code = (int)$lastCode + 1;
        $generateId = sprintf("%s%s", $category,str_pad($code, 5, '0', STR_PAD_LEFT));
        $data = array('code' => $code,'generateId' => $generateId);
        return $data;


    }

    public function productStockBarcodeCode($queryParams = [])
    {


        $table      = $queryParams['table'];
        $product      = $queryParams['product'];
        $barcode      = $queryParams['barcode'];
        $entity = DB::table("{$table} as e")
            ->where('e.product_id', $product)
            ->count('id');
        $lastCode = $entity;
        $code = (int)$lastCode + 1;
        $generateId = sprintf("%s%s",$barcode, str_pad($code, 2, '0', STR_PAD_LEFT));
        $data = array('code' => $code,'generateId' => $generateId);
        return $data;


    }

    public function bedDisplayName($queryParams = [],$model)
    {

        dd($model);
        $config     = $queryParams['config'];
        $category   = $queryParams['category'];
        $table      = $queryParams['table'];

        $entity = DB::table("{$table} as e")
            ->where('e.config_id', $config)
            ->count('id');
        $lastCode = $entity;
        $code = (int)$lastCode + 1;
        $generateId = sprintf("%s%s", $category,str_pad($code, 5, '0', STR_PAD_LEFT));
        $data = array('code' => $code,'generateId' => $generateId);
        return $data;


    }

}
