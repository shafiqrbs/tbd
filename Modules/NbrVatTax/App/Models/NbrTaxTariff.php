<?php

namespace Modules\NbrVatTax\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NbrTaxTariff extends Model
{
    use HasFactory;

    protected $table = 'nbr_tax_tariff';
    public $timestamps = true;
    protected $guarded = ['id'];


    public static function getNbrTaxTariffDropdown()
    {
        $query = self::select(['name', 'slug', 'id'])->where([['status', 1]]);
        return $query->get()->toArray();
    }
}
