<?php 

function cleanSqlLiteDB()
{
	$fileName = __DIR__ . "/sqlLite.db";
	if (file_exists($fileName)) {
		unlink($fileName);
	}
}