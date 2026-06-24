<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\Contracts\PaymentGatewayAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentGatewayAdapter $gateway
    ) {
    }

    public function process(Request $request, Order $order): JsonResponse
    {
        try {
            $result = $this->gateway->charge(
                $order->id,
                $order->total,
                'USD'
            );

            $payment = $order->payment;

            if ($payment) {
                $payment->status = $result->success ? 'completed' : 'failed';
                $payment->external_transaction_id = $result->transactionId;
                $payment->raw_response = $result->rawResponse;
                $payment->processed_at = $result->success ? now() : null;
                $payment->save();
            }

            if ($result->success) {
                $order->transitionTo('paid');
                $order->save();
                //$order->notify('paid');
            }

            \App\Support\Logger::getInstance()->log(
                'Payment ' . ($result->success ? 'succeeded' : 'failed') . " for order {$order->id}"
            );

            return response()->json([
                'success' => $result->success,
                'transaction_id' => $result->transactionId,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function refund(Request $request, Payment $payment): JsonResponse
    {
        try {
            $result = $payment->refund();

            return response()->json([
                'success' => $result,
            ]);

        } catch (\RuntimeException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}