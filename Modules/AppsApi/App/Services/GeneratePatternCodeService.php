<?php

namespace Modules\AppsApi\App\Services;

use Illuminate\Support\Facades\DB;


class GeneratePatternCodeService
{

    public function customerCode($queryParams = [])
    {

        $domain = $queryParams['domain'];
        $table =  $queryParams['table'];
        //  $entity = self::where('global_option_id',$domain)->count();
        $datetime = new \DateTime("now");
        $today_startdatetime = $datetime->format('Y-m-01 00:00:00');
        $today_enddatetime = $datetime->format('Y-m-t 23:59:59');

        $entity = DB::table("{$table} as e")
            ->select(DB::raw("MAX(e.code) as code"))
            //->where('s.created','>=', $today_startdatetime)
            //->where('s.created','<=', $today_startdatetime)
            ->where('e.global_option_id', $domain)->first();
        $lastCode = $entity->code;

        $code = (int)$lastCode + 1;
        $customerId = sprintf("%s%s", $datetime->format('my'), str_pad($lastCode,5, '0', STR_PAD_LEFT));
        $data = array('code'=>$code,'generateId'=>$customerId);
        return $data;


    }

}
