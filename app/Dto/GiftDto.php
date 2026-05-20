<?php

namespace App\Dto;

use App\Extensions\Mapper\Contracts\StringManipulation;
use App\Extensions\Mapper\Contracts\WithMapper;

final class GiftDto extends StringManipulation implements WithMapper
{
    public string $matchId;
    public ?string $username;
    public ?array $gift;
    public ?int $count;
}
