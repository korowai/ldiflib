<?php

/*
 * This file is part of Korowai framework.
 *
 * (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * Distributed under MIT license.
 */

declare(strict_types=1);

namespace Korowai\Lib\Ldif;

use Korowai\Lib\Ldif\ParserStateInterface as State;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;

/**
 * LDIF parser.
 */
class Parser implements ParserInterface
{
//    /**
//     * @var array
//     */
//    protected $config;
//
//    /**
//     * Returns the name of the records parsing method for the given
//     * *$fileType*. Throws an exception for unsupported file type.
//     *
//     * Supported file types are:
//     *
//     * - ``'content'``,
//     * - ``'changes'``,
//     * - ``'mixed'``, and
//     * - ``'detect'``.
//     *
//     * @param  string $fileType
//     *
//     * @return string
//     * @throws RuntimeException
//     */
//    protected function getRecordParserMethod(string $fileType) : string
//    {
//        $methods = [
//            'content' => 'parseLdifAttrValRecord',
//            'changes' => 'parseLdifChangeRecord',
//            'mixed'   => 'parseMixedRecord',
//            'detect'  => 'parseDetectRecord',
//        ];
//        if (($method = $methods[$fileType] ?? null) === null) {
//            throw new \RuntimeException('internal error: invalid file type: "'.$type.'"');
//        }
//        return $method;
//    }
//
//    /**
//     * Initializes the parser
//     *
//     * @param  array $config
//     */
//    public function __construct(array $config = [])
//    {
//        $this->configure($config);
//    }
//
//    /**
//     * Configure the parser with configuration options.
//     *
//     * @param  array $config
//     *
//     * @return object $this
//     */
//    public function configure(array $config)
//    {
//        $this->config = $this->resolveConfig($config);
//        return $this;
//    }
//
//    /**
//     * Return configuration array previously set with configure().
//     *
//     * If configuration is not set yet, null is returned.
//     *
//     * @return array|null
//     */
//    public function getConfig() : ?array
//    {
//        return $this->config;
//    }
//
    /**
     * {@inheritdoc}
     */
    public function parse(State $state) : bool
    {
//        $prevErrCount = count($state->getErrors());
//
//        // skip leading empty lines
//        if ($this->skipEmptyLines($state) === 0 && (count($state->getErrors()) > $prevErrCount)) {
//            return false;
//        }
//
//        // version-spec (may be optional or required, depending on parser's config option)
//        $tryOnly = !(($this->getConfig())['version_required'] ?? true);
//        $success = $this->parseVersionSpec($state, $tryOnly);
//
//        if ((!$success && (count($state->getErrors()) > $prevErrCount)) ||
//            ($success && ($this->skipEmptyLines($state) === 0)) ||
//            (!$this->parseRecords($state))
//        ) {
//            return false;
//        }
//
//        if ($state->getCursor()->isValid()) {
//            $state->errorHere("syntax error: parsing finished before end of file");
//            return false;
//        }

        return true;
    }
//
//    /**
//     * @todo Write documentation
//     */
//    public function parseRecords(State $state) : bool
//    {
//        $fileType = ($this->getConfig())['file_type'];
//        $method = $this->getRecordParserMethod($fileType);
//
//        //
//        // ldif-foo-record *(1*SEP ldif-foo-record)
//        //
//        if (!call_user_func([$this, $method], $state)) {
//            return false;
//        }
//
//        while ($this->skipEmptyLines($state) > 0) {
//            if ($state->getCursor()->isValid() && !call_user_func([$this, $method], $state)) {
//                return false;
//            }
//        }
//
//        return true;
//    }
//
//    /**
//     * Parse line separators.
//     *
//     * @param  State $state
//     * @param  int $max Maximum number of repetitions.
//     *
//     * @return int the number of empty lines skipped
//     */
//    public function skipEmptyLines(State $state, int $max = PHP_INT_MAX) : int
//    {
//        $sep = $this->sepRule(true);
//        for ($i = 0; $i < $max && $sep->parse($state); ++$i) {
//        }
//        return $i;
//    }
//
//    /**
//     * @todo Write documentation
//     */
//    public function parseVersionSpec(State $state, bool $tryOnly = false) : bool
//    {
//        $cursor = $state->getCursor();
//        $begin = $cursor->getClonedLocation();
//        if (!$this->versionSpecRule($tryOnly)->parse($state, $version)) {
//            return false;
//        }
//        $snippet = new Snippet($begin, $cursor->getOffset() - $begin->getOffset());
//        $versionSpec = new VersionSpec($snippet, $version);
//        $state->setVersionSpec($versionSpec);
//        return true;
//    }
//
//    /**
//     * @todo Write documentation
//     */
//    public function parseLdifAttrValRecord(State $state, bool $tryOnly = false) : bool
//    {
//        $cursor = $state->getCursor();
//        $begin = $cursor->getClonedLocation();
//
//        //
//        // dn-spec SEP 1*attrval-spec
//        //
//        if (!$this->dnSpecRule($tryOnly)->parse($state, $dn) ||
//            !$this->sepRule()->parse($state) ||
//            !$this->attrValSpecRule()->parse($state, $attrValSpec)) {
//            return false;
//        }
//
//        $attrValSpecs[] = $attrValSpec;
//
//        while ($this->attrValSpecRule(true)->parse($state, $attrValSpec)) {
//            $attrValSpecs[] = $attrValSpec;
//        }
//
//        $snippet = new Snippet($begin, $cursor->getOffset() - $begin->getOffset());
//        $record = new AttrValRecord($dn, $attrValSpecs, compact('snippet'));
//        $state->appendRecord($record);
//
//        return true;
//    }
//
//    /**
//     * @todo Write documentation
//     */
//    public function parseLdifChangeRecord(State $state, bool $tryOnly = false) : bool
//    {
//        // FIXME: remove this line when implemented
//        throw new \BadMethodCallException('not implemented');
//
//        $cursor = $state->getCursor();
//        $begin = $cursor->getClonedLocation();
//
//        //
//        // dn-spec SEP *control changerecord
//        //
//        if (!$this->dnSpecRule($tryOnly)->parse($state, $dn) ||
//            !$this->sepRule()->parse($state)) {
//            return false;
//        }
//
//        $controls = [];
//        $prevErrCount = count($state->getErrors());
//        if ($this->controlRule(true)->parse($state, $control)) {
//            $controls[] = $control;
//            while ($this->controlRule(true)->parse($state, $control)) {
//                $controls[] = $control;
//            }
//        }
//        if (count($state->getErrors()) > $prevErrCount) {
//            return false;
//        }
//
//        return $this->parseChangeRecord($state);
//    }
//
//    /**
//     * @todo Write documentation
//     */
//    public function parseChangeRecord(State $state, bool $tryOnly = false) : bool
//    {
//    }
//
//    /**
//     * @todo Write documentation
//     */
//    public function parseMixedRecord(State $state) : bool
//    {
//    }
//
//    /**
//     * @todo Write documentation
//     */
//    public function parseDetectRecord(State $state) : bool
//    {
//    }
//
//    /**
//     * Validate and resolve configuration options for the Parser.
//     *
//     * @param  array $config Input config options.
//     *
//     * @return array returns the array of resolved config options.
//     */
//    protected function resolveConfig(array $config) : array
//    {
//        $resolver = new OptionsResolver;
//        $this->configureOptionsResolver($resolver);
//        return $resolver->resolve($config);
//    }
//
//    /**
//     * Configures OptionsResolver for this Parser
//     *
//     * @param OptionsResolver $resolver The resolver to be configured
//     */
//    protected function configureOptionsResolver(OptionsResolver $resolver)
//    {
//        $resolver->setDefaults([
//            'file_type' => 'content',
//            'version_required' => false,
//        ]);
//
//        $resolver->setAllowedValues('file_type', ['content', 'changes', 'mixed', 'detect']);
//        $resolver->setAllowedTypes('version_required', 'bool');
//    }
}

// vim: syntax=php sw=4 ts=4 et:
