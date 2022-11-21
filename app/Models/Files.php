<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Files extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'file_name',
        'thumbnail',
        'type',
    ];

    public static function getFiels($user_id, $file_type){
        $result = Files::where('user_id', $user_id)->where('file_type', $file_type)->get()->toArray();
        return $result;       
    }

    public static function saveFile($user_id, $file_name, $file_type, $thumbnail=''){
        $arg = [
            'user_id' => $user_id,
            'file_name' => $file_name,
            'thumbnail' => $thumbnail,
            'type' => $file_type
        ];
        $res = Files::create($arg);    
        return $res->id;   
    }
}
