<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\BalanceRequest;
use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

class BalanceController extends Controller
{
    public function __construct(private BalanceService $balanceService)
    {

    }

    // Пополнение баланса
    public function deposit(BalanceRequest $request): JsonResponse
    {
        try {
            $result = $this->balanceService->deposit(
                $request->input('user_id'),
                (float) $request->input('amount'),
                $request->input('comment')
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Пользователь не найден',
            ], 404);

        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    // Списание средств с баланса
    public function withdraw(BalanceRequest $request): JsonResponse
    {
        try {
            $result = $this->balanceService->withdraw(
                $request->input('user_id'),
                (float) $request->input('amount'),
                $request->input('comment')
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Пользователь не найден',
            ], 404);

        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 409);
        }
    }

    // Переводы между пользователями
    public function transfer(BalanceRequest $request): JsonResponse
    {
        $request->validate([
            'to_user_id' => 'required|integer|min:1',
        ]);

        try {
            $result = $this->balanceService->transfer(
                $request->input('user_id'),
                $request->input('to_user_id'),
                (float) $request->input('amount'),
                $request->input('comment')
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Пользователь не найден',
            ], 404);

        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 409);
        }
    }

    // Получение текущего баланса пользователя
    public function getBalance(int $userId): JsonResponse
    {
        try {
            $result = $this->balanceService->getBalance($userId);

            return response()->json([
                'success' => true,
                'data' => $result,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Пользователь не найден',
            ], 404);
        }
    }
}
