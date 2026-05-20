<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;




/**
 * 
 *
 * @property string $id
 * @property string $sample_id
 * @property string $product_id
 * @property string|null $notes
 * @property int $qty
 * @property array|null $selected_variants
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Sample|null $sample
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\samples_variants> $variants
 * @property-read int|null $variants_count
 * @method static \Illuminate\Database\Eloquent\Builder|samples_items newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|samples_items newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|samples_items query()
 * @method static \Illuminate\Database\Eloquent\Builder|samples_items whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|samples_items whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|samples_items whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|samples_items whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|samples_items whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|samples_items whereSampleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|samples_items whereSelectedVariants($value)
 * @method static \Illuminate\Database\Eloquent\Builder|samples_items whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class samples_items extends Model
{
    use HasFactory;

    protected $table = 'samples_items';
    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'selected_variants' => 'json',
    ];

    public function sample(): HasOne
    {
        return $this->hasOne(Sample::class, 'id', 'sample_id');
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(samples_variants::class, 'sample_id', 'id');
    }



}
