<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use Hcode\PageAdmin;
use Hcode\Model\User;
use Hcode\Model\Category;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    $page = new Page();
    $page->setTpl("index");
});

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

$app->get('/admin/users', function (){
    User::verifyLogin();
    $users = User::listAll();
    $page = new PageAdmin();
    $page->setTpl("users", array(
        "users"=>$users
    ));
});

$app->get('/admin/users/create', function (){
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl("users-create");
});

//Adicionado
$app->post("/admin/users/create", function () {
    User::verifyLogin();
    $user = new User();
    $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
    $_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [
        "cost"=>12
    ]);
    $user->setData($_POST);
    $user->save();
    header("Location: /admin/users");
    exit;
});

$app->get('/admin/users/:iduser/delete', function ($iduser){
    User::verifyLogin();
    $user = new User();
    $user->get((int)$iduser);
    $user->delete();
    header("Location: /admin/users");
    exit;
});

$app->get('/admin/users/:iduser', function ($iduser){
    User::verifyLogin();
    $user = new User();
    $user->get((int)$iduser);
    $page = new PageAdmin();
    $page->setTpl("users-update", array(
        "user"=>$user->getValues()
    ));
});

$app->post('/admin/users/:iduser', function ($iduser){
    User::verifyLogin();
    $user = new User();
    $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
    $user->get((int)$iduser);
    $user->setData($_POST);
    $user->update();
    header("Location: /admin/users");
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

//Lista todas as categorias
$app->get("/admin/categories", function (){
    User::verifyLogin();
    $categories = Category::listAll();

    $page = new PageAdmin();

    $page->setTpl("categories", array(
        'categories'=>$categories
    ));
});

$app->get("/admin/categories/create", function (){
    User::verifyLogin();
    $page = new PageAdmin();

    $page->setTpl("categories-create");
});

$app->post("/admin/categories/create", function (){
    User::verifyLogin();
    $category = new Category();
    $category->setData($_POST);
    $category->save();
    header('Location: /admin/categories');
    exit;
});

//Rota para deletar as categorias
//Parâmetro está dentro da função pq ele vem da URL
$app->get("/admin/categories/:idcategory/delete", function ($idcategory){
    User::verifyLogin();
    $category = new Category();
    //Estamos verificando se a categoria existe
    $category->get((int)$idcategory);
    $category->delete();
    header('Location: /admin/categories');
    exit;
});

$app->get("/admin/categories/:idcategory", function ($idcategory){
    User::verifyLogin();
    $category = new Category();
    //Estamos verificando se a categoria existe
    $category->get((int)$idcategory);
    $page = new PageAdmin();
    $page->setTpl("categories-update", array(
        //Transforma a variável de string para array, através do método getValues()
        'category'=>$category->getValues()
    ));
});

$app->post("/admin/categories/:idcategory", function ($idcategory) {
    User::verifyLogin();
    $category = new Category();
    //Estamos verificando se a categoria existe
    $category->get((int)$idcategory);
    //Só pode fazer desse jeito se a variavel tiver o mesmo nome do banco
    $category->setData($_POST);
    $category->save();
    header('Location: /admin/categories');
    exit;
});

$app->get("/categories/:idcategory", function ($idcategory){
   $category = new Category();
   $category->get((int)$idcategory);
    $page = new Page();
    $page->setTpl("category", array(
        'category'=>$category->getValues(),
        'products'=>[]
    ));
});

$app->run();

 ?>