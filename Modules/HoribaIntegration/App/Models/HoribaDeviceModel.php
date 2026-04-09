<?php

namespace Modules\HoribaIntegration\App\Models;

use Illuminate\Database\Eloquent\Model;

class HoribaDeviceModel extends Model
{
    protected $table = 'horiba_devices';

    public $timestamps = true;

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'manufacturer',
        'model',
        'serial_number',
        'connection_type',
        'bridge_ip',
        'protocol',
        'api_token',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function results()
    {
        return $this->hasMany(HoribaResultModel::class, 'device_id');
    }
}
