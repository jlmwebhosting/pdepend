<?php
/**
 * This file is part of PDepend.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2013, Manuel Pichler <mapi@pdepend.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright 2008-2013 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since     0.10.0
 */

namespace PDepend\Util\Configuration;

use PDepend\Util\Workarounds;

/**
 * Default implementation of a PDepend configuration parser.
 *
 * This class provides the default implementation of the configuration parser
 * used to handle PDepend configuration files. This parses provides an
 * adaptive parser that takes the default configuration tree as a constructor
 * argument and then overwrites the default settings during each call to the
 * <em>parse()</em> method with the newly specified values. This solutions
 * allows us the established concept of a default configuration file named
 * <em>pdepend.xml.dist</em> and a local customization <em>pdepend.xml</em>.
 *
 * @copyright 2008-2013 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 0.10.0
 */
class ConfigurationParser
{
    /**
     * @var \PDepend\Util\Workarounds
     */
    protected $workarounds;

    /**
     * The default configuration settings.
     *
     * @var stdClass
     */
    protected $settings = null;

    /**
     * Root element of the currently parsed configuration file.
     *
     * @var \SimpleXMLElement
     */
    protected $sxml = null;

    /**
     * Constructs a new parser instance that uses the given settings as a default
     * configuration.
     *
     * @param \PDepend\Util\Workarounds $workarounds
     * @param \stdClass $settings
     */
    public function __construct(Workarounds $workarounds, \stdClass $settings)
    {
        $this->workarounds = $workarounds;
        $this->settings = $settings;
    }

    /**
     * Parses the given <b>$file</b> and overwrites all those default values that
     * are specified in the given configuration file. The return value of this
     * method represents the updated PDepend configuration values.
     *
     * @param string $file
     * @return \stdClass
     */
    public function parse($file)
    {
        $this->sxml = new \SimpleXMLElement($file, null, true);

        $this->parseCache();
        $this->parseParser();
        $this->parseImageConvert();

        return $this->settings;
    }

    /**
     * This method parses the cache related configuration settings.
     *
     * @return void
     */
    protected function parseCache()
    {
        if ($this->workarounds->hasSerializeReferenceIssue()) {
            $this->settings->cache->driver = 'memory';
            return;
        }

        if (isset($this->sxml->cache->driver)) {
            $this->settings->cache->driver = (string) $this->sxml->cache->driver;
        }
        if (isset($this->sxml->cache->location)) {
            $this->settings->cache->location = (string) $this->sxml->cache->location;
        }
    }

    /**
     * This method parses the imagick related configuration settings.
     *
     * @return void
     */
    protected function parseImageConvert()
    {
        if (isset($this->sxml->imageConvert->fontFamily)) {
            $this->settings->imageConvert->fontFamily
                = (string) $this->sxml->imageConvert->fontFamily;
        }
        if (isset($this->sxml->imageConvert->fontSize)) {
            $this->settings->imageConvert->fontSize
                = (float) $this->sxml->imageConvert->fontSize;
        }
    }

    /**
     * This method parses the parser related configuration settings.
     *
     * @return void
     * @since 1.0.1
     */
    protected function parseParser()
    {
        if (isset($this->sxml->parser->nesting)) {
            $this->settings->parser->nesting = (int) $this->sxml->parser->nesting;
        }
    }
}
