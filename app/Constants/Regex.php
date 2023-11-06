<?php

namespace App\Constants;

final class Regex
{
    //catch regex
    public const PASSWORD_REGEX = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*?[!@#$%^&*.()])[0-9a-zA-Z!@#$%^&*.()]{8,}$/";
    public const MOBILE_REGEX = "/(0)[0-9]/";
    public const MOBILE_NOT_REGEX = "/[a-z]/";
    public const URL_REGEX = "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/";

    //return error
    public const PASSWORD_MESSAGE = "Vui lòng chỉ sử dụng chữ cái (a-z và A-Z), số (0-9), ít nhất một chữ cái, chữ cái in hoa, số và các ký tự đặc biệt như !@#$%^&*.()";
}
