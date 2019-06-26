<?php

namespace Beanbun\Middleware;

use \FontLib\Font;

class Decode {

    public $auto = true;
    public $FontLib = null;
    public $glyfLists = [];
    public $unicodeLists = [];
    public $decodeLuan = '';
    public $decodeUnicode = '';
    public $base64 = '';

    public function __construct($config = []) {

        if (isset($config['auto'])) {
            $this->auto = boolval($config['auto']);
        }
    }

    public function handle($beanbun) {
        $beanbun->decode = $this;
        if ($this->auto) {
            $beanbun->afterDownloadPageHooks[] = [$this, 'parseDecode'];
            $beanbun->afterDiscoverHooks[] = [$this, 'cleanDecodeRes'];
        }
    }

    public function parseDecode($beanbun) {

        $pattern = "/;src:url\((.*?)'\)/";
        preg_match_all($pattern, $beanbun->page, $matches);
        $this->base64 = !empty($matches[1][0]) ? $matches[1][0] : '';
        if ($this->base64) {
            $this->parseBase64($beanbun);
        }
    }

    public function cleanDecodeRes($beanbun) {
        $beanbun->unicodeLists = [];
        $this->FontLib = null;
        $this->glyfLists = [];
        $this->unicodeLists = [];
        $this->decodeLuan = '';
        $this->decodeUnicode = '';
        $this->base64 = '';
    }

    /**
     * @param string string 龒室驋厅驋卫龥龥㎡
     * */
    public function deCodeByLuan($string = '') {
        return $this->decodeLuan = ($string && is_string($string)) ? $this->_getDeCodeByLuanMany($string) : $string;
    }

    /**
     * @param string string &#x9a4b;&#x9476;&#x9fa4;&#x9fa4;哈哈
     * */
    public function deCodeByUnicode($string = '') {
        return $this->decodeUnicode = ($string && is_string($string)) ? $this->_getDeCodeByUnicodeMany($string) : $string;
    }

    public function parseBase64($beanbun) {
        $file = $this->_base64_to_blob($this->base64);
        $file_name = __DIR__ . '/../font/temp.ttf';
        file_put_contents($file_name, $file['blob']);
        $this->FontLib = Font::load($file_name);
        $this->FontLib->parse();
        $this->FontLib->getFontName() . '<br>';
        $this->glyfLists = $this->FontLib->getUnicodeCharMap();
        $this->unicodeLists = $this->_parseUnicodeList($this->glyfLists);
        $beanbun->unicodeLists = $this->unicodeLists;
        return $this;
    }

    private function _getDeCodeByUnicodeMany($unicodes = '') {
        if (!$unicodes || !is_string($unicodes)) {
            return $unicodes;
        }
        $unicodesArray = array_filter(explode(';', $unicodes));
        $res = '';
        foreach ($unicodesArray as $key => $value) {
            $one = $this->_getDeCodeByUnicodeOne($value . ';', $this->unicodeLists);
            $res .= isset($one) ? $one : $value;
        }

        return $res;
    }

    private function _getDeCodeByUnicodeOne($unicode = '') {
        if (!$unicode || !is_string($unicode)) {
            return $unicode;
        }
        $res = $unicode;
        foreach ($this->unicodeLists as $key => $val) {
            if ($unicode == $val['unicode']) {
                $res = $val['char'];
            }
        }
        return $res;
    }

    private function _getDeCodeByLuanMany($strings = '') {
        if (!$strings || !is_string($strings)) {
            return $strings;
        }
        $stringArrays = $this->_mb_str_split($strings);
        if (!$stringArrays || !is_array($stringArrays)) {
            return $strings;
        }
        $stringRes = '';
        foreach ($stringArrays as $key => $value) {
            $stringRes .= !empty($this->glyfLists[$this->_getDeCodeByLuanOne($value)]) ? $this->glyfLists[$this->_getDeCodeByLuanOne($value)] - 1 : $value;
        }
        return $stringRes;
    }

    private function _getDeCodeByLuanOne($string, $flag = false) {
        if (!$string || !is_string($string)) {
            return $string;
        }
        $unicodeStr = '';
        preg_match_all('/./u', $string, $matches);
        if (!empty($matches[0])) {
            foreach ($matches[0] as $m) {
                $unicodeStr .= ($flag === true ? "&#" : "") . base_convert(bin2hex(iconv('UTF-8', "UCS-4", $m)), 16, 10);
            }
        }
        return $unicodeStr;
    }

    private function _parseUnicodeList($glyf) {
        $this->unicodeLists = [];
        if ($this->glyfLists) {
            foreach ($this->glyfLists as $key => $value) {
                $this->unicodeLists[$key]['char'] = $value - 1;
                $this->unicodeLists[$key]['unicode'] = '&#x' . dechex($key) . ';';
            }
        }
        return $this->unicodeLists;
    }

    private function _base64_to_blob($base64Str) {
        if ($index = strpos($base64Str, 'base64,', 0)) {
            $blobStr = substr($base64Str, $index + 7);
            $typestr = substr($base64Str, 0, $index);
            preg_match("/^data:(.*);$/", $typestr, $arr);
            return ['blob' => base64_decode($blobStr), 'type' => !empty($arr[1])?$arr[1]:'ttf'];
        }
        return false;
    }

    //字符串转数组
    private function _mb_str_split($str, $count = 1) {
        if (!$str || !is_string($str)) {
            return $str;
        }
        $leng = strlen($str) / 3;     //中文长度
        $arr = array();
        for ($i = 0; $i < $leng; $i += $count) {
            $arr[] = mb_substr($str, $i, $count);
        }
        return $arr;
    }

}
