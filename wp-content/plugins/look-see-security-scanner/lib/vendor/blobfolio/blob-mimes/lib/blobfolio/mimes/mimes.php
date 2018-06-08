<?php
 namespace blobfolio\wp\looksee\vendor\mimes; use \blobfolio\wp\looksee\vendor\common; class mimes { const MIME_DEFAULT = 'application/octet-stream'; const MIME_EMPTY = 'inode/x-empty'; public static function get_mimes() { return data::BY_MIME; } public static function get_mime($mime = '') { common\ref\cast::to_string($mime, true); common\ref\sanitize::mime($mime); return array_key_exists($mime, data::BY_MIME) ? data::BY_MIME[$mime] : false; } public static function get_extensions() { return data::BY_EXT; } public static function get_extension($ext = '') { common\ref\cast::to_string($ext, true); common\ref\sanitize::file_extension($ext); return array_key_exists($ext, data::BY_EXT) ? data::BY_EXT[$ext] : false; } public static function check_ext_and_mime($ext = '', $mime = '', $soft=true) { common\ref\cast::to_string($ext, true); common\ref\cast::to_string($mime, true); common\ref\cast::to_bool($soft, true); common\ref\sanitize::file_extension($ext); if (!common\mb::strlen($ext)) { return false; } common\ref\sanitize::mime($mime); if ( !strlen($mime) || (static::MIME_EMPTY === $mime) || ($soft && (static::MIME_DEFAULT === $mime)) ) { return true; } if (false === ($ext = static::get_extension($ext))) { return $soft; } if (0 === strpos($mime, 'application/cdfv2')) { $mime = 'application/vnd.ms-office'; } $real = $ext['mime']; $test = array($mime); $parts = explode('/', $mime); $subtype = count($parts) - 1; if ('x-' === substr($parts[$subtype], 0, 2)) { $parts[$subtype] = substr($parts[$subtype], 2); } else { $parts[$subtype] = 'x-' . $parts[$subtype]; } $test[] = implode('/', $parts); $parts = explode('/', $mime); $subtype = count($parts) - 1; if ('vnd.' === substr($parts[$subtype], 0, 4)) { $parts[$subtype] = substr($parts[$subtype], 4); } else { $parts[$subtype] = 'vnd.' . $parts[$subtype]; } $test[] = implode('/', $parts); $found = array_intersect($real, $test); return count($found) > 0; } public static function finfo($path = '', $nice = null) { common\ref\cast::to_string($path, true); if (!is_null($nice)) { common\ref\cast::to_string($nice, true); } $out = array( 'dirname'=>'', 'basename'=>'', 'extension'=>'', 'filename'=>'', 'path'=>'', 'mime'=>static::MIME_DEFAULT, 'suggested_filename'=>array(), ); common\ref\cast::to_string($path); if ( (false === common\mb::strpos($path, '.')) && (false === common\mb::strpos($path, '/')) && (false === common\mb::strpos($path, '\\')) ) { $out['extension'] = common\sanitize::file_extension($path); if (false !== ($ext = static::get_extension($path))) { $out['mime'] = $ext['primary']; } return $out; } common\ref\file::path($path, false); $out['path'] = $path; $out = common\data::parse_args(pathinfo($path), $out); if (!is_null($nice)) { $pathinfo = pathinfo($nice); $out['filename'] = isset($pathinfo['filename']) ? $pathinfo['filename'] : ''; $out['extension'] = isset($pathinfo['extension']) ? $pathinfo['extension'] : ''; } common\ref\sanitize::file_extension($out['extension']); if (false !== ($ext = static::get_extension($out['extension']))) { $out['mime'] = $ext['primary']; } try { if (false !== ($path = realpath($path))) { $out['path'] = $path; $out['dirname'] = dirname($path); } if ( (false !== $path) && function_exists('finfo_file') && defined('FILEINFO_MIME_TYPE') && @is_file($path) && @filesize($path) > 0 ) { $finfo = finfo_open(FILEINFO_MIME_TYPE); $magic_mime = common\sanitize::mime(finfo_file($finfo, $path)); finfo_close($finfo); if ( ('svg' === $out['extension']) && ('image/svg+xml' !== $magic_mime) ) { $tmp = @file_get_contents($path); if ( is_string($tmp) && preg_match('/\s*<svg/iu', $tmp) ) { $magic_mime = 'image/svg+xml'; } } if ( $magic_mime && (static::MIME_DEFAULT !== $magic_mime) && ( (static::MIME_DEFAULT === $out['mime']) || !preg_match('/^text\//', $magic_mime) ) && !static::check_ext_and_mime($out['extension'], $magic_mime) ) { if (false !== ($mime = static::get_mime($magic_mime))) { $out['mime'] = $magic_mime; $out['extension'] = $mime['ext'][0]; foreach ($mime['ext'] as $ext) { $out['suggested_filename'][] = "{$out['filename']}.$ext"; } } } } } catch (\Throwable $e) { return $out; } catch (\Exception $e) { return $out; } return $out; } } 