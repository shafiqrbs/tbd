<?php

namespace Modules\Accounting\App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class AccountingModel extends Model
{
    use HasFactory;

    protected $table = 'acc_config';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'domain_id',
        'financial_start_date',
        'financial_end_date',
        'capital_investment_id',
        'account_cash_id',
        'account_bank_id',
        'account_mobile_id',
        'account_user_id',
        'account_vendor_id',
        'account_customer_id',
        'account_product_group_id',
        'account_category_id',
        'account_stock_opening_id',
        'voucher_stock_opening_id',
        'voucher_purchase_id',
        'voucher_sales_id',
        'voucher_sales_return_id',
        'voucher_purchase_return_id',
        'voucher_stock_reconciliation_id'
    ];

    public function capital_investment(): BelongsTo
    {
        return $this->BelongsTo(AccountHeadModel::class, 'capital_investment_id', 'id')->select(['id','name','slug','code']);
    }

    public function account_cash(): BelongsTo
    {
        return $this->BelongsTo(AccountHeadModel::class, 'account_cash_id', 'id')->select(['id','name','slug','code']);
    }

    public function account_bank(): BelongsTo
    {
        return $this->BelongsTo(AccountHeadModel::class, 'account_bank_id', 'id')->select(['id','name','slug','code']);
    }

    public function account_mobile(): BelongsTo
    {
        return $this->BelongsTo(AccountHeadModel::class, 'account_mobile_id', 'id')->select(['id','name','slug','code']);
    }

    public function account_user(): BelongsTo
    {
        return $this->BelongsTo(AccountHeadModel::class, 'account_user_id', 'id')->select(['id','name','slug','code']);
    }
    public function account_vendor(): BelongsTo
    {
        return $this->BelongsTo(AccountHeadModel::class, 'account_vendor_id', 'id')->select(['id','name','slug','code']);
    }
    public function account_customer(): BelongsTo
    {
        return $this->BelongsTo(AccountHeadModel::class, 'account_customer_id', 'id')->select(['id','name','slug','code']);
    }
    public function account_product_group(): BelongsTo
    {
        return $this->BelongsTo(AccountHeadModel::class, 'account_product_group_id', 'id')->select(['id','name','slug','code']);
    }

    public function account_category(): BelongsTo
    {
        return $this->BelongsTo(AccountHeadModel::class, 'account_category_id', 'id')->select(['id','name','slug','code']);
    }

    public function voucher_stock_opening(): BelongsTo
    {
        return $this->BelongsTo(AccountVoucherModel::class, 'voucher_stock_opening_id', 'id')->select(['id','name','slug','short_name']);
    }

    public function voucher_purchase(): BelongsTo
    {
        return $this->BelongsTo(AccountVoucherModel::class, 'voucher_purchase_id', 'id')->select(['id','name','slug','short_name']);
    }

    public function voucher_sales(): BelongsTo
    {
        return $this->BelongsTo(AccountVoucherModel::class, 'voucher_sales_id', 'id')->select(['id','name','slug','short_name']);
    }

    public function voucher_sales_return(): BelongsTo
    {
        return $this->BelongsTo(AccountVoucherModel::class, 'voucher_sales_return_id', 'id')->select(['id','name','slug','short_name']);
    }

    public function voucher_stock_reconciliation(): BelongsTo
    {
        return $this->BelongsTo(AccountVoucherModel::class, 'voucher_stock_reconciliation_id', 'id')->select(['id','name','slug','short_name']);
    }

    public function voucher_purchase_return(): BelongsTo
    {
        return $this->BelongsTo(AccountVoucherModel::class, 'voucher_purchase_return_id', 'id')->select(['id','name','slug','short_name']);
    }


    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });

    }

    public static function resetConfig($id){

        $table = 'acc_config';
        $columnsToExclude = [
            'id',
            'domain_id',
            'financial_start_date',
            'financial_end_date',
            'created_at',
            'updated_at'
        ];

        $booleanFields = [];

        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        $columnsToReset = array_diff($columns, $columnsToExclude);

        // Create SQL SET expressions
        $setStatements = [];

        foreach ($columnsToReset as $column) {
            if (array_key_exists($column, $booleanFields)) {
                // Handle boolean fields
                $value = $booleanFields[$column] ? 1 : 0;
                $setStatements[] = "`$column` = $value";
            } else {
                // Set other fields to NULL
                $setStatements[] = "`$column` = NULL";
            }
        }

        // Execute raw query with properly formatted SET statements
        DB::statement("UPDATE `$table` SET " . implode(', ', $setStatements) . " WHERE id = ?", [$id]);

    }


}
