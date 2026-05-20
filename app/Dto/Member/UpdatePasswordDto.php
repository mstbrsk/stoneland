<?php

namespace App\Dto\Member;

use App\Extensions\Mapper\Contracts\StringManipulation;
use App\Extensions\Mapper\Contracts\WithMapper;

final class UpdatePasswordDto extends StringManipulation implements WithMapper
{
    public string $oldPassword;
    public string $password;
    public string $rePassword;
}
