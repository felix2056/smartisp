<?php

namespace App\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemHistory extends Model
{
    use HasFactory;

    protected $table = 'inv_item_history';

    protected $guarded = ['id'];


    public function item()
    {
        return $this->belongsTo(ProductItem::class, 'item_id');
    }
}
