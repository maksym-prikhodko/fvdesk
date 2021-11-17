<?php
namespace phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Type\Collection;
class ReturnTag extends Tag
{
    protected $type = '';
    protected $types = null;
    public function getContent()
    {
        if (null === $this->content) {
            $this->content = "{$this->type} {$this->description}";
        }
        return $this->content;
    }
    public function setContent($content)
    {
        parent::setContent($content);
        $parts = preg_split('/\s+/Su', $this->description, 2);
        $this->type = $parts[0];
        $this->types = null;
        $this->setDescription(isset($parts[1]) ? $parts[1] : '');
        $this->content = $content;
        return $this;
    }
    public function getTypes()
    {
        return $this->getTypesCollection()->getArrayCopy();
    }
    public function getType()
    {
        return (string) $this->getTypesCollection();
    }
    protected function getTypesCollection()
    {
        if (null === $this->types) {
            $this->types = new Collection(
                array($this->type),
                $this->docblock ? $this->docblock->getContext() : null
            );
        }
        return $this->types;
    }
}
