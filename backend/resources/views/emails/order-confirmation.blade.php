<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f3f4f6;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 30px 20px;">
        <tr>
            <td align="center">
                <table width="560" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%); padding: 32px 24px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700; letter-spacing: 3px;">
                                SHOP.CO
                            </h1>
                        </td>
                    </tr>

                    <!-- Success Badge -->
                    <tr>
                        <td style="padding: 32px 24px 24px; text-align: center;">
                            <div style="display: inline-block; background-color: #10b981; padding: 8px 24px; border-radius: 24px; margin-bottom: 16px;">
                                <span style="color: #ffffff; font-size: 13px; font-weight: 600; letter-spacing: 0.5px;">✓ ORDER CONFIRMED</span>
                            </div>
                            <h2 style="margin: 0 0 8px; color: #111827; font-size: 24px; font-weight: 700;">
                                Thank You for Your Order!
                            </h2>
                            <p style="margin: 0; color: #6b7280; font-size: 15px;">
                                Hi <strong style="color: #111827;">{{ $customerName }}</strong>, your order has been successfully placed.
                            </p>
                        </td>
                    </tr>

                    <!-- Order Details Card -->
                    <tr>
                        <td style="padding: 0 24px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); border-radius: 12px; padding: 20px; border: 1px solid #e5e7eb;">
                                <tr>
                                    <td style="padding-bottom: 4px;">
                                        <span style="color: #9ca3af; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Order Number</span>
                                    </td>
                                    <td align="right" style="padding-bottom: 4px;">
                                        <span style="color: #9ca3af; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Order Date</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong style="color: #111827; font-size: 17px; font-weight: 700;">#{{ $order->order_code }}</strong>
                                    </td>
                                    <td align="right">
                                        <strong style="color: #111827; font-size: 17px; font-weight: 700;">{{ date('M d, Y', strtotime($order->created_at)) }}</strong>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Order Items Section -->
                    <tr>
                        <td style="padding: 0 24px 16px;">
                            <div style="border-bottom: 2px solid #111827; padding-bottom: 8px; margin-bottom: 16px;">
                                <h3 style="margin: 0; color: #111827; font-size: 16px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                    Order Items
                                </h3>
                            </div>
                            
                            @foreach($orderItems as $item)
                            @php
                                $isLastItem = $loop->last;
                            @endphp
                            <table width="100%" cellpadding="0" cellspacing="0" @if(!$isLastItem) style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb;" @endif>
                                <tr>
                                    <td width="70" style="padding-right: 12px; vertical-align: top;">
                                        {{-- 🔥 FIX: Dùng $item->image thay vì $item['image'] --}}
                                        @if(!empty($item->image))
                                        <img src="{{ $item->image }}" alt="{{ $item->product_name }}" style="width: 70px; height: 70px; object-fit: cover; border-radius: 10px; border: 1px solid #e5e7eb;">
                                        @else
                                        <div style="width: 70px; height: 70px; background-color: #f3f4f6; border-radius: 10px; border: 1px solid #e5e7eb;"></div>
                                        @endif
                                    </td>
                                    <td style="vertical-align: top;">
                                        <p style="margin: 0 0 6px; color: #111827; font-size: 15px; font-weight: 600; line-height: 1.3;">
                                            {{-- 🔥 FIX: Dùng $item->product_name --}}
                                            {{ $item->product_name ?? 'Product' }}
                                        </p>
                                        <p style="margin: 0; color: #6b7280; font-size: 13px; line-height: 1.4;">
                                            {{-- 🔥 FIX: Dùng $item->size và $item->color --}}
                                            @if(!empty($item->size))
                                            <span style="display: inline-block; background-color: #f3f4f6; padding: 2px 8px; border-radius: 4px; margin-right: 4px;">{{ $item->size }}</span>
                                            @endif
                                            @if(!empty($item->color))
                                            <span style="display: inline-block; background-color: #f3f4f6; padding: 2px 8px; border-radius: 4px;">{{ $item->color }}</span>
                                            @endif
                                        </p>
                                        <p style="margin: 6px 0 0; color: #6b7280; font-size: 13px;">
                                            {{-- 🔥 FIX: Dùng $item->quantity --}}
                                            Qty: <strong style="color: #111827;">{{ $item->quantity ?? 1 }}</strong>
                                        </p>
                                    </td>
                                    <td align="right" style="vertical-align: top; padding-left: 12px;">
                                        <p style="margin: 0; color: #111827; font-size: 16px; font-weight: 700; white-space: nowrap;">
                                            {{-- 🔥 FIX: Dùng $item->total hoặc tính từ price * quantity --}}
                                            {{ number_format($item->total ?? ($item->price * $item->quantity), 0, ',', '.') }}₫
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            @endforeach
                        </td>
                    </tr>

                    <!-- Order Summary -->
                    <tr>
                        <td style="padding: 16px 24px; background-color: #fafafa;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">
                                        Subtotal
                                    </td>
                                    <td align="right" style="padding: 6px 0; color: #111827; font-size: 14px; font-weight: 600;">
                                        {{ number_format($order->subtotal ?? 0, 0, ',', '.') }}₫
                                    </td>
                                </tr>
                                @if(isset($order->discount) && $order->discount > 0)
                                <tr>
                                    <td style="padding: 6px 0; color: #10b981; font-size: 14px;">
                                        Discount
                                    </td>
                                    <td align="right" style="padding: 6px 0; color: #10b981; font-size: 14px; font-weight: 600;">
                                        -{{ number_format($order->discount, 0, ',', '.') }}₫
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding: 6px 0 12px; color: #6b7280; font-size: 14px; border-bottom: 1px solid #e5e7eb;">
                                        Shipping Fee
                                    </td>
                                    <td align="right" style="padding: 6px 0 12px; color: #111827; font-size: 14px; font-weight: 600; border-bottom: 1px solid #e5e7eb;">
                                        {{ number_format($order->delivery_fee ?? 15000, 0, ',', '.') }}₫
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 0 0; color: #111827; font-size: 17px; font-weight: 700;">
                                        Total
                                    </td>
                                    <td align="right" style="padding: 12px 0 0; color: #111827; font-size: 20px; font-weight: 700;">
                                        {{ number_format($order->total, 0, ',', '.') }}₫
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Shipping Address -->
                    <tr>
                        <td style="padding: 24px;">
                            <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); border-radius: 12px; padding: 20px; border: 1px solid #e5e7eb;">
                                <p style="margin: 0 0 4px; color: #9ca3af; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                                    Shipping Address
                                </p>
                                <p style="margin: 8px 0 0; color: #111827; font-size: 15px; line-height: 1.6;">
                                    {{-- 🔥 FIX: Dùng $order->full_name và $order->phone (MySQL) --}}
                                    <strong style="font-weight: 700;">{{ $order->full_name }}</strong><br>
                                    <span style="color: #6b7280;">{{ $order->phone }}</span><br>
                                    <span style="color: #374151;">
                                        {{-- 🔥 FIX: Dùng các column riêng của MySQL --}}
                                        {{ $order->house_number }}
                                        @if(!empty($order->ward))
                                        , {{ $order->ward }}
                                        @endif
                                        @if(!empty($order->province))
                                        <br>{{ $order->province }}
                                        @endif
                                    </span>
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer Note -->
                    <tr>
                        <td style="padding: 24px; background-color: #fafafa; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0 0 8px; color: #111827; font-size: 14px; font-weight: 600;">
                                📦 What's Next?
                            </p>
                            <p style="margin: 0; color: #6b7280; font-size: 13px; line-height: 1.6;">
                                We will contact you shortly to confirm your order and provide delivery updates. Thank you for shopping with us!
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%); padding: 24px; text-align: center;">
                            <p style="margin: 0 0 4px; color: #ffffff; font-size: 18px; font-weight: 700; letter-spacing: 2px;">
                                SHOP.CO
                            </p>
                            <p style="margin: 0; color: #9ca3af; font-size: 12px;">
                                Thank you for choosing SHOP.CO
                            </p>
                        </td>
                    </tr>

                    <!-- Copyright -->
                    <tr>
                        <td style="padding: 16px; text-align: center; background-color: #fafafa;">
                            <p style="margin: 0; color: #9ca3af; font-size: 11px;">
                                © 2024 SHOP.CO. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>