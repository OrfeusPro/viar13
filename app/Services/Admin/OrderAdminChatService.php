<?php

namespace App\Services\Admin;

use App\Models\AdminChats;
use App\Models\Orders;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderAdminChatService
{
    public function add(Orders $order, User $author, string $comment): AdminChats
    {
        $data = validator(['comment' => $comment], [
            'comment' => ['required', 'string', 'max:10000'],
        ])->validate();

        return DB::transaction(function () use ($order, $author, $data): AdminChats {
            Orders::query()->lockForUpdate()->findOrFail($order->id);

            $message = (new AdminChats)->forceFill([
                'orders_id' => $order->id,
                'user_id' => $author->id,
                'comment' => trim($data['comment']),
            ]);
            $message->save();

            return $message;
        });
    }
}
