<?php
namespace PhpSpec\Runner\Maintainer;
use PhpSpec\Loader\Node\ExampleNode;
use PhpSpec\Matcher\MatcherInterface;
use PhpSpec\SpecificationInterface;
use PhpSpec\Runner\MatcherManager;
use PhpSpec\Runner\CollaboratorManager;
use PhpSpec\Formatter\Presenter\PresenterInterface;
use PhpSpec\Wrapper\Unwrapper;
use PhpSpec\Matcher;
class MatchersMaintainer implements MaintainerInterface
{
    private $presenter;
    private $unwrapper;
    private $defaultMatchers = array();
    public function __construct(PresenterInterface $presenter, array $matchers)
    {
        $this->presenter = $presenter;
        $this->defaultMatchers = $matchers;
        @usort($this->defaultMatchers, function ($matcher1, $matcher2) {
            return $matcher2->getPriority() - $matcher1->getPriority();
        });
    }
    public function supports(ExampleNode $example)
    {
        return true;
    }
    public function prepare(
        ExampleNode $example,
        SpecificationInterface $context,
        MatcherManager $matchers,
        CollaboratorManager $collaborators
    ) {
        $matchers->replace($this->defaultMatchers);
        if (!$context instanceof Matcher\MatchersProviderInterface) {
            return;
        }
        foreach ($context->getMatchers() as $name => $matcher) {
            if ($matcher instanceof Matcher\MatcherInterface) {
                $matchers->add($matcher);
            } else {
                $matchers->add(new Matcher\CallbackMatcher(
                    $name,
                    $matcher,
                    $this->presenter
                ));
            }
        }
    }
    public function teardown(
        ExampleNode $example,
        SpecificationInterface $context,
        MatcherManager $matchers,
        CollaboratorManager $collaborators
    ) {
    }
    public function getPriority()
    {
        return 50;
    }
}
