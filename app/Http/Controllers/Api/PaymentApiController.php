<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentApiController extends ApiController
{
    public function downPayment(Request $request): JsonResponse
    {
        return $this->recordPayment($request, 'down_payment');
    }

    public function remainingBalance(Request $request): JsonResponse
    {
        return $this->recordPayment($request, 'remaining_balance');
    }

    public function forUser(Request $request, string $userId): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) return $user;

        if ((string) $user->id !== (string) $userId) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        return response()->json(Payment::where('user_id', $userId)->orderByDesc('created_at')->get()->map(fn (Payment $payment) => $this->payload($payment)));
    }

    public function checkoutSession(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) return $user;

        $data = $request->validate(['orderId' => ['required', 'string']]);
        $order = Order::find($data['orderId']);
        if (! $order) {
            return response()->json(['error' => 'Order not found'], 404);
        }
        if ((string) $order->user_id !== (string) $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        return response()->json([
            'error' => 'PayMongo checkout is not configured in this Laravel migration yet. Use the manual payment endpoints or add PAYMONGO_SECRET_KEY integration.',
        ], 501);
    }

    public function syncCheckoutSession(): JsonResponse
    {
        return response()->json(['paid' => false, 'status' => 'pending']);
    }

    public function webhook(): JsonResponse
    {
        return response()->json(['received' => true]);
    }

    private function recordPayment(Request $request, string $type): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) return $user;

        $data = $request->validate([
            'orderId' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'paymentMethod' => ['nullable', 'string'],
        ]);

        $order = Order::find($data['orderId']);
        if (! $order) {
            return response()->json(['error' => 'Order not found'], 404);
        }
        if ((string) $order->user_id !== (string) $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $payment = Payment::create([
            'order_id' => (string) $order->id,
            'user_id' => (string) $user->id,
            'amount' => $data['amount'],
            'payment_method' => $data['paymentMethod'] ?? 'cash',
            'payment_type' => $type,
            'status' => 'completed',
        ]);

        if ($type === 'remaining_balance') {
            $order->update([
                'payment_status' => 'fully_paid',
                'remaining_balance' => 0,
                'status' => 'paid',
            ]);
        } else {
            $order->update([
                'payment_status' => 'down_payment_paid',
                'status' => 'confirmed',
            ]);
        }

        return response()->json($this->payload($payment), 201);
    }

    private function payload(Payment $payment): array
    {
        return [
            'id' => (string) $payment->id,
            'orderId' => (string) $payment->order_id,
            'userId' => (string) $payment->user_id,
            'amount' => (float) $payment->amount,
            'paymentMethod' => $payment->payment_method,
            'paymentType' => $payment->payment_type,
            'status' => $payment->status,
            'createdAt' => $this->iso($payment->created_at),
            'updatedAt' => $this->iso($payment->updated_at),
        ];
    }
}
