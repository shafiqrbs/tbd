<?php

namespace Modules\Accounting\App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Entities\AccountHead;
use Modules\Inventory\App\Models\CategoryModel;

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
        'account_purchase_discount_id',
        'account_sales_discount_id',
        'account_vat_id',
        'account_ait_id',
        'account_zakat_id',
        'account_sd_id',
        'account_purchase_id',
        'account_sales_id',
        'account_tds_id',
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

    public static function initiateConfig($domain){


        self::updateOrCreate(
            ['id' =>$domain['acc_config']],
            [
                'capital_investment_id' => self::getHeadConfig($domain['acc_config'],147),
                'account_cash_id' => self::getHeadConfig($domain['acc_config'],96),
                'account_bank_id' => self::getHeadConfig($domain['acc_config'],97),
                'account_mobile_id' => self::getHeadConfig($domain['acc_config'],98),
                'account_user_id' => self::getHeadConfig($domain['acc_config'],109),
                'account_vendor_id' => self::getHeadConfig($domain['acc_config'],101),
                'account_customer_id' => self::getHeadConfig($domain['acc_config'],99),
                'account_product_group_id' => self::getHeadConfig($domain['acc_config'],105),
                'account_stock_opening_id' => self::getHeadConfig($domain['acc_config'],141),
                'account_purchase_id' => self::getHeadConfig($domain['acc_config'],120),
                'account_purchase_discount_id' => self::getHeadConfig($domain['acc_config'],152),
                'account_sales_id' => self::getHeadConfig($domain['acc_config'],154),
                'account_sales_discount_id' => self::getHeadConfig($domain['acc_config'],135),
                'account_vat_id' => self::getHeadConfig($domain['acc_config'],136),
                'account_ait_id' => self::getHeadConfig($domain['acc_config'],138),
                'account_zakat_id' => self::getHeadConfig($domain['acc_config'],140),
                'account_sd_id' => self::getHeadConfig($domain['acc_config'],139),
                'account_tds_id' => self::getHeadConfig($domain['acc_config'],137),
                'voucher_stock_opening_id' => self::getVoucherConfig($domain['acc_config'],13),
                'voucher_purchase_id' => self::getVoucherConfig($domain['acc_config'],17),
                'voucher_sales_id' => self::getVoucherConfig($domain['acc_config'],18),
                'voucher_sales_return_id' => self::getVoucherConfig($domain['acc_config'],6),
                'voucher_purchase_return_id' => self::getVoucherConfig($domain['acc_config'],9),
                'voucher_stock_reconciliation_id' => self::getVoucherConfig($domain['acc_config'],19),
            ]
        );
        self::setAccountHeadVoucher($domain);
    }

    public static function getHeadConfig($config,$id){

        $accountId = AccountHeadModel::where('config_id', $config)
            ->where('account_master_head_id', $id)
            ->where('status', 1)
            ->value('id');
        return $accountId;
    }

    public static function getVoucherConfig($config,$id){


        $accountId = AccountVoucherModel::where('config_id', $config)
            ->where('master_voucher_id', $id)
            ->where('status', 1)
            ->value('id');
        return $accountId;
    }

    public static function setAccountHeadVoucher($domain)
    {

        $cash = self::getHeadConfig($domain['acc_config'],96);
        $bank = self::getHeadConfig($domain['acc_config'],97);
        $mobile = self::getHeadConfig($domain['acc_config'],98);

        $userHead = self::getHeadConfig($domain['acc_config'],109);
        $vendorHead = self::getHeadConfig($domain['acc_config'],101);
        $customerHead = self::getHeadConfig($domain['acc_config'],99);

         $customer = self::getVoucherConfig($domain['acc_config'],14);
         $vendor = self::getVoucherConfig($domain['acc_config'],15);
         $user = self::getVoucherConfig($domain['acc_config'],20);

        VoucherAccountPrimaryModel::insertOrIgnore(
            [
              //  ['account_voucher_id' => $customer, 'primary_account_head_id' => $cash],
              //  ['account_voucher_id' => $customer, 'primary_account_head_id' => $bank],
              //  ['account_voucher_id' => $customer, 'primary_account_head_id' => $mobile],
                ['account_voucher_id' => $customer, 'primary_account_head_id' => $customerHead],
              //  ['account_voucher_id' => $vendor, 'primary_account_head_id' => $cash],
              //  ['account_voucher_id' => $vendor, 'primary_account_head_id' => $bank],
              //  ['account_voucher_id' => $vendor, 'primary_account_head_id' => $mobile],
                ['account_voucher_id' => $vendor, 'primary_account_head_id' => $vendorHead],
             //   ['account_voucher_id' => $user, 'primary_account_head_id' => $cash],
             //   ['account_voucher_id' => $user, 'primary_account_head_id' => $bank],
             //   ['account_voucher_id' => $user, 'primary_account_head_id' => $mobile],
                ['account_voucher_id' => $user, 'primary_account_head_id' => $userHead],
            ],
            ['account_voucher_id', 'primary_account_head_id'], // Unique keys
            [] // Fields to update (none in your case)
        );

        VoucherAccountSecondaryModel::insertOrIgnore(
            [
                ['account_voucher_id' => $customer, 'secondary_account_head_id' => $cash],
                ['account_voucher_id' => $customer, 'secondary_account_head_id' => $bank],
                ['account_voucher_id' => $customer, 'secondary_account_head_id' => $mobile],
           //     ['account_voucher_id' => $customer, 'secondary_account_head_id' => $customerHead],
                ['account_voucher_id' => $vendor, 'secondary_account_head_id' => $cash],
                ['account_voucher_id' => $vendor, 'secondary_account_head_id' => $bank],
                ['account_voucher_id' => $vendor, 'secondary_account_head_id' => $mobile],
            //    ['account_voucher_id' => $vendor, 'secondary_account_head_id' => $vendorHead],
                ['account_voucher_id' => $user, 'secondary_account_head_id' => $cash],
                ['account_voucher_id' => $user, 'secondary_account_head_id' => $bank],
                ['account_voucher_id' => $user, 'secondary_account_head_id' => $mobile],
           //     ['account_voucher_id' => $user, 'secondary_account_head_id' => $userHead],
            ],
            ['account_voucher_id', 'secondary_account_head_id'],
            []
        );

    }


}
