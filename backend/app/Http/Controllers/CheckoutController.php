<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\CreateOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Notifications\SendMailOrderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function createOrderCOD(CreateOrderRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $order_code = 'ORD' . time() . rand(1000, 9999);

            $userId = $data['user_id'] ?? Auth::id();
            // Tạo đơn hàng
            $order = Order::create([
                'order_code' => $order_code,
                'user_id' => $userId,
                'full_name' => $data['customer_info']['fullName'],
                'email' => $data['customer_info']['email'],
                'phone' => $data['customer_info']['phone'],
                'house_number' => $data['shipping_address']['houseNumber'],
                'province' => $data['shipping_address']['province'],
                'ward' => $data['shipping_address']['ward'],
                'note' => $data['note'] ?? '',
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'subtotal' => $data['subtotal'],
                'discount' => $data['discount'] ?? 0,
                'delivery_fee' => $data['delivery_fee'],
                'total' => $data['total_vnpay'],
            ]);

            // Tạo các order items
            foreach ($data['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'size' => $item['size'] ?? null,
                    'color' => $item['color'] ?? null,
                    'image' => $item['image'] ?? null,
                    'total' => $item['quantity'] * $item['price'],
                ]);
            }

            DB::commit();

            // Gửi email
            $order->notify(new SendMailOrderNotification($order));

            return response()->json([
                'code' => '00',
                'message' => 'Order created successfully',
                'order_code' => $order_code
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create COD order failed: ' . $e->getMessage());
            
            return response()->json([
                'code' => '01',
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo đơn hàng và khởi tạo thanh toán VNPAY
     */
    public function vnpay_payment(CreateOrderRequest $request)
    {
        $data = $request->validated();
        
        $order_code = 'ORD' . time() . rand(1000, 9999);
        
        DB::beginTransaction();
        try {

            $userId = $data['user_id'] ?? Auth::id();
            // Tạo đơn hàng
            $order = Order::create([
                'order_code' => $order_code,
                'user_id' => $userId,
                'full_name' => $data['customer_info']['fullName'],
                'email' => $data['customer_info']['email'],
                'phone' => $data['customer_info']['phone'],
                'house_number' => $data['shipping_address']['houseNumber'],
                'province' => $data['shipping_address']['province'],
                'ward' => $data['shipping_address']['ward'],
                'note' => $data['note'] ?? '',
                'payment_method' => 'vnpay',
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'subtotal' => $data['subtotal'],
                'discount' => $data['discount'] ?? 0,
                'delivery_fee' => $data['delivery_fee'],
                'total' => $data['total_vnpay'],
            ]);

            // Tạo các order items
            foreach ($data['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'size' => $item['size'] ?? null,
                    'color' => $item['color'] ?? null,
                    'image' => $item['image'] ?? null,
                    'total' => $item['quantity'] * $item['price'],
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create VNPAY order failed: ' . $e->getMessage());
            
            return response()->json([
                'code' => '02',
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }

        // Khởi tạo thanh toán VNPAY
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = "http://localhost:5173/vnpay-return";
        $vnp_TmnCode = "G011CVNP";
        $vnp_HashSecret = "E4ZGGZTK5K153ANLDSBVXLNG0NEU6ZJJ";

        $vnp_TxnRef = $order_code;
        $vnp_OrderInfo = 'Thanh toán đơn hàng ' . $order_code;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $data['total_vnpay'] * 100;
        $vnp_Locale = 'vn';
        $vnp_IpAddr = $request->ip();

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        
        return response()->json([
            'code' => '00',
            'message' => 'success',
            'order_id' => $order->id,
            'order_code' => $order_code,
            'payment_url' => $vnp_Url
        ]);
    }

    /**
     * Xử lý callback từ VNPAY
     */
    public function vnpay_return(Request $request)
    {
        $vnp_HashSecret = "E4ZGGZTK5K153ANLDSBVXLNG0NEU6ZJJ";
        $inputData = $request->all();
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        
        $order_code = $request->vnp_TxnRef;
        $response_code = $request->vnp_ResponseCode;
        
        // Tìm đơn hàng
        $order = Order::where('order_code', $order_code)->first();
        
        if (!$order) {
            return response()->json([
                'code' => '01',
                'message' => 'Order not found'
            ], 404);
        }

        if ($secureHash == $vnp_SecureHash) {
            if ($response_code == '00') {
                // Chỉ xử lý nếu đơn chưa paid
                if ($order->payment_status !== 'paid') {
                    DB::beginTransaction();
                    try {
                        // Cập nhật đơn hàng
                        $order->update([
                            'payment_status' => 'paid',
                            'order_status' => 'confirmed',
                        ]);

                        // Lưu thông tin thanh toán
                        Payment::create([
                            'order_id' => $order->id,
                            'transaction_no' => $request->vnp_TransactionNo,
                            'bank_code' => $request->vnp_BankCode,
                            'card_type' => $request->vnp_CardType,
                            'pay_date' => \Carbon\Carbon::createFromFormat('YmdHis', $request->vnp_PayDate),
                            'response_code' => $response_code,
                        ]);

                        DB::commit();

                        // Gửi email
                        try {
                            $order->notify(new SendMailOrderNotification($order));
                        } catch (\Exception $e) {
                            Log::error('Failed to send order confirmation email: ' . $e->getMessage());
                        }
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Failed to update order payment: ' . $e->getMessage());
                    }
                }

                return response()->json([
                    'code' => '00',
                    'message' => 'Payment success',
                    'order' => $order->load(['items', 'payment'])
                ]);
            } else {
                // Thanh toán thất bại
                $order->update([
                    'payment_status' => 'failed',
                    'order_status' => 'cancelled',
                ]);

                // Lưu thông tin lỗi thanh toán
                Payment::create([
                    'order_id' => $order->id,
                    'response_code' => $response_code,
                ]);

                Log::warning('Payment failed for order: ' . $order_code . ' - Response code: ' . $response_code);

                return response()->json([
                    'code' => '01',
                    'message' => 'Payment failed',
                    'response_code' => $response_code
                ]);
            }
        } else {
            Log::error('Invalid VNPAY signature for order: ' . $order_code);
            
            return response()->json([
                'code' => '97',
                'message' => 'Invalid signature'
            ], 400);
        }
    }

    /**
     * Lấy thông tin đơn hàng
     */
    public function getOrder($order_code)
    {
        $order = Order::with(['items', 'payment'])
            ->where('order_code', $order_code)
            ->first();
        
        if (!$order) {
            return response()->json([
                'code' => '01',
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'code' => '00',
            'message' => 'success',
            'order' => $order
        ]);
    }
}