<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 *
 * @package app\models
 *
 * @property int $id
 * @property string $title
 * @property string $code
 *
 * @property Ingredient[] $ingredients
 */
class IngredientType extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'ingredient_type';
    }

    public function getIngredients(): ActiveQuery
    {
        return $this->hasMany(Ingredient::class, ['type_id' => 'id']);
    }
}