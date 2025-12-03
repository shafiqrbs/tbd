<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Http\Requests\UserLoginRequest;
use Modules\Core\App\Models\UserWarehouseModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Tymon\JWTAuth\Facades\JWTAuth;

final class AuthController extends Controller
{
    public function login(UserLoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::where('username', $data['username'])->first();

        if (! $user || ! password_verify($data['password'], $user->password)) {
            return new JsonResponse([
                'status' => 401,
                'message' => 'Invalid credentials',
            ]);
        }

        // Generate JWT token manually
        $token = JWTAuth::fromUser($user);

        // Fetch additional data
//        $warehouse = UserWarehouseModel::getUserActiveWarehouse($user->id);
//        $productionItems = StockItemHistoryModel::getUserWarehouseProductionItem($user->id);

        $payload = [
//            'user_warehouse' => $warehouse ?? [],
//            'production_item' => $productionItems ?? [],
            'token' => $token,
        ];

        return new JsonResponse([
            'status' => 200,
            'message' => 'Login successful',
            'data' => $payload,
        ]);
    }
    public function loginTB(UserLoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::where('username', $data['username'])->first();

        if (! $user || ! password_verify($data['password'], $user->password)) {
            return new JsonResponse([
                'status' => 401,
                'message' => 'Invalid credentials',
            ]);
        }

        // Generate JWT token manually
        $token = JWTAuth::fromUser($user);

        // Fetch additional data
        $warehouse = UserWarehouseModel::getUserActiveWarehouse($user->id);
        $configData = DomainModel::domainHospitalConfig($user->domain_id);

        $payload = [
            'user_warehouse' => $warehouse ?? [],
            'hospital_config' => $configData ?? [],
            'token' => $token,
        ];

        return new JsonResponse([
            'status' => 200,
            'message' => 'Login successful',
            'data' => $payload,
        ]);
    }

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully.',
        ]);
    }
    public function me(): \Illuminate\Http\JsonResponse
    {
        // ✅ Best practice - always wrap token-required code in try-catch
        try {
            $user = \Tymon\JWTAuth\Facades\JWTAuth::parseToken()->authenticate();

            return response()->json([
                'status' => 'success',
                'user' => $user,
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token is invalid or expired',
            ], 401);
        }
    }

}
