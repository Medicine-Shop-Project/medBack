<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AddNewMedicine;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {

        try {
            $order = Order::all();
            return response()->json($order, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something Error ',
                'error' => $e->getMessage()
            ], 500);

        }
    }
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => [
                'required',
                'integer',
                Rule::exists('add_new_medicines', 'id')
            ],
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $orderItemsData = $validatedData['items'];

        DB::beginTransaction();

        try {
            $orderTotalAmount = 0;
            $createdOrderItems = [];


            $order = Order::create([
                'user_email' => auth()->user()->email,
                'status' => 'completed',
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'total_amount' => 0,
            ]);

            foreach ($orderItemsData as $itemData) {
                $medicineId = $itemData['medicine_id'];
                $quantityOrdered = $itemData['quantity'];

                $medicine = AddNewMedicine::where('id', $medicineId)->lockForUpdate()->first();

                if (!$medicine) {
                    DB::rollBack();
                    return response()->json(['message' => "Medicine with ID {$medicineId} not found."], 404);
                }

                if ($medicine->stock < $quantityOrdered) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Insufficient stock for medicine: {$medicine->name} (ID: {$medicineId}). Available: {$medicine->stock}, Ordered: {$quantityOrdered}"
                    ], 400);
                }

                // Deduct stock
                $medicine->stock -= $quantityOrdered;
                $medicine->save();


                $priceAtPurchase = $medicine->price;
                $subTotal = $priceAtPurchase * $quantityOrdered;
                $orderTotalAmount += $subTotal;


                $createdOrderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'medicine_id' => $medicine->id,
                    'quantity' => $quantityOrdered,
                    'price_per_unit' => $priceAtPurchase,
                    'sub_total' => $subTotal,
                ]);
                $createdOrderItems[] = $createdOrderItem->load('medicine');
            }


            $order->total_amount = $orderTotalAmount;
            $order->save();

            DB::commit();

            return response()->json([
                'user_email' => auth()->user()->email,
                'message' => 'Order processed successfully, stock updated, and order saved.',
                'order_id' => $order->id,
                'order_status' => $order->status,
                'coustomer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'total_amount' => $order->total_amount,
                'items_processed' => $createdOrderItems
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to process order.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $query = Order::with('items.medicine');

            if ($request->filled('order_id')) {
                $query->where('id', $request->order_id);
            }

            if ($request->filled('customer_name')) {
                $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
            }

            if ($request->filled('customer_phone')) {
                $query->where('customer_phone', 'like', '%' . $request->customer_phone . '%');
            }

            if ($request->filled('user_email')) {
                $query->where('user_email', 'like', '%' . $request->user_email . '%');
            }

            if ($request->filled('from_date') && $request->filled('to_date')) {
                $query->whereBetween('created_at', [
                    $request->from_date . ' 00:00:00',
                    $request->to_date . ' 23:59:59',
                ]);
            }
            $orders = $query->orderBy('created_at', 'desc')->paginate(100);

            if ($orders->isEmpty()) {
                return response()->json([
                    'message' => 'No orders found.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'message' => 'Orders fetched successfully.',
                'data' => $orders
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



}

