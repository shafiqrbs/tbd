<?php

namespace Modules\NbrVatTax\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NbrItemVat extends Model
{
    use HasFactory;

    protected $table = 'nbr_item_vat';
    public $timestamps = true;
    protected $guarded = ['id'];

}
