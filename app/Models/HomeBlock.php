<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HomeBlock extends Model {
    protected $guarded = [];
    protected $casts = ['config' => 'array', 'is_active' => 'boolean'];
    public function scopeActive($q){ return $q->where('is_active', true); }
}
