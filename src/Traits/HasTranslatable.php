<?php

namespace EslamFaroug\PermissionPlus\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Trait to provide dynamic translation for attributeس stored as array/json.
 */

trait HasTranslatable
{

    /**
     * Get all translations for a given translatable attribute as array.
     *
     * @param string $attribute
     * @return array|null
     */
    public function getTranslations(string $attribute): ?array
    {
        if (!in_array($attribute, $this->translatable ?? [])) {
            return null;
        }
        $value = $this->getAttribute($attribute);
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : null;
        }
        return null;
    }
   
    public function __call($method, $parameters)
    {
        if (isset($this->translatable) && in_array($method, $this->translatable)) {
            return Attribute::make(
                get: function ($value) {
                    $arr = is_array($value) ? $value : (is_string($value) ? json_decode($value, true) : []);
                    $locale = app()->getLocale();
                    $configLangs = config('permission-plus.languages', []);
                    $fallbacks = array_unique(array_merge([$locale], $configLangs));
                    // أضف بقية اللغات الموجودة في الترجمة (بدون تكرار)
                    if (is_array($arr)) {
                        foreach (array_keys($arr) as $lang) {
                            if (!in_array($lang, $fallbacks)) {
                                $fallbacks[] = $lang;
                            }
                        }
                    }
                    foreach ($fallbacks as $lang) {
                        if (isset($arr[$lang])) {
                            return $arr[$lang];
                        }
                    }
                    return is_array($arr) && count($arr) ? reset($arr) : null;
                },
                set: function ($value) {
                    if (is_array($value)) {
                        return json_encode($value, JSON_UNESCAPED_UNICODE);
                    }
                    return $value;
                }
            );
        }
        return parent::__call($method, $parameters);
    }
}
