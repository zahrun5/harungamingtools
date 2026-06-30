<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CalculatorUsage extends Model
{
    protected $fillable = ['user_id', 'calculator_type', 'used_at'];
public $timestamps = false;


public function user()
{
    return $this->belongsTo(User::class);
}
}
