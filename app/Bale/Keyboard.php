<?php

declare(strict_types=1);

namespace App\Bale;

use App\Telegram\Keyboard as TelegramKeyboard;

class Keyboard {
    public static function __callStatic($name, $arguments) {
        return TelegramKeyboard::$name(...$arguments);
    }

    public static function mainMenu() { return TelegramKeyboard::mainMenu(); }
    public static function donateMenu($donateUrl) { return TelegramKeyboard::donateMenu($donateUrl); }
    public static function youtubeMenu($youtubeUrl) { return TelegramKeyboard::youtubeMenu($youtubeUrl); }
    public static function vipMenu() { return TelegramKeyboard::vipMenu(); }
    public static function backButton($callbackData = 'home') { return TelegramKeyboard::backButton($callbackData); }
    public static function removeKeyboard() { return TelegramKeyboard::removeKeyboard(); }
    public static function paginationList(array $items, $currentPage = 1, $perPage = 5, $callbackPrefix = 'item_', $totalPages = null) { return TelegramKeyboard::paginationList($items, $currentPage, $perPage, $callbackPrefix, $totalPages); }
    public static function mixedButtons(array $buttons) { return TelegramKeyboard::mixedButtons($buttons); }
    public static function confirmButtons($confirmCallback, $cancelCallback = 'home') { return TelegramKeyboard::confirmButtons($confirmCallback, $cancelCallback); }
    public static function selectionButtons(array $options, $callbackPrefix = 'select_') { return TelegramKeyboard::selectionButtons($options, $callbackPrefix); }
    public static function contactButtons($phone = null, $username = null, $email = null) { return TelegramKeyboard::contactButtons($phone, $username, $email); }
    public static function socialMediaButtons($instagram = null, $twitter = null, $youtube = null, $telegram = null) { return TelegramKeyboard::socialMediaButtons($instagram, $twitter, $youtube, $telegram); }
    public static function callbackButton($text, $callbackData) { return TelegramKeyboard::callbackButton($text, $callbackData); }
    public static function urlButton($text, $url) { return TelegramKeyboard::urlButton($text, $url); }
    public static function textButton($text) { return TelegramKeyboard::textButton($text); }
    public static function switchInlineButton($text, $query = '') { return TelegramKeyboard::switchInlineButton($text, $query); }
    public static function switchInlineButtonOther($text, $query = '') { return TelegramKeyboard::switchInlineButtonOther($text, $query); }
    public static function payButton($text = '💳 پرداخت') { return TelegramKeyboard::payButton($text); }
    public static function requestContactButton($text = '📱 ارسال شماره تماس') { return TelegramKeyboard::requestContactButton($text); }
    public static function requestLocationButton($text = '📍 ارسال موقعیت مکانی') { return TelegramKeyboard::requestLocationButton($text); }
    public static function custom(array $buttons) { return TelegramKeyboard::custom($buttons); }
    public static function grid(array $buttons, $columns = 2) { return TelegramKeyboard::grid($buttons, $columns); }
    public static function infoKeyboard(array $info, $backCallback = 'home') { return TelegramKeyboard::infoKeyboard($info, $backCallback); }
    public static function userListKeyboard(array $users, $page = 1, $perPage = 5) { return TelegramKeyboard::userListKeyboard($users, $page, $perPage); }
    public static function messageListKeyboard(array $messages, $page = 1, $perPage = 5) { return TelegramKeyboard::messageListKeyboard($messages, $page, $perPage); }
    public static function toJson(array $keyboard) { return TelegramKeyboard::toJson($keyboard); }
    public static function addRow(array $keyboard, array $buttons) { return TelegramKeyboard::addRow($keyboard, $buttons); }
    public static function addButton(array $keyboard, array $button) { return TelegramKeyboard::addButton($keyboard, $button); }
    public static function empty() { return TelegramKeyboard::empty(); }
}
