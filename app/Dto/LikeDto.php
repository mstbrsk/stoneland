<?php

namespace App\Dto;

use App\Extensions\Mapper\Contracts\StringManipulation;
use App\Extensions\Mapper\Contracts\WithMapper;

final class LikeDto extends StringManipulation implements WithMapper
{
    public string $matchId;
}
