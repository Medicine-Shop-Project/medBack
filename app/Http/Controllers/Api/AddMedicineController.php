<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AddNewMedicine;

class AddMedicineController extends Controller
{
    public function index()
    {
        try {
         $medicine = AddNewMedicine::all();
          return response()->json($medicine, 201);
        } catch (\Exception $e) {
         return response()->json([
            'message' => 'Somting Error ',
            'error' => $e->getMessage()
         ], 500);

        }
    }


    public function store(Request $request)
    {
        $medicineData=$request->validate([
            'name'=>'required|string|max:255',
            'category'=>'required|string|max:255',
            'manufacturer'=>'required|string|max:255',
            'stock'=>'required|integer|min:0',
            'price'=>'required|numeric|min:0',
            'expiry_date'=>'required|date',

        ]);

        try {
         $medicine = AddNewMedicine::create($medicineData);
          return response()->json($medicine, 201);
        } catch (\Exception $e) {
         return response()->json([
            'message' => 'Error saving medicine',
            'error' => $e->getMessage()
         ], 500);

        }

    }


    public function show(string $id)
    {
        try {
         $medicine = AddNewMedicine::findOrFail($id);
          return response()->json($medicine, 201);
        }catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e){
            return response()->json(['message'=>'Medicine not found'],400);
        }catch (\Exception $e) {
         return response()->json([
            'message' => 'Somting Error ',
            'error' => $e->getMessage()
         ], 500);

        }
    }

    public function update(Request $request, string $id)
    {
        $medicineData=$request->validate([

            'manufacturer'=>'sometimes|required|string|max:255',
            'stock'=>'sometimes|required|integer|min:0',
            'price'=>'sometimes|required|numeric|min:0',

        ]);
        try {
         $medicine = AddNewMedicine::findOrFail($id);
         $medicine->update($medicineData);
          return response()->json(['message'=>'update successfully',$medicine],200);
        }catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e){
            return response()->json(['message'=>'Medicine not found'],400);
        }catch (\Exception $e) {
         return response()->json([
            'message' => 'Somting Error ',
            'error' => $e->getMessage()
         ], 500);

        }
    }


    public function destroy(string $id)
    {
         try {
         $medicine = AddNewMedicine::findOrFail($id);
         $medicine->delete();
          return response()->json(['message'=>'Medicine Deleted successfully'],200);
        }catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e){
            return response()->json(['message'=>'Medicine not found'],400);
        }catch (\Exception $e) {
         return response()->json([
            'message' => 'Somting Error ',
            'error' => $e->getMessage()
         ], 500);

        }
    }
     /* // Format expiry_date if it's present
    if (isset($medicineData['expiry_date'])) {
        $medicineData['expiry_date'] = date('Y-m-d', strtotime($medicineData['expiry_date']));
    }

    try {
        $medicine = AddNewMedicine::findOrFail($id);
        $medicine->update($medicineData);

        return response()->json([
            'message' => 'Updated successfully',
            'data' => $medicine
        ], 200);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['message' => 'Medicine not found'], 404);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
  }*/
}
