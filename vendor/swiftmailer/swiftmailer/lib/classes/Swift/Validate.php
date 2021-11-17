<?php
class Swift_Validate
{
    private static $grammar = null;
    public static function email($email)
    {
        if (self::$grammar === null) {
            self::$grammar = Swift_DependencyContainer::getInstance()
                ->lookup('mime.grammar');
        }
        return (bool) preg_match(
                '/^'.self::$grammar->getDefinition('addr-spec').'$/D',
                $email
            );
    }
}
