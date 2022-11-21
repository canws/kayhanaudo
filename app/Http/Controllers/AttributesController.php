<?php

namespace App\Http\Controllers;

use App\Models\Attributes;
use Illuminate\Http\Request;

class AttributesController extends Controller
{

    public function deleteAttributes(Request $request){
       
        $id = $request->id;
        $attribute = Attributes::where('id', $id)->first();
        if($attribute == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Attribute not found. Reload page'
            ]);
        }

        if($attribute->delete()){
            return response()->json([
                'sts' => true,
                'msg' => ''
            ]);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'Some error occurred.'
            ]);
        }

       

    }

    public function saveAttributes(Request $request){
       
        $attributeIds = $request->attributeIds;
        $attributeName = $request->attributeName;
        $attributeValue = $request->attributeValue;

        foreach ( $attributeIds as $key => $attribute_id ) {
            Attributes::updateOrCreate([
                'id' => $attribute_id
            ],[
                'name' => $attributeName[$key],
                'value' => $attributeValue[$key],
            ]);

        }

        return response()->json([
            'sts' => true,
            'msg' => ''
        ], 200);

    }

    public function fetchAttributes(){
       
        $result = Attributes::select('id as attributeIds','name as attributeName', 'value as attributeValue')->get()->toArray();

        return response()->json([
            'sts' => true,
            'result' => $result,
        ]);

    }
}
