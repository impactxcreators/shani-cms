<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Advert extends Model {
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean', 'starts_at' => 'datetime', 'ends_at' => 'datetime'];
    public function scopeLive($q){
        return $q->where('is_active', true)
            ->where(fn($x)=>$x->whereNull('starts_at')->orWhere('starts_at','<=',now()))
            ->where(fn($x)=>$x->whereNull('ends_at')->orWhere('ends_at','>=',now()));
    }
}
