<?php

namespace Logaretm\Depo\Tests\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Class Task
 * @package Logaretm\Depo\tests\Models
 */
class Task extends Model
{
    protected $fillable = [
        'body',
        'completed'
    ];

    /**
     * @param $query
     * @return mixed
     */
    public function scopeCompleted ($query)
    {
        return $query->where('completed', true);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeInProgress ($query)
    {
        return $query->where('completed', false);
    }
}