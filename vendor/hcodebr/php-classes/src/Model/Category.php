<?php
/**
 * Created by PhpStorm.
 * User: Aiua Reis Queiroz
 * Date: 19/01/2019
 * Time: 20:31
 */

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model;

class Category extends Model
{

    //Função para listar todos as Categorias do Banco
    public static function listAll(){
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
    }

    public function save(){
        $sql = new Sql();
        $results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)",
            array(
                ":idcategory"=>$this->getIdcategory(),
                ":descategory"=>$this->getDescategory()
            ));
        $this->setData($results[0]);
    }

    public function get($idcategory){
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
            ':idcategory'=>$idcategory
        ));
        $this->setData($results[0]);

    }

    public function delete(){
        $sql = new Sql();
        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(
            ':idcategory'=>$this->getIdcategory()
        ));
    }




}