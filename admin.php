<?php
/**
 * Created by PhpStorm.
 * User: Aiua Reis Queiroz
 * Date: 23/01/2019
 * Time: 22:33
 */

use Hcode\PageAdmin;
use Hcode\Model\User;


$app->get('/admin', function() {
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl("index");
});

$app->get('/admin/login', function (){
    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("login");
});

$app->post('/admin/login', function (){
    User::login($_POST["login"], $_POST["password"]);
    header("Location: /admin");
    exit;
});

$app->get('/admin/logout', function (){
    User::logout();
    header("Location: /admin/login");
    exit;
});

//Rota da tela de recuperação de senha
$app->get("/admin/forgot", function (){
    $page = new PageAdmin([
        //Não utiliza o header e o footer do template padrão
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("forgot");
});

//Rota que recebe o e-mail enviado da tela de recuperação de senha
$app->post("/admin/forgot", function (){
    //Função que recebe o e-mail do usuário
    User::getForgot($_POST["email"]);
    header("Location: /admin/forgot/sent");
    exit;
});

$app->get("/admin/forgot/sent", function (){
    $page = new PageAdmin([
        //Não utiliza o header e o footer do template padrão
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("forgot-sent");
});

$app->get("/admin/forgot/reset", function (){
    //Verifica os dados do usuário e se positivo retorna os dados
    $user = User::validForgotDecrypt($_GET["code"]);

    $page = new PageAdmin([
        //Não utiliza o header e o footer do template padrão
        "header"=>false,
        "footer"=>false
    ]);
    //O código será validado novamente na outra página, para evitar invasão
    $page->setTpl("forgot-reset", array(
        "name"=>$user["desperson"],
        "code"=>$_GET["code"]
    ));
});

$app->post("/admin/forgot/reset", function () {
    //Valida novamente a código de recuperação
    $forgot = User::validForgotDecrypt($_POST["code"]);

    //Chama o método para gravar no banco que a recuperação ocorreu
    User::setForgotUsed($forgot ["idrecovery"]);

    //Cria uma instancia de User
    $user = new User();

    //Carrega os dados do usuário
    $user->get((int)$forgot["iduser"]);

    $password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
        "cost"=>12
    ]);

    //Grava a nova senha vinda por POST no banco
    $user->setPassword($password);

    $page = new PageAdmin([
        //Não utiliza o header e o footer do template padrão
        "header"=>false,
        "footer"=>false
    ]);

    //O código será validado novamente na outra página, para evitar invasão
    $page->setTpl("forgot-reset-success");
});

