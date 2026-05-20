<?php

namespace App\Dto\Member;

use App\Extensions\Mapper\Contracts\StringManipulation;
use App\Extensions\Mapper\Contracts\WithConvert;
use App\Extensions\Mapper\Contracts\WithExclude;
use App\Extensions\Mapper\Contracts\WithExtra;
use App\Extensions\Mapper\Contracts\WithMapper;
use App\Models\Country;
use Illuminate\Support\Facades\Hash;

final class MemberDto extends StringManipulation implements WithMapper, WithConvert, WithExclude, WithExtra
{
    public string $email;
    public string $fullName;
    public string $nickname;
    public string $phone;
    public string $country;
    public ?int $countryId;
    public string $gender;
    public string $timezone;
    public string $password;
    public string $avatar;
    public string $birthDate;
    public string $referenceId;

    public function convert(): array
    {
        return [
            'password' => fn(string $password) => Hash::make($password),
            'status' => false,
        ];
    }


    public function exclude(): array
    {
        return [
            'country',
        ];
    }

    public function extra(): array
    {
        return [
            'countryId' => fn() => Country::where('country_name', $this->country)->sole()->id
        ];
    }
}
