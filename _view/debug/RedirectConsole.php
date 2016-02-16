<?php
use app\decibel\http\DRedirectableResponse;

///@cond INTERNAL
function generateRedirectConsole(DRedirectableResponse $response)
{
    $trace = $response->getTrace();
    $traceString = nl2br($response->getTraceAsString());
    $output = <<<EOD
	<!DOCTYPE html>
	<html>
	<head>
		<meta charset="utf-8" />
		<title>Decibel Debug Console</title>
		<link type="text/css" rel="stylesheet" href="/theme/styles/Debug.css" media="all" />
	</head>
	<body class="app-decibel-debug-debugconsole">
		<div class="app-decibel-notification">
			<img src="data:image/png;base64,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" style="float: left;height: 30px; margin: 4px;" />
			<div class="inner">
				 <span style="float: right;">Decibel Debug Console (Redirect Advice)<span>
			</div>
		</div>
		<div class="app-decibel-debug-ddebug">
			<div>
				<strong>A redirect has been issued ({$trace[0]['file']}, line {$trace[0]['line']})<br /><br />
				<strong>Redirect URL</strong>: {$response->getRedirectUrl()}<br />
				<strong>Reason</strong>: {$response->getRedirectReason()}<br />
				<strong>Status Code</strong>: {$response->getResponseType()}<br /><br />
				<form method="GET" action="{$response->getRedirectUrl()}">
					<input type="submit" value="Continue" />
				</form><br />
				<strong>Backtrace</strong>:<br />
				<span class=\"App_Error_trace\">{$traceString}</span><br />
			</div>
		</div>
	</body>
	</html>
EOD;

    return $output;
}
///@endcond
