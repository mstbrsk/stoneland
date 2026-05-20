<?php

namespace App\Dto;

use App\Extensions\Mapper\Contracts\StringManipulation;
use App\Extensions\Mapper\Contracts\WithExtra;
use App\Extensions\Mapper\Contracts\WithMapper;
use Carbon\Carbon;

final class SendMessageDto extends StringManipulation implements WithMapper, WithExtra
{
    public string $type;
    public ?string $from = null;
    public string $to;
    public string $message;
    public ?string $messageSessionId;
    public ?int $giftCount = null;
    public ?string $createdAt = null;

    public function extra(): array
    {
        return [
            'createdAt' => fn() => Carbon::now(),
        ];
    }
}
