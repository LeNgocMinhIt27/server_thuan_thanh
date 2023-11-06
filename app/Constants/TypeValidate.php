<?php

namespace App\Constants;

final class TypeValidate
{
    public const IMAGE = "nullable|mimes:jpeg,jpg,png,gif|max:10000";
    public const VIDEO = "nullable|mimes:mp4,mov,ogg,qt,avi|max:20000";
}
