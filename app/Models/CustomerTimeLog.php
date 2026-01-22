<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerTimeLog extends Model
{
    use HasFactory;

    /**
     * Note: Using 'customer_time_logs_new' due to MySQL tablespace corruption issue.
     * The original 'customer_time_logs' table had a corrupted .ibd file that prevents
     * table creation. To fix permanently, delete the .ibd file from MySQL data directory
     * and change this back to 'customer_time_logs'.
     */
    protected $table = 'customer_time_logs_new';

    protected $fillable = [
        'customer_id',
        'action',
        'ip_address',
        'user_agent',
        'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    /**
     * Get the customer that owns the time log
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
