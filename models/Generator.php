<?php

namespace app\models;

use app\controllers\Combinatorics;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class Generator extends ActiveRecord
{
    /**
     * @throws Exception
     */
    public function validateCode($code): array
    {
        $ingredientTypes = IngredientType::find()
            ->asArray()
            ->all();
        $ingredientsInArr = str_split(trim($code));

        $inputIngredientsCountByType = $this->setIngredientsByCount($ingredientTypes, $ingredientsInArr);

        $this->validateCountNeedleIngredients($inputIngredientsCountByType);

        return $inputIngredientsCountByType;
    }

    /**
     * Получение списка необходимых ингредиентов с их необходимым количеством
     *
     * @throws Exception
     */
    private function setIngredientsByCount(array $ingredientTypes, array $ingredientsInArr): array
    {
        $result = [];

        foreach($ingredientsInArr as $ingredientCode) {
            if ($ingredientCode === ' ') {
                continue;
            }
            if (!array_key_exists($ingredientCode, ArrayHelper::map($ingredientTypes, 'code', 'code'))) {
                throw new Exception("В БД нет ингредиента с кодом '${ingredientCode}'");
            }

            isset($result[$ingredientCode]) ? $result[$ingredientCode] += 1 : $result[$ingredientCode] = 1;
        }

        return $result;
    }

    /**
     * Проверка на то, что в БД существует переданное кол-во ингредиентов того или иного типа
     *
     * @param array $inputIngredientsCountByType
     * @return void
     */
    private function validateCountNeedleIngredients(array $inputIngredientsCountByType): void
    {
        $allIngredientsCountByType = Ingredient::find()
            ->select(['ingredient_type.code', 'COUNT(*) as ingredients_count'])
            ->innerJoin('ingredient_type', 'ingredient_type.id = ingredient.type_id')
            ->groupBy('type_id')
            ->asArray()
            ->all();

        $allIngredientsCountByType = ArrayHelper::map($allIngredientsCountByType, 'code', 'ingredients_count');

        foreach ($inputIngredientsCountByType as $ingredientCode => $inputIngredientCount) {
            if ($allIngredientsCountByType[$ingredientCode] < $inputIngredientCount) {
                throw new \InvalidArgumentException("Указано слишком много ингредиентов с кодом '$ingredientCode'");
            }
        }
    }

    /**
     * @param array $code
     * @return array
     */
    public function generateVariants(array $code): array
    {
        $combinationsByIngredientCode = [];
        foreach ($code as $ingredientCode => $count) {
            $ingredientsCollection = IngredientType::find()
                ->where(['code' => $ingredientCode])
                ->one()
                ->ingredients;

            $combinatorics = new Combinatorics;
            $combinationsByIngredientCode[$ingredientCode] = $combinatorics->combinations($ingredientsCollection, $count);
        }

        return $this->getOutput($this->createDishes($combinationsByIngredientCode));
    }

    /**
     * Получение списка готовых блюд
     *
     * @param array $dishes
     * @return array
     */
    private function getOutput(array $dishes): array
    {
        $result = [];

        foreach ($dishes as $ingredients) {
            $dish = [
                'products' => [],
                'price' => 0
            ];
            foreach ($ingredients as $ingredient) {
                $dish['products'][] = [
                    'type' => $ingredient->ingredientType->title,
                    'value' => $ingredient->title,
                ];

                $dish['price'] += (int)$ingredient->price;
            }

            $result[] = $dish;
        }
        return $result;
    }

    /**
     * Генерация блюд
     *
     * @param $combinationsByIngredientCode
     * @return array
     */
    private function createDishes($combinationsByIngredientCode): array
    {
        $result = [];
        $isAddIngredient = false;

        foreach ($combinationsByIngredientCode as $code => $combinations) {
            $temp = $result;
            if ($isAddIngredient) {
                $result = $this->addIngredientsToDishes($temp, $combinations);
            } else {
                foreach ($combinations as $combination) {
                    $result[] = $combination;
                }
            }
            $isAddIngredient = true;
        }

        return $result;
    }

    private function addIngredientsToDishes($dishes, $combinations)
    {
        $result = [];

        foreach ($dishes as $dish) {
            foreach ($combinations as $combination) {
                $result[] = array_merge($dish, $combination);
            }
        }

        return $result;
    }
}