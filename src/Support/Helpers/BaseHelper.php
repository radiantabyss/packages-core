<?php
function dmp($text, $text2 = null) {
    $pre = true;
    if ( php_sapi_name() == 'cli' || request()->ajax() ) {
        $pre = false;
    }

    if ( $pre ) {
        echo '<pre>';
    }

    if ( $text2 !== null ) {
        echo $text.': ';
        var_dump($text2);
    }
    else {
        var_dump($text);
    }

    if ( $pre ) {
        echo '</pre>';
    }
    else {
        echo "\n";
    }
}

function ddmp($text, $text2 = null) {
    dmp($text, $text2);
    die();
}

function is_assoc($arr) {
    return array_keys($arr) !== range(0, count($arr) - 1);
}

function is_me() {
    return file_get_contents(base_path()."/ip.txt") == $_SERVER["REMOTE_ADDR"] || $_SERVER["REMOTE_ADDR"] == '127.0.0.1';
}

function yes_no($var, $value = null, $red = false) {
    $yes = '<span class="color-green">Yes</span>';
    $no = $red ? '<span class="color-red">No</span>' : 'No';

    if ( !$value ) {
        return $var ? $yes : $no;
    }

    return $var === $value ? $yes : $no;
}

function checked($var, $val) {
    if ( is_array($val) ) {
        return in_array($var, $val) ? 'checked' : '';
    }
    return ($var == $val ? 'checked' : '');
}

function selected($var, $val) {
    return ($var == $val ? 'selected="selected"' : '');
}

function get_controller_name() {
    $routeArray = app('request')->route()->getAction();
    $controllerAction = class_basename($routeArray['controller']);
    list($controllerName, $actionName) = explode('@', $controllerAction);
    return str_replace('_', '-', \Str::snake((str_replace('Controller', '', $controllerName))));
}

function ints_to_strings($arr) {
    foreach( $arr as &$var ) {
        $var = (string) $var;
    }

    return $arr;
}

function get_first_last_name($name) {
    $name = ucwords($name);
    $exp = explode(' ', $name);
    $last_name = array_pop($exp);
    $first_name = implode(' ', $exp);

    if ( !$first_name ) {
        $first_name = $last_name;
        $last_name = '';
    }

    return compact('first_name', 'last_name');
}

function log_db($message, $type = '') {
    \RA\Core\Models\Log::create(compact('message', 'type'));
}

function start_query_log() {
    \DB::enableQueryLog();
}

function get_query_log() {
    dmp(\DB::getQueryLog());
}

function get_blocks_data_html($folder, $blocks_data) {
    \RA\Core\Core\BuilderAdmin::initialize(str_replace('.', '/', $folder).'/partials/blocks');
    return \RA\Core\Core\BuilderAdmin::writeBlocks($blocks_data, true);
}

function to_words($str) {
    return ucwords(str_replace(['-', '_'], ' ', $str));
}

function decode_json($string) {
    if (gettype($string) == 'string') {
        return json_decode($string, true);
    }

    return $string;
}

function encode_json($array) {
    return json_encode($array);
}

function parse_window_value($window) {
    if (preg_match('/\:/', $window)) {
        $exp = explode(':', $window);

        return date('d M', strtotime($exp[0])) . ' - ' . date('d M', strtotime($exp[1]));
    }

    return ucwords(str_replace('_', ' ', $window));
}

function random_string($length = 8) {
    $string = "";
    $possible = "12346789ABCDEFGHJKLMNPQRTVWXYZ";
    $maxlength = strlen($possible);
    if ($length > $maxlength) {
        $length = $maxlength;
    }
    $i = 0;
    while ($i < $length) {
        $char = substr($possible, mt_rand(0, $maxlength - 1), 1);
        if (!strstr($string, $char)) {
            $string .= $char;
            $i++;
        }
    }
    return $string;
}

function delete_recursive($directory) {
    foreach(glob("{$directory}/*") as $file) {
        if ( is_dir($file) ) {
            delete_recursive($file);
        }
        else {
            @unlink($file);
        }
    }

    if ( !glob("{$directory}/*") ) {
        foreach( glob("{$directory}/.*") as $file ) {
            if ( $file == $directory.'/.' || $file == $directory.'/..' ) continue;

            @unlink($file);
        }
    }

    @rmdir($directory);
}

function copy_recursive($source, $dest) {
    if ( !file_exists($source) ) {
        return;
    }

    // Check for symlinks
    if ( is_link($source) ) {
        return symlink(readlink($source), $dest);
    }

    // Simple copy for a file
    if ( is_file($source) ) {
        return copy($source, $dest);
    }

    // Make destination directory
    if ( !is_dir($dest) ) {
        mkdir($dest);
    }

    // Loop through the folder
    $dir = dir($source);
    while ( false !== $entry = $dir->read() ) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        copy_recursive("$source/$entry", "$dest/$entry");
    }

    // Clean up
    $dir->close();
    return true;
}

function get_files_recursive(string $directory, array $allFiles = []) {
    $files = array_diff(scandir($directory), ['.', '..']);

    foreach ($files as $file) {
        $fullPath = $directory. DIRECTORY_SEPARATOR .$file;

        if( is_dir($fullPath) ) {
            $allFiles += get_files_recursive($fullPath, $allFiles);
        }
        else {
            $allFiles[] = $fullPath;
        }
    }

    return $allFiles;
}

function get_block_css_classes($data) {
    extract($data);

    if ( !isset($classes) ) {
        $classes = '';
    }

    $classes .= isset($width) ? ' w-'.$width : '';
    $classes .= isset($width_mobile) ? ' wm-'.$width_mobile : '';

    foreach ( config('settings.directions') as $direction ) {
        $classes .= isset(${'margin_'.$direction}) ? ' m'.$direction[0].'-'.${'margin_'.$direction} : '';
        $classes .= isset(${'padding_'.$direction}) ? ' p'.$direction[0].'-'.${'padding_'.$direction} : '';
        $classes .= isset(${'margin_mobile_'.$direction}) ? ' mm'.$direction[0].'-'.${'margin_mobile_'.$direction} : '';
        $classes .= isset(${'padding_mobile_'.$direction}) ? ' pm'.$direction[0].'-'.${'padding_mobile_'.$direction} : '';
    }

    return trim(preg_replace('/\s+/', ' ', $classes));

}