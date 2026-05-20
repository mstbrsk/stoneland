<?php

namespace App\Dto;

use App\Extensions\Mapper\Contracts\StringManipulation;
use App\Extensions\Mapper\Contracts\WithExtra;
use App\Extensions\Mapper\Contracts\WithMapper;

final class FollowDto extends StringManipulation implements WithMapper, WithExtra
{
    public string $followed;
    public string $followedBy;

    public function extra(): array
    {
        return [
            'followedBy' => fn() => auth('member')->id(),
        ];
    }
}
