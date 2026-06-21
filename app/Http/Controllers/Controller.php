<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Shop GazTochka API',
    version: '1.0.0',
    description: 'Документация API для интернет-магазина ГазТочка',
    contact: new OA\Contact(email: 'admin@gaztochka.ru')
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'Основной сервер API'
)]
abstract class Controller
{
    //
}
