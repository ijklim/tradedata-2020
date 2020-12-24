<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOption extends Model
{
    protected $fillable = [
        'symbol', 'date', 'option_data'
    ];

    use \App\Traits\Model;

    /**
     * Format field value if necessary based on field name.
     *
     * @param  string  $fieldName
     * @param  mixed  $fieldValue
     * @return mixed
     */
    public static function formatField($fieldName, $fieldValue) {
        switch ($fieldName) {
            case 'symbol':
                return strtoupper($fieldValue);
                break;
            default:
                return $fieldValue;
                break;
        }
    }

    public function stock()
    {
        return $this->belongsTo(\App\Stock::class, 'symbol');
    }
}
