<?php

namespace App\Dto;

use App\Enums\MatchStatus;
use App\Extensions\Mapper\Contracts\StringManipulation;
use App\Extensions\Mapper\Contracts\WithExtra;
use App\Extensions\Mapper\Contracts\WithMapper;
use App\Models\Member;

final class MatchDto extends StringManipulation implements WithMapper, WithExtra
{
    public ?string $userToCall;
    public ?string $channelName;
    public string $from;
    public ?string $token;
    public ?string $appId;
    public ?string $matchId;
    public ?bool $follow;
    public ?MatchStatus $status;
    public ?Member $to;

    public function extra(): array
    {
        return [
            'channelName' => fn() => md5(sprintf('%s_%s', $this->from, $this?->userToCall)),
        ];
    }

    public function setMatchId(string $matchId): void
    {
        $this->matchId = $matchId;
    }

    public function setTo(Member $to): void
    {
        $this->to = $to;
    }

    public function setFrom(string $from): void
    {
        $this->from = $from;
    }

    public function setMatchStatus(MatchStatus $status): void
    {
        $this->status = $status;
    }
}
