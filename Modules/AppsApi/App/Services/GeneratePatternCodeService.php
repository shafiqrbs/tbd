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
            ->where('e.global_option_id', $domain)
            ->whereBetween('e.created', [
                Carbon::parse($date),
                Carbon::parse($date)->endOfMonth()
            ])->count('id');
        $lastCode = $entity;
        $code = (int)$lastCode + 1;
        if(empty($prefix)) {
            $customerId = sprintf("%s%s", $datetime->format('my'), str_pad($lastCode, 5, '0', STR_PAD_LEFT));
        }else{
            $customerId = sprintf("%s%s%s",$prefix, $datetime->format('my'), str_pad($lastCode, 5, '0', STR_PAD_LEFT));
        }
        $data = array('code'=>$code,'generateId'=>$customerId);
        return $data;


    }

    public function invoiceNo($queryParams = [])
    {

        $prefix     = $queryParams['prefix'];
        $domain     = $queryParams['domain'];
        $table      = $queryParams['table'];

        $datetime = new \DateTime("now");
        $date = $datetime->format('Y-m-01');
        $entity = DB::table("{$table} as e")
            ->where('e.global_option_id', $domain)
            ->whereBetween('e.created', [
                Carbon::parse($date),
                Carbon::parse($date)->endOfMonth()
            ])->count('id');
        $lastCode = $entity;
        $code = (int)$lastCode + 1;
        if(empty($prefix)) {
            $customerId = sprintf("%s%s", $datetime->format('my'), str_pad($lastCode, 5, '0', STR_PAD_LEFT));
        }else{
            $customerId = sprintf("%s%s%s",$prefix, $datetime->format('my'), str_pad($lastCode, 5, '0', STR_PAD_LEFT));
        }
        $data = array('code'=>$code,'generateId'=>$customerId);
        return $data;


    }

}
