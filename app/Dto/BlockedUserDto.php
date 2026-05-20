<?php

namespace App\Dto;

use App\Extensions\Mapper\Contracts\StringManipulation;
use App\Extensions\Mapper\Contracts\WithExtra;
use App\Extensions\Mapper\Contracts\WithMapper;

final class BlockedUserDto extends StringManipulation implements WithMapper, WithExtra
{
    public string $blocked;
    public string $blockedBy;

    public function extra(): array
    {
        return [
            'blockedBy' => fn() => auth('member')->id(),
        ];
    }
}
