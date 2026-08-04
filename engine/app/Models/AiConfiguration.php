<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AiConfiguration extends Model
{
    protected $table = 'ai_configurations';
    protected $fillable = ['name','provider_type','base_url','api_key','chat_model','embedding_model','embedding_base_url','embedding_api_key','embedding_config_id','max_tokens','temperature','is_active','headers','input_price_per_1m','output_price_per_1m'];
    protected $casts = ['is_active'=>'boolean','headers'=>'array','temperature'=>'float','input_price_per_1m'=>'float','output_price_per_1m'=>'float'];
    protected $hidden = ['api_key','embedding_api_key'];
}
