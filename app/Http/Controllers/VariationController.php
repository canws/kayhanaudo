<?php

namespace App\Http\Controllers;

use App\Models\Attributes;
use App\Models\Variation;
use Illuminate\Http\Request;

class VariationController extends Controller
{
    public function addVariation(Request $request){
       
        $variationAction = $request->variationAction;
        $productId = $request->productId;
        $uniqueId = $request->uniqueId;
       
        if($variationAction == 'add_variation'){
            $variationData = [
                'id' => 0,
                'product_id' => $productId,
                'unique_id' => $uniqueId,
                'variation_value' => '',
                'image_id' => 0,
                'sku' => '',
                'regular_price' => '',
                'sale_price' => '',
                'description' => '',
            ];

            Variation::saveVariation($variationData);
            return response()->json([
                'sts' => true,
                'msg' => ''
            ]);
           
        }
        else if($variationAction == 'link_all_variations'){
            $attributes = Attributes::select('id', 'name', 'value')->get()->toArray();

            if(!empty($attributes)){
                $attr = array();
                foreach($attributes as $key => $value){
                    $attr[$value['name']] = explode('|', $value['value']);
                }

                $combinations = $this->generateCombinations($attr);

                if(!empty($combinations)){
                    foreach($combinations as $key => $value){
                        $variationData = [
                            'id' => 0,
                            'product_id' => $productId,
                            'unique_id' => $uniqueId,
                            'variation_value' => implode(',', $value),
                            'image_id' => 0,
                            'sku' => '',
                            'regular_price' => '',
                            'sale_price' => '',
                            'description' => '',
                        ];

                        Variation::saveVariation($variationData);
                       
                    }
                }

                return response()->json([
                    'sts' => true,
                    'msg' => ''
                ], 200);
            }
            else{
                return response()->json([
                    'sts' => false,
                    'msg' => 'Attribute not found.'
                ]); 
            }
            
        }
        else{

        }

    }

    private function generateCombinations(array $data, array &$all = array(), array $group = array(), $value = null, $i = 0)
    {
        $keys = array_keys($data);
        if (isset($value) === true) {
            array_push($group, $value);
        }
    
        if ($i >= count($data)) {
            array_push($all, $group);
        } else {
            $currentKey     = $keys[$i];
            $currentElement = $data[$currentKey];
            foreach ($currentElement as $val) {
                $this->generateCombinations($data, $all, $group, $val, $i + 1);
            }
        }
        return $all;
    }

    public function fetchVariation(Request $request){
       
        $productId = $request->productId;
        $uniqueId = $request->uniqueId;

        if($productId == 0){
            $variations = Variation::where('variations.unique_id', $uniqueId)
                ->join('files', 'files.id', '=', 'variations.image_id', 'LEFT')
                ->select('variations.id','variations.product_id','variations.unique_id', 'variations.variation_value', 'variations.image_id', 'variations.sku', 'variations.regular_price', 'variations.sale_price', 'variations.description', 'files.file_name', 'files.thumbnail')
                ->orderBy('variations.id', 'DESC')->get()->toArray();
        } else{
            $variations = Variation::where('variations.product_id', $productId)
            ->join('files', 'files.id', '=', 'variations.image_id', 'LEFT')
            ->select('variations.id','variations.product_id','variations.unique_id', 'variations.variation_value', 'variations.image_id', 'variations.sku', 'variations.regular_price', 'variations.sale_price', 'variations.description', 'files.file_name', 'files.thumbnail')
                ->orderBy('variations.id', 'DESC')->get()->toArray();
        }

        $variationsArr = array();
        if(!empty($variations)){
            foreach($variations as $key => $value){
                $variationsArr[] = array(
                    'id' => $value['id'],
                    'product_id' => $value['product_id'],
                    'unique_id' => $value['unique_id'],
                    'variation_value' => explode(',',$value['variation_value']),
                    'image_id' => $value['image_id'],
                    'image_url' => ($value['thumbnail'] != null ? url('uploads/thumbnail/'.$value['thumbnail']) : '' ),
                    'sku' => $value['sku'],
                    'regular_price' => $value['regular_price'],
                    'sale_price' => $value['sale_price'],
                    'description' => $value['description'],
                );
            }
        }

        return response()->json([
            'sts' => true,
            'result' => $variationsArr,
        ]);

    }

    public function deleteVariation(Request $request){
       
        $id = $request->id;
        $variation = Variation::where('id', $id)->first();
        if($variation == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Variation not found. Reload page'
            ]);
        }

        if($variation->delete()){
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

    public function saveVariation(Request $request){
    
        $varitionData = $request->varitionData;
        $productId = $request->productId;

        if(!empty($varitionData)){

            foreach($varitionData as $key => $value){
                Variation::where('id', $value['variation_id'])->update([
                    'product_id' => $productId,
                    'variation_value' => (!empty($value['varitionValues']) ? implode(',', $value['varitionValues']) : ''),
                    'image_id' => $value['image_id'],
                    'sku' => $value['variation_sku'],
                    'regular_price' => $value['variation_regular_price'],
                    'sale_price' => $value['variation_sale_price'],
                    'description' => $value['variation_description'],
                ]);
              
            }
        }
    
        return response()->json([
            'sts' => true,
            'msg' => ''
        ], 200);
    }

    public function updateVariationImage(Request $request){
        $file_id = $request->file_id;
        $variation_id = $request->variation_id;

        Variation::where('id', $variation_id)->update(['image_id' => $file_id]);
        return response()->json([
            'sts' => true,
            'msg' => ''
        ], 200);
    }
}
