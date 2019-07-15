<?php

namespace Core\Log;

class LogLevel
{
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const SUCCESS = 'success';
    const DEBUG = 'debug';
    const ALERT = 'alert';
    const NOTICE = 'notice';
    const EMERGENCY = 'emergency';
    const INFO = 'info';

    const CRITICAL_COLOR = 91;
    const ERROR_COLOR = 91;
    const SUCCESS_COLOR = 92;
    const WARNING_COLOR = 93;
    const DEBUG_COLOR = 39;
    const ALERT_COLOR = 91;
    const EMERGENCY_COLOR = 93;
    const INFO_COLOR = 95;
    const NOTICE_COLOR = 89;
}