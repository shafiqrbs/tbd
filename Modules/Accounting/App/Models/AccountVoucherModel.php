<?php

namespace Modules\Accounting\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class AccountVoucherModel extends Model
{
    use HasFactory;

    protected $table = 'acc_voucher';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'voucher_type_id',
        'name',
        'short_name',
        'short_code',
        'mode',
        'status'
    ];

    public function voucher_type(): BelongsTo
    {
        return $this->belongsTo(SettingTypeModel::class);
    }


    public static function getEntityDropdown($request, $domain)
    {

        return DB::table('acc_voucher')
            ->leftJoin('acc_setting','acc_setting.id','=','acc_voucher.voucher_type_id')
            ->select([
                'acc_voucher.id',
                'acc_setting.id as voucher_type_id',
                'acc_setting.name as voucher_type_name',
                'acc_voucher.name as name',
                'acc_voucher.short_name as short_name',
                'acc_voucher.short_code as short_code',
                'acc_voucher.mode as mode',
                'acc_voucher.status as status',
            ])
            ->where([
                ['acc_voucher.config_id',$domain['acc_config']],
                ['acc_voucher.status','1']
            ])
            ->get();
    }

    public static function getRecords($request, $domain)
    {

        $page = isset($request['page']) && $request['page'] > 0 ? ($request['page'] - 1) : 1;
        $perPage = isset($request['offset']) && $request['offset'] != '' ? (int)($request['offset']) : 50;
        $skip = isset($page) && $page != '' ? (int)$page * $perPage : 0;
        $entity = self:: where('acc_voucher.config_id', $domain['acc_config'])
            ->leftJoin('acc_setting','acc_setting.id','=','acc_voucher.voucher_type_id')
            ->select([
                'acc_voucher.id',
                'acc_setting.id as voucher_type_id',
                'acc_setting.name as voucher_type_name',
                'acc_voucher.name as name',
                'acc_voucher.short_name as short_name',
                'acc_voucher.short_code as short_code',
                'acc_voucher.mode as mode',
                'acc_voucher.status as status',
            ])
            ->orderBy('acc_voucher.name','ASC');

        if (isset($request['term']) && !empty($request['term'])) {
            $entity = $entity->whereAny(
                ['acc_voucher.name', 'acc_voucher.slug'], 'LIKE', '%' . $request['term'] . '%');
        }
        if (isset($request['type']) && !empty($request['type'])) {
            $entity = $entity->where(
                ['acc_setting.voucher_type_id'=>$request['type']]);
        }
        $total = $entity->count();
        $entities = $entity->skip($skip)
            ->take($perPage)
            ->orderBy('acc_setting.id', 'DESC')
            ->get();

        $data = array('count' => $total, 'entities' => $entities);
        return $data;
    }




}
