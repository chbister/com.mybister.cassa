<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = ['name', 'amount_info', 'price', 'category_id', 'deposit_product_id'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function depositProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'deposit_product_id');
    }
}
