<?php

namespace app\commands;

use yii\console\Controller;
use app\models\User;
use Yii;

class UserController extends Controller
{
    public function actionCreateAdmin($username, $password)
    {
        $user = new User();
        $user->username = $username;
        $user->setPassword($password);
        $user->generateAuthKey();
        $user->created_at = time();
        $user->updated_at = time();

        if ($user->save()) {
            echo "Admin user '{$username}' created successfully.\n";
        } else {
            print_r($user->errors);
        }
    }
}
