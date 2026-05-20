<?php

namespace App\Dto\Member;

use App\Extensions\Mapper\Contracts\StringManipulation;
use App\Extensions\Mapper\Contracts\WithMapper;

final class UpdateMemberDto extends StringManipulation implements WithMapper
{
    public string $about;
    public string $fullName;
    public string $nickname;
}
