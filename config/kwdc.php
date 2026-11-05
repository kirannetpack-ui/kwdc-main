<?php

return [
    'demo' => (bool) env('PHASE_ONE_DEMO', false),
    'serverless' => (bool) env('VERCEL', false),
    'launch_ready' => filter_var(env('APP_LAUNCH_READY', ! env('VERCEL', false)), FILTER_VALIDATE_BOOLEAN),
];
