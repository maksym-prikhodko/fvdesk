<?php
namespace Symfony\Component\VarDumper\Cloner;
class VarCloner extends AbstractCloner
{
    private static $hashMask = 0;
    private static $hashOffset = 0;
    protected function doClone($var)
    {
        $useExt = $this->useExt;
        $i = 0;                         
        $len = 1;                       
        $pos = 0;                       
        $refs = 0;                      
        $queue = array(array($var));    
        $arrayRefs = array();           
        $hardRefs = array();            
        $objRefs = array();             
        $resRefs = array();             
        $values = array();              
        $maxItems = $this->maxItems;
        $maxString = $this->maxString;
        $cookie = (object) array();     
        $gid = uniqid(mt_rand(), true); 
        $a = null;                      
        $stub = null;                   
        $zval = array(                  
            'type' => null,
            'zval_isref' => null,
            'zval_hash' => null,
            'array_count' => null,
            'object_class' => null,
            'object_handle' => null,
            'resource_type' => null,
        );
        if (!self::$hashMask) {
            self::initHashMask();
        }
        $hashMask = self::$hashMask;
        $hashOffset = self::$hashOffset;
        for ($i = 0; $i < $len; ++$i) {
            $indexed = true;            
            $j = -1;                    
            $step = $queue[$i];         
            foreach ($step as $k => $v) {
                if ($indexed && $k !== ++$j) {
                    $indexed = false;
                }
                if ($useExt) {
                    $zval = symfony_zval_info($k, $step);
                } else {
                    $step[$k] = $cookie;
                    if ($zval['zval_isref'] = $queue[$i][$k] === $cookie) {
                        $zval['zval_hash'] = $v instanceof Stub ? spl_object_hash($v) : null;
                    }
                    $zval['type'] = gettype($v);
                }
                if ($zval['zval_isref']) {
                    $queue[$i][$k] =& $stub;    
                    unset($stub);               
                    if (isset($hardRefs[$zval['zval_hash']])) {
                        $queue[$i][$k] = $useExt ? ($v = $hardRefs[$zval['zval_hash']]) : ($step[$k] = $v);
                        if ($v->value instanceof Stub && (Stub::TYPE_OBJECT === $v->value->type || Stub::TYPE_RESOURCE === $v->value->type)) {
                            ++$v->value->refCount;
                        }
                        ++$v->refCount;
                        continue;
                    }
                }
                switch ($zval['type']) {
                    case 'string':
                        if (isset($v[0]) && !preg_match('
                            $stub = new Stub();
                            $stub->type = Stub::TYPE_STRING;
                            $stub->class = Stub::STRING_BINARY;
                            if (0 <= $maxString && 0 < $cut = strlen($v) - $maxString) {
                                $stub->cut = $cut;
                                $stub->value = substr($v, 0, -$cut);
                            } else {
                                $stub->value = $v;
                            }
                        } elseif (0 <= $maxString && isset($v[1 + ($maxString >> 2)]) && 0 < $cut = iconv_strlen($v, 'UTF-8') - $maxString) {
                            $stub = new Stub();
                            $stub->type = Stub::TYPE_STRING;
                            $stub->class = Stub::STRING_UTF8;
                            $stub->cut = $cut;
                            $stub->value = iconv_substr($v, 0, $maxString, 'UTF-8');
                        }
                        break;
                    case 'integer':
                        break;
                    case 'array':
                        if ($v) {
                            $stub = $arrayRefs[$len] = new Stub();
                            $stub->type = Stub::TYPE_ARRAY;
                            $stub->class = Stub::ARRAY_ASSOC;
                            $stub->value = $zval['array_count'] ?: count($v);
                            $a = $v;
                            $a[$gid] = true;
                            if (isset($v[$gid])) {
                                unset($v[$gid]);
                                $a = array();
                                foreach ($v as $gk => &$gv) {
                                    $a[$gk] =& $gv;
                                }
                            } else {
                                $a = $v;
                            }
                        }
                        break;
                    case 'object':
                        if (empty($objRefs[$h = $zval['object_handle'] ?: ($hashMask ^ hexdec(substr(spl_object_hash($v), $hashOffset, PHP_INT_SIZE)))])) {
                            $stub = new Stub();
                            $stub->type = Stub::TYPE_OBJECT;
                            $stub->class = $zval['object_class'] ?: get_class($v);
                            $stub->value = $v;
                            $stub->handle = $h;
                            $a = $this->castObject($stub, 0 < $i);
                            if ($v !== $stub->value) {
                                if (Stub::TYPE_OBJECT !== $stub->type) {
                                    break;
                                }
                                if ($useExt) {
                                    $zval['type'] = $stub->value;
                                    $zval = symfony_zval_info('type', $zval);
                                    $h = $zval['object_handle'];
                                } else {
                                    $h = $hashMask ^ hexdec(substr(spl_object_hash($stub->value), $hashOffset, PHP_INT_SIZE));
                                }
                                $stub->handle = $h;
                            }
                            $stub->value = null;
                            if (0 <= $maxItems && $maxItems <= $pos) {
                                $stub->cut = count($a);
                                $a = null;
                            }
                        }
                        if (empty($objRefs[$h])) {
                            $objRefs[$h] = $stub;
                        } else {
                            $stub = $objRefs[$h];
                            ++$stub->refCount;
                            $a = null;
                        }
                        break;
                    case 'resource':
                    case 'unknown type':
                        if (empty($resRefs[$h = (int) $v])) {
                            $stub = new Stub();
                            $stub->type = Stub::TYPE_RESOURCE;
                            $stub->class = $zval['resource_type'] ?: get_resource_type($v);
                            $stub->value = $v;
                            $stub->handle = $h;
                            $a = $this->castResource($stub, 0 < $i);
                            $stub->value = null;
                            if (0 <= $maxItems && $maxItems <= $pos) {
                                $stub->cut = count($a);
                                $a = null;
                            }
                        }
                        if (empty($resRefs[$h])) {
                            $resRefs[$h] = $stub;
                        } else {
                            $stub = $resRefs[$h];
                            ++$stub->refCount;
                            $a = null;
                        }
                        break;
                }
                if (isset($stub)) {
                    if ($zval['zval_isref']) {
                        if ($useExt) {
                            $queue[$i][$k] = $hardRefs[$zval['zval_hash']] = $v = new Stub();
                            $v->value = $stub;
                        } else {
                            $step[$k] = new Stub();
                            $step[$k]->value = $stub;
                            $h = spl_object_hash($step[$k]);
                            $queue[$i][$k] = $hardRefs[$h] =& $step[$k];
                            $values[$h] = $v;
                        }
                        $queue[$i][$k]->handle = ++$refs;
                    } else {
                        $queue[$i][$k] = $stub;
                    }
                    if ($a) {
                        if ($i && 0 <= $maxItems) {
                            $k = count($a);
                            if ($pos < $maxItems) {
                                if ($maxItems < $pos += $k) {
                                    $a = array_slice($a, 0, $maxItems - $pos);
                                    if ($stub->cut >= 0) {
                                        $stub->cut += $pos - $maxItems;
                                    }
                                }
                            } else {
                                if ($stub->cut >= 0) {
                                    $stub->cut += $k;
                                }
                                $stub = $a = null;
                                unset($arrayRefs[$len]);
                                continue;
                            }
                        }
                        $queue[$len] = $a;
                        $stub->position = $len++;
                    }
                    $stub = $a = null;
                } elseif ($zval['zval_isref']) {
                    if ($useExt) {
                        $queue[$i][$k] = $hardRefs[$zval['zval_hash']] = new Stub();
                        $queue[$i][$k]->value = $v;
                    } else {
                        $step[$k] = $queue[$i][$k] = new Stub();
                        $step[$k]->value = $v;
                        $h = spl_object_hash($step[$k]);
                        $hardRefs[$h] =& $step[$k];
                        $values[$h] = $v;
                    }
                    $queue[$i][$k]->handle = ++$refs;
                }
            }
            if (isset($arrayRefs[$i])) {
                if ($indexed) {
                    $arrayRefs[$i]->class = Stub::ARRAY_INDEXED;
                }
                unset($arrayRefs[$i]);
            }
        }
        foreach ($values as $h => $v) {
            $hardRefs[$h] = $v;
        }
        return $queue;
    }
    private static function initHashMask()
    {
        $obj = (object) array();
        self::$hashOffset = 16 - PHP_INT_SIZE;
        self::$hashMask = -1;
        if (defined('HHVM_VERSION')) {
            self::$hashOffset += 16;
        } else {
            $obFuncs = array('ob_clean', 'ob_end_clean', 'ob_flush', 'ob_end_flush', 'ob_get_contents', 'ob_get_flush');
            foreach (debug_backtrace(PHP_VERSION_ID >= 50400 ? DEBUG_BACKTRACE_IGNORE_ARGS : false) as $frame) {
                if (isset($frame['function'][0]) && !isset($frame['class']) && 'o' === $frame['function'][0] && in_array($frame['function'], $obFuncs)) {
                    $frame['line'] = 0;
                    break;
                }
            }
            if (!empty($frame['line'])) {
                ob_start();
                debug_zval_dump($obj);
                self::$hashMask = substr(ob_get_clean(), 17);
            }
        }
        self::$hashMask ^= hexdec(substr(spl_object_hash($obj), self::$hashOffset, PHP_INT_SIZE));
    }
}
