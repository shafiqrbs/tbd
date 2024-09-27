<?php
/**
 * Created by PhpStorm.
 * User: shafiq
 * Date: 10/9/15
 * Time: 8:05 AM
 */

namespace Modules\Core\App\Repositories;

use Doctrine\ORM\EntityRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Filters\CustomerFilter;
use Modules\Core\App\Models\UserModel;
use Modules\Domain\App\Entities\GlobalOption;


class UserRepository extends EntityRepository {


    public function listWithSearch(array $queryParams = [])
    {

        $page = (isset($queryParams['page']) and $queryParams['page'] ) ? $queryParams['page']:1;
        $limit = (isset($queryParams['limit']) and $queryParams['limit'] ) ? $queryParams['limit']:200;
        $data = Cache::remember('users'.$page, 200, function() use ($queryParams,$limit){
            $queryBuilder = UserModel::where('isDelete',0)->select('id','username as name','email','created_at')->orderBy('created_at','DESC');
            $query = resolve(CustomerFilter::class)->getResults([
                'builder' => $queryBuilder,
                'params' => $queryParams,
                'limit' => $limit
            ]);
            return $query;
        });
        return $data;

    }

    public function getAccessRoleGroup(GlobalOption $globalOption){

        $array['Core Module'] = array(
            'ROLE_CORE'                                   => 'Core Module',
            'ROLE_CORE_MANAGER'                                   => 'Core Manager',
            'ROLE_CORE_ADMIN'                        => 'Core Admin',
        );
        $array['Customer'] = array(
            'ROLE_CRM'                          => 'Customer',
            'ROLE_CRM_MANAGER'                  => 'Managers',
        );
        $array['Reports'] = array(
            'ROLE_REPORT'                        => 'Reports',
            'ROLE_REPORT_FINANCIAL'              => 'Accounting Financial',
            'ROLE_REPORT_ADMIN'                  => 'Admin',
        );
        $array['SMS'] = array(
            'ROLE_SMS'                                          => 'Sms/E-mail',
            'ROLE_SMS_MANAGER'                                  => 'Sms/E-mail Manager',
            'ROLE_SMS_CONFIG'                                   => 'SMS/E-mail Setup',
            'ROLE_SMS_BULK'                                     => 'SMS Bulk',

        );
        return $array;
    }

    public function getAccessRoleGroupOld(GlobalOption $globalOption){


        /* $modules = $globalOption->getSiteSetting()->getAppModules();
         $arrSlugs = array();
         if (!empty($globalOption->getSiteSetting()) and !empty($modules)) {
             foreach ($globalOption->getSiteSetting()->getAppModules() as $mod) {
                 if (!empty($mod->getModuleClass())) {
                     $arrSlugs[] = $mod->getSlug();
                 }
             }
         }

         $array = array();

         $accounting = array('accounting');
         $result = array_intersect($arrSlugs, $accounting);
         if (!empty($result)) {

             $array['Accounting'] = array(
                 'ROLE_ACCOUNTING'                               => 'Accounting',
                 'ROLE_DOMAIN_ACCOUNTING_EXPENDITURE'            => 'Expenditure',
                 'ROLE_DOMAIN_ACCOUNTING_PURCHASE'               => 'Purchase',
                 'ROLE_DOMAIN_ACCOUNTING_SALES'                  => 'Sales',
                 'ROLE_DOMAIN_ACCOUNTING_EXPENDITURE_PURCHASE'   => 'Expenditure Purchase',
                 'ROLE_DOMAIN_ACCOUNTING_CASH'                   => 'Account Cash',
                 'ROLE_DOMAIN_ACCOUNTING_JOURNAL'                => 'Journal',
                 'ROLE_DOMAIN_ACCOUNTING_TRANSACTION'            => 'Transaction',
                 'ROLE_DOMAIN_ACCOUNTING_PURCHASE_REPORT'        => 'Purchase Report',
                 'ROLE_DOMAIN_ACCOUNTING_SALES_REPORT'           => 'Sales Report',
                 'ROLE_DOMAIN_ACCOUNTING_REPORT'                 => 'Financial Report',
                 'ROLE_DOMAIN_ACCOUNTING_SALES_ADJUSTMENT'       => 'Cash Adjustment',
                 'ROLE_DOMAIN_ACCOUNTING_RECONCILIATION'         => 'Cash Reconciliation',
                 'ROLE_DOMAIN_ACCOUNTING_CONDITION'              => 'Condition Account',
                 'ROLE_DOMAIN_ACCOUNTING_BANK'                   => 'Bank & Mobile',
                 'ROLE_DOMAIN_FINANCE_APPROVAL'                  => 'Approval',
                 'ROLE_DOMAIN_ACCOUNTING_LOAN'                   => 'Loan',
                 'ROLE_DOMAIN_ACCOUNT_REVERSE'                   => 'Reverse',
                 'ROLE_DOMAIN_ACCOUNTING_CONFIG'                 => 'Configuration',
                 'ROLE_DOMAIN_ACCOUNTING'                        => 'Admin',
             );
         }


         $hms = array('hms');
         $result = array_intersect($arrSlugs, $hms);
         if (!empty($result)) {
             $array['HMS'] = array(
                 'ROLE_HOSPITAL'                              => 'Hospital & Diagnostic',
                 'ROLE_DOMAIN_HOSPITAL_OPERATOR'              => 'Receptionist',
                 'ROLE_DOMAIN_HOSPITAL_ADMISSION'             => 'Admission',
                 'ROLE_DOMAIN_HOSPITAL_VISIT'                 => 'Doctor Visit',
                 'ROLE_DOMAIN_HOSPITAL_MANAGER'               => 'Manager',
                 'ROLE_DOMAIN_HOSPITAL_COMMISSION'            => 'Doctor/Referred Commission',
                 'ROLE_DOMAIN_HOSPITAL_LAB'                   => 'Lab Assistant',
                 'ROLE_DOMAIN_HOSPITAL_REPORT_PRINT'          => 'Report Print',
                 'ROLE_DOMAIN_HOSPITAL_DUTY_DOCTOR'           => 'Duty Doctor',
                 'ROLE_DOMAIN_HOSPITAL_DUTY_NURSE'            => 'Duty Nurse',
                 'ROLE_DOMAIN_HOSPITAL_OT'                    => 'OT',
                 'ROLE_DOMAIN_HOSPITAL_DOCTOR'                => 'Doctor',
                 'ROLE_DOMAIN_HOSPITAL_REPORT_REQUEST'        => 'Request Report',
                 'ROLE_DOMAIN_HOSPITAL_MASTERDATA'            => 'Master Data',
                 'ROLE_DOMAIN_HOSPITAL_REPORT'                => 'Reports',
                 'ROLE_HOSPITAL_FINANCIAL_APPROVE'            => 'Financial Approve',
                 'ROLE_DOMAIN_HOSPITAL_CONFIG'                => 'Configuration',
                 'ROLE_DOMAIN_HOSPITAL_ADMIN'                 => 'Administrator',
             );
         }

         $miss = array('miss');
         $result = array_intersect($arrSlugs, $miss);
         if (!empty($result)) {
             $array['Medicine'] = array(
                 'ROLE_MEDICINE'                                  => 'Medicine',
                 'ROLE_MEDICINE_SALES'                            => 'Medicine Sales',
                 'ROLE_MEDICINE_PURCHASE'                         => 'Medicine Purchase',
                 'ROLE_MEDICINE_STOCK'                            => 'Medicine Stock',
                 'ROLE_MEDICINE_MANAGER'                          => 'Medicine Manager',
                 'ROLE_MEDICINE_REVERSE'                          => 'Medicine Reverse',
                 'ROLE_MEDICINE_REPORT'                           => 'Medicine Report',
                 'ROLE_MEDICINE_ADMIN'                            => 'Medicine Admin',
             );
         }

         $dps = array('dps');
         $result = array_intersect($arrSlugs, $dps);
         if (!empty($result)) {
             $array['DPS'] = array(
                 'ROLE_DPS'                                      => 'Doctor Prescription',
                 'ROLE_DPS_DOCTOR'                               => 'Doctor',
                 'ROLE_DPS_ADMIN'                                => 'Doctor Admin',
             );
         }

         $payroll = array('payroll');
         $result = array_intersect($arrSlugs, $payroll);
         if (!empty($result)) {

             $array['HR & Payroll'] = array(
                 'ROLE_HR'                                   => 'Human Resource',
                 'ROLE_HR_EMPLOYEE'                          => 'HR Employee',
                 'ROLE_HR_ATTENDANCE'                        => 'HR Attendance',
                 'ROLE_PAYROLL'                              => 'Payroll',
                 'ROLE_PAYROLL_SALARY'                       => 'Payroll Salary',
                 'ROLE_PAYROLL_APPROVAL'                     => 'Payroll Approval',
                 'ROLE_PAYROLL_REPORT'                       => 'Payroll Report',
             );
         }*/

        $array['Customer'] = array(
            'ROLE_CRM'                          => 'Customer',
            'ROLE_CRM_MANAGER'                  => 'Managers',
        );

        $array['Reports'] = array(
            'ROLE_REPORT'                        => 'Reports',
            'ROLE_REPORT_FINANCIAL'              => 'Accounting Financial',
            'ROLE_REPORT_ADMIN'                  => 'Admin',
        );

        $array['SMS'] = array(
            'ROLE_SMS'                                          => 'Sms/E-mail',
            'ROLE_SMS_MANAGER'                                  => 'Sms/E-mail Manager',
            'ROLE_SMS_CONFIG'                                   => 'SMS/E-mail Setup',
            'ROLE_SMS_BULK'                                     => 'SMS Bulk',

        );
        return $array;
    }

    public function getAndroidRoleGroup(){

        $array = array();
        $array['Android Apps'] = array(
            'ROLE_MANAGER'                                   => 'Manager',
            'ROLE_PURCHASE'                                  => 'Purchase',
            'ROLE_SALES'                                     => 'Sales',
            'ROLE_EXPENSE'                                   => 'Expense',
            'ROLE_STOCK'                                     => 'Stock',
        );
        return $array;
    }
}
