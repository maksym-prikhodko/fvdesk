<?php
namespace phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock;
class Tag implements \Reflector
{
    const REGEX_TAGNAME = '[\w\-\_\\\\]+';
    protected $tag = '';
    protected $content = '';
    protected $description = '';
    protected $parsedDescription = null;
    protected $location = null;
    protected $docblock = null;
    private static $tagHandlerMappings = array(
        'author'
            => '\phpDocumentor\Reflection\DocBlock\Tag\AuthorTag',
        'covers'
            => '\phpDocumentor\Reflection\DocBlock\Tag\CoversTag',
        'deprecated'
            => '\phpDocumentor\Reflection\DocBlock\Tag\DeprecatedTag',
        'example'
            => '\phpDocumentor\Reflection\DocBlock\Tag\ExampleTag',
        'link'
            => '\phpDocumentor\Reflection\DocBlock\Tag\LinkTag',
        'method'
            => '\phpDocumentor\Reflection\DocBlock\Tag\MethodTag',
        'param'
            => '\phpDocumentor\Reflection\DocBlock\Tag\ParamTag',
        'property-read'
            => '\phpDocumentor\Reflection\DocBlock\Tag\PropertyReadTag',
        'property'
            => '\phpDocumentor\Reflection\DocBlock\Tag\PropertyTag',
        'property-write'
            => '\phpDocumentor\Reflection\DocBlock\Tag\PropertyWriteTag',
        'return'
            => '\phpDocumentor\Reflection\DocBlock\Tag\ReturnTag',
        'see'
            => '\phpDocumentor\Reflection\DocBlock\Tag\SeeTag',
        'since'
            => '\phpDocumentor\Reflection\DocBlock\Tag\SinceTag',
        'source'
            => '\phpDocumentor\Reflection\DocBlock\Tag\SourceTag',
        'throw'
            => '\phpDocumentor\Reflection\DocBlock\Tag\ThrowsTag',
        'throws'
            => '\phpDocumentor\Reflection\DocBlock\Tag\ThrowsTag',
        'uses'
            => '\phpDocumentor\Reflection\DocBlock\Tag\UsesTag',
        'var'
            => '\phpDocumentor\Reflection\DocBlock\Tag\VarTag',
        'version'
            => '\phpDocumentor\Reflection\DocBlock\Tag\VersionTag'
    );
    final public static function createInstance(
        $tag_line,
        DocBlock $docblock = null,
        Location $location = null
    ) {
        if (!preg_match(
            '/^@(' . self::REGEX_TAGNAME . ')(?:\s*([^\s].*)|$)?/us',
            $tag_line,
            $matches
        )) {
            throw new \InvalidArgumentException(
                'Invalid tag_line detected: ' . $tag_line
            );
        }
        $handler = __CLASS__;
        if (isset(self::$tagHandlerMappings[$matches[1]])) {
            $handler = self::$tagHandlerMappings[$matches[1]];
        } elseif (isset($docblock)) {
            $tagName = (string)new Type\Collection(
                array($matches[1]),
                $docblock->getContext()
            );
            if (isset(self::$tagHandlerMappings[$tagName])) {
                $handler = self::$tagHandlerMappings[$tagName];
            }
        }
        return new $handler(
            $matches[1],
            isset($matches[2]) ? $matches[2] : '',
            $docblock,
            $location
        );
    }
    final public static function registerTagHandler($tag, $handler)
    {
        $tag = trim((string)$tag);
        if (null === $handler) {
            unset(self::$tagHandlerMappings[$tag]);
            return true;
        }
        if ('' !== $tag
            && class_exists($handler, true)
            && is_subclass_of($handler, __CLASS__)
            && !strpos($tag, '\\') 
        ) {
            self::$tagHandlerMappings[$tag] = $handler;
            return true;
        }
        return false;
    }
    public function __construct(
        $name,
        $content,
        DocBlock $docblock = null,
        Location $location = null
    ) {
        $this
            ->setName($name)
            ->setContent($content)
            ->setDocBlock($docblock)
            ->setLocation($location);
    }
    public function getName()
    {
        return $this->tag;
    }
    public function setName($name)
    {
        if (!preg_match('/^' . self::REGEX_TAGNAME . '$/u', $name)) {
            throw new \InvalidArgumentException(
                'Invalid tag name supplied: ' . $name
            );
        }
        $this->tag = $name;
        return $this;
    }
    public function getContent()
    {
        if (null === $this->content) {
            $this->content = $this->description;
        }
        return $this->content;
    }
    public function setContent($content)
    {
        $this->setDescription($content);
        $this->content = $content;
        return $this;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function setDescription($description)
    {
        $this->content = null;
        $this->parsedDescription = null;
        $this->description = trim($description);
        return $this;
    }
    public function getParsedDescription()
    {
        if (null === $this->parsedDescription) {
            $description = new Description($this->description, $this->docblock);
            $this->parsedDescription = $description->getParsedContents();
        }
        return $this->parsedDescription;
    }
    public function getDocBlock()
    {
        return $this->docblock;
    }
    public function setDocBlock(DocBlock $docblock = null)
    {
        $this->docblock = $docblock;
        return $this;
    }
    public function getLocation()
    {
        return $this->location;
    }
    public function setLocation(Location $location = null)
    {
        $this->location = $location;
        return $this;
    }
    public static function export()
    {
        throw new \Exception('Not yet implemented');
    }
    public function __toString()
    {
        return "@{$this->getName()} {$this->getContent()}";
    }
}
