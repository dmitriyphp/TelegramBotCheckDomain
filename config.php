<?php

const BOT_TOKEN   = '5170734919:AAH0nM5duM19DNrAVno8V0rgxMrF6EZN_2M';
const API_URL     = 'https://api.telegram.org/bot' . BOT_TOKEN . '/';
const API_METHOD  = 'sendMessage';

const CURLOPT_CONNECTTIMEOUT = 5;
const CURLOPT_TIMEOUT = 60;

const HELLO_TEXT = 'Hello there. This is domain availability bot. Please type any domain and I will check it';
const ERROR_TEXT = 'Oops. I do not understand you. Please try again';

const DOMAIN_API_URL = 'http://ip-api.com/json/';
const DOMAIN_EXISTS_TEXT = 'This domain is already exists :(';
const DOMAIN_NOT_EXISTS_TEXT = 'This domain is not existing!';