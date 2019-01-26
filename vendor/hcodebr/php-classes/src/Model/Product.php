<?php
/**
 * Created by PhpStorm.
 * User: Aiua Reis Queiroz
 * Date: 23/01/2019
 * Time: 22:14
 */

namespace Hcode\Model;


use Hcode\DB\Sql;
use Hcode\Model;

class Product extends Model
{

    public static function listAll(){
        $sql = new Sql();
        return $sql->select("SELECT * FROM db_ecommerce.tb_products ORDER BY desproduct");
    }

    public function save(){
        $sql = new Sql();
        $results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, 
                                    :vllength, :vlweight, :desurl)", array(
                ":idproduct"=>$this->getidproduct(),
                ":desproduct"=>$this->getdesproduct(),
                ":vlprice"=>$this->getvlprice(),
                ":vlwidth"=>$this->getvlwidth(),
                ":vlheight"=>$this->getvlheight(),
                ":vllength"=>$this->getvllength(),
                ":vlweight"=>$this->getvlweight(),
                ":desurl"=>$this->getdesurl()
            ));
        $this->setData($results[0]);
    }

    public function get($idproduct){
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", array(
            ':idproduct'=>$idproduct
        ));
        $this->setData($results[0]);

    }

    public function delete(){
        $sql = new Sql();
        $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", array(
            ':idproduct'=>$this->getidproduct()
        ));
    }
    //Função para adicionar as foto dos produtos
    public function getValues()
    {
        $this->checkPhoto();

        //Carrega todos os valores vindos da função de referencia na classe Model
        $values =  parent::getValues();
        return $values;
    }

    public function checkPhoto(){
        //Caminho de pasta do sistema operacional
        if(file_exists($_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR .
                        "res". DIRECTORY_SEPARATOR .
                        "site". DIRECTORY_SEPARATOR .
                        "img". DIRECTORY_SEPARATOR .
                        "products". DIRECTORY_SEPARATOR .
                        $this->getIdproduct() . "jpg")){
            //URL que será chamada
            $url = "/res/site/img/products/" . $this->getIdproduct() . "jpg";
        } else{
            $url = "/res/site/img/product.jpg";
        }
        return $this->setDesphoto($url);
    }
    //Função para receber, converter e gravar o arquivo
    public function setPhoto($file){
        //Divide o nome da imagem no ponto
        $extension = explode('.', $file['name']);
        //Pega o final do nome depois do ponto
        $extension = end($extension);

        switch ($extension){
            //Transforma a imagem carregada conforme extenção
            case "png":
                $image = imagecreatefrompng($file["tmp_name"]);
                break;
            case "gif":
                $image = imagecreatefromgif($file["tmp_name"]);
                break;
            case "jpeg":
                $image = imagecreatefromjpeg($file["tmp_name"]);
                break;
            case "jpg":
                $image = imagecreatefromjpeg($file["tmp_name"]);
                break;
        }
        //Caminho de onde será salvo a imagem
        $dist = $_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR .
            "res". DIRECTORY_SEPARATOR .
            "site". DIRECTORY_SEPARATOR .
            "img". DIRECTORY_SEPARATOR .
            "products". DIRECTORY_SEPARATOR .
            $this->getIdproduct() . ".jpg";
        //Grava a imagem na pasta
        imagejpeg($image, $dist);
        //Destroi a imagem do arquivo temporário do servidor
        imagedestroy($image);
        //Carrega a foto no objeto
        $this->checkPhoto();
    }


}