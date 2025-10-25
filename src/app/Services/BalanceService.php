<?php

namespace App\Services;

use App\Models\User;
use App\Models\Balance;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

class BalanceService
{
    // Пополнение баланса
    public function deposit(int $userId, float $amount, string $comment = null): array
    {
        return DB::transaction(function () use ($userId, $amount, $comment) {

            $this->validateAmount($amount);
    
            $user = User::find($userId);
            if (!$user) {
                throw new ModelNotFoundException("Пользователь не найден");
            }

            $balance = Balance::firstOrCreate(
                ['user_id' => $userId],
                ['amount' => 0]
            );

            $balance->increment('amount', $amount);

            $transaction = Transaction::create([
                'user_id' => $userId,
                'type' => 'deposit',
                'amount' => $amount,
                'comment' => $comment,
            ]);

            return [
                'balance' => $balance->fresh()->amount,
                'transaction_id' => $transaction->id,
            ];
        });
    }

    // Списание средств с баланса
    public function withdraw(int $userId, float $amount, string $comment = null): array
    {
        return DB::transaction(function () use ($userId, $amount, $comment) {

            $this->validateAmount($amount);
            
            $user = User::find($userId);
            if (!$user) {
                throw new ModelNotFoundException("Пользователь не найден");
            }

            $balance = Balance::where('user_id', $userId)->lockForUpdate()->first();
            
            if (!$balance) {
                throw new InvalidArgumentException("Недостаточно средств");
            }

            if ($balance->amount < $amount) {
                throw new InvalidArgumentException("Недостаточно средств");
            }

            $balance->decrement('amount', $amount);

            $transaction = Transaction::create([
                'user_id' => $userId,
                'type' => 'withdraw',
                'amount' => $amount,
                'comment' => $comment,
            ]);

            return [
                'balance' => $balance->fresh()->amount,
                'transaction_id' => $transaction->id,
            ];
        });
    }

    // Переводы между пользователями
    public function transfer(int $fromUserId, int $toUserId, float $amount, string $comment = null): array
    {
        return DB::transaction(function () use ($fromUserId, $toUserId, $amount, $comment) {

            $this->validateAmount($amount);
            
            if ($fromUserId === $toUserId) {
                throw new InvalidArgumentException("Невозможно перевести самому себе");
            }

            $fromUser = User::find($fromUserId);
            $toUser = User::find($toUserId);
            
            if (!$fromUser || !$toUser) {
                throw new ModelNotFoundException("Пользователь не найден");
            }

            // Блокируем баланс пользователей, до завершения транзакций
            $fromBalance = Balance::where('user_id', $fromUserId)->lockForUpdate()->first();
            $toBalance = Balance::firstOrCreate(
                ['user_id' => $toUserId],
                ['amount' => 0]
            );

            if (!$fromBalance || $fromBalance->amount < $amount) {
                throw new InvalidArgumentException("Недостаточно средств");
            }

            $fromBalance->decrement('amount', $amount);
            $toBalance->increment('amount', $amount);

            Transaction::create([
                'user_id' => $fromUserId,
                'type' => 'transfer_out',
                'amount' => $amount,
                'related_user_id' => $toUserId,
                'comment' => $comment,
            ]);

            Transaction::create([
                'user_id' => $toUserId,
                'type' => 'transfer_in',
                'amount' => $amount,
                'related_user_id' => $fromUserId,
                'comment' => $comment,
            ]);

            return [
                'from_user_balance' => $fromBalance->fresh()->amount,
                'to_user_balance' => $toBalance->fresh()->amount,
                'transferred_amount' => $amount,
            ];
        });
    }

    // Получение текущего баланса пользователя
    public function getBalance(int $userId): array
    {
        $user = User::find($userId);
        if (!$user) {
            throw new ModelNotFoundException("Пользователь не найден");
        }

        $balance = Balance::where('user_id', $userId)->first();

        return [
            'user_id' => $userId,
            'balance' => $balance ? (float) $balance->amount : 0.0,
        ];
    }

    // Валидация суммы
    private function validateAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("Сумма должна быть больше 0");
        }

        if ($amount > 1000000) {
            throw new InvalidArgumentException("Сумма слишком большая");
        }
    }
}