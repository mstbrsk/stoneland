<?php

namespace App\Dto;

use App\Enums\MatchStatus;
use App\Extensions\Mapper\Contracts\StringManipulation;
use App\Extensions\Mapper\Contracts\WithExtra;
use App\Extensions\Mapper\Contracts\WithMapper;
use App\Models\Country;
use App\Models\Member;

final class ReportedUserDto extends StringManipulation implements WithMapper, WithExtra
{
    public string $reported;
    public string $reportedBy;
    public string $notes;


    public function extra(): array
    {
        return [
            'reportedBy' => fn() => auth('member')->id(),
        ];
    }
}
