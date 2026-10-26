<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HomeBanner extends Model {
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];
    public function scopeActive($q){ return $q->where('is_active', true); }
}
