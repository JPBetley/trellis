<?php

use Carbon\Carbon;
use App\Services\Auth\User;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Validator;

/**
 * @return \App\Services\Auth\Back\User|\App\Services\Auth\Front\User|null
 *
 * @throws \Exception
 */
function current_user(): ?User
{
    if (request()->isFront()) {
        return auth()->guard('front')->user();
    }

    if (request()->isBack()) {
        return auth()->guard('back')->user();
    }

    throw new Exception('Coud not determine current user');
}

function diff_date_for_humans(Carbon $date) : string
{
    return (new Jenssegers\Date\Date($date->timestamp))->ago();
}

function roman_year(int $year = null): string
{
    $year = $year ?? date('Y');

    $romanNumerals = [
        'M' => 1000,
        'CM' => 900,
        'D' => 500,
        'CD' => 400,
        'C' => 100,
        'XC' => 90,
        'L' => 50,
        'XL' => 40,
        'X' => 10,
        'IX' => 9,
        'V' => 5,
        'IV' => 4,
        'I' => 1,
    ];

    $result = '';

    foreach ($romanNumerals as $roman => $yearNumber) {
        // Divide to get  matches
        $matches = intval($year / $yearNumber);

        // Assign the roman char * $matches
        $result .= str_repeat($roman, $matches);

        // Substract from the number
        $year = $year % $yearNumber;
    }

    return $result;
}

/**
 * Shortens a string in a pretty way. It will clean it by trimming
 * it, remove all double spaces and html. If the string is then still
 * longer than the specified $length it will be shortened. The end
 * of the string is always a full word concatenated with the
 * specified moreTextIndicator.
 *
 * @param string $string
 * @param int    $length
 * @param string $moreTextIndicator
 *
 * @return string
 */
function str_tease(string $string, int $length = 200, string $moreTextIndicator = '...'): string
{
    $string = trim($string);

    //remove html
    $string = strip_tags($string);

    //replace multiple spaces
    $string = preg_replace("/\s+/", ' ', $string);

    if (strlen($string) == 0) {
        return '';
    }

    if (strlen($string) <= $length) {
        return $string;
    }

    $ww = wordwrap($string, $length, "\n");

    $string = substr($ww, 0, strpos($ww, "\n")).$moreTextIndicator;

    return $string;
}

function svg($filename): HtmlString
{
    return new HtmlString(
        file_get_contents(resource_path("assets/svg/{$filename}.svg"))
    );
}

/**
 * Validate some data.
 *
 * @param string|array $fields
 * @param string|array $rules
 *
 * @return bool
 */
function validate($fields, $rules): bool
{
    if (! is_array($fields)) {
        $fields = ['default' => $fields];
    }

    if (! is_array($rules)) {
        $rules = ['default' => $rules];
    }

    return Validator::make($fields, $rules)->passes();
}

function class_has_trait($className, string $traitName): bool
{
    if (is_object($className)) {
        $className = get_class($className);
    }

    return in_array($traitName, class_uses_recursive($className));
}
