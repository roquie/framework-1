<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors\Templater\Behaviours;

use Spiral\Components\View\Compiler\Processors\TemplateProcessor;
use Spiral\Components\View\Compiler\Processors\Templater\BehaviourInterface;
use Spiral\Components\View\Compiler\Processors\Templater\ImporterInterface;
use Spiral\Components\View\Compiler\Processors\Templater\Node;
use Spiral\Support\Html\Tokenizer;

class IncludeBehaviour implements BehaviourInterface
{
    /**
     * Associated templater.
     *
     * @var TemplateProcessor
     */
    protected $templater = null;

    /**
     * View namespace to be imported.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * View to be imported.
     *
     * @var string
     */
    protected $view = '';

    /**
     * Import context includes everything between opening and closing tag.
     *
     * @var array
     */
    protected $context = [];

    /**
     * Context token.
     *
     * @var array
     */
    protected $token = [];

    /**
     * User able to define custom attributes while importing element, this attributes will be treated
     * as node blocks.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Include behaviour mount external view source and inner block under parent Node, it will
     * additionally keep import context (everything between opening and closing tag) and tag
     * attributes as node sblocks.
     *
     * @param TemplateProcessor $templater
     * @param string            $namespace
     * @param string            $view
     * @param array             $context
     * @param array             $token
     */
    public function __construct(
        TemplateProcessor $templater,
        $namespace,
        $view,
        array $context,
        array $token = []
    )
    {
        $this->templater = $templater;

        $this->namespace = $namespace;
        $this->view = $view;

        $this->context = $context;

        $this->token = $token;
        $this->attributes = $token[Tokenizer::TOKEN_ATTRIBUTES];
    }

    /**
     * Pack node context (everything between open and close tag).
     *
     * @return string
     */
    public function packContext()
    {
        $context = '';

        foreach ($this->context as $token)
        {
            $context .= $token[Tokenizer::TOKEN_CONTENT];
        }

        return $context;
    }

    /**
     * Create imported node to be included into parent container (node).
     *
     * @return Node
     */
    public function createNode()
    {
        //We have to extend included node first
        $node = new Node($this->templater, $this->templater->uniqueName());

        //Let's exclude node content
        $node->handleBehaviour(new ExtendsBehaviour(
            $include = $this->templater->createNode($this->namespace, $this->view, '', $this->token),
            []
        ));

        //Let's register user defined blocks (context and attributes) as plain text
        $node->registerBlock('context', [], [$this->packContext()]);
        foreach ($this->attributes as $attribute => $value)
        {
            $node->registerBlock($attribute, [], [$value]);
        }

        //We now have to compile node content to pass it's body to parent node
        $content = $node->compile($compiled, $outerBlocks);

        //Some blocks (usually user attributes) can be exported to template using non default
        //rendering technique, for example every "extra" attribute can be passed to specific
        //template location.
        $content = $this->templater->exportBlocks($content, $outerBlocks);

        //Some imports may define in-context imports (definitive imports), we can process them
        //using new combined templater
        $templater = clone $this->templater;

        /**
         * @var TemplateProcessor $includeTemplater
         * @var ImporterInterface $importer
         */
        $includeTemplater = $include->getSupervisor();

        foreach (array_reverse($includeTemplater->getImporters()) as $importer)
        {
            if ($importer->isDefinitive())
            {
                $templater->addImporter($importer);
            }
        }

        return new Node($templater, $templater->uniqueName(), $content);
    }
}