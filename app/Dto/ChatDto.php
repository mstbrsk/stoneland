<?php

namespace App\Dto;

use App\Extensions\Mapper\Contracts\StringManipulation;
use App\Extensions\Mapper\Contracts\WithMapper;

final class ChatDto extends StringManipulation implements WithMapper
{
    public ?string $matchId;
    public ?string $messageId;
    public ?string $avatar;
    public string $username;
    public ?string $message;
}
