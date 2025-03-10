<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\App\Http\Requests\WarehouseRequest;
use Modules\Core\App\Models\SettingModel;
use Modules\Core\App\Models\SettingTypeModel;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\WarehouseModel;

class WarehouseController extends Controller
{
    protected $domain;

    public function __construct(Request $request)
    {
        $userId = $request->header('X-Api-User');
        if ($userId && !empty($userId)){
            $userData = UserModel::getUserData($userId);
            $this->domain = $userData;
        }
    }
    public function index(Request $request){

        $data = WarehouseModel::getRecords($request,$this->domain);

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Warehouse fetched successfully.',
            'total' => $data['data']['count'],
            'data' => $data['data']['entities'],
        ], 200);
    }
    public function warehouseDropdown(Request $request){

        $data = WarehouseModel::getDropdown($request,$this->domain);

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Warehouse fetched successfully.',
            'data' => $data,
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(WarehouseRequest $request)
    {
        $input = $request->validated();
        $input['domain_id'] = $this->domain['global_id'];
        $input['status'] = true;

        // Ensure the setting type exists using firstOrCreate to avoid race conditions
        $wareHouseSettingType = SettingTypeModel::firstOrCreate(
            ['slug' => 'warehouse'],
            ['name' => 'Warehouse', 'status' => true, 'is_show_setting_dropdown' => false]
        );

        DB::beginTransaction();
        try {
            $input['setting_type_id'] = $wareHouseSettingType->id;

            // Create setting entry
            $wareHouseSetting = SettingModel::create($input);

            $input['setting_id'] = $wareHouseSetting->id;

            // Create warehouse entry
            $warehouse = WarehouseModel::create($input);

            // Commit transaction
            DB::commit();

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Warehouse created successfully.',
                'data' => $warehouse,
            ], 200);
        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();

            // Log the detailed error
            Log::error('Warehouse creation failed: ' . $e->getMessage(), ['trace' => $e->getTrace()]);

            // Show detailed error in non-production environments
            $errorMessage = app()->environment('production') ? 'Failed to create warehouse' : $e->getMessage();

            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => $errorMessage,
            ], 500);
        }
    }



    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        try {
            $entity = WarehouseModel::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Warehouse fetched successfully.',
                'data' => $entity
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Warehouse not found.'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(WarehouseRequest $request, $id)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $entity = WarehouseModel::find($id);

            if (!$entity) {
                return response()->json([
                    'message' => 'Warehouse not found.',
                    'errors' => ['id' => ['Invalid warehouse ID.']]
                ], 404);
            }

            if (!empty($data['mobile'])) {
                $warehouseExists = WarehouseModel::where('mobile', $data['mobile'])
                    ->where('id', '!=', $id)
                    ->first();

                if ($warehouseExists) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Validation failed.',
                        'errors' => ['mobile' => ['Mobile is already in use.']]
                    ], 422);
                }
            }

            $entity->update($data);

            DB::commit(); // Commit Transaction

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Warehouse updated successfully.',
                'data' => $entity,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on error
            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Find the warehouse
            $warehouse = WarehouseModel::find($id);

            // If no warehouse found, return 404 response
            if (!$warehouse) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => 'Warehouse not found.',
                ], 404);
            }

            // Delete the warehouse (supports soft deletes if enabled)
            $warehouse->update(['is_delete' => true]);

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Warehouse deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            // Catch unexpected errors
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'An error occurred while deleting the warehouse.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
