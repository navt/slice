<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('getPage'))
{
	function getPage($url='')
	{
		if ($url === '') return false;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		$str = curl_exec($curl);
		curl_close($curl);
		return $str;
	}
}
if ( ! function_exists('cropContent'))
{
	function cropContent($content='',$run=250)
	{
		if ($content ==='') return false;
		$content = ltrim($content);
		$content = mb_substr($content, 0, $run,'UTF-8');
		$content = mb_substr($content, 0, mb_strrpos($content, ' ','UTF-8'),'UTF-8');
		$content = rtrim($content, "?!,.-");
		$content.=' &#8230;';
		return $content;
	}
}
if ( ! function_exists('regularTime'))
{
	function regTime($inStr='')
	{
		$format = 'Y-m-d H:i:s';
		$date = DateTime::createFromFormat($format, $inStr);
		return $date->format('H:i d/m/Y');
		// http://php.net/manual/ru/datetime.createfromformat.php
	}
}
if ( ! function_exists('modifyTime'))
{
	function modifyTime($inStr='',$delta='-30 minute')
	{
		$format = 'Y-m-d H:i:s';
		$date = DateTime::createFromFormat($format, $inStr);
		$date->modify($delta);
		return $date->format($format);
	}
}