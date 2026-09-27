<?php
namespace App\Support;

final class SkinToneLabel
{
    // Keep stored/API categories compatible while presenting the chosen wording.
    public static function display(?string $text): string
    {
        return str_replace(['Very Deep', 'Deep', 'deep brown'], ['Very Dark', 'Dark', 'dark brown'], $text ?? '');
    }
}
