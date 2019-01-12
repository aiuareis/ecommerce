<?php
/**
 * Created by PhpStorm.
 * User: Aiua Reis Queiroz
 * Date: 09/01/2019
 * Time: 22:16
 */

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model;

class User extends Model
{

    const SESSION = "User";

    public static function login($login, $password){
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ":LOGIN" => $login
        ));

        if (count($results) === 0){
            throw new \Exception("Usuario inexistente ou senha inválida.");
        }

        $data = $results[0];

        if(password_verify($password, $data["despassword"]) === true){
            $user = new User();
            $user->setData($data);
            $_SESSION[User::SESSION] = $user->getValues();
            return $user;
        } else {
            throw new \Exception("Usuario inexistente ou senha inválida.");
        }

    }

    public static function verifyLogin($inadmin = true){
        if(//Se a sessão não existe
            !isset($_SESSION[User::SESSION])
            || //Se ela for falsa
            !$_SESSION[User::SESSION]
            || //Se o id não for maior que zero
            !(int)$_SESSION[User::SESSION]["iduser"] > 0
            || //Se ele é administrador
            (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
        ){
            header("Location: /admin/login");
            exit;
        }

    }

    public static function logout(){
        $_SESSION[User::SESSION] = null;
    }

}