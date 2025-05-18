<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AddNewMedicine;
use App\Models\Order;         // <-- ADD THIS
use App\Models\OrderItem;     // <-- ADD THIS
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
            $createdOrderItems = []; // To store details for the response or further processing

            // First, create the main order record
            $order = Order::create([
                'status' => 'completed', // Or 'pending' if there are more steps
                'total_amount' => 0, // We'll update this after calculating
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

                // Calculate sub_total for this item
                $priceAtPurchase = $medicine->price; // Assuming your AddNewMedicine model has a 'price' attribute
                $subTotal = $priceAtPurchase * $quantityOrdered;
                $orderTotalAmount += $subTotal;

                // Create the order item
                $createdOrderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'medicine_id' => $medicine->id,
                    'quantity' => $quantityOrdered,
                    'price_per_unit' => $priceAtPurchase,
                    'sub_total' => $subTotal,
                ]);
                $createdOrderItems[] = $createdOrderItem->load('medicine'); // Load medicine details for response
            }

            // Update the order with the final total amount
            $order->total_amount = $orderTotalAmount;
            $order->save();

            DB::commit();

            return response()->json([
                'message' => 'Order processed successfully, stock updated, and order saved.',
                'order_id' => $order->id,
                'order_status' => $order->status,
                'total_amount' => $order->total_amount,
                'items_processed' => $createdOrderItems
            ], 201); // 201 Created

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to process order.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
