<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyLedger extends Model
{

    protected $table = 'acc_ledger_daily';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'config_id',
        'account_head_id',
        'account_sub_head_id',
        'amount',
        'debit',
        'credit',
        'opening_amount',
        'closing_amount'
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $date = new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date = new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    /**
     * Update or create today's DailyLedger entry for a given account head.
     *
     * @param int $configId
     * @param int $accountHeadId
     * @param int $accountSubHeadId
     * @param float $debit
     * @param float $credit
     * @param float $openingAmount
     * @return bool
     */
    public static function dailyLedgerManage(
        int $configId,
        int $accountHeadId,
        int $accountSubHeadId,
        float $debit = 0,
        float $credit = 0,
        float $openingAmount = 0
    ): bool {
        $amount = $debit + $credit;

        $findDailyLedger = DailyLedger::query()
            ->where('config_id', $configId)
            ->where('account_head_id', $accountHeadId)
            ->where('account_sub_head_id', $accountSubHeadId)
            ->whereDate('created_at', now())
            ->first();

        if ($findDailyLedger) {
            $newDebit  = $findDailyLedger->debit + $debit;
            $newCredit = $findDailyLedger->credit + $credit;
            $newAmount = $findDailyLedger->amount + $amount;

            $closing = $findDailyLedger->closing_amount + $debit - $credit;

            $findDailyLedger->update([
                'debit'          => $newDebit,
                'credit'         => $newCredit,
                'amount'         => $newAmount,
                'closing_amount' => $closing,
            ]);
        } else {
            $closing = $openingAmount + $debit - $credit;

            DailyLedger::create([
                'config_id'        => $configId,
                'account_head_id'  => $accountHeadId,
                'account_sub_head_id' => $accountSubHeadId,
                'opening_amount'   => $openingAmount,
                'debit'            => $debit,
                'credit'           => $credit,
                'amount'           => $amount,
                'closing_amount'   => $closing,
            ]);
        }

        return true;
    }
}

