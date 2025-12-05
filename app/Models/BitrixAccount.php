<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BitrixAccount extends Model
{
    use HasFactory;

    protected $table = 'bitrix_accounts';

    protected $fillable = [
        'domain', 'member_id', 'access_token',
        'refresh_token', 'user_id', 'expires_in', 'expires_at'
    ];

    /*
     * Чтобы получить токен от Битрикс24 в первый раз, нужно сделать следующие шаги
     * Шаг1. Нужно пройти в браузере по ссылке: https://portal.dlg.kz/oauth/authorize/?client_id=local.68909c1bdd46c9.50369748&response_type=code
     * Шаг2. Получаем данные и передаем к контроллеру Bitrix24Controller в методе createAccount
     * Шаг3. Проходим в браузере по ссылке: https://kpp.dlg.kz:8900/bitrix24/create тут мы сохраняем все данные в БД
     *
     * Дальнейшее шаги по обновление токена происходит автоматическое
     *
     * P.S. если хотим токены просто в браузере то проходим по ссылке ниже
     * https://portal.dlg.kz/oauth/token/?
    grant_type=authorization_code
    &client_id=local.68909c1bdd46c9.50369748
    &client_secret=c5nnwNtVJUNzFWcR9AXQNqDV7QA5N2o1DErcMhG5umi72C7x29
    &code=03389b68007ae4e6003a5cc800000434000007647ac66abb679a28dce639f161ddff21
	&redirect_uri=https://kpp.dlg.kz:8900/bitrix24/access-token

    * code - получаем в шаге 1.
     * */
}
