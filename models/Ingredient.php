<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 *
 * @package app\models
 *
 * @property int $id
 * @property int $type_id
 * @property string $title
 * @property int $price
 *
 * @property IngredientType $ingredientType
 */
class Ingredient extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'ingredient';
    }

    public function getIngredientType(): ActiveQuery
    {
        return $this->hasOne(IngredientType::class, ['id' => 'type_id']);
    }
}