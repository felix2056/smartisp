<?php

namespace App\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierInvoice extends Model
{
    use HasFactory;
    protected $table = 'inv_supplier_invoice';
    protected $dates = [
        'invoice_date'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function product_items()
    {
        return $this->hasMany(ProductItem::class, 'supplier_invoice_id');
    }
}
