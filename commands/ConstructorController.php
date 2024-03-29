<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace app\commands;

use app\controllers\Combinatorics;
use app\models\Generator;
use yii\base\Exception;
use yii\console\Controller;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ConstructorController extends Controller
{
    /**
     * Получение блюд в соответствии с переданным кодом ингредиентов
     *
     * @param string $code Коды ингредиентов
     * @throws Exception
     * @throws \Exception
     */
    public function actionGenerate(string $code = 'dcii'): void
    {
        $generator = new Generator();
        $validatedCodeAsArr = $generator->validateCode($code);

        echo json_encode($generator->generateVariants($validatedCodeAsArr), JSON_UNESCAPED_UNICODE);
    }
}
