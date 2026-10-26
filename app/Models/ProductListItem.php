<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductListItem extends Model
{
    protected $guarded = [];

    public function list(): BelongsTo { return $this->belongsTo(ProductList::class, 'product_list_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
