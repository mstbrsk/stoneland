<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property int $city_id
 * @property string|null $county
 * @property int|null $code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TaxOffice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaxOffice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaxOffice query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaxOffice whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxOffice whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxOffice whereCounty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxOffice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxOffice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxOffice whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxOffice whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TaxOffice extends Model
{
    protected $table = 'tax_offices';
    protected $guarded = [];
}
