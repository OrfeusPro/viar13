<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\GiftCardNom
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|GiftCardNom newModelQuery()
 * @method static Builder|GiftCardNom newQuery()
 * @method static Builder|GiftCardNom query()
 * @method static Builder|GiftCardNom whereCreatedAt($value)
 * @method static Builder|GiftCardNom whereId($value)
 * @method static Builder|GiftCardNom whereText($value)
 * @method static Builder|GiftCardNom whereUpdatedAt($value)
 */
class GiftCardNom extends Model
{
    protected $table = 'gift_card_noms';
}
