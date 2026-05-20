<?php

namespace App\Enums\Proposal;

use App\Concerns\EnumToArray;

enum ProposalStatus: int
{
    use EnumToArray;

    case DRAFT = 1;
    case APPROVED = 2;
    case REJECTED = 3;
    case CONVERTED_TO_SALE = 4;
    case ARCHIVE = 5;

    public function text(): string
    {
        return match ($this) {
            self::DRAFT => 'Taslak',
            self::APPROVED => 'Onaylandı',
            self::REJECTED => 'Reddedildi',
            self::CONVERTED_TO_SALE => 'Satışa Çevrildi',
            self::ARCHIVE => 'Arşiv',
        };
    }

    public function style(): string
    {
        return match ($this) {
            self::DRAFT => 'info',
            self::APPROVED => 'success',
            self::REJECTED => 'error',
            self::CONVERTED_TO_SALE => 'secondary',
            self::ARCHIVE => 'warning',
        };
    }

    public function canMakeArchive(): bool
    {
        return $this === self::APPROVED || $this === self::REJECTED;
    }

    public function isDraft(): bool
    {
        return $this === self::DRAFT;
    }

    public function isSent(): bool
    {
        return $this === self::CONVERTED_TO_SALE;
    }

    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }

    public function isArchive(): bool
    {
        return $this === self::ARCHIVE;
    }

    public function editable(): bool
    {
        return in_array($this, [
            self::DRAFT,
        ]);
    }

    public function notEditable(): bool
    {
        return !$this->editable();
    }
}
