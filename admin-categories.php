<?php
/**
 * Created by PhpStorm.
 * User: Aiua Reis Queiroz
 * Date: 23/01/2019
 * Time: 22:35
 */

use Hcode\PageAdmin;
use Hcode\Model\Category;
use Hcode\Model\User;
use Hcode\Model\Product;


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

$app->get("/admin/categories/:idcategory/products", function ($idcategory){
    User::verifyLogin();
    $category = new Category();
    $category->get((int)$idcategory);
    $page = new PageAdmin();
    $page->setTpl("categories-products", array(
        'category'=>$category->getValues(),
        'productsRelated'=>$category->getProducts(),
        'productsNotRelated'=>$category->getProducts(false)
    ));
});

$app->get("/admin/categories/:idcategory/products/:idproduct/add", function ($idcategory, $idproduct){
    User::verifyLogin();
    $category = new Category();
    $category->get((int)$idcategory);
    $product = new Product();
    $product->get((int)$idproduct);
    $category->addProduct($product);
    header("Location: /admin/categories/".$idcategory."/products");
    exit;
});

$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function ($idcategory, $idproduct){
    User::verifyLogin();
    $category = new Category();
    $category->get((int)$idcategory);
    $product = new Product();
    $product->get((int)$idproduct);
    $category->removeProduct($product);
    header("Location: /admin/categories/".$idcategory."/products");
    exit;
});

