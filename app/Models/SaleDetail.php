<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id', 
        'product_id', 
        'warehouse_id',
        'regular_price', 
        'quantity', 
        'sale_price', 
        'discount',
        'freight_amount',
        'exchange_rate',
        'metadata',
        'created_at',
        'updated_at'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function getProductNameAttribute()
    {
        $meta = is_string($this->metadata) ? json_decode($this->metadata, true) : $this->metadata;
        if (is_array($meta) && !empty($meta['custom_name'])) {
            return $meta['custom_name'];
        }
        return $this->product->name ?? 'Producto Eliminado';
    }
}
