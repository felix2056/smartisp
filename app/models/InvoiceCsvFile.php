<?php

namespace App\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceCsvFile extends Model
{
    use HasFactory;

    protected $table = 'invoice_csv_files';

    protected $guarded = ['id'];
}
