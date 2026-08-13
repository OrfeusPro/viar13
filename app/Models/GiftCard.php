<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\GiftCard
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $meta_title
 * @property string|null $title
 * @property string|null $desc
 * @property string|null $nom_title
 * @property string|null $when_send_title
 * @property string|null $torj_title
 * @property string|null $or_enter_custom_summ
 * @property string|null $hide_nominal
 * @property string|null $sender
 * @property string|null $receiver
 * @property string|null $from_title
 * @property string|null $for_title
 * @property string|null $card_el_type_title
 * @property string|null $card_conv_title
 * @property string|null $torj_desc_title
 * @property string|null $grats_text
 * @property string|null $of_pol_title
 * @property string|null $of_opl_after_text
 * @property string|null $adv_title
 * @property string|null $adv1_text
 * @property string|null $adv2_text
 * @property string|null $adv3_text
 * @property string|null $adv4_text
 * @property string|null $adv5_text
 * @property string|null $adv6_text
 * @property string|null $rules_title
 * @property string|null $rules_list
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $custom_summ
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|GiftCard newModelQuery()
 * @method static Builder|GiftCard newQuery()
 * @method static Builder|GiftCard query()
 * @method static Builder|GiftCard whereAdv1Text($value)
 * @method static Builder|GiftCard whereAdv2Text($value)
 * @method static Builder|GiftCard whereAdv3Text($value)
 * @method static Builder|GiftCard whereAdv4Text($value)
 * @method static Builder|GiftCard whereAdv5Text($value)
 * @method static Builder|GiftCard whereAdv6Text($value)
 * @method static Builder|GiftCard whereAdvTitle($value)
 * @method static Builder|GiftCard whereCardConvTitle($value)
 * @method static Builder|GiftCard whereCardElTypeTitle($value)
 * @method static Builder|GiftCard whereCreatedAt($value)
 * @method static Builder|GiftCard whereCustomSumm($value)
 * @method static Builder|GiftCard whereDesc($value)
 * @method static Builder|GiftCard whereForTitle($value)
 * @method static Builder|GiftCard whereFromTitle($value)
 * @method static Builder|GiftCard whereGratsText($value)
 * @method static Builder|GiftCard whereHideNominal($value)
 * @method static Builder|GiftCard whereId($value)
 * @method static Builder|GiftCard whereMetaTitle($value)
 * @method static Builder|GiftCard whereNomTitle($value)
 * @method static Builder|GiftCard whereOfOplAfterText($value)
 * @method static Builder|GiftCard whereOfPolTitle($value)
 * @method static Builder|GiftCard whereOrEnterCustomSumm($value)
 * @method static Builder|GiftCard whereReceiver($value)
 * @method static Builder|GiftCard whereRulesList($value)
 * @method static Builder|GiftCard whereRulesTitle($value)
 * @method static Builder|GiftCard whereSender($value)
 * @method static Builder|GiftCard whereTitle($value)
 * @method static Builder|GiftCard whereTorjDescTitle($value)
 * @method static Builder|GiftCard whereTorjTitle($value)
 * @method static Builder|GiftCard whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|GiftCard whereUpdatedAt($value)
 * @method static Builder|GiftCard whereWhenSendTitle($value)
 * @method static Builder|GiftCard withTranslation($locale = null, $fallback = true)
 * @method static Builder|GiftCard withTranslations($locales = null, $fallback = true)
 */
class GiftCard extends Model
{
    use Translatable;

    protected $table = 'gift_card';

    protected $fillable = [];

    protected $translatable = [
        'meta_title', 'title', 'desc', 'nom_title',

        'when_send_title', 'torj_title', 'or_enter_custom_summ', 'hide_nominal', 'sender',

        'receiver', 'from_title', 'for_title', 'card_el_type_title', 'card_conv_title', 'torj_desc_title',

        'grats_text', 'of_pol_title', 'of_opl_after_text', 'adv_title', 'adv1_text',

        'adv2_text', 'adv3_text', 'adv4_text', 'adv5_text', 'adv6_text', 'rules_title', 'rules_list', 'custom_summ',
    ];
}
