<?php
/**
 * Created by PhpStorm.
 * User: Aiua Reis Queiroz
 * Date: 03/02/2019
 * Time: 16:07
 */

function formatPrice(float $vlprice){
    return number_format($vlprice, 2,",",".");
}