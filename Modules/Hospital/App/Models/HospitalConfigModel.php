<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\Inventory\App\Models\CategoryModel;
use Ramsey\Collection\Collection;

class HospitalConfigModel extends Model
{
    use HasFactory;

    protected $table = 'hms_config';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

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

    public function admission_fee()
    {
        return $this->hasOne(ParticularModel::class,'id', 'admission_fee_id');
    }

    public function opd_ticket_fee()
    {
        return $this->hasOne(ParticularModel::class, 'id','opd_ticket_fee_id');
    }

    public function emergency_fee()
    {
        return $this->hasOne(ParticularModel::class, 'id','emergency_fee_id');
    }

    public function ot_fee()
    {
        return $this->hasOne(ParticularModel::class, 'id','ot_fee_id');
    }

    public static function resetConfig($id){

        $table = 'hms_config';
        $columnsToExclude = [
            'id',
            'domain_id',
            'created_at',
            'updated_at'
        ];


        $booleanFields = [
            'opd_select_doctor' => true,
            'special_discount_doctor' => true,
            'special_discount_investigation' => true,
        ];

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

    public static function resetMasterData($domain)
    {

        DB::transaction(function () use ($domain) {

            $configId = $domain['hms_config'];

            $parentHeads = ParticularTypeMasterModel::all();
            foreach ($parentHeads as $head) {
                ParticularTypeModel::updateOrCreate(
                    [
                        'config_id' => $configId,
                        'particular_master_type_id' => $head->id,
                    ],
                    [
                        'name' => $head->name,
                        'slug' => $head->slug,
                        'short_code' => $head->slug,
                        'is_private' => true,
                        'parent_id' => null,
                    ]
                );
            }

        });

    }

    public static function investigationMasterReport($domain)
    {
        $configId = $domain['hms_config'];
        self::defaultResetMasterData($domain);
        $parentHeads = InvestigationReportModel::all();
        if($parentHeads){
            foreach ($parentHeads as $entity) {

                $particularType =  ParticularTypeModel::where('particular_master_type_id',9)->where('config_id',$configId)->first();
                $category = self::setGetCategoryGroup($domain,$entity->category_id);
                $investigation = ParticularModel::updateOrCreate(
                    [
                        'config_id' => $configId,
                        'particular_type_id' => $particularType->id,
                        'investigation_report_id' => $entity->id
                    ],
                    [

                        'code'           => $entity->code,
                        'name'           => $entity->name,
                        'category_id'    => $category->id ?? null,
                        'status'         => 1,
                        'price'          => $entity->price,
                        'service'        => $entity->service,
                        'sepcimen'       => $entity->sepcimen,
                        'instruction'    => $entity->instruction,


                    ]
                );
                self::investigationMasterReportFormat($entity,$investigation);
            }
        }
        return "success";

    }

    public static function defaultResetMasterData($domain)
    {
        $categories = [
            [
                'name' => 'IPD Fee',
                'subcategories' => [
                    [
                        'name' => 'IPD',
                        'is_private' => 1,
                        'products' => [
                            [
                                'name' => 'Admission Fee',
                                'slug' => 'admission-fee',
                                'price' => 15
                            ],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'OPD Fee',
                'subcategories' => [
                    [
                        'name' => 'OPD',
                        'is_private' => 1,
                        'products' => [
                            [
                                'name' => 'OPD Ticket Fee',
                                'slug' => 'opd-ticket-fee',
                                'price' => 10
                            ],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'Emergency Fee',
                'subcategories' => [
                    [
                        'name' => 'Emergency',
                        'is_private' => 1,
                        'products' => [
                            [
                                'name' => 'Emergency Fee',
                                'slug' => 'emergency-fee',
                                'price' => 10
                            ],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'OT Fee',
                'subcategories' => [
                    [
                        'name' => 'OT',
                        'is_private' => 1,
                        'products' => [
                            [
                                'name' => 'OT Fee',
                                'slug' => 'ot-fee',
                                'price' => 50
                            ],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'ICU Fee',
                    'subcategories' => [
                        [
                            'name' => 'ICU',
                            'is_private' => 1,
                            'products' => []
                        ],
                    ],
            ],
            [
            'name' => 'Doctor Fee',
                'subcategories' => [
                    [
                        'name' => 'Doctor',
                        'is_private' => 1,
                        'products' => []
                    ],
                ],
            ],
            [
                'name' => 'Cabin Fee',
                'subcategories' => [
                    [
                        'name' => 'Cabin',
                        'is_private' => 1,
                        'products' => []
                    ],
                ],
            ],
            [
                'name' => 'Bed Fee',
                'subcategories' => [
                    [
                        'name' => 'Bed',
                        'is_private' => 1,
                        'products' => []
                    ],
                ],
            ]
        ];
        self::insertCategoryTreeWithProducts($domain,$categories);
    }

    public static function setGetCategoryGroup($domain,$id)
        {
            $invConfig = $domain['inv_config'];
            $accConfig = $domain['acc_config'];
            $masterCategory = HmsCategoryModel::find($id);
            if($masterCategory){
                $entity = CategoryModel::updateOrCreate(
                    [
                        'config_id' => $invConfig,
                        'name' => $masterCategory->name
                    ],
                    [
                        'code'           => $masterCategory->code,
                        'status'         => 1,
                    ]
                );
                if($entity){
                    $ledgerExist = AccountHeadModel::where('product_group_id',$entity->id)->where('config_id', $accConfig)->first();
                    if(empty($ledgerExist)){
                        AccountHeadModel::insertCategoryGroupLedger($accConfig,$entity);
                    }
                }
                $category = self::setGetCategory($invConfig,$entity);
                return $category;
            }

        }

    public static function setGetCategory($invConfig,$group)
    {
        $category = CategoryModel::updateOrCreate(
            [
                'parent' => $group->id,
                'config_id' => $invConfig
            ],
            [
                'name'           => $group->name,
                'status'         => 1,
            ]
        );
        return $category;

    }

    public static function investigationMasterReportFormat($entity,$investigation){


        /*$formats = InvestigationMasterReportFormatModel::where('diagnostic_report_id', $entity->id)->get();
        foreach ($formats as $report) {
            $format = InvestigationReportFormatModel::updateOrCreate(
                [
                    'particular_id' => $investigation->id,
                    'master_report_format_id'=> $report->id, // Use unique field(s) for matching
                ],
                [
                    'parent_id'       => $report->parent_id,
                    'reference_value' => $report->reference_value,
                    'unit'            => $report->unit,
                    'sorting'         => $report->sorting,
                    'status'          => 1,
                ]
            );
        }*/
    }

    public static function insertCategoryTreeWithProducts($domain,$categories, $parentId = null)
    {
        $invConfigId = $domain['inv_config'];
        $configId = $domain['hms_config'];
        foreach ($categories as $category) {

            // Insert Category
            $newCategory = CategoryModel::updateOrCreate(
                [
                    'config_id' => $invConfigId,
                    'name' => $category['name']
                ],
                [
                    'parent' => $parentId,
                    'is_private'     => 1,
                    'status' => 1,
                ]
            );

            // Insert products if present (for subcategories)
            if (isset($category['products']) && is_array($category['products'])) {
                foreach ($category['products'] as $product) {
                    $particular = ParticularModel::updateOrCreate(
                        [
                            'config_id' => $configId,
                            'name' => $product['name'],
                            'category_id' => $newCategory->id
                        ],
                        [
                            'slug' => $product['slug'],
                            'price' => $product['price'],
                            'status' => 1,
                        ]
                    );
                    self::insertDefaultOperationFee($configId,$particular);

                }
            }

            // If there are subcategories, insert them recursively
            if (isset($category['subcategories']) && is_array($category['subcategories'])) {
                self::insertCategoryTreeWithProducts($domain,$category['subcategories'], $newCategory->id);
            }
        }
    }

    public static function insertDefaultOperationFee($configId,$particular)
    {
        $feeMap = [
            'admission-fee'   => 'admission_fee_id',
            'opd-ticket-fee'  => 'opd_ticket_fee_id',
            'emergency-fee'   => 'emergency_fee_id',
            'ot-fee'          => 'ot_fee_id',
        ];

        if (isset($feeMap[$particular->slug])) {
           HospitalConfigModel::updateOrCreate(
                [
                    'id' => $configId,
                ],
               [
                   $feeMap[$particular->slug] => $particular->id,
               ]
            );
        }

    }


}
