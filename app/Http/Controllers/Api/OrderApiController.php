<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderApiController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) return $admin;

        return response()->json(Order::orderByDesc('created_at')->get()->map(fn (Order $order) => $this->payload($order)));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) return $user;

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'totalAmount' => ['required', 'numeric', 'min:0'],
            'downPayment' => ['required', 'numeric', 'min:0'],
            'shippingAddress' => ['required', 'string'],
        ]);

        $order = Order::create([
            'user_id' => (string) $user->id,
            'items' => $data['items'],
            'total_amount' => $data['totalAmount'],
            'down_payment' => $data['downPayment'],
            'remaining_balance' => max(0, (float) $data['totalAmount'] - (float) $data['downPayment']),
            'shipping_address' => $data['shippingAddress'],
            'status' => 'pending',
            'payment_status' => 'pending_down_payment',
            'delivery_status' => 'processing',
        ]);

        return response()->json($this->payload($order), 201);
    }

    public function forUser(Request $request, string $userId): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) return $user;

        if ((string) $user->id !== (string) $userId) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        return response()->json(Order::where('user_id', (string) $userId)->orderByDesc('created_at')->get()->map(fn (Order $order) => $this->payload($order)));
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $access = $this->authorizeOrder($request, $order);
        if ($access instanceof JsonResponse) return $access;

        return response()->json($this->payload($order));
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) return $admin;

        $order->update($this->mapUpdate($request->all()));

        return response()->json($this->payload($order->refresh()));
    }

    public function updateLocation(Request $request, Order $order): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) return $admin;

        $data = $request->validate([
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'estimatedDelivery' => ['nullable', 'date'],
        ]);

        $order->update([
            'current_location' => ['lat' => (float) $data['lat'], 'lng' => (float) $data['lng']],
            'estimated_delivery' => $data['estimatedDelivery'] ?? null,
            'delivery_status' => 'in_transit',
        ]);

        return response()->json($this->payload($order->refresh()));
    }

    public function chat(Request $request, Order $order): JsonResponse
    {
        $access = $this->authorizeOrder($request, $order);
        if ($access instanceof JsonResponse) return $access;

        $messages = OrderMessage::where('order_id', $order->id)->orderBy('created_at')->get()->map(fn (OrderMessage $message) => [
            'id' => (string) $message->id,
            'orderId' => (string) $message->order_id,
            'senderId' => $message->sender_id,
            'senderEmail' => $message->sender_email,
            'senderRole' => $message->sender_role,
            'message' => $message->message,
            'createdAt' => $this->iso($message->created_at),
        ]);

        return response()->json($messages);
    }

    public function sendChat(Request $request, Order $order): JsonResponse
    {
        $user = $this->authorizeOrder($request, $order);
        if ($user instanceof JsonResponse) return $user;

        $data = $request->validate(['message' => ['required', 'string', 'max:1000']]);
        $role = $user->role === 'admin' ? 'admin' : 'customer';

        $message = OrderMessage::create([
            'order_id' => $order->id,
            'sender_id' => (string) $user->id,
            'sender_email' => $user->email,
            'sender_role' => $role,
            'message' => trim($data['message']),
        ]);

        $order->update([
            'chat_updated_at' => now(),
            'last_chat_message' => (string) str($message->message)->limit(160),
            'last_chat_sender_role' => $role,
        ]);

        return response()->json([
            'id' => (string) $message->id,
            'orderId' => (string) $order->id,
            'senderId' => (string) $user->id,
            'senderEmail' => $user->email,
            'senderRole' => $role,
            'message' => $message->message,
            'createdAt' => $this->iso($message->created_at),
        ], 201);
    }

    private function authorizeOrder(Request $request, Order $order)
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) return $user;

        if ($user->role !== 'admin' && (string) $order->user_id !== (string) $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        return $user;
    }

    private function mapUpdate(array $data): array
    {
        $map = [
            'items' => 'items',
            'totalAmount' => 'total_amount',
            'downPayment' => 'down_payment',
            'remainingBalance' => 'remaining_balance',
            'shippingAddress' => 'shipping_address',
            'status' => 'status',
            'paymentStatus' => 'payment_status',
            'deliveryStatus' => 'delivery_status',
            'currentLocation' => 'current_location',
            'estimatedDelivery' => 'estimated_delivery',
        ];

        $update = [];
        foreach ($map as $input => $column) {
            if (array_key_exists($input, $data)) {
                $update[$column] = $data[$input];
            }
        }

        return $update;
    }

    public function payload(Order $order): array
    {
        return [
            'id' => (string) $order->id,
            'userId' => (string) $order->user_id,
            'items' => $order->items ?? [],
            'totalAmount' => (float) $order->total_amount,
            'downPayment' => (float) $order->down_payment,
            'remainingBalance' => (float) $order->remaining_balance,
            'shippingAddress' => $order->shipping_address,
            'status' => $order->status,
            'paymentStatus' => $order->payment_status,
            'deliveryStatus' => $order->delivery_status,
            'currentLocation' => $order->current_location,
            'estimatedDelivery' => $this->iso($order->estimated_delivery),
            'paymongoCheckoutSessionId' => $order->paymongo_checkout_session_id,
            'paymongoCheckoutStatus' => $order->paymongo_checkout_status,
            'paymongoCheckoutUrl' => $order->paymongo_checkout_url,
            'chatUpdatedAt' => $this->iso($order->chat_updated_at),
            'lastChatMessage' => $order->last_chat_message,
            'lastChatSenderRole' => $order->last_chat_sender_role,
            'createdAt' => $this->iso($order->created_at),
            'updatedAt' => $this->iso($order->updated_at),
        ];
    }
}
